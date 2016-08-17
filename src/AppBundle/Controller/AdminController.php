<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Season;



class AdminController extends BaseController
{

    protected $hasTeamAccess = false;

    /**
     *
     */
    public function indexAction($team, Request $req)
    {
        if (!$this->checkAdminAccess($team, $req)) {
            return $this->showAdminAccessPage($team);
        }

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $team = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $data['teamUniqueName'] = $team->getUniqueName();
        $data['teamName'] = $team->getName();
        $data['contacts'] = explode(',', $team->getContacts());
        $data['contactsNewLine'] = str_replace("|", "\n", $team->getContacts());

        $season = $db->getRepository('AppBundle:Season')->createQueryBuilder('s')
                    ->where('s.teamId = :teamId AND s.isCurrent = 1')
                    ->setParameter('teamId', $team->getId())
                    ->getQuery()->setMaxResults(1)->getOneOrNullResult();
        if (!$season) {
            return $this->redirectToRoute('adminSeasons', array('team' => $data['teamUniqueName']));
        }

        $data['seasonName'] = $season->getName();
        $this->seasonId = $season->getId();
        $data['seasonId'] = $this->seasonId;

        $gamesDb = $db->getRepository('AppBundle:Game')->findBy(array('seasonId' => $season->getId()), array('date' => 'ASC'));
        $this->games = $this->doctrineToArray($gamesDb, array('id', 'date', 'home', 'away', 'date', 'homeScore', 'awayScore'));
        //$this->enhanceGameStatus($this->games);
        $data['games'] = $this->games;


        $data['sheets'] = array('paid' => array(), 'notPaid' => array());
        $sheetsDb = $db->getRepository('AppBundle:Sheet')->findBy(array('seasonId' => $season->getId(), 'hasPaid' => true), array('name' => 'ASC'));
        foreach ($sheetsDb as $sheet) {
            $data['sheets']['paid'][] = array(
                'id' => $sheet->getId(),
                'name' => $sheet->getName(),
                'contact' => $sheet->getContact()
            );
        }

        $sheetsDb = $db->getRepository('AppBundle:Sheet')->findBy(array('seasonId' => $season->getId(), 'hasPaid' => false), array('name' => 'ASC'));
        foreach ($sheetsDb as $sheet) {
            $data['sheets']['notPaid'][] = array(
                'id' => $sheet->getId(),
                'name' => $sheet->getName(),
                'contact' => $sheet->getContact()
            );
        }

        return $this->render('AppBundle::admin.html.twig', $data);
    }


    /**
     * Action Seasons
     *
     * @param type $team
     * @param Request $req
     * @return type
     */
    public function seasonsAction($team, Request $req) {
        if (!$this->checkAdminAccess($team, $req)) {
            return $this->showAdminAccessPage($team);
        }

        $data = array();

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $data['team'] = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);

        /* @var $seasons \AppBundle\Entity\Season */
        $seasons = $db->getRepository('AppBundle:Season')->findByTeamId($data['team']->getId());

        $seasonArray = array();
        foreach ($seasons as $i => $season) {
            $games = $db->getRepository('AppBundle:Game')->findBySeasonId($season->getId());

            $seasonArray[] = array(
                'id' => $season->getId(),
                'name' => $season->getName(),
                'isCurrent' => $season->getIsCurrent(),
                'numOfGames' => count($games),
            );

        }
        $data['seasons'] = $seasonArray;

