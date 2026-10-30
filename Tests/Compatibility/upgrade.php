<?php

require dirname(__DIR__).'/bootstrap.php';

use Composer\InstalledVersions;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Assert;
use Shaygan\AffiliateBundle\Entity\Purchase;
use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Entity\ReferrerUrl;
use Shaygan\AffiliateBundle\Tests\Fixtures\Kernel;
use Shaygan\AffiliateBundle\Tests\Fixtures\User;

// Both stages deliberately use DBAL 3; only the ORM changes between processes.
function schemaContract(Schema $schema): array
{
    $contract = [];
    foreach ($schema->getTables() as $table) {
        $columns = [];
        foreach ($table->getColumns() as $column) {
            $columns[$column->getName()] = [
                'type' => $column->getType()->getName(), 'length' => $column->getLength(),
                'notnull' => $column->getNotnull(), 'default' => $column->getDefault(),
                'autoincrement' => $column->getAutoincrement(), 'unsigned' => $column->getUnsigned(),
                'precision' => $column->getPrecision(), 'scale' => $column->getScale(),
            ];
        }
        ksort($columns);
        $indexes = [];
        foreach ($table->getIndexes() as $index) {
            $indexes[] = [$index->getColumns(), $index->isPrimary(), $index->isUnique()];
        }
        sort($indexes);
        $foreignKeys = [];
        foreach ($table->getForeignKeys() as $foreignKey) {
            $foreignKeys[] = [$foreignKey->getLocalColumns(), $foreignKey->getForeignTableName(), $foreignKey->getForeignColumns()];
        }
        sort($foreignKeys);
        $options = array_intersect_key($table->getOptions(), array_flip(['charset', 'collate', 'collation']));
        ksort($options);
        $contract[$table->getName()] = compact('columns', 'indexes', 'foreignKeys', 'options');
    }
    ksort($contract);
    return $contract;
}

$stage = $argv[1] ?? '';
$manifestPath = $argv[2] ?? '';
if (!in_array($stage, ['seed', 'verify'], true) || $manifestPath === '') {
    throw new InvalidArgumentException('Usage: php Tests/Compatibility/upgrade.php seed|verify manifest.json');
}
if (!str_starts_with(getenv('DATABASE_URL') ?: '', 'mysql://')) {
    throw new RuntimeException('Set DATABASE_URL to a disposable MySQL test database. The seed stage replaces affiliate tables.');
}
$version = InstalledVersions::getVersion('doctrine/orm');
Assert::assertStringStartsWith($stage === 'seed' ? '2.' : '3.', $version, 'Run seed with ORM 2 and verify with ORM 3.');
$kernel = new Kernel('upgrade_'.$stage, true);
$kernel->boot();
$em = $kernel->getContainer()->get('test.service_container')->get('doctrine.orm.entity_manager');
$metadata = $em->getMetadataFactory()->getAllMetadata();
$tool = new SchemaTool($em);
$manager = $em->getConnection()->createSchemaManager();

