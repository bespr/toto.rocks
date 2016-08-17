<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomeController extends BaseController
{

    protected $hasTeamAccess = true;

    /**
     *
     */
    public function indexAction()
    {
        $db = $this->getDoctrine();

        $data = array();
        $data['teams'] = $db->getRepository('AppBundle:Team')->findByIsActive(true);

        return $this->render('AppBundle::home.html.twig', $data);
    }
}
