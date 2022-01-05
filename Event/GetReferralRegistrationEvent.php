<?php

namespace Shaygan\AffiliateBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Shaygan\AffiliateBundle\Entity\Referral;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *  This event contain registered user and referral entities
 * 
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class GetReferralRegistrationEvent extends Event
{

    private $referral;
    private $user;

    public function __construct(Referral $referral,UserInterface $user)
    {
        $this->referral = $referral;
        $this->user = $user;
    }

    /**
     * 
     * @return Referral 
     */
    public function getReferal()
    {
        return $this->referral;
    }

    /**
     * 
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

}
