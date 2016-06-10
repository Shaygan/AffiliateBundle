<?php

namespace Shaygan\AffiliateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */

/**
 * AffiliateLogs
 *
 * @ORM\Table(name="affiliate_referral_registration")
 * @ORM\Entity(repositoryClass="ReferralRegistrationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ReferralRegistration
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
     * @ORM\OneToOne(targetEntity="Referral", inversedBy="registration")
     */
    private $referral;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Referrer", inversedBy="referrals")
     */
    private $referrer;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="diposit_count", type="integer", nullable=false)
     */
    private $dipositCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="purchase_count", type="integer", nullable=false)
     */
    private $purchaseCount = 0;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new \DateTime);
    }

    /**
     * inc dipositCount
     *
     * @return integer
     */
    public function incDipositCount()
    {
        return ++$this->dipositCount;
    }

    /**
     * inc purchaseCount
     *
     * @return integer
     */
    public function incPurchaseCount()
    {
        return ++$this->purchaseCount;
    }

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
     * @return ReferralRegistration
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
     * Set userId
     *
     * @param integer $userId
     * @return ReferralRegistration
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set dipositCount
     *
     * @param integer $dipositCount
     * @return ReferralRegistration
     */
    public function setDipositCount($dipositCount)
    {
        $this->dipositCount = $dipositCount;

        return $this;
    }

    /**
     * Get dipositCount
     *
     * @return integer 
     */
    public function getDipositCount()
    {
        return $this->dipositCount;
    }

    /**
     * Set purchaseCount
     *
     * @param integer $purchaseCount
     * @return ReferralRegistration
     */
    public function setPurchaseCount($purchaseCount)
    {
        $this->purchaseCount = $purchaseCount;

        return $this;
    }

    /**
     * Get purchaseCount
     *
     * @return integer 
     */
    public function getPurchaseCount()
    {
        return $this->purchaseCount;
    }

    /**
     * Set referral
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referral $referral
     * @return ReferralRegistration
     */
    public function setReferral(\Shaygan\AffiliateBundle\Entity\Referral $referral = null)
    {
        $this->referral = $referral;

        return $this;
    }

    /**
     * Get referral
     *
     * @return \Shaygan\AffiliateBundle\Entity\Referral 
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * Set referrer
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referrer $referrer
     * @return ReferralRegistration
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
}
