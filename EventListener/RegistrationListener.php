<?php

namespace Shaygan\AffiliateBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class RegistrationListener implements EventSubscriberInterface
{

    private $affiliate;

    public function __construct(\Shaygan\AffiliateBundle\Model\Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
    }

    public static function getSubscribedEvents()
    {
        if ( class_exists ( '\FOS\UserBundle\FOSUserEvents' )) {
            return array(\FOS\UserBundle\FOSUserEvents::REGISTRATION_COMPLETED => "onRegistrationComleted");
        } else if (class_exists ( '\App\AppEvents' )  && defined('\App\AppEvents::USER_REGISTRATION_COMPLETED')) {
            return array(\App\AppEvents::USER_REGISTRATION_COMPLETED => "onRegistrationComleted");
        } else {
            throw new \Exception('No User Registration Event found');
        }
    }

    public function onRegistrationComleted(FilterUserResponseEvent $event)
    {
        $this->affiliate->recordRegistration($event->getResponse(), $event->getUser());
    }

}