        return $this->render('AppBundle::adminSeasons.html.twig', $data);
    }


    public function seasonsPostAction($team, Request $req)
    {
        $post = $req->request;
        $cmd = $post->get('cmd');

        $db = $this->getDoctrine();
        $teamEntry = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $em = $db->getManager();


        if ($cmd == 'addSeason') {
            $seasonName = $post->get('newSeason');
            $this->removeCurrentForAllSeasons($teamEntry->getId());

            $season = new Season();
            $season->setName($seasonName);
            $season->setIsCurrent(true);
            $season->setTeamId($teamEntry->getId());
            $em->persist($season);
        } elseif ($cmd == 'setActiveSeason') {
            $this->removeCurrentForAllSeasons($teamEntry->getId());

            $seasonId = $post->get('seasonId');
            /* @var $season \AppBundle\Entity\Season */
            $season = $em->getRepository('AppBundle:Season')->findOneById($seasonId);
            $season->setIsCurrent(true);
            $em->persist($season);
        }

        $em->flush();

        return $this->redirectToRoute('adminSeasons', array('team' => $team));

    }


    /**
     * Post Action
     *
     * @param type $team
     * @param Request $req
     * @return type
     */
    public function postAction($team, Request $req)
    {
        $post = $req->request;
        $cmd = $post->get('cmd');
        $sheetId = $post->get('sheetId');

        $em = $this->getDoctrine()->getManager();

        if ($cmd === 'markAsPaid') {
            /* @var $sheet \AppBundle\Entity\Sheet */
            $sheet = $em->getRepository('AppBundle:Sheet')->findOneById($sheetId);
            $sheet->setHasPaid(true);
        } elseif ($cmd === 'markAsNotPaid') {
            /* @var $sheet \AppBundle\Entity\Sheet */
            $sheet = $em->getRepository('AppBundle:Sheet')->findOneById($sheetId);
            $sheet->setHasPaid(false);
        } elseif ($cmd === 'saveResult') {
            $gameId = $post->get('gameId');
            $homeScore = $post->get('homeScore');
            $awayScore = $post->get('awayScore');
            /* @var $game \AppBundle\Entity\Game */
            $game = $em->getRepository('AppBundle:Game')->findOneById($gameId);
            if (trim($homeScore) === '' || trim($awayScore) === '') {
                $game->setHomeScore(null);
                $game->setAwayScore(null);
            } else {
                $game->setHomeScore($homeScore);
                $game->setAwayScore($awayScore);
            }
        } elseif ($cmd === 'updateContacts') {
            $contactsStr = $post->get('contacts');
            $contactsArr = explode("\n", $contactsStr);
            sort($contactsArr);

            /* @var $teamDb \AppBundle\Entity\Team */
            $teamDb = $em->getRepository('AppBundle:Team')->findOneByUniqueName($team);            
            $teamDb->setContacts(implode('|', $contactsArr));
            $em->persist($teamDb);
        }

        $em->flush();

        return $this->redirectToRoute('admin', array('team' => $team));
    }


    /**
     * Action Games
     *
     * @param type $team
     * @param Request $req
     * @return type
     */
    public function gamesAction($team, Request $req) {
        if (!$this->checkAdminAccess($team, $req)) {
            return $this->showAdminAccessPage($team);
        }

        $data = array();

        $db = $this->getDoctrine();
        
        /* @var $team \AppBundle\Entity\Team */
        $teamDb = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $activeSeason = $db->getRepository('AppBundle:Season')->findOneBy(array('teamId' => $teamDb->getId(), 'isCurrent' => true));
        $games = $db->getRepository('AppBundle:Game')->findBySeasonId($activeSeason->getId(), array('date' => 'ASC'));
        
        $data = array(
            'team' => $teamDb,
            'season' => $activeSeason,
            'games' => $games
        );
                
        return $this->render('AppBundle::adminGames.html.twig', $data);
    }
    
    
    
    public function gamesPostAction($team, Request $req)
    {
        $post = $req->request;
        $cmd = $post->get('cmd');

        $em = $this->getDoctrine()->getManager();

        if ($cmd === 'addGame') {
            $game = new \AppBundle\Entity\Game();
            $game->setDate(\DateTime::createFromFormat('d.m.Y', $post->get('date')));
            $game->setHome($post->get('home'));
            $game->setAway($post->get('away'));
            $game->setHomeNotes($post->get('homeNotes'));
            $game->setAwayNotes($post->get('awayNotes'));            
            $game->setSeasonId($post->get('seasonId'));
            $em->persist($game);
        } elseif ($cmd === 'editGame') {
            $game = $em->getRepository('AppBundle:Game')->findOneById($post->get('gameId'));
            $game->setDate($post->get('date'));
            $game->setHome($post->get('home'));
            $game->setAway($post->get('away'));
            $game->setHomeNotes($post->get('homeNotes'));
            $game->setAwayNotes($post->get('awayNotes'));     
            $em->persist($game);
        } 
        $em->flush();

        return $this->redirectToRoute('adminGames', array('team' => $team));
    }    




    public function accessAction($team, Request $req) {
        $session = $req->getSession();

        $post = $req->request;
        $enteredAdminword = $post->get('adminword');
        $path = $post->get('path');

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $team = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $correctAdminword = $team->getAdminword();

        if ($enteredAdminword == $correctAdminword) {
            $session->set('hasAdminAccess', true);
        } else {
            $session->set('hasAdminAccess', false);
        }

        return $this->redirect($path);
    }



    /**
     *
     */
    private function removeCurrentForAllSeasons($teamId) {
        $em = $this->getDoctrine()->getManager();

        $seasons = $em->getRepository('AppBundle:Season')->findByTeamId($teamId);
        foreach ($seasons as $season) {
            $season->setIsCurrent(false);
            $em->persist($season);
        }
        $em->flush();
    }




}
