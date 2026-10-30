<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Tests\Fixtures\User;

final class DirectRegistrationTest extends DatabaseTestCase
{
    public function testPublicRegistrationOperationPersistsBothSidesOfAttribution(): void
    {
        $affiliate = $this->kernel->getContainer()->get('test.service_container')->get('shaygan_affiliate');
        $registration = $affiliate->saveRegistration(new User(77), new User(42));
        $id = $registration->getId();
        $this->em->clear();
        $registration = $this->em->find(ReferralRegistration::class, $id);
        self::assertSame(77, $registration->getUserId());
        self::assertSame(42, $registration->getReferrer()->getId());
        self::assertSame($registration, $registration->getReferral()->getRegistration());
        self::assertInstanceOf(\DateTime::class, $registration->getCreateAt());
        self::assertSame(1, $this->em->find(Referrer::class, 42)->getSignupCount());
        self::assertInstanceOf(Referral::class, $registration->getReferrer()->getReferrals()->first());
    }
}
