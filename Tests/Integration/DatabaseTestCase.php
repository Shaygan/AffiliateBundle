<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Shaygan\AffiliateBundle\Tests\Fixtures\Kernel;

abstract class DatabaseTestCase extends TestCase
{
    protected Kernel $kernel;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
        $this->em = $this->kernel->getContainer()->get('test.service_container')->get('doctrine.orm.entity_manager');
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schema = new SchemaTool($this->em);
        $schema->dropSchema($metadata);
        $schema->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->em->close();
        $this->kernel->shutdown();
    }
}
