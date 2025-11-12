<?php

namespace Shaygan\AffiliateBundle\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Shaygan\AffiliateBundle\Entity\ReferralRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/affiliate')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly ReferralRegistrationRepository $referralRegistrationRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    #[Route('/', name: 'shaygan_affiliate_user_index')]
    public function indexAction(Request $request): Response
    {
        if ($this->getUser()) {
            $query = $this->referralRegistrationRepository->getRegistrationByUser($this->getUser());
            $pagination = $this->paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                10
            );

            return $this->render('@ShayganAffiliate/user/index.html.twig', ['pagination' => $pagination]);
        }

        return $this->render('@ShayganAffiliate/user/index.html.twig');
    }

    #[Route('/report', name: 'shaygan_affiliate_user_report')]
    #[IsGranted('ROLE_USER')]
    public function reportAction(Request $request): Response
    {
        $query = $this->referralRegistrationRepository->getRegistrationByUser($this->getUser());
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('@ShayganAffiliate/user/report.html.twig', ['pagination' => $pagination]);
    }
}
