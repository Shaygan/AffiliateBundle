<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Shaygan\AffiliateBundle\Entity\Purchase;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Event\GetPurchaseEvent;
use Shaygan\AffiliateBundle\ShayganAffiliateEvents;
use Shaygan\AffiliateBundle\Tests\Fixtures\ConsumerPurchase;
use Shaygan\AffiliateBundle\Tests\Fixtures\User;

final class PurchaseCommissionTest extends DatabaseTestCase
{
    public function testPercentagePurchasesPersistValuesAndRespectTheProgramLimit(): void
    {
        $container = $this->kernel->getContainer()->get('test.service_container');
        $affiliate = $container->get('shaygan_affiliate');
        $user = new User(77);
        $registrationId = $affiliate->saveRegistration($user, new User(42))->getId();
        $events = [];
        $container->get('event_dispatcher')->addListener(ShayganAffiliateEvents::PURCHASE_COMPLETED, static function (GetPurchaseEvent $event) use (&$events): void {
            $events[] = $event->getPurchase()->getId();
        });
        $this->em->clear();

        $first = $affiliate->getPurchaseCommission(new ConsumerPurchase(501, $user, 1000));
        self::assertInstanceOf(Purchase::class, $first);
        $firstId = $first->getId();
        self::assertNotNull($firstId);
        $this->em->clear();
        $first = $this->em->find(Purchase::class, $firstId);
        self::assertSame(150, $first->getCommissionAmount());
        self::assertSame(15.0, $first->getCommission());
        self::assertSame(1000, $first->getPurchaseAmount());
        self::assertSame(501, $first->getOrderId());
        self::assertSame('default', $first->getProgram());
        self::assertSame(Purchase::TYPE_PERCENTAGE, $first->getType());
        self::assertSame(42, $first->getReferrerId());
        self::assertSame($registrationId, $first->getReferralRegistration()->getId());
        self::assertInstanceOf(\DateTime::class, $first->getCreateAt());
        self::assertSame(1, $first->getReferralRegistration()->getPurchaseCountByProgram('default'));
        self::assertInstanceOf(ReferralRegistration::class, $affiliate->getReferrerIfPurchaseCommissionEligible($user, 'default'));
        $this->em->clear();

        $second = $affiliate->getPurchaseCommission(new ConsumerPurchase(502, $user, 2500));
        self::assertInstanceOf(Purchase::class, $second);
        $secondId = $second->getId();
        self::assertNotSame($firstId, $secondId);
        $this->em->clear();
        $second = $this->em->find(Purchase::class, $secondId);
        self::assertSame(250, $second->getCommissionAmount());
        self::assertSame(10.0, $second->getCommission());
        self::assertSame(2500, $second->getPurchaseAmount());
        self::assertSame(502, $second->getOrderId());
        self::assertSame($registrationId, $second->getReferralRegistration()->getId());
        self::assertSame(42, $second->getReferrerId());
        self::assertInstanceOf(\DateTime::class, $second->getCreateAt());
        self::assertSame(2, $second->getReferralRegistration()->getPurchaseCountByProgram('default'));
        self::assertSame(0, $second->getReferralRegistration()->getPurchaseCountByProgram('another-program'));
        self::assertFalse($affiliate->getReferrerIfPurchaseCommissionEligible($user, 'default'));
        self::assertNull($affiliate->getPurchaseCommission(new ConsumerPurchase(503, $user, 1000)));
        self::assertSame(2, $this->em->getRepository(Purchase::class)->count([]));
        self::assertSame([$firstId, $secondId], $events);
    }

    public function testAnUnreferredUserDoesNotCreateACommissionOrDispatchAPurchaseEvent(): void
    {
        $container = $this->kernel->getContainer()->get('test.service_container');
        $events = [];
        $container->get('event_dispatcher')->addListener(ShayganAffiliateEvents::PURCHASE_COMPLETED, static function (GetPurchaseEvent $event) use (&$events): void {
            $events[] = $event->getPurchase();
        });
        $affiliate = $container->get('shaygan_affiliate');

        self::assertNull($affiliate->getPurchaseCommission(new ConsumerPurchase(601, new User(99), 1000)));
        self::assertFalse($affiliate->getReferrerIfPurchaseCommissionEligible(new User(99), 'default'));
        self::assertSame(0, $this->em->getRepository(Purchase::class)->count([]));
        self::assertSame([], $events);
    }

    public function testFixedCommissionRecordsRoundTripWithoutCertifyingTheirCalculationWorkflow(): void
    {
        $affiliate = $this->kernel->getContainer()->get('test.service_container')->get('shaygan_affiliate');
        $user = new User(77);
        $registration = $affiliate->saveRegistration($user, new User(42));
        $registrationId = $registration->getId();
        $purchase = new Purchase();
        $purchase->setReferrer($registration->getReferrer());
        $purchase->setReferralRegistration($registration);
        $purchase->setPurchaseAmount(800);
        $purchase->setCommissionAmount(7);
        $purchase->setCommission(7);
        $purchase->setOrderId(701);
        $purchase->setProgram('another-program');
        $purchase->setType(Purchase::TYPE_FIXED_AMOUNT);
        $this->em->persist($purchase);
        $this->em->flush();
        $id = $purchase->getId();
        self::assertNotNull($id);
        $this->em->clear();

        $purchase = $this->em->getRepository(Purchase::class)->find($id);
        self::assertSame(Purchase::TYPE_FIXED_AMOUNT, $purchase->getType());
        self::assertSame('another-program', $purchase->getProgram());
        self::assertSame(800, $purchase->getPurchaseAmount());
        self::assertSame(7, $purchase->getCommissionAmount());
        self::assertSame(7.0, $purchase->getCommission());
        self::assertSame(701, $purchase->getOrderId());
        self::assertSame(42, $purchase->getReferrerId());
        self::assertSame($registrationId, $purchase->getReferralRegistration()->getId());
        self::assertInstanceOf(\DateTime::class, $purchase->getCreateAt());
        self::assertSame(1, $purchase->getReferralRegistration()->getPurchaseCount());
        self::assertSame(1, $purchase->getReferralRegistration()->getPurchaseCountByProgram('another-program'));
        self::assertSame(0, $purchase->getReferralRegistration()->getPurchaseCountByProgram('default'));
        self::assertInstanceOf(ReferralRegistration::class, $affiliate->getReferrerIfPurchaseCommissionEligible($user, 'default'));
    }
}
