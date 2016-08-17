<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;



class BaseController extends Controller
{
    protected $hasTeamAccess = false;
    protected $hasAdminAccess;



    /**
     *
     * @param type $results
     * @param type $fields
     * @return type
     */
    protected function doctrineToArray($results, $fields)
    {
        $re = array();
        foreach ($results as $result) {
            $row = array();

            foreach ($fields as $f) {
                $func = 'get' . ucfirst($f);
                $row[$f] = $result->{$func}();
            }

            $re[] = $row;
        }
        return $re;
    }


    protected function checkTeamAccess($team, Request $req) {
        if ($this->hasTeamAccess) {
            return true;
        } else {
            $session = $req->getSession();
            return ($session->get('hasTeamAccess') ? true : false);
        }
    }


    protected function showTeamAccessPage($team) {
        return $this->render('AppBundle::teamAccess.html.twig', array('team' => $team));
    }


    protected function checkAdminAccess($team, Request $req) {
        if ($this->hasTeamAccess) {
            return true;
        } else {
            $session = $req->getSession();
            return ($session->get('hasAdminAccess') ? true : false);
        }
    }

    protected function showAdminAccessPage($team) {
        return $this->render('AppBundle::adminAccess.html.twig', array('team' => $team));
    }


}
