<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Doctrine\Persistence\ManagerRegistry;
use Generator;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaiementController extends AbstractController
{

    private $orderRepository;
    private $entityManager;
    private UrlGeneratorInterface $generator;
    private $panierrepositiry;
    private $productRepository;
    public function __construct(ManagerRegistry $doctrine, OrderRepository $orderRepository, UrlGeneratorInterface $generator, PanierController $panierrepositiry, ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->generator = $generator;
        $this->panierrepositiry = $panierrepositiry;
        $this->productRepository = $productRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/paiement', name: 'app_paiement')]
    public function index(): Response
    {
        return $this->render('paiement/index.html.twig', [
            'controller_name' => 'PaiementController',
        ]);
    }

    /**
     * @return PayPalHttpClient
     */
    public function getClientId(): PayPalHttpClient
    {
        $clientId = "AQwmcAMmQBdf9ooqlmn1EZ8wkvb-t5kZ1cu86QzSYip7aXI-2HW3fEy23GVExjPXnOFLspTvNgZaJ_pg";
        $clientScrete = "EMJbP-gyvnuhemD6m-EVpDn_m3P4WaYVKmx5o6FbaJOpzgQrQgL-6Fthhb8brCTJ4hO3e98bqFBXG2Yw";
        $environement = new SandboxEnvironment($clientId, $clientScrete);
        return new PayPalHttpClient($environement);
    }
    #[Route('/paiement/paypal', name: 'paiement_paiement'/*, methods: ['POST']*/)]
    public function createSessionPaypal(OrderRepository $orderRepository): RedirectResponse
    {
        $userOrders = $orderRepository->findBy(['user' => $this->getUser()]);


        // Initialiser un tableau pour stocker les quantités de chaque produit
        $productQuantities = [];
        $exist = [];
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == false) {
                $productName = $order->getPname();
                if (!isset($exist[$productName])) {
                    $exist[$productName] = $order;
                }
            }
        }

        // Calculer la quantité de chaque produit commandé par l'utilisateur
        foreach ($userOrders as $order) {
            if ($order->isIsPaye() == false) {
                $productName = $order->getPname();
                if (!isset($productQuantities[$productName])) {
                    $productQuantities[$productName] = 1;
                } else {
                    $productQuantities[$productName]++;
                }
            }
        }


        $items = [];
        $total = 0;
        foreach ($exist as $key => $product) {
            $items[] = [
                'name' => $product->getPname(),
                'unit_amount' => [
                    'currency_code' => 'EUR',
                    'value' => $product->getPrice() // Adjust according to your actual data
                ],
                'quantity' => $productQuantities[$product->getPname()]
            ];
            // Update the total by adding the price of each item multiplied by its quantity
            $total += $product->getPrice() * $productQuantities[$product->getPname()];
        }

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'default',
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $total, // Update the total value here
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value' => $total // Ensure this matches the total value above
                            ],
                            'shipping' => [
                                'currency_code' => 'EUR',
                                'value' => 0
                            ]
                        ]
                    ],
                    'items' => $items // Update to use the items array
                ]
            ],
            'application_context' => [
                'return_url' => $this->generator->generate(
                    'payment_success_paypal',
                    ['refernce' => $this->getUser()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'cancel_url' => $this->generator->generate(
                    'payment_error',
                    ['refernce' => $this->getUser()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ]
        ];

        $client = $this->getClientId();
        $respons = $client->execute($request);
        if ($respons->statusCode != 201) {
            return $this->redirectToRoute('user_order_list');
        }
        $approvalink = '';
        foreach ($respons->result->links as $link) {
            if ($link->rel === 'approve') {
                $approvalink = $link->href;
                break;
            }
        }
        if (empty($approvalink)) {
            return $this->redirectToRoute('user_order_list');
        }
        $this->entityManager->flush();
        return new RedirectResponse($approvalink);
    }




    #[Route('/paiement/success', name: 'payment_success_paypal')]
    public function successPaypal()
    {

        $userOrders = $this->orderRepository->findBy(['user' => $this->getUser()]);
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $exist = [];
        foreach ($userOrders as $order) {
            $productName = $order->getPname();
            if (!isset($exist[$productName])) {
                $exist[$productName] = $order;
            }
        }
        foreach ($userOrders as $order) {

            $productName = $order->getPname();
            if (!isset($productQuantities[$productName])) {
                $productQuantities[$productName] = 1;
            } else {
                $productQuantities[$productName]++;
            }
        }
        $total = 0;
        foreach ($exist as $key => $product) {

            $total += $product->getPrice() * $productQuantities[$product->getPname()];
        }

        foreach ($userOrders as $order) {

            $product = $this->productRepository->findOneBy(['name' => $order->getPname()]);


            if ($product) {
                if ($order->isIsPaye() == false) {

                    $order->setIsPaye(1);
                    $order->setStatus('processing');


                    if ($product->getQuantite() >= $productQuantities[$order->getPname()]) {
                        $newQuantity = $product->getQuantite() - $productQuantities[$order->getPname()];
                        $product->setQuantite($newQuantity);
                    }


                    $this->entityManager->persist($order);
                    $this->entityManager->persist($product);
                }
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
        $this->panierrepositiry->clearPanier($this->getUser());
        $paiement = new Paiement();
        $paiement->setUser($this->getUser());
        $paiement->setPrice($total); // Assuming $total is defined in your method
        $paiement->setDatePaiement(new \DateTime()); // Set the current date

        $this->entityManager->persist($paiement);

        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'success paiement'
        );
        return $this->redirectToRoute('app_home');
    }
    #[Route('/paiement/erroe', name: 'payment_error')]
    public function errorData()
    {
    }
}
