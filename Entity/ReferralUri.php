<?php

namespace Shaygan\AffiliateBundle\Entity;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */

/**
 * ReferralUri
 */
class ReferralUri
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createAt;

    /**
     * @var \DateTime
     */
    private $lastRefferAt;

    /**
     * @var integer
     */
    private $referCount;

    /**
     * @var string
     */
    private $uri;

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
     * @return ReferralUri
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
     * Set lastRefferAt
     *
     * @param \DateTime $lastRefferAt
     *
     * @return ReferralUri
     */
    public function setLastRefferAt($lastRefferAt)
    {
        $this->lastRefferAt = $lastRefferAt;

        return $this;
    }

    /**
     * Get lastRefferAt
     *
     * @return \DateTime
     */
    public function getLastRefferAt()
    {
        return $this->lastRefferAt;
    }

    /**
     * Set referCount
     *
     * @param integer $referCount
     *
     * @return ReferralUri
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
     * Set uri
     *
     * @param string $uri
     *
     * @return ReferralUri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

}
