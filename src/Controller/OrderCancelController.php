<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    /**
     * @Route("/commande/erreur/{stripeSessionId}", name="order_cancel")
     */
    public function index($stripeSessionId, EntityManagerInterface $em): Response
    {
        
        $order = $em->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }

        //Envoyer un email à notre client pour lui indiquer l'échec de paiement


        return $this->render('order_cancel/index.html.twig', [
            'order' => $order,
        ]);
    }
}
