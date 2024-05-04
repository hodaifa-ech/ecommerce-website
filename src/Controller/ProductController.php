<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProductController extends AbstractController
{
    private $productRepository;
    private $entityManager;
    public function __construct(ProductRepository $productRepository, ManagerRegistry $doctrine)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/product', name: 'app_product')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/store/product', name: 'product_store')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function store(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'your Product was add '
            );
            return $this->redirectToRoute('app_product');
        }
        return $this->renderForm('product/create.html.twig', [
            'form' => $form,
        ]);
    }



    #[Route('/product/detail/{id}', name: 'show_product')]
    public function show($id): Response
    {
        $product = $this->productRepository->find($id);
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }



    #[Route('/product/edit/{id}', name: 'product_edit')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function editProduct($id,  Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }

            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'your update was add '
            );
            return $this->redirectToRoute('app_product');
        }
        return $this->renderForm('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function delete($id,  Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }
        $fileSystem = new Filesystem();
        $imagePath = './uploads/' . $product->getImage();
        if ($fileSystem->exists($imagePath)) {
            $fileSystem->remove($imagePath);
        }
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'was  remove was add '
        );
        return $this->redirectToRoute('app_product');
    }
}
