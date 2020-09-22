<?php

namespace Shaygan\AffiliateBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
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
        if ( class_exists ( FOSUserEvents )) {
            return array(FOSUserEvents::REGISTRATION_COMPLETED => "onRegistrationComleted");
        } else (class_exists ( FOSUserEvents )) {
            return array(AppEvents::REGISTRATION_COMPLETED => "onRegistrationComleted");
        } else {
            throw new \Exception('No User Registration Even found');
        }
    }

    public function onRegistrationComleted(FilterUserResponseEvent $event)
    {
        $this->affiliate->recordRegistration($event->getResponse(), $event->getUser());
    }

}
