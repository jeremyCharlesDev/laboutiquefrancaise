<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Adress;
use App\Form\AdressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAdressController extends AbstractController
{
    /**
     * @Route("/compte/adresses", name="account_adress")
     */
    public function index(): Response
    {
        // dd($this->getUser()->getAdresses());
        return $this->render('account/adress.html.twig');
    }

    /**
     * @Route("/compte/ajouter-une-adresse", name="account_adress_add")
     */
    public function add(Cart $cart, Request $request, EntityManagerInterface $em): Response
    {

        $adress = new Adress();
        $form = $this->createForm(AdressType::class, $adress);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $adress->setUser($this->getUser());
            $adress = $form->getData();

            // $this->entityManager->persist($user);
            // $this->entityManager->flush();
            $em->persist($adress);
            $em->flush();

            if($cart->get()){
                return $this->redirectToRoute('order');
            } else {
                return $this->redirectToRoute('account_adress');
            }
            
        }

        // dd($this->getUser()->getAdresses());
        return $this->render('account/adress_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/compte/modifier-une-adresse/{id}", name="account_adress_edit")
     */
    public function edit(Request $request, EntityManagerInterface $em, $id): Response
    {
        $adress = $em->getRepository(Adress::class)->findOneById($id);

        // dd($adress->getUser());
        // dd($this->getUser());
        if(!$adress || $adress->getUser() != $this->getUser() ){
            return $this->redirectToRoute('account_adress');
        }

        $form = $this->createForm(AdressType::class, $adress);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->flush();
            return $this->redirectToRoute('account_adress');
        }

        // dd($this->getUser()->getAdresses());
        return $this->render('account/adress_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/compte/supprimer-une-adresse/{id}", name="account_adress_delete")
     */
    public function delete(EntityManagerInterface $em, $id): Response
    {
        $adress = $em->getRepository(Adress::class)->findOneById($id);

        if($adress && $adress->getUser() == $this->getUser() ){
            $em->remove($adress);
            $em->flush();
        }

        return $this->redirectToRoute('account_adress');
    }

}
