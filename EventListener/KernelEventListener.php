<?php

namespace Shaygan\AffiliateBundle\EventListener;

use Shaygan\AffiliateBundle\Model\Affiliate;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

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


    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->affiliate->record($event->getResponse());
    }

}
