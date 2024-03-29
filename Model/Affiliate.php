<?php

namespace Shaygan\AffiliateBundle\Model;

use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface as User;
use Shaygan\AffiliateBundle\Entity\Purchase;
use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Entity\ReferrerUrl;
use Shaygan\AffiliateBundle\Event\GetPurchaseEvent;
use Shaygan\AffiliateBundle\Event\GetReferralRegistrationEvent;
use Shaygan\AffiliateBundle\ShayganAffiliateEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Affiliate {

    private $em;
    private $session;
    private $request;
    private $dispatcher;
    private $config;

    public function __construct(EntityManager $em, RequestStack $requestStack, Session $session, EventDispatcherInterface $eventDispatcher, $config) {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
        $this->dispatcher = $eventDispatcher;
        $this->config = $config;
    }

    /**
     * Record referral if detect referrer parameter in query string for tracking users
     * ReferralRegistration and purchase
     *
     * @param Response $response
     */
    public function record(Response $response) {
        $referrerId = (int) $this->getRefParam();
        if ($referrerId) {
            if (!$this->session->has($this->config['session_referral_id_param_name'])) {
                $this->logReferral($referrerId, $response);
            } else {
                $this->setCookie($response, $this->session->get($this->config['session_referral_id_param_name']));
            }
        } else {
            if ($this->getRequest()->cookies->has($this->config['cookie_referral_id_param_name'])) {
                $referralId = $this->getRequest()->cookies->get($this->config['cookie_referral_id_param_name']);
                $this->session->set($this->config['session_referral_id_param_name'], $referralId);
            }
        }
    }

    private function getRefParam() {
        $key = $this->config['referrer_param_name'];
        if (filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT) > 0) {
            return filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
        }

        $altKeys = $this->config['referrer_alternative_param_names'];
        foreach ($altKeys as $key) {
            if (filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT) > 0) {
                return filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
            }
        }

        return 0;
    }

    private function logReferral($referrerId, Response $response) {
        $q = $this->em->createQuery(
                'UPDATE ShayganAffiliateBundle:Referrer r SET r.referCount=r.referCount+1 WHERE r.id = :referrerId'
        );
        $q->setParameters(
                array(
                    'referrerId' => $referrerId,
                )
        );
        $rowCount = $q->execute();
        if ($rowCount == 1) {
            $referrer = $this->getReferrer($referrerId);
            $referral = $this->createReferral($referrer);
            $this->setSession($referral->getId());
            $this->setCookie($response, $referral->getId());
        } else {
            $this->createReferrer($referrerId);
            $this->logReferral($referrerId, $response);
        }
    }

    /**
     * @param $referrerId
     * @return Referrer
     */
    private function getReferrer($referrerId) {
        $referrer = $this->em->getRepository(Referrer::class)->find($referrerId);

        return $referrer;
    }

    /**
     * @param Referrer $referrer
     * @return Referral
     */
    private function createReferral(Referrer $referrer) {
        $referral = new Referral();
        $referral->setReferrer($referrer);
        $referral->setReferrerUrl($this->getReferrerUrl());
        $referral->setIp($this->getReferralIp());
        $this->em->persist($referral);
        $this->em->flush();

        return $referral;
    }

    private function getReferrerUrl() {

        if (filter_input(INPUT_SERVER, "HTTP_REFERER") !== null) {
            $url = filter_input(INPUT_SERVER, "HTTP_REFERER");
            $referrerUrl = $this->em->getRepository(ReferrerUrl::class)->findOneByUrl($url);
            if ($referrerUrl) {
                $q = $this->em->createQuery(
                        'UPDATE ShayganAffiliateBundle:ReferrerUrl r SET r.referCount=r.referCount+1 WHERE r.id = :id'
                );
                $q->setParameters(
                        array(
                            'id' => $referrerUrl->getId(),
                        )
                );
                $q->execute();

                return $referrerUrl;
            } else {
                $referrerUrl = new ReferrerUrl();
                $referrerUrl->setUrl($url);
                $referrerUrl->setReferCount(1);
                $this->em->persist($referrerUrl);

                return $referrerUrl;
            }
        }

        return null;
    }

    private function getReferralIp() {
        return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }

    private function setSession($referralId) {
        $this->session->set($this->config['session_referral_id_param_name'], $referralId);
    }

    private function setCookie(Response $response, $referralId) {
        $response->headers
                ->setCookie(
                        new Cookie(
                        $this->config['cookie_referral_id_param_name']
                        , $referralId
                        , time() + $this->config['cookie_expire_in']
                        , $this->config['cookie_path']
                        , null
                        , $this->config['cookie_secure']
                        , $this->config['cookie_httponly']
                        )
        );
    }

    public function createReferrer($referrerId) {
        $referrer = new Referrer();
        $referrer->setId($referrerId);
        $referrer->setReferCount(1);

        $this->em->persist($referrer);
        $this->em->flush();
        
        return $referrer;
    }

    /**
     *
     * @return Request
     */
    private function getRequest() {
        return $this->request;
    }

    /**
     * Check referral logs if found referral record the user as referred for
     * next events
     *
     * @param Response $response
     * @param User $user
     */
    public function recordRegistration(Response $response, $user) {
        if ($this->hasReferral()) {
            $referral = $this->getReferral();
            if ($referral !== null && $referral->getRegistration() === null) {
                $this->saveRegistrationLog($user, $referral);
                $this->getDispatcher()->dispatch(
                         new GetReferralRegistrationEvent($referral, $user),
                    ShayganAffiliateEvents::REGISTER_COMPLETED
                );
            }
            $this->clearReferral($response);
        }
    }

    private function hasReferral() {
        return $this->session->has($this->config['session_referral_id_param_name']);
    }

    /**
     *
     * @return Referral
     */
    private function getReferral() {
        $referralId = $this->session->get($this->config['session_referral_id_param_name']);
        $referral = $this->em->getRepository(Referral::class)->find($referralId);

        return $referral;
    }

    private function saveRegistrationLog($user, Referral $referral) {
        $reg = new ReferralRegistration();
        $reg->setUserId($user->getId());
        $reg->setReferrer($referral->getReferrer());
        $reg->setReferral($referral);
        $referral->setRegistration($reg);
        $referral->getReferrer()->incSignupCount();
        $this->em->persist($reg);
        $this->em->flush();

        return $reg;
    }

    /**
     *
     * @return EventDispatcher
     */
    private function getDispatcher() {
        return $this->dispatcher;
    }

    private function clearReferral(Response $response) {
        $this->session->remove($this->config['session_referral_id_param_name']);
        $response->headers->clearCookie($this->config['cookie_referral_id_param_name']);
    }

    /**
     * @param  PurchaseInterface $order
     * @param string $program
     * @return Purchase | null
     */
    public function getPurchaseCommission(PurchaseInterface $order, $program = "default") {
        if ($this->isPurchaseEligible($order->getReferredUser(), $program)) {
            $commission = $this->createPurchaseEntity($order, $program);
            $this->em->persist($commission);
            $this->em->flush();
            $this->getDispatcher()->dispatch(
                new GetPurchaseEvent($commission,
                ShayganAffiliateEvents::PURCHASE_COMPLETED)
            );

            return $commission;
        } else {
            return null;
        }
    }

    /**
     * @param User $user
     * @param string $program
     * @return bool
     */
    private function isPurchaseEligible(User $user, $program) {
        $reg = $this->getUserReferralRegistration($user);

        if ($reg === null) {
            return false;
        }

        if ($reg->getPurchaseCountByProgram($program) < $this->config['programs'][$program]['max_count']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param User $user
     * @return ReferralRegistration
     */
    private function getUserReferralRegistration(User $user) {
        return $this->em->getRepository(ReferralRegistration::class)
                        ->findOneBy(array("userId" => $user->getId()));
    }

    /**
     *
     * @param PurchaseInterface $order
     * @param string $program
     * @return Purchase
     */
    private function createPurchaseEntity(PurchaseInterface $order, $program) {
        $type = $this->config['programs'][$program]['type'];
        $referralRegistration = $this->getUserReferralRegistration($order->getReferredUser());
        $purchasePrice = $order->getPurchasePrice();


        $purchase = new Purchase();
        $purchase->setProgram($program);
        $purchase->setType($type);
        $purchase->setOrderId($order->getId());
        $purchase->setReferralRegistration($referralRegistration);
        $purchase->setReferrer($referralRegistration->getReferrer());
        $purchase->setPurchaseAmount($purchasePrice);
        $purchase->setCommissionAmount($this->getCommissionAmount($order, $program));
        $purchase->setCommission($this->getCommissionValue($order, $program));

        return $purchase;
    }

    private function getCommissionAmount(PurchaseInterface $order, $program) {
        $type = $this->config['programs'][$program]['type'];
        $totalPrice = $order->getPurchasePrice();
        if ($type == "percentage") {
            if ($this->isFirstPurchase($order->getReferredUser(), $program)) {
                $commissionAmount = (int) ($totalPrice * ($this->config['programs'][$program]['first_commission_percent'] / 100));
            } else {
                $commissionAmount = (int) ($totalPrice * ($this->config['programs'][$program]['commission_percent'] / 100));
            }
        } elseif ($type == "fixed-price") {
            if ($this->isFirstPurchase($order->getReferredUser(), $program)) {
                $commissionAmount = $this->config['programs'][$program]['first_commission_amount'];
            } else {
                $commissionAmount = $this->config['programs'][$program]['first_commission_amount'];
            }
        } else {
            throw new Exception("Invalid commission type.");
        }

        return $commissionAmount;
    }

    private function isFirstPurchase(User $user, $program) {
        $reg = $this->getUserReferralRegistration($user);

        if ($reg === null) {
            return false;
        }

        return $reg->getPurchaseCountByProgram($program) == 0;
    }

    private function getCommissionValue(PurchaseInterface $order, $program) {
        $type = $this->config['programs'][$program]['type'];
        if ($type == "percentage") {
            if ($this->isFirstPurchase($order->getReferredUser(), $program)) {
                $commissionValue = $this->config['programs'][$program]['first_commission_percent'];
            } else {
                $commissionValue = $this->config['programs'][$program]['commission_percent'];
            }
        } elseif ($type == "fixed-price") {
            if ($this->isFirstPurchase($order->getReferredUser(), $program)) {
                $commissionValue = $this->config['programs'][$program]['first_commission_amount'];
            } else {
                $commissionValue = $this->config['programs'][$program]['first_commission_amount'];
            }
        } else {
            throw new Exception("Invalid commission type.");
        }

        return $commissionValue;
    }

    public function getRegistrationCountByUser(User $user) {
        return $this->em->getRepository(ReferralRegistration::class)->getRegistrationCountByUser($user);
    }

    public function getReferrerIfPurchaseCommissionEligible(User $user, $program) {
        if ($this->isPurchaseEligible($user, $program)) {
            return $this->getUserReferralRegistration($user);
        } else {
            return false;
        }
    }

    public function saveRegistration(User $user, User $referrerUser) {
        $referrer = $this->getReferrer($referrerUser->getId());

        if (null === $referrer) {
            $referrer = $this->createReferrer($referrerUser->getId());
        }

        $referral = new Referral();
        $referral->setReferrer($referrer);

        $reg = new ReferralRegistration();
        $reg->setUserId($user->getId())
                ->setReferral($referral)
                ->setReferrer($referrer)
        ;
        $referrer->incSignupCount();
        $referral->setRegistration($reg);
        $this->em->persist($reg);
        $this->em->persist($referral);
        $this->em->flush();

        $this->getDispatcher()->dispatch(
                ShayganAffiliateEvents::REGISTER_COMPLETED
                , new GetReferralRegistrationEvent($referral, $user)
        );

        return $reg;
    }

}
