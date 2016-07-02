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
 * @ORM\Table(name="affiliate_purchase",
 *              indexes={
 *                      @ORM\Index(name="program", columns={"program"}),
 *              }
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Purchase
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
     * @ORM\ManyToOne(targetEntity="ReferralRegistration", inversedBy="commissions")
     */
    private $referralRegistration;

    /**
     * @var integer
     *
     * @ORM\Column(name="purchase_amount", type="integer", nullable=false)
     */
    private $purchaseAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="commission_amount", type="integer", nullable=false)
     */
    private $commissionAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="commission", type="float", nullable=false)
     */
    private $commission;

    /**
     * @var integer
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer
     *
     * @ORM\Column(name="program", type="string", nullable=false)
     */
    private $program;

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
     * @return Purchase
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

//    /**
//     * Set id
//     *
//     * @param integer $id
//     *
//     * @return Commission
//     */
//    public function setId($id)
//    {
//        $this->id = $id;
//
//        return $this;
//    }
/////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////


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
     * @return Purchase
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
     * Set purchaseAmount
     *
     * @param integer $purchaseAmount
     *
     * @return Purchase
     */
    public function setPurchaseAmount($purchaseAmount)
    {
        $this->purchaseAmount = $purchaseAmount;

        return $this;
    }

    /**
     * Get purchaseAmount
     *
     * @return integer
     */
    public function getPurchaseAmount()
    {
        return $this->purchaseAmount;
    }

    /**
     * Set commissionAmount
     *
     * @param integer $commissionAmount
     *
     * @return Purchase
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
     * Set commission
     *
     * @param float $commission
     *
     * @return Purchase
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Get commission
     *
     * @return float
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     *
     * @return Purchase
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
     * Set program
     *
     * @param string $program
     *
     * @return Purchase
     */
    public function setProgram($program)
    {
        $this->program = $program;

        return $this;
    }

    /**
     * Get program
     *
     * @return string
     */
    public function getProgram()
    {
        return $this->program;
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

    /**
     * Set referrer
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referrer $referrer
     *
     * @return Purchase
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
     * Set referralRegistration
     *
     * @param \Shaygan\AffiliateBundle\Entity\ReferralRegistration $referralRegistration
     *
     * @return Purchase
     */
    public function setReferralRegistration(\Shaygan\AffiliateBundle\Entity\ReferralRegistration $referralRegistration = null)
    {
        $this->referralRegistration = $referralRegistration;

        return $this;
    }

    /**
     * Get referralRegistration
     *
     * @return \Shaygan\AffiliateBundle\Entity\ReferralRegistration
     */
    public function getReferralRegistration()
    {
        return $this->referralRegistration;
    }
}
