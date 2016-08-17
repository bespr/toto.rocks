<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sheet
 */
class Sheet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $seasonId;

    /**
     * @var string
     */
    private $contact;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $token;

    /**
     * @var boolean
     */
    private $hasPaid;

    /**
     * @var boolean
     */
    private $isDisqualified;


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
     * Set seasonId
     *
     * @param integer $seasonId
     * @return Sheet
     */
    public function setSeasonId($seasonId)
    {
        $this->seasonId = $seasonId;

        return $this;
    }

    /**
     * Get seasonId
     *
     * @return integer 
     */
    public function getSeasonId()
    {
        return $this->seasonId;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return Sheet
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Sheet
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Sheet
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Sheet
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
     * Set hasPaid
     *
     * @param boolean $hasPaid
     * @return Sheet
     */
    public function setHasPaid($hasPaid)
    {
        $this->hasPaid = $hasPaid;

        return $this;
    }

    /**
     * Get hasPaid
     *
     * @return boolean 
     */
    public function getHasPaid()
    {
        return $this->hasPaid;
    }

    /**
     * Set isDisqualified
     *
     * @param boolean $isDisqualified
     * @return Sheet
     */
    public function setIsDisqualified($isDisqualified)
    {
        $this->isDisqualified = $isDisqualified;

        return $this;
    }

    /**
     * Get isDisqualified
     *
     * @return boolean 
     */
    public function getIsDisqualified()
    {
        return $this->isDisqualified;
    }
}
