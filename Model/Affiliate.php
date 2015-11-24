<?php

namespace Shaygan\AffiliateBundle\Model;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\User;
use Shaygan\AffiliateBundle\Entity\Referral;
use Shaygan\AffiliateBundle\Entity\Referrer;
use Shaygan\AffiliateBundle\Entity\ReferralRegistration;
use Shaygan\AffiliateBundle\Event\GetReferralRegistrationEvent;
use Shaygan\AffiliateBundle\ShayganAffiliateEvents;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Affiliate
{

    protected $em;
    protected $session;
    protected $request;
    protected $cookies;
    protected $dispatcher;
    protected $config;

    function __construct(EntityManager $em, Container $container)
    {

        $this->request = $container->get("request");
        $this->session = $container->get("session");
        $this->cookies = $this->request->cookies;
        $this->dispatcher = $container->get("event_dispatcher");
        $this->em = $em;

        $this->config = $container->getParameter('shaygan_affiliate.config');
    }

    /**
     * Record referral if detect referrer parameter in query for traking user 
     * ReferralRegistration, purchasee and purchase
     * 
     * @param type $response
     */
    function record($response)
    {
        if ($this->getGetParam($this->config['referrer_param_name'])) {
            $referrerId = (int) $this->getGetParam($this->config['referrer_param_name']);
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

    public function createReferrer($referrerId)
    {
        $referrer = new Referrer();
        $referrer->setId($referrerId);
        $referrer->setReferCount(1);

        $this->em->persist($referrer);
        $this->em->flush();
    }

    /**
     * Check referral logs if found referral record the user as referred for 
     * next events
     * 
     * @param Response $response
     * @param User $userhas
     */
    public function recordRegistration(Response $response, User $user)
    {
        if ($this->hasReferral()) {
            $referral = $this->getReferral();
            $this->saveRegistrationLog($user, $referral);
            $this->clearReferral($response);

            $this->getDispatcher()->dispatch(
                    ShayganAffiliateEvents::REGISTER_COMPLETED
                    , new GetReferralRegistrationEvent($referral, $user)
            );
        }
    }

    public function getPurchaseCommission(OrderInterface $order)
    {
        if ($this->isPurchaseEligible($order->getOwnerUser())) {

            $commission = $this->createCommissionEntity($order);
            $this->em->persist($commission);
            $this->em->flush();
            return $commission;
        } else {
            return null;
        }
    }

    protected function createCommissionEntity(OrderInterface $order)
    {
        $type = $this->config['purchase']['type'];
        $referralRegistration = $this->getUserReferralRegistration($order->getOwnerUser());
        $totalPrice = $order->getTotalPrice();
        if ($type == "percent") {
            $commissionAmount = (int) ($totalPrice * ($this->config['purchase']['percent'] / 100));
        } else {
            $commissionAmount = $totalPrice * $this->config['purchase']['amount'];
        }
        $commission = new \Shaygan\AffiliateBundle\Entity\Commission;
        $commission->setType($type);
        $commission->setOrderId($order->getId());
        $commission->setReferrer($referralRegistration->getReferrer());
        $commission->setTotalAmount($totalPrice);
        $commission->setCommissionAmount($commissionAmount);

        $referralRegistration->incPurchaseCount();

        return $commission;
    }

    public function getReferrerIfPurchaseCommissionEligible(User $user)
    {
        $reg = $this->isPurchaseEligible($user);
        if ($reg) {
            return $reg->getReferrer();
        } else {
            return false;
        }
    }

    protected function isPurchaseEligible(User $user)
    {
        $reg = $this->getUserReferralRegistration($user);

        if ($reg === null) {
            return false;
        }

        if ($reg->getPurchaseCount() < $this->config['purchase']['max_count']) {
            return true;
        } else {
            return false;
        }
    }

    protected function purchaseCommissionPaied(User $user)
    {
        $reg = $this->getUserReferralRegistration($user);
        $reg->incPurchaseCount();
        $this->em->flush();
    }

    /**
     * 
     * @param User $user
     * @return ReferralRegistration
     */
    protected function getUserReferralRegistration(User $user)
    {
        return $this->em->getRepository("ShayganAffiliateBundle:ReferralRegistration")
                        ->findOneBy(array("userId" => $user->getId()));
    }

    protected function logReferral($referrerId, Response $response)
    {
        $q = $this->em->createQuery('UPDATE ShayganAffiliateBundle:Referrer r SET r.referCount=r.referCount+1 WHERE r.id = :referrerId');
        $q->setParameters(array(
            'referrerId' => $referrerId,
        ));
        $rowCount = $q->execute();
        if ($rowCount == 1) {
            $referrer = $this->em->getRepository("ShayganAffiliateBundle:Referrer")->find($referrerId);
            $referral = new Referral();
            $referral->setReferrer($referrer);
            $this->em->persist($referral);
            $this->em->flush();
            $this->setSession($referral->getId());
            $this->setCookie($response, $referral->getId());
        } else {
            $this->createReferrer($referrerId);
            $this->logReferral($referrerId, $response);
        }
    }

    protected function setSession($referralId)
    {
        $this->session->set($this->config['session_referral_id_param_name'], $referralId);
    }

    protected function setCookie($response, $referralId)
    {
        $response->headers
                ->setCookie(new Cookie($this->config['cookie_referral_id_param_name']
                        , $referralId
                        , time() + $this->config['cookie_expire_in']
                        , $this->config['cookie_path']
                        , null
                        , $this->config['cookie_secure']
                        , $this->config['cookie_httponly']
                        )
        );
    }

    protected function hasReferral()
    {
        return $this->session->has($this->config['session_referral_id_param_name']);
    }

    /**
     * 
     * @return Referral
     */
    protected function getReferral()
    {
        $referralId = $this->session->get($this->config['session_referral_id_param_name']);
        $referral = $this->em->getRepository("ShayganAffiliateBundle:Referral")->find($referralId);
        return $referral;
    }

    protected function saveRegistrationLog(User $user, Referral $referral)
    {
        $reg = new ReferralRegistration();
        $reg->setUserId($user->getId());
        $reg->setReferrer($referral->getReferrer());
        $this->em->persist($reg);
        return $reg;
    }

    protected function clearReferral(Response $response)
    {
        $this->session->remove($this->config['session_referral_id_param_name']);
        $response->headers->clearCookie($this->config['cookie_referral_id_param_name']);
    }

    protected function getGetParam($key)
    {
        return filter_input(INPUT_GET, $key);
    }

    /**
     * 
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * 
     * @return EventDispatcher
     */
    protected function getDispatcher()
    {
        return $this->dispatcher;
    }

}
