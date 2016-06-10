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
 * @ORM\Table(name="affiliate_referrer")
 * @ORM\Entity(repositoryClass="ReferrerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Referrer
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
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
     * @ORM\Column(name="refer_count", type="integer", nullable=false)
     */
    private $referCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="signup_count", type="integer", nullable=false)
     */
    private $signupCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="Referral", mappedBy="referrer")
     */
    private $referrals;

    /**
     * @ORM\OneToMany(targetEntity="Commission", mappedBy="referrer")
     */
    private $commissions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->referrals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Referrer
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
     * @return Referrer
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
     * Set referCount
     *
     * @param integer $referCount
     * @return Referrer
     */
    public function setReferCount($referCount)
    {
        $this->referCount = $referCount;

        return $this;
    }

    /**
     * Get referCount
     *
     * @return integer 
     */
    public function getReferCount()
    {
        return $this->referCount;
    }

    /**
     * Set signupCount
     *
     * @param integer $signupCount
     * @return Referrer
     */
    public function setSignupCount($signupCount)
    {
        $this->signupCount = $signupCount;

        return $this;
    }

    /**
     * Get signupCount
     *
     * @return integer 
     */
    public function getSignupCount()
    {
        return $this->signupCount;
    }

    /**
     * Add referrals
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referral $referrals
     * @return Referrer
     */
    public function addReferral(\Shaygan\AffiliateBundle\Entity\Referral $referrals)
    {
        $this->referrals[] = $referrals;

        return $this;
    }

    /**
     * Remove referrals
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referral $referrals
     */
    public function removeReferral(\Shaygan\AffiliateBundle\Entity\Referral $referrals)
    {
        $this->referrals->removeElement($referrals);
    }

    /**
     * Get referrals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferrals()
    {
        return $this->referrals;
    }

    /**
     * Add commissions
     *
     * @param \Shaygan\AffiliateBundle\Entity\Commission $commissions
     * @return Referrer
     */
    public function addCommission(\Shaygan\AffiliateBundle\Entity\Commission $commissions)
    {
        $this->commissions[] = $commissions;

        return $this;
    }

    /**
     * Remove commissions
     *
     * @param \Shaygan\AffiliateBundle\Entity\Commission $commissions
     */
    public function removeCommission(\Shaygan\AffiliateBundle\Entity\Commission $commissions)
    {
        $this->commissions->removeElement($commissions);
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
