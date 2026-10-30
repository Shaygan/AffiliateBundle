<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Tests\Fixtures\User;

final class RegistrationReportTest extends DatabaseTestCase
{
    public function testRegistrationsAreFilteredAndOrderedForTheirReferrer(): void
    {
        $referrer = (new Referrer())->setId(42);
        $other = (new Referrer())->setId(99);
        $this->em->persist($referrer);
        $this->em->persist($other);
        foreach ([[101, $referrer], [102, $other], [103, $referrer]] as [$userId, $owner]) {
            $registration = (new ReferralRegistration())->setUserId($userId)->setReferrer($owner);
            $this->em->persist($registration);
        }
        $this->em->flush();
        $this->em->clear();
        $repository = $this->em->getRepository(ReferralRegistration::class);
        $rows = $repository->getRegistrationByUser(new User(42))->getResult();
        self::assertSame([103, 101], array_map(static fn ($row) => $row->getUserId(), $rows));
        self::assertSame(2, (int) $repository->getRegistrationCountByUser(new User(42)));
        self::assertSame(0, (int) $repository->getRegistrationCountByUser(new User(1000)));
    }
    public function testAuthenticatedReportPaginatesOnlyTheCurrentReferrersRegistrations(): void
    {
        $referrer = (new Referrer())->setId(42);
        $other = (new Referrer())->setId(99);
        $this->em->persist($referrer);
        $this->em->persist($other);
        $registrations = [];
        for ($i = 0; $i < 13; ++$i) {
            $registration = (new ReferralRegistration())->setUserId(200 + $i)->setReferrer($i === 12 ? $other : $referrer);
            $registrations[] = $registration;
            $this->em->persist($registration);
        }
        $this->em->flush();
        foreach ($registrations as $i => $registration) {
            $registration->setCreateAt(new \DateTime($i === 12 ? '2040-01-01' : '2030-01-01'));
        }
        $this->em->flush();
        $client = new \Symfony\Bundle\FrameworkBundle\KernelBrowser($this->kernel);
        $client->catchExceptions(false);
        $client->loginUser(new User(42));
        $client->request('GET', '/affiliate/report');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertSame(10, substr_count($client->getResponse()->getContent(), '2030'));
        self::assertStringNotContainsString('2040', $client->getResponse()->getContent());
        $client->request('GET', '/affiliate/report?page=2');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertSame(2, substr_count($client->getResponse()->getContent(), '2030'));
        $container = $this->kernel->getContainer()->get('test.service_container');
        self::assertInstanceOf(\Shaygan\AffiliateBundle\Model\Affiliate::class, $container->get('shaygan_affiliate'));
        self::assertSame($container->get('doctrine.orm.entity_manager')->getRepository(ReferralRegistration::class), $container->get(\Shaygan\AffiliateBundle\Entity\ReferralRegistrationRepository::class));

    }

}
