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
 * @ORM\Entity
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
     * @var string
     *
     * @ORM\Column(name="referrer_url", type="string", length=255, nullable=true)
     */
    private $referrerUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="landing_url", type="string", length=255, nullable=true)
     */
    private $landingUrl;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new \DateTime);
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
     * Set referrerUrl
     *
     * @param string $referrerUrl
     *
     * @return Referral
     */
    public function setReferrerUrl($referrerUrl)
    {
        $this->referrerUrl = $referrerUrl;

        return $this;
    }

    /**
     * Get referrerUrl
     *
     * @return string
     */
    public function getReferrerUrl()
    {
        return $this->referrerUrl;
    }

    /**
     * Set landingUrl
     *
     * @param string $landingUrl
     *
     * @return Referral
     */
    public function setLandingUrl($landingUrl)
    {
        $this->landingUrl = $landingUrl;

        return $this;
    }

    /**
     * Get landingUrl
     *
     * @return string
     */
    public function getLandingUrl()
    {
        return $this->landingUrl;
    }

    /**
     * Set referrer
     *
     * @param \Shaygan\AffiliateBundle\Entity\Referrer $referrer
     *
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

}
