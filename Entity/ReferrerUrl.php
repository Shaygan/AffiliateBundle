<?php

namespace Shaygan\AffiliateBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */

/**
 * Affiliates
 *
 * @ORM\Table(name="affiliate_referrer_url", options={"collate"="ascii_general_ci", "charset"="ascii"})
 * @ORM\Entity(repositoryClass="ReferrerUrlRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class ReferrerUrl
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
     * @var DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_refer_at", type="datetime", nullable=false)
     */
    private $lastReferAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="refer_count", type="integer", nullable=false)
     */
    private $referCount = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=767, nullable=false, unique=true)
     */
    private $url;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreateAt(new DateTime);
        $this->setLastReferAt(new DateTime);
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
     * @param DateTime $createAt
     * @return ReferrerUrl
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return DateTime 
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set lastReferAt
     *
     * @param DateTime $lastReferAt
     * @return ReferrerUrl
     */
    public function setLastReferAt($lastReferAt)
    {
        $this->lastReferAt = $lastReferAt;

        return $this;
    }

    /**
     * Get lastReferAt
     *
     * @return DateTime 
     */
    public function getLastReferAt()
    {
        return $this->lastReferAt;
    }

    /**
     * Set referCount
     *
     * @param integer $referCount
     * @return ReferrerUrl
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
     * Set url
     *
     * @param string $url
     * @return ReferrerUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

}
