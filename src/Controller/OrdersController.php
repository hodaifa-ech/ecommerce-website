<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Panier;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\PanierRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class OrdersController extends AbstractController
{


    private $orderRepository;
    private $entityManager;
    public function __construct(ManagerRegistry $doctrine, OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;

        $this->entityManager = $doctrine->getManager();
    }


    #[Route('/orders', name: 'orders_list')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function index(): Response
    {

        $orders = $this->orderRepository->findAll();
        return $this->render('orders/index.html.twig', [
            'orders' => $orders,
        ]);
    }






    #[Route('/store/order', name: 'order_store')]

    public function store(PanierRepository $panierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $panierRepository->findBy(['user' => $this->getUser()]);

        foreach ($panier as $orders) {
            $order = new Order();
            $order->setPname($orders->getPname());
            $order->setPrice($orders->getPrice());
            $order->setStatus('no paiement');
            $order->setUser($this->getUser());
            $order->setIsPaye(0);
            $this->entityManager->persist($order);
        }
        $this->entityManager->flush();
        // $panierRepository->clearPanier($this->getUser());to clrear the panier
        $this->addFlash(
            'success',
            'your order was save '
        );
        return $this->redirectToRoute('user_order_list');
    }

    #[Route('/user/orders', name: 'user_order_list')]

    public function user_orders(OrderRepository $orderRepository): Response
    {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }


        $userOrders = $orderRepository->findBy(['user' => $this->getUser()]);


        // Initialiser un tableau pour stocker les quantités de chaque produit
        $productQuantities = [];
        $orderExist = [];
        $productQuantitiesnotPayed = [];
        $orderExistnotPayed = [];

        // Calculer la quantité de chaque produit commandé par l'utilisateur
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == 1) {

                $productName = $order->getPname();
                if (!isset($productQuantities[$productName])) {
                    $productQuantities[$productName] = 1;
                } else {
                    $productQuantities[$productName]++;
                }
            }
        }
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == 1) {
                $productName = $order->getPname();
                if (!isset($orderExist[$productName])) {
                    $orderExist[$productName] = 1;
                } else {
                    $orderExist[$productName]++;
                }
            }
        }
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == 0) {

                $productName = $order->getPname();
                if (!isset($productQuantitiesnotPayed[$productName])) {
                    $productQuantitiesnotPayed[$productName] = 1;
                } else {
                    $productQuantitiesnotPayed[$productName]++;
                }
            }
        }
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == 0) {
                $productName = $order->getPname();
                if (!isset($orderExistnotPayed[$productName])) {
                    $orderExistnotPayed[$productName] = 1;
                } else {
                    $orderExistnotPayed[$productName]++;
                }
            }
        }


        return $this->render('orders/user.html.twig', [
            'user' => $this->getUser(),
            'productQuantities' => $productQuantities,
            'orderExist' => $orderExist,
            'orderExistnotPayed' => $orderExistnotPayed,
            'productQuantitiesnotPayed' => $productQuantitiesnotPayed,
        ]);
    }
    #[Route('/update/order/{id}/{status}', name: 'order_status_update')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function updateOrderStatus($id, OrderRepository $orderRepository, $status): Response
    {
        $order = new Order();
        $order = $orderRepository->find($id);
        $order->setStatus($status);
        if (!$order) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'your order was save '
        );
        return $this->redirectToRoute('orders_list');
    }
    #[Route('/update/order/{id}', name: 'order_delete')]
    /**
     * @IsGranted("ROLE_ADMIN",statusCode=404,message="Page not found")
     */
    public function delete_order($id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);
        if (!$order) {
            throw $this->createNotFoundException('Product not found for id ' . $id);
        }
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'was  remove '
        );
        return $this->redirectToRoute('orders_list');
    }
}
