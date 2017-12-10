<?php

namespace Ibtikar\GoogleServicesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Device
 *
 * @ORM\Table(name="device")
 * @ORM\Entity(repositoryClass="Ibtikar\GoogleServicesBundle\Repository\DeviceRepository")
 */
class Device
{

    use \Ibtikar\ShareEconomyToolsBundle\Entity\TrackableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="DeviceUserInterface")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=190)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="text")
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=190, unique=true)
     */
    private $identifier;

    /**
     * @var int
     *
     * @ORM\Column(name="badgeNumber", type="integer", nullable=true)
     */
    private $badgeNumber;



    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=2, nullable=true)
     */
    protected $locale;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param DeviceUserInterface $user
     *
     * @return Device
     */
    public function setUser(DeviceUserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return DeviceUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Device
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
     * Set token
     *
     * @param string $token
     *
     * @return Device
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return Device
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set badgeNumber
     *
     * @param integer $badgeNumber
     *
     * @return Device
     */
    public function setBadgeNumber($badgeNumber)
    {
        $this->badgeNumber = $badgeNumber;

        return $this;
    }

    /**
     * Get badgeNumber
     *
     * @return int
     */
    public function getBadgeNumber()
    {
        return $this->badgeNumber;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
