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
        return array(FOSUserEvents::REGISTRATION_COMPLETED => "onRegistrationComleted");
    }

    public function onRegistrationComleted(FilterUserResponseEvent $event)
    {
        $this->affiliate->recordRegistration($event->getResponse(), $event->getUser());
    }

}
