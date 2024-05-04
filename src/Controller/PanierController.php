<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Panier;
use App\Repository\OrderRepository;
use App\Repository\PanierRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    private $orderRepository;
    private $entityManager;
    private $panierRepository;
    public function __construct(ManagerRegistry $doctrine, OrderRepository $orderRepository, PanierRepository $panierRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->panierRepository = $panierRepository;
        $this->entityManager = $doctrine->getManager();
    }



    #[Route('/store/panier{id}', name: 'panier_store')]

    public function store($id, ProductRepository $productRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }

        $panier = new Panier();
        $panier->setPname($product->getName());
        $panier->setPrice($product->getPrice());

        $panier->setUser($this->getUser());
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'you are add to card '
        );
        return $this->redirectToRoute('user_panier');
    }

    #[Route('/user/panier', name: 'user_panier')]

    public function index(PanierRepository $panierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $userPanier = $panierRepository->findBy(['user' => $this->getUser()]);


        // Initialiser un tableau pour stocker les quantités de chaque produit
        $productQuantities = [];


        // Calculer la quantité de chaque produit commandé par l'utilisateur
        foreach ($userPanier as $panier) {
            $productName = $panier->getPname();
            if (!isset($productQuantities[$productName])) {
                $productQuantities[$productName] = 1;
            } else {
                $productQuantities[$productName]++;
            }
        }



        return $this->render('panier/index.html.twig', [
            'user' => $userPanier,
            'productQuantities' => $productQuantities,

        ]);
    }
    #[Route('/panier/clear', name: 'panier_clear')]
    public function clearPanier()
    {
        // Ensure user is logged in
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Get the current user's cart items
        $userPanier = $this->panierRepository->findBy(['user' => $this->getUser()]);

        // Remove each item from the cart
        foreach ($userPanier as $panier) {
            $this->entityManager->remove($panier);
        }

        // Flush changes to the database
        $this->entityManager->flush();
    }
}
