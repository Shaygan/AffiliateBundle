<?php

namespace Shaygan\AffiliateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */

/**
 * Affiliates
 *
 * @ORM\Table(name="affiliate_commission")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Commission
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Referrer", inversedBy="commissions")
     */
    private $referrer;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_amount", type="integer", nullable=false)
     */
    private $totalAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="commission_amount", type="integer", nullable=false)
     */
    private $commissionAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    const TYPE_FIXED_AMOUNT = "fixed-amount";
    const TYPE_PERCENTAGE = "percentage";

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Commission
     */
    public function setType($type)
    {
        if ($type != self::TYPE_FIXED_AMOUNT && $type != self::TYPE_PERCENTAGE) {
            throw new \InvalidArgumentException;
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Return referrer User Id
     * @return integer
     */
    public function getReferrerId()
    {
        return $this->getReferrer()->getId();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new \DateTime);
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Commission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return Commission
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     *
     * @return Commission
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return integer
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set referrer
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referrer $referrer
     *
     * @return Commission
     */
    public function setReferrer(\Shaygan\AffiliateBundle\Entity\Referrer $referrer = null)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return \Shaygan\AffiliateBundle\Entity\Referrer
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Set totalAmount
     *
     * @param integer $totalAmount
     *
     * @return Commission
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount
     *
     * @return integer
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set commissionAmount
     *
     * @param integer $commissionAmount
     *
     * @return Commission
     */
    public function setCommissionAmount($commissionAmount)
    {
        $this->commissionAmount = $commissionAmount;

        return $this;
    }

    /**
     * Get commissionAmount
     *
     * @return integer
     */
    public function getCommissionAmount()
    {
        return $this->commissionAmount;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
