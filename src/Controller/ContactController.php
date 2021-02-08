<?php

namespace App\Controller;

use App\Classe\Mailjet;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');
            $mail = new Mailjet();

            $prenom = $form->get('firstname')->getData();
            $nom = $form->get('lastname')->getData();
            $email = $form->get('email')->getData();
            $message = $form->get('content')->getData();

            $content = 'Nom : '.$prenom.' '.$nom.'<br>';
            $content .= 'Email : '.$email.'<br>';
            $content .= 'Message : <br>'.$message;
            
            //Destinataire / Nom de la personne / Sujet / Contenue
            $mail->send('jrmprod@gmail.com', 'La Boutique Française', 'Vous avez reçu une demande de contact', $content);

        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
