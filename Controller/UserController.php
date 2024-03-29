<?php

namespace Shaygan\AffiliateBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Shaygan\AffiliateBundle\Entity\ReferralRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/affiliate")
 */
class UserController extends AbstractController
{
    private $referralRegistrationRepository;
    public function __construct(ReferralRegistrationRepository $referralRegistrationRepository)
    {
        $this->referralRegistrationRepository = $referralRegistrationRepository;
    }

    /**
     * @Route("/", name="shaygan_affiliate_user_index")
     */
    public function indexAction(Request $request, ReferralRegistrationRepository $referralRegistrationRepository)
    {
        if ($this->getUser()) {
            $query = $this->referralRegistrationRepository->getRegistrationByUser($this->getUser());
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1)/* page number */, 10/* limit per page */
            );

            return $this->render('@ShayganAffiliate/user/index.html.twig', ['pagination' => $pagination]);
        } else {
            return $this->render('@ShayganAffiliate/user/index.html.twig');
        }
    }

    /**
     * @Route("/report", name="shaygan_affiliate_user_report")
     * @IsGranted("ROLE_USER")
     */
    public function reportAction(Request $request)
    {
        $query = $this->referralRegistrationRepository->getRegistrationByUser($this->getUser());
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->getInt('page', 1)/* page number */, 10/* limit per page */
        );

        return $this->render('@ShayganAffiliate/user/report.html.twig', ['pagination' => $pagination]);
    }

    private function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

}
