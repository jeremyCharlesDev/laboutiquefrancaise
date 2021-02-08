<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Classe\Mailjet;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {

        if($this->getUser()){
            return $this->redirectToRoute('home');
        }

        if($request->get('email')){
            // dd($request->get('email'));
            $user = $em->getRepository(User::class)->findOneByEmail($request->get('email'));
            if($user){
                // 1 : Enregistrer en base la demande de reset_password avec user, token, createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());
                $em->persist($reset_password);
                $em->flush();

                // 2 : Envoyer un email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe.
                $mail = new Mailjet();
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname().".<br>Vous avez demandé à réinitialiser votre mot de passe sur le site La Boutique Française.<br><br>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";
                
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe sur la boutique Française', $content);

                $this->addFlash('notice', 'Vous allez recevoir dans quelques secondes un mail avec la procédure pour réinitialiser votre mot de passe.');

            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnu.');
            }
        }
        return $this->render('reset_password/index.html.twig');
    }


    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update($token, EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $em->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password){
            return $this->redirectToRoute('reset_password');
        }

        //Vérifier si le createdAt = now -3h
        $now = new \DateTime();
        if($now > $reset_password->getCreatedAt()->modify('+ 3 hour')){
            $this->addFlash('notice', 'Votre mot de passe a expiré. Merci de la renouveller');
            return $this->redirectToRoute('reset_password');
        }
        //Rendre une vue avec modifier mot de passe et confirmer mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new_pwd = $form->get('new_password')->getData();
            //Encoder le mot de passe 
            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);
    
            //Flush en base de donnée
            $em->flush();
            //Redirection de l'utilisateur vers la page de connection
            $this->addFlash('notice', 'Votre mot de passe à bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);

    }
}
