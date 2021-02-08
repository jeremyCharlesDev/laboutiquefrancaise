<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $em, Cart $cart, $reference): Response
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'https://127.0.0.1:8000';

        $order = $em->getRepository(Order::class)->findOneByReference($reference);

        if(!$order){
            new JsonResponse(['error' => 'order']);    
        }
        // dd($order->getOrderDetails()->getValues());

        //Information de la commande
        foreach($order->getOrderDetails()->getValues() as $product) {
            $product_object = $em->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/products/".$product_object->getIllustration()],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        //Information pour la livraison
        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ],
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51HyxpYLQLVat3oSkx0wncgPsFi5J1hG41KKlUAm4HQBJIaFFoO3lZqLEFHexmjZXYV20lZclSn9J0ypSND34UAOy004bukQKve');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $products_for_stripe
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $em->flush();

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
