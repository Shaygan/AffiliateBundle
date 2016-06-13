<?php

namespace Shaygan\AffiliateBundle\Event;

use Shaygan\AffiliateBundle\Model\PurchaseInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class GetPurchaseEvent extends Event
{

    private $order;

    public function __construct(PurchaseInterface $order)
    {
        $this->order = $order;
    }

    /**
     * 
     * @return PurchaseInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

}
