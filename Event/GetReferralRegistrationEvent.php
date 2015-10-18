<?php

namespace Shaygan\AffiliateBundle\Event;

use FOS\UserBundle\Model\User;
use Shaygan\AffiliateBundle\Entity\Referral;
use Symfony\Component\EventDispatcher\Event;

/**
 *  This event contain registered user and referral entities
 * 
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class GetReferralRegistrationEvent extends Event
{

    private $referral;
    private $user;

    public function __construct(Referral $referral, User $user)
    {
        $this->referral = $referral;
        $this->user = $user;
    }

}
