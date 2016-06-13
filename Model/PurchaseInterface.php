<?php

namespace Shaygan\AffiliateBundle\Model;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */

interface PurchaseInterface
{

    public function getId();

    public function getReferredUser();

    public function getPurchasePrice();
}
