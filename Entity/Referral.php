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
 * @ORM\Table(name="affiliate_referral")
 * @ORM\Entity(repositoryClass="ReferralRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Referral
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
     * @ORM\ManyToOne(targetEntity="Referrer", inversedBy="referrals")
     */
    private $referrer;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="ReferralRegistration", mappedBy="referral")
     */
    private $registration;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="ReferrerUrl")
     */
    private $referrerUrl;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new \DateTime);
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
     * @return Referral
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
     * Set referrer
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referrer $referrer
     * @return Referral
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
     * Set registration
     *
     * @param \Shaygan\AffiliateBundle\Entity\ReferralRegistration $registration
     * @return Referral
     */
    public function setRegistration(\Shaygan\AffiliateBundle\Entity\ReferralRegistration $registration = null)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration
     *
     * @return \Shaygan\AffiliateBundle\Entity\ReferralRegistration 
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set referrerUrl
     *
     * @param \Shaygan\AffiliateBundle\Entity\ReferrerUrl $referrerUrl
     * @return Referral
     */
    public function setReferrerUrl(\Shaygan\AffiliateBundle\Entity\ReferrerUrl $referrerUrl = null)
    {
        $this->referrerUrl = $referrerUrl;

        return $this;
    }

    /**
     * Get referrerUrl
     *
     * @return \Shaygan\AffiliateBundle\Entity\ReferrerUrl 
     */
    public function getReferrerUrl()
    {
        return $this->referrerUrl;
    }
}
