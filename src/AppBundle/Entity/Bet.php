<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bet
 */
class Bet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sheetId;

    /**
     * @var integer
     */
    private $gameId;

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
     * Set sheetId
     *
     * @param integer $sheetId
     * @return Bet
     */
    public function setSheetId($sheetId)
    {
        $this->sheetId = $sheetId;

        return $this;
    }

    /**
     * Get sheetId
     *
     * @return integer 
     */
    public function getSheetId()
    {
        return $this->sheetId;
    }

    /**
     * Set gameId
     *
     * @param integer $gameId
     * @return Bet
     */
    public function setGameId($gameId)
    {
        $this->gameId = $gameId;

        return $this;
    }

    /**
     * Get gameId
     *
     * @return integer 
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Set homeScore
     *
     * @param integer $homeScore
     * @return Bet
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
     * @return Bet
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
