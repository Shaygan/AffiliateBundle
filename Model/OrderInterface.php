<?php

namespace Shaygan\AffiliateBundle\Model;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
interface OrderInterface
{

    public function getId();

    public function getOwnerUser();

    public function getTotalPrice();
}
