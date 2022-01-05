<?php

namespace Shaygan\AffiliateBundle\Event;

use Shaygan\AffiliateBundle\Model\PurchaseInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class GetPurchaseEvent extends Event
{

    private $purchase;

    public function __construct(\Shaygan\AffiliateBundle\Entity\Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * 
     * @return \Shaygan\AffiliateBundle\Entity\Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

}
