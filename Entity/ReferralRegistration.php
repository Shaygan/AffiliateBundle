<?php

namespace Shaygan\AffiliateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\Criteria;
use \Doctrine\Common\Collections\ArrayCollection;
use DateTime;

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
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="referralRegistration")
     */
    private $commissions;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new DateTime);
    }

    public function getPurchaseCountByProgram($progam)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("program", $progam));
        $programCommissions = $this->commissions->matching($criteria);
        return $programCommissions->count();
    }

    public function getPurchaseCount()
    {
        return $this->commissions->count();
    }

/////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commissions = new ArrayCollection();
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
     *
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
     * Set referral
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referral $referral
     *
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
     *
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

    /**
     * Add commission
     *
     * @param \Shaygan\AffiliateBundle\Entity\Purchase $commission
     *
     * @return ReferralRegistration
     */
    public function addCommission(\Shaygan\AffiliateBundle\Entity\Purchase $commission)
    {
        $this->commissions[] = $commission;

        return $this;
    }

    /**
     * Remove commission
     *
     * @param \Shaygan\AffiliateBundle\Entity\Purchase $commission
     */
    public function removeCommission(\Shaygan\AffiliateBundle\Entity\Purchase $commission)
    {
        $this->commissions->removeElement($commission);
    }

    /**
     * Get commissions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommissions()
    {
        return $this->commissions;
    }

}
