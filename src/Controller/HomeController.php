<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PanierRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $categoryRepository;

    private $productRepository;
    private $entityManager;
    private $panierRepository;
    public function __construct(ProductRepository $productRepository, ManagerRegistry $doctrine, CategoryRepository $categoryRepository, PanierRepository $panierRepository)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->panierRepository = $panierRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $categorys = $this->categoryRepository->findAll();
        $panier = $this->panierRepository->findBy(['user' => $this->getUser()]);
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categorys' => $categorys,
            'panier' => $panier,
        ]);
    }

    #[Route('/product/{id}', name: 'product_category')]
    public function categoryProduct($id): Response
    {
        $category = $this->categoryRepository->find($id);
        $categorys = $this->categoryRepository->findAll();
        $panier = $this->panierRepository->findBy(['user' => $this->getUser()]);
        return $this->render('home/index.html.twig', [
            'products' => $category->getProducts(),
            'categorys' => $categorys,
            'panier' => $panier,
        ]);
    }
}
