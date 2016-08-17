<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Sheet;
use AppBundle\Entity\Bet;

class SheetController extends BaseController
{
    /**
     *
     */
    public function newAction($team, Request $req)
    {
        if (!$this->checkTeamAccess($team, $req)) {
            return $this->showTeamAccessPage($team);
        }

        $data = $this->getSheetData($team);
        $data['name'] = '';
        $data['email'] = '';
        $data['selectedContact'] = '';
        $data['hasPaid'] = false;
        $data['cmd'] = 'insert';
        return $this->render('AppBundle::sheet.html.twig', $data);
    }



    public function existingAction($team, $token, Request $req) {

        if (!$this->checkTeamAccess($team, $req)) {
            return $this->showAccessPage($team);
        }

        $db = $this->getDoctrine();
        /* @var $sheet \AppBundle\Entity\Sheet */
        $sheet = $db->getRepository('AppBundle:Sheet')->findOneByToken($token);

        $data = $this->getSheetData($team, $sheet->getSeasonId());

        $data['name'] = $sheet->getName();
        $data['email'] = $sheet->getEmail();
        $data['selectedContact'] = $sheet->getContact();
        $data['hasPaid'] = $sheet->getHasPaid();
        $data['token'] = $token;
        $data['message'] = $req->query->get('message');

        $bets = $db->getRepository('AppBundle:Bet')->findBySheetId($sheet->getId());
        $scores = array();
        /* @var $bet \AppBundle\Entity\Bet */
        foreach ($bets as $bet) {
            $data['score'][$bet->getGameId()]['home'] = $bet->getHomeScore();
            $data['score'][$bet->getGameId()]['away'] = $bet->getAwayScore();
        }
        $data['cmd'] = 'update';

        return $this->render('AppBundle::sheet.html.twig', $data);
    }

    public function saveAction($team, Request $req)
    {
        $post = $req->request;
        $name = $post->get('name');
        $seasonId = $post->get('seasonId');
        $contact = $post->get('contact');
        $email = $post->get('email');
        $gameHome = $post->get('gameHome');
        $gameAway = $post->get('gameAway');

        $token = $post->get('token');

        $em = $this->getDoctrine()->getManager();
        if ($token) {
            $sheet = $em->getRepository('AppBundle:Sheet')->findOneByToken($token);
            $sheet->setName($name);
            $sheet->setEmail($email);
            $sheet->setContact($contact);
        } else {
            $token = $this->createToken();
            $sheet = new Sheet();
            $sheet->setName($name);
            $sheet->setSeasonId($seasonId);
            $sheet->setEmail($email);
            $sheet->setContact($contact);
            $sheet->setToken($token);
            $sheet->setHasPaid(false);
            $sheet->setIsDisqualified(false);
            $em->persist($sheet);
        }
        $em->flush();
        $sheetId = $sheet->getId();

        foreach ($gameHome as $gameId => $scoreHome) {
            if (trim($scoreHome) !== '') {
                if (isset($gameAway[$gameId])) {
                    $scoreAway = trim($gameAway[$gameId]);
                    if ($scoreAway !== '') {
                        settype($scoreHome, 'integer');
                        settype($scoreAway, 'integer');

                        $bet = $em->getRepository('AppBundle:Bet')->createQueryBuilder('b')
                                ->where('b.sheetId = :sheetId AND b.gameId = :gameId')
                                ->setParameter('sheetId', $sheetId)
                                ->setParameter('gameId', $gameId)
                                ->getQuery()
                                ->getOneOrNullResult();

                        if ($bet) {
                            $bet->setHomeScore($scoreHome);
                            $bet->setAwayScore($scoreAway);
                        } else {
                            $bet = new Bet();
                            $bet->setSheetId($sheetId);
                            $bet->setGameId($gameId);
                            $bet->setHomeScore($scoreHome);
                            $bet->setAwayScore($scoreAway);
                            $em->persist($bet);
                        }
                        $em->flush();
                    }
                }
            }
        }

        return $this->redirectToRoute('existingSheet', array('team' => $team, 'token' => $token, 'message' => 'token'));
    }



    private function getSheetData($team, $seasonId = false)
    {
        $data = array();

        $db = $this->getDoctrine();

        /* @var $team \AppBundle\Entity\Team */
        $team = $db->getRepository('AppBundle:Team')->findOneByUniqueName($team);
        $data['teamUniqueName'] = $team->getUniqueName();
        $data['teamName'] = $team->getName();
        $data['contacts'] = explode('|', $team->getContacts());

        if (!$seasonId) {
            $season = $db->getRepository('AppBundle:Season')->createQueryBuilder('s')
                    ->where('s.teamId = :teamId AND s.isCurrent = 1')
                    ->setParameter('teamId', $team->getId())
                    ->getQuery()->setMaxResults(1)->getOneOrNullResult();
        } else {
            $season = $db->getRepository('AppBundle:Season')->findOneById($seasonId);
        }
        $data['seasonName'] = $season->getName();
        $data['seasonId'] = $season->getId();


        $gamesDb = $db->getRepository('AppBundle:Game')->findBy(array('seasonId' => $season->getId()), array('date' => 'ASC'));
        $data['games'] = $this->doctrineToArray($gamesDb, array('id', 'date', 'home', 'away', 'date', 'homeNotes', 'awayNotes'));

        $today = new \DateTime(date('Y-m-d') . ' 00:00:00');
        foreach ($data['games'] as $i => $g) {
            if ($g['date'] <= $today) {
                $data['games'][$i]['locked'] = true;
            } else {
                $data['games'][$i]['locked'] = false;
            }
        }

        return $data;
    }





    private function createToken()
    {

        $letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9');

        $uniqueTokenIsCreated = false;
        $loopCount = 0;
        while (!$uniqueTokenIsCreated) {
            $token = '';
            for ($i = 0; $i < 5; $i++) {
                $token .= $letters[array_rand($letters)];
            }

            $db = $this->getDoctrine();
            $existingToken = $db->getRepository('AppBundle:Sheet')->findOneByToken($token);

            if (!$existingToken) {
                $uniqueTokenIsCreated = true;
            }

            $loopCount++;
            if ($loopCount > 10) {
                throw new \Exception('Cannot create token within 10 tries. Maybe we should increase the token length.');
            }
        }

        return $token;
    }
}
