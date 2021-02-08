<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {   

        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $products = $em->getRepository(Product::class)->findWithSearch($search);
        } else {
            $products = $em->getRepository(Product::class)->findAll();
        }


        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug, EntityManagerInterface $em): Response
    {   
        $product = $em->getRepository(Product::class)->findOneBySlug($slug);
        $isBest = $em->getRepository(Product::class)->findByIsBest(1);


        if(!$product){
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'isBest' => $isBest
        ]);
    }
}
