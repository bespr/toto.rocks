<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 */
class Game
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
    private $home;

    /**
     * @var string
     */
    private $homeNotes;

    /**
     * @var string
     */
    private $away;

    /**
     * @var string
     */
    private $awayNotes;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $homeScore;

    /**
     * @var integer
     */
    private $awayScore;


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
     * @return Game
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
     * Set home
     *
     * @param string $home
     * @return Game
     */
    public function setHome($home)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * Get home
     *
     * @return string 
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Set homeNotes
     *
     * @param string $homeNotes
     * @return Game
     */
    public function setHomeNotes($homeNotes)
    {
        $this->homeNotes = $homeNotes;

        return $this;
    }

    /**
     * Get homeNotes
     *
     * @return string 
     */
    public function getHomeNotes()
    {
        return $this->homeNotes;
    }

    /**
     * Set away
     *
     * @param string $away
     * @return Game
     */
    public function setAway($away)
    {
        $this->away = $away;

        return $this;
    }

    /**
     * Get away
     *
     * @return string 
     */
    public function getAway()
    {
        return $this->away;
    }

    /**
     * Set awayNotes
     *
     * @param string $awayNotes
     * @return Game
     */
    public function setAwayNotes($awayNotes)
    {
        $this->awayNotes = $awayNotes;

        return $this;
    }

    /**
     * Get awayNotes
     *
     * @return string 
     */
    public function getAwayNotes()
    {
        return $this->awayNotes;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Game
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set homeScore
     *
     * @param integer $homeScore
     * @return Game
     */
    public function setHomeScore($homeScore)
    {
        $this->homeScore = $homeScore;

        return $this;
    }

    /**
     * Get homeScore
     *
     * @return integer 
     */
    public function getHomeScore()
    {
        return $this->homeScore;
    }

    /**
     * Set awayScore
     *
     * @param integer $awayScore
     * @return Game
     */
    public function setAwayScore($awayScore)
    {
        $this->awayScore = $awayScore;

        return $this;
    }

    /**
     * Get awayScore
     *
     * @return integer 
     */
    public function getAwayScore()
    {
        return $this->awayScore;
    }
}
