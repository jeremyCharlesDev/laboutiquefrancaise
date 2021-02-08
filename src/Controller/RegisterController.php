<?php

namespace App\Controller;

use App\Entity\User;
use App\Classe\Mailjet;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    // private $entityManager;

    // public function __construct(EntityMagerInterface $entityManager){
    //     $this->entityManager = $entityManager;
    // }
    
    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = $form->getData();

            $search_email = $em->getRepository(User::class)->findOneByEmail($user->getEmail());
            if(!$search_email){
                $password = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);

                $em->persist($user);
                $em->flush();
                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
                $mail = new Mailjet();
                $content = "Bonjour ".$user->getFirstname().".<br>Bienvenue sur la première boutique dédiée au made in France.<br><br> Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorum qui soluta a quod. Unde maiores repellendus officiis explicabo in, cupiditate et. Ratione, amet? Eius nobis reiciendis dolore maxime sunt deleniti? Excepturi alias fugiat sit? Adipisci repellendus velit reprehenderit voluptatibus perferendis, expedita amet porro illo recusandae soluta numquam, rem magni voluptatem ipsam beatae, vitae nulla unde ex dolore explicabo quis aliquid exercitationem enim consectetur! Dicta mollitia cum in vitae vero. Assumenda at aperiam iste ipsam quam doloremque illo iure quo corrupti, voluptatum, consequatur dolor error incidunt. Quod sint aperiam hic asperiores dolorum vitae vel tempore ullam, facilis provident illum architecto nostrum!";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur la Boutique Française', $content);
        
            } else {
                $notification = "L'email que vous avez renseigné existe déjà.";
            }            
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
