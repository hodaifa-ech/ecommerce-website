<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    public function __construct(ManagerRegistry $doctrine, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/users', name: 'users_list')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function index(): Response
    {

        $users = $this->userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/update/user/{id}', name: 'user_delete')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function delete_order($id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found for id ' . $id);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'was  remove '
        );
        return $this->redirectToRoute('users_list');
    }
}
