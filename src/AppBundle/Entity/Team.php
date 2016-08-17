<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Team
 */
class Team
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $uniqueName;

    /**
     * @var string
     */
    private $accessword;

    /**
     * @var string
     */
    private $adminword;

    /**
     * @var string
     */
    private $contacts;

    /**
     * @var boolean
     */
    private $isActive;


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
     * Set name
     *
     * @param string $name
     * @return Team
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
     * Set uniqueName
     *
     * @param string $uniqueName
     * @return Team
     */
    public function setUniqueName($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        return $this;
    }

    /**
     * Get uniqueName
     *
     * @return string
     */
    public function getUniqueName()
    {
        return $this->uniqueName;
    }

    /**
     * Set accessword
     *
     * @param string $accessword
     * @return Team
     */
    public function setAccessword($accessword)
    {
        $this->accessword = $accessword;

        return $this;
    }

    /**
     * Get accessword
     *
     * @return string
     */
    public function getAccessword()
    {
        return $this->accessword;
    }

    /**
     * Set adminword
     *
     * @param string $accessword
     * @return Team
     */
    public function setAdminword($adminword)
    {
        $this->adminword = $adminword;

        return $this;
    }

    /**
     * Get adminword
     *
     * @return string
     */
    public function getAdminword()
    {
        return $this->adminword;
    }

    /**
     * Set contacts
     *
     * @param string $contacts
     * @return Team
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return string
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Team
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
