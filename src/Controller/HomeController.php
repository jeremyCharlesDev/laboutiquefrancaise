<?php

namespace App\Controller;

use App\Entity\Header;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $isBest = $em->getRepository(Product::class)->findByIsBest(1);
        $headers = $em->getRepository(Header::class)->findAll();

        // SESSION SessionInterface $session
        // $session->set('cart', [
        //     [
        //         'id' => 444,
        //         'quantity' => 12
        //     ]
        // ]);
        // $cart = $session->get('cart');

        // $session->remove('cart');
        // dd($cart);

        return $this->render('home/index.html.twig', [
            'isBest' => $isBest,
            'headers' => $headers
        ]);
    }
}
