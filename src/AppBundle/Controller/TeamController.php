<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


use AppBundle\Entity\Sheet;
use AppBundle\Entity\Bet;

class TeamController extends BaseController
{

    const POINTS_EXACT = 10;
    const POINTS_TENDENCY_WINNER_DIFF = 6;
    const POINTS_TENDENCY_DRAW = 5;
    const POINTS_TENDENCY_WINNER_ALMOST_DIFF = 4;
    const POINTS_TENDENCY_WINNER = 3;
    const POINTS_ALMOST_TENDENCY= 1;
    const POINTS_FAILURE = 0;

    private $seasonId;

    private $games;

    private $countGamesPlayed = 0;
    private $countGamesNotPlayed = 0;




    /**
     *
     */
    public function indexAction($team, Request $req)
    {
        if (!$this->checkTeamAccess($team, $req)) {
            return $this->showTeamAccessPage($team);
        }

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $team = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $data['teamUniqueName'] = $team->getUniqueName();
        $data['teamName'] = $team->getName();
        $data['contacts'] = explode(',', $team->getContacts());

        $season = $db->getRepository('AppBundle:Season')->createQueryBuilder('s')
                    ->where('s.teamId = :teamId AND s.isCurrent = 1')
                    ->setParameter('teamId', $team->getId())
                    ->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if (!$season) {
            return $this->render('AppBundle::teamNoSeasonFound.html.twig', $data);
        }

        $data['hasSeason'] = true;

        $data['seasonName'] = $season->getName();
        $this->seasonId = $season->getId();
        $data['seasonId'] = $this->seasonId;

        $gamesDb = $db->getRepository('AppBundle:Game')->findBy(array('seasonId' => $season->getId()), array('date' => 'ASC'));
        $this->games = $this->doctrineToArray($gamesDb, array('id', 'date', 'home', 'away', 'date', 'homeScore', 'awayScore'));
        $this->enhanceGameStatus($this->games);
        $data['games'] = $this->games;
        $data['countGamesPlayed'] = $this->countGamesPlayed;
        $data['countGamesNotPlayed'] = $this->countGamesNotPlayed;

        $data['participants'] = $this->getParticipants();

        return $this->render('AppBundle::team.html.twig', $data);
    }



    public function accessAction($team, Request $req) {
        $session = $req->getSession();

        $post = $req->request;
        $enteredAccessword = $post->get('accessword');
        $path = $post->get('path');

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $team = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $correctAccessword = $team->getAccessword();

        if ($enteredAccessword == $correctAccessword) {
            $session->set('hasTeamAccess', true);
        } else {
            $session->set('hasTeamAccess', false);
        }

        return $this->redirect($path);
    }


    private function enhanceGameStatus(&$games) {
        foreach ($games as $i => $game) {
            if (is_null($game['homeScore']) || is_null($game['homeScore'])) {
                $games[$i]['isPlayed'] = false;
                $this->countGamesNotPlayed++;
            } else {
                $games[$i]['isPlayed'] = true;
                $this->countGamesPlayed++;
            }
        }
    }



    private function getParticipants()
    {
        $re = array('paid' => array(), 'notPaid' => array());

        $sheets = $this->getDoctrine()->getRepository('AppBundle:Sheet')->findBy(array('seasonId' => $this->seasonId, 'hasPaid' => true, 'isDisqualified' => false), array('name' => 'ASC'));
        /* @var $sheet \AppBundle\Entity\Sheet */
        foreach ($sheets as $sheet) {
            $betInfo = $this->addBets($sheet->getId());
            $re['paid'][] = array(
                'name' => $sheet->getName(),
                'contact' => $sheet->getContact(),
                'bets' => $betInfo['bets'],
                'points' => $betInfo['points']
            );
        }
        usort($re['paid'], function($a, $b) {
            if ($a['points'] === $b['points']) {
                return 0;
            }
            return $a['points'] > $b['points'] ? -1 : 1;
        });

        $formerPoints = false;
        $formerRank = false;
        foreach ($re['paid'] as $i => $p) {
            if ($p['points'] !== $formerPoints) {
                $formerRank = ($i + 1);
                $formerPoints = $p['points'];
            }
            $re['paid'][$i]['rank'] = $formerRank;
        }


        $sheets = $this->getDoctrine()->getRepository('AppBundle:Sheet')->findBy(array('seasonId' => $this->seasonId, 'hasPaid' => false, 'isDisqualified' => false), array('name' => 'ASC'));
        /* @var $sheet \AppBundle\Entity\Sheet */
        foreach ($sheets as $sheet) {
            $re['notPaid'][] = array(
                'name' => $sheet->getName(),
                'contact' => $sheet->getContact()
            );
        }

        return $re;
    }



    private function addBets($sheetId) {

        $bets = array();
        $points = 0;

        foreach ($this->games as $game) {
            if ($game['isPlayed']) {
                $betDb = $this->getDoctrine()->getRepository('AppBundle:Bet')->findOneBy(array('gameId' => $game['id'], 'sheetId' => $sheetId));
                if ($betDb) {
                    $b = array(
                        'homeScore' => $betDb->getHomeScore(),
                        'awayScore' => $betDb->getAwayScore(),
                        'points' => 0
                    );
                    $p = $this->calculatePoints($game['homeScore'], $game['awayScore'], $b['homeScore'], $b['awayScore']);
                    $b['points'] = $p;
                    $points += $p;
                    $bets[$game['id']] = $b;
                } else {
                    $bets[$game['id']] = array(
                        'homeScore' => null,
                        'awayScore' => null,
                        'points' => 0
                    );
                }
            }
        }

        return array('points' => $points, 'bets' => $bets);

    }


    private function calculatePoints($gh, $ga, $bh, $ba) {

// exact
        if ($gh == $bh && $ga == $ba) {
            return self::POINTS_EXACT;
        } else {

            // gt = Tendency Game (0 = Home, 1 = Draw, 2 = Away)
            // gd = Diffenerence Game
            $gt = 1;
            if ($gh > $ga) {
                $gt = 0;
            } elseif ($gh < $ga) {
                $gt = 2;
            }
            $gd = abs($gh - $ga);

            // bt = Tendency Bet (0 = Home, 1 = Draw, 2 = Away)
            // bd = Diffenerence Bet
            $bt = 1;
            if ($bh > $ba) {
                $bt = 0;
            } elseif ($bh < $ba) {
                $bt = 2;
            }
            $bd = abs($bh - $ba);

            // Same tendency
            if ($gt === $bt) {

                if ($gt === 1) { // Draw
                    return self::POINTS_TENDENCY_DRAW;
                } else {
                    if ($gd === $bd) {
                        return self::POINTS_TENDENCY_WINNER_DIFF;
                    } elseif (abs($gd - $bd) === 1) {
                        return self::POINTS_TENDENCY_WINNER_ALMOST_DIFF;
                    } else {
                        return self::POINTS_TENDENCY_WINNER;
                    }
                }
            } else { // No tendendy

                if (abs($gt - $bt) === 1) {
                    return self::POINTS_ALMOST_TENDENCY;
                } else {
                    return self::POINTS_FAILURE;
                }
            }
        }
    }

}
