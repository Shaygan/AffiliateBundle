<?php

namespace Shaygan\AffiliateBundle\EventListener;

use Shaygan\AffiliateBundle\Model\Affiliate;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class KernelEventListener
{

    protected $affiliate;

    public function __construct(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
    }

//    public function onKernelRequest(GetResponseEvent $event)
//    {
//        $this->affiliate->record($event->getResponse());
//    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
//        if (!$event->isMasterRequest()) {
//            return;
//        }
        $this->affiliate->record($event->getResponse());
    }

}
