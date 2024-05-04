<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategoryController extends AbstractController
{

    private $categoryRepository;
    private $entityManager;
    public function __construct(CategoryRepository $categoryRepository, ManagerRegistry $doctrine)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/category', name: 'app_category')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function index(): Response
    {
        $categorys = $this->categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categorys' => $categorys,
        ]);
    }
    #[Route('/add/category', name: 'create_category')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function create(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'your category was add '
            );
            return $this->redirectToRoute('app_category');
        }
        return $this->renderForm('category/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/category/edit/{id}', name: 'category_edit')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function editProduct($id,  Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found for id ' . $id);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'your update was add '
            );
            return $this->redirectToRoute('app_category');
        }
        return $this->renderForm('category/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/category/delete/{id}', name: 'category_delete')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function delete($id,  Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }
        $this->entityManager->remove($category);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'was  remove '
        );
        return $this->redirectToRoute('app_category');
    }
}
