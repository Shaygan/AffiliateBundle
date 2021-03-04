<?php

namespace Shaygan\AffiliateBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/affiliate")
 */
class UserController extends AbstractController {

    /**
     * @Route("/", name="shaygan_affiliate_user_index")
     * @Template()
     */
    public function indexAction(Request $request) {
        if ($this->getUser()) {
            $query = $this->getEm()->getRepository('ShayganAffiliateBundle:ReferralRegistration')->getRegistrationByUser($this->getUser());
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                    $query, $request->query->getInt('page', 1)/* page number */, 10/* limit per page */
            );


            return array('pagination' => $pagination);
        } else {
            return array();
        }
    }

    /**
     * @Route("/report", name="shaygan_affiliate_user_report")
     * @Template()
     * @Security("has_role('ROLE_USER')");
     */
    public function reportAction(Request $request) {
        $query = $this->getEm()->getRepository('ShayganAffiliateBundle:ReferralRegistration')->getRegistrationByUser($this->getUser());
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1)/* page number */, 10/* limit per page */
        );


        return array('pagination' => $pagination);
    }

    private function getEm() {
        return $this->getDoctrine()->getManager();
    }

}
