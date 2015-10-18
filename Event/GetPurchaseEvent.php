<?php

namespace Shaygan\AffiliateBundle\Event;

use Shaygan\AffiliateBundle\Model\OrderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class GetPurchaseEvent extends Event
{

    private $order;

    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * 
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

}
