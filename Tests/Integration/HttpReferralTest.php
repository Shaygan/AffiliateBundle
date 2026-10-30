<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Entity\ReferrerUrl;

final class HttpReferralTest extends HttpTestCase
{
    public function testRealReferralRequestPersistsAttributionAndTracking(): void
    {
        $this->request('/visit?ref=42', 'https://example.org/article');
        $this->em->clear();
        $referral = $this->em->getRepository(Referral::class)->findOneBy([]);
        self::assertNotNull($referral);
        self::assertSame(42, $referral->getReferrer()->getId());
        self::assertSame('https://example.org/article', $referral->getReferrerUrl()->getUrl());
        self::assertSame((string) $referral->getId(), $this->cookies['pca_affiliate']);
        $tracking = $this->request('/tracking');
        self::assertSame($referral->getId(), json_decode($tracking['body'], true)['pca_affiliate']);
    }
    public function testExistingReferrerAndReferringUrlAreReusedAcrossVisitors(): void
    {
        $this->em->persist((new Referrer())->setId(42));
        $this->em->flush();
        $this->request('/visit?ref=42', 'https://example.org/article');
        $this->cookies = [];
        $this->request('/visit?ref=42', 'https://example.org/article');
        $this->em->clear();
        self::assertSame(2, $this->em->getRepository(Referral::class)->count([]));
        self::assertSame(1, $this->em->getRepository(ReferrerUrl::class)->count([]));
        self::assertSame(2, $this->em->find(Referrer::class, 42)->getReferCount());
        self::assertSame(2, $this->em->getRepository(ReferrerUrl::class)->findOneBy([])->getReferCount());
    }
    public function testVisitWithoutAReferringUrlAndExistingSessionKeepTheirAttribution(): void
    {
        $this->request('/visit?ref=42');
        $referralCookie = $this->cookies['pca_affiliate'];
        $this->request('/visit?ref=99');
        $this->em->clear();
        $referral = $this->em->getRepository(Referral::class)->findOneBy([]);
        self::assertSame(1, $this->em->getRepository(Referral::class)->count([]));
        self::assertNull($referral->getReferrerUrl());
        self::assertNull($this->em->find(Referrer::class, 99));
        self::assertSame($referralCookie, $this->cookies['pca_affiliate']);
        self::assertSame(0, $this->em->getRepository(ReferrerUrl::class)->count([]));
    }

    public function testReferralCookieRestoresTrackingInANewSession(): void
    {
        $this->request('/visit?ref=42');
        $referralCookie = $this->cookies['pca_affiliate'];
        $this->cookies = ['pca_affiliate' => $referralCookie];
        $this->request('/visit');
        $tracking = $this->request('/tracking');
        self::assertSame($referralCookie, (string) json_decode($tracking['body'], true)['pca_affiliate']);
        self::assertSame(1, $this->em->getRepository(Referral::class)->count([]));
    }
}
