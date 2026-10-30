<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;

final class RegistrationAttributionTest extends HttpTestCase
{
    public function testHostRegistrationEventPersistsAttributionAndClearsTrackingAtTheEventBoundary(): void
    {
        $this->request('/visit?ref=42');
        $response = $this->request('/register?user=77');
        self::assertArrayNotHasKey('pca_affiliate', json_decode($response['body'], true)['tracking_after_registration']);
        self::assertArrayNotHasKey('pca_affiliate', $this->cookies);
        $this->em->clear();
        $registration = $this->em->getRepository(ReferralRegistration::class)->findOneBy(['userId' => 77]);
        self::assertNotNull($registration);
        self::assertSame(42, $registration->getReferrer()->getId());
        self::assertSame($registration, $registration->getReferral()->getRegistration());
        self::assertInstanceOf(\DateTime::class, $registration->getCreateAt());
        self::assertInstanceOf(\DateTime::class, $registration->getReferral()->getCreateAt());
        self::assertSame(1, $registration->getReferrer()->getSignupCount());
        $referrals = $registration->getReferrer()->getReferrals();
        self::assertCount(1, $referrals);
        self::assertInstanceOf(Referral::class, $referrals->first());
        $this->request('/register?user=88');
        self::assertSame(1, $this->em->getRepository(ReferralRegistration::class)->count([]));
    }
}
