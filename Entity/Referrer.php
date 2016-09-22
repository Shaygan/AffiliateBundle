<?php

namespace Shaygan\AffiliateBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
class Referrer {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var DateTime
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
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="referrer")
     */
    private $commissions;

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->setCreateAt(new DateTime);
    }

    /**
     * Inc signupCount
     *
     * @return Referrer
     */
    public function incSignupCount() {
        $this->signupCount++;

        return $this;
    }

    /////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////

    /**
     * Constructor
     */
    public function __construct() {
        $this->referrals = new ArrayCollection();
        $this->commissions = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return Referrer
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set createAt
     *
     * @param DateTime $createAt
     * @return Referrer
     */
    public function setCreateAt($createAt) {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return DateTime 
     */
    public function getCreateAt() {
        return $this->createAt;
    }

    /**
     * Set referCount
     *
     * @param integer $referCount
     * @return Referrer
     */
    public function setReferCount($referCount) {
        $this->referCount = $referCount;

        return $this;
    }

    /**
     * Get referCount
     *
     * @return integer 
     */
    public function getReferCount() {
        return $this->referCount;
    }

    /**
     * Set signupCount
     *
     * @param integer $signupCount
     * @return Referrer
     */
    public function setSignupCount($signupCount) {
        $this->signupCount = $signupCount;

        return $this;
    }

    /**
     * Get signupCount
     *
     * @return integer 
     */
    public function getSignupCount() {
        return $this->signupCount;
    }

    /**
     * Add referrals
     *
     * @param Referral $referrals
     * @return Referrer
     */
    public function addReferral(Referral $referrals) {
        $this->referrals[] = $referrals;

        return $this;
    }

    /**
     * Remove referrals
     *
     * @param Referral $referrals
     */
    public function removeReferral(Referral $referrals) {
        $this->referrals->removeElement($referrals);
    }

    /**
     * Get referrals
     *
     * @return Collection 
     */
    public function getReferrals() {
        return $this->referrals;
    }

    /**
     * Add commissions
     *
     * @param Purchase $commissions
     * @return Referrer
     */
    public function addCommission(Purchase $commissions) {
        $this->commissions[] = $commissions;

        return $this;
    }

    /**
     * Remove commissions
     *
     * @param Purchase $commissions
     */
    public function removeCommission(Purchase $commissions) {
        $this->commissions->removeElement($commissions);
    }

    /**
     * Get commissions
     *
     * @return Collection 
     */
    public function getCommissions() {
        return $this->commissions;
    }

}
