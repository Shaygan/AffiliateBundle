<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Shaygan\AffiliateBundle\Model\PurchaseInterface;

final class ConsumerPurchase implements PurchaseInterface
{
    public function __construct(private int $id, private User $user, private int $price) {}
    public function getId(): int { return $this->id; }
    public function getReferredUser(): User { return $this->user; }
    public function getPurchasePrice(): int { return $this->price; }
}
