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

    /**
     * 
     * @return \Shaygan\AffiliateBundle\Entity\Commission
     * @return type
     */
    public function getPurchaseCommission(OrderInterface $order, $program = "default")
    {
        if ($this->isPurchaseEligible($order->getOwnerUser(), $program)) {
            $commission = $this->createCommissionEntity($order, $program);
            $this->em->persist($commission);
            $this->em->flush();
            return $commission;
        } else {
            return null;
        }
    }

    /**
     * 
     * @param \Shaygan\AffiliateBundle\Model\OrderInterface $order
     * @return \Shaygan\AffiliateBundle\Entity\Commission
     */
    protected function createCommissionEntity(OrderInterface $order, $program)
    {
        $type = $this->config['programs'][$program]['type'];
        $referralRegistration = $this->getUserReferralRegistration($order->getOwnerUser());
        $totalPrice = $order->getTotalPrice();
        if ($type == "percentage") {
            if ($this->isFirstPurchase()) {
                $commissionAmount = (int) ($totalPrice * ($this->config['programs'][$program]['first_commission_percent'] / 100));
            } else {
                $commissionAmount = (int) ($totalPrice * ($this->config['programs'][$program]['commission_percent'] / 100));
            }
        } elseif ($type == "fixed-price") {
            if ($this->isFirstPurchase()) {
                $commissionAmount = $totalPrice * $this->config['programs'][$program]['first_commission_amount'];
            } else {
                $commissionAmount = $totalPrice * $this->config['programs'][$program]['commission_amount'];
            }
        } else {
            throw new \Exception("Invalid commission type.");
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

    protected function isPurchaseEligible(User $user, $program)
    {
        $reg = $this->getUserReferralRegistration($user);

        if ($reg === null) {
            return false;
        }

        if ($reg->getPurchaseCount() < $this->config['programs'][$program]['max_count']) {
            return true;
        } else {
            return false;
        }
    }

    protected function isFirstPurchase(User $user, $program)
    {
        $reg = $this->getUserReferralRegistration($user);

        if ($reg === null) {
            return false;
        }

        return $reg->getPurchaseCount() == 1;
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

    private function getReferrerUrl()
    {

        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
            $referrerUrl = $this->em->getRepository("ShayganAffiliateBundle:ReferrerUrl")->findOneByUrl($url);
            if ($referrerUrl) {
                $q = $this->em->createQuery('UPDATE ShayganAffiliateBundle:ReferrerUrl r SET r.referCount=r.referCount+1 WHERE r.id = :id');
                $q->setParameters(array(
                    'id' => $referrerUrl->getId(),
                ));
                $q->execute();
                return $referrerUrl;
            } else {
                $referrerUrl = new \Shaygan\AffiliateBundle\Entity\ReferrerUrl();
                $referrerUrl->setUrl($url);
                $referrerUrl->setReferCount(1);
                $this->em->persist($referrerUrl);
                return $referrerUrl;
            }
        }
        return null;
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
            $referral->setReferrerUrl($this->getReferrerUrl());
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
        $reg->setReferral($referral);
        $this->em->persist($reg);
        $this->em->flush();
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
