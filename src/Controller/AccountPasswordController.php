<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    /**
     * @Route("/compte/modifier-mon-mot-de-passe", name="account_password")
     */
    public function index(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = $this->getUser();
        $form = $this->createForm(UpdatePasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $old_pwd = $form->get('old_password')->getData();
            if($encoder->isPasswordValid($user, $old_pwd)){
                $new_pwd = $form->get('new_password')->getData();
                $password = $encoder->encodePassword($user, $new_pwd);
                $user->setPassword($password);

                //Pas de besoin de persit car c'est une modification et non une création
                $em->flush();
                $notification = $this->flash('success');
            } else {
                $notification = $this->flash('err');
            }        
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);

    }

    public function flash($value) {
        if($value === 'err'){
            return ['message' => "Votre mot de passe actuel n'est pas le bon.", 'class' => 'danger'];
        }else if($value === 'success'){
            return ['message' => "Votre mot de passe a bien été mis a jour.", 'class' => 'success'];
        }
    }
}