try {
    if ($stage === 'seed') {
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
        $referrer = (new Referrer())->setId(201);
        $url = (new ReferrerUrl())->setUrl('https://example.com/orm-upgrade');
        $referral = (new Referral())->setReferrer($referrer)->setReferrerUrl($url)->setIp('192.0.2.15')->setHitId(123456789);
        $registration = (new ReferralRegistration())->setReferrer($referrer)->setReferral($referral)->setUserId(301);
        $referral->setRegistration($registration);
        $entities = [$referrer, $url, $referral, $registration];
        foreach ([['percentage', 1000, 150, 15.0, 501], ['fixed-amount', 2000, 25, 25.0, 502]] as [$type, $amount, $commissionAmount, $commission, $order]) {
            $entities[] = (new Purchase())->setReferrer($referrer)->setReferralRegistration($registration)
                ->setPurchaseAmount($amount)->setCommissionAmount($commissionAmount)->setCommission($commission)
                ->setOrderId($order)->setProgram('upgrade')->setType($type);
        }
        foreach ($entities as $entity) {
            $em->persist($entity);
        }
        $em->flush();
        $manifest = ['orm' => $version, 'dbal' => InstalledVersions::getVersion('doctrine/dbal'), 'framework' => InstalledVersions::getVersion('symfony/framework-bundle'), 'php' => PHP_VERSION, 'records' => [], 'schema' => schemaContract($manager->introspectSchema()), 'mapping' => schemaContract($tool->getSchemaFromMetadata($metadata)), 'lastReferAt' => $url->getLastReferAt()->format('Y-m-d H:i:s')];
        foreach ($entities as $entity) {
            $manifest['records'][] = ['class' => get_class($entity), 'id' => $entity->getId(), 'created' => $entity->getCreateAt()->format('Y-m-d H:i:s')];
        }
        if (!is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0777, true);
        }
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        echo "Seeded all five entity types and both purchase types using ORM {$version}.\n";
    } else {
        $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertSame($manifest['dbal'], InstalledVersions::getVersion('doctrine/dbal'), 'Keep DBAL unchanged across the ORM transition.');
        Assert::assertSame($manifest['framework'], InstalledVersions::getVersion('symfony/framework-bundle'), 'Keep Symfony unchanged across the ORM transition.');
        Assert::assertSame($manifest['php'], PHP_VERSION, 'Keep PHP unchanged across the ORM transition.');
        Assert::assertSame($manifest['schema'], schemaContract($manager->introspectSchema()), 'The existing database schema must remain unchanged.');
        Assert::assertSame($manifest['mapping'], schemaContract($tool->getSchemaFromMetadata($metadata)), 'ORM 3 must require the same mapped schema as ORM 2.');
        foreach ($manifest['records'] as $record) {
            $entity = $em->find($record['class'], $record['id']);
            Assert::assertNotNull($entity, 'ORM 2 records must be readable with ORM 3.');
            Assert::assertEquals($record['id'], $entity->getId());
            Assert::assertSame($record['created'], $entity->getCreateAt()->format('Y-m-d H:i:s'));
        }
        $registration = $em->getRepository(ReferralRegistration::class)->findOneBy(['userId' => 301]);
        Assert::assertSame(201, $registration->getReferrer()->getId());
        Assert::assertSame($registration->getId(), $registration->getReferral()->getRegistration()->getId());
        Assert::assertSame('https://example.com/orm-upgrade', $registration->getReferral()->getReferrerUrl()->getUrl());
        Assert::assertSame($manifest['lastReferAt'], $registration->getReferral()->getReferrerUrl()->getLastReferAt()->format('Y-m-d H:i:s'));
        Assert::assertSame('192.0.2.15', $registration->getReferral()->getIp());
        Assert::assertEquals(123456789, $registration->getReferral()->getHitId());
        Assert::assertSame(2, $registration->getPurchaseCountByProgram('upgrade'));
        $repository = $em->getRepository(ReferralRegistration::class);
        Assert::assertEquals(1, $repository->getRegistrationCountByUser(new User(201)));
        Assert::assertSame([$registration], $repository->getRegistrationByUser(new User(201))->getResult());
        foreach ([[501, 'percentage', 1000, 150, 15.0], [502, 'fixed-amount', 2000, 25, 25.0]] as [$order, $type, $amount, $commissionAmount, $commission]) {
            $purchase = $em->getRepository(Purchase::class)->findOneBy(['orderId' => $order]);
            Assert::assertSame($type, $purchase->getType());
            Assert::assertSame($amount, $purchase->getPurchaseAmount());
            Assert::assertSame($commissionAmount, $purchase->getCommissionAmount(), 'Stored commission values must survive the ORM upgrade.');
            Assert::assertEquals($commission, $purchase->getCommission());
            Assert::assertSame(201, $purchase->getReferrer()->getId());
            Assert::assertSame($registration->getId(), $purchase->getReferralRegistration()->getId());
        }
        $registration->getReferral()->setIp('192.0.2.99');
        $em->flush();
        $em->clear();
        $updated = $em->getRepository(ReferralRegistration::class)->findOneBy(['userId' => 301]);
        Assert::assertSame('192.0.2.99', $updated->getReferral()->getIp(), 'ORM 3 must update and reload ORM 2 records.');
        Assert::assertSame($manifest['schema'], schemaContract($manager->introspectSchema()), 'Verification must not apply a schema migration.');
        echo "Verified ORM {$manifest['orm']} data and schema under ORM {$version}; read/update/reload succeeded without migrations.\n";
    }
} finally {
    $em->close();
    $kernel->shutdown();
}
