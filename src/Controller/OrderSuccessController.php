<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Classe\Mailjet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index($stripeSessionId, EntityManagerInterface $em, Cart $cart): Response
    {
        $order = $em->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }

        if($order->getState() == 0){
            //Vider la session "Cart"
            $cart->remove();

            //Modifier le statut isPaid de notre commande en mettant 1
            $order->setState(1);
            $em->flush();

            //Envoyer un email à notre client pour lui confirmer sa commande
            $mail = new Mailjet();
            $content = "Bonjour ".$order->getUser()->getFirstname().".<br>Merci pour votre commande sur La Boutique Française.<br><br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorum qui soluta a quod. Unde maiores repellendus officiis explicabo in, cupiditate et. Ratione, amet? Eius nobis reiciendis dolore maxime sunt deleniti? Excepturi alias fugiat sit? Adipisci repellendus velit reprehenderit voluptatibus perferendis, expedita amet porro illo recusandae soluta numquam, rem magni voluptatem ipsam beatae, vitae nulla unde ex dolore explicabo quis aliquid exercitationem enim consectetur! Dicta mollitia cum in vitae vero. Assumenda at aperiam iste ipsam quam doloremque illo iure quo corrupti, voluptatum, consequatur dolor error incidunt. Quod sint aperiam hic asperiores dolorum vitae vel tempore ullam, facilis provident illum architecto nostrum!";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande La Boutique Française est bien validée.', $content);
        }


        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
