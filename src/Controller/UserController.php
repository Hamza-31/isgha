<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    /**
     * Cette méthode permet la modification du profil de l'utilisateur.
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'app_user')]
    public function index(Request $request,User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if (!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() !== $user){
            $this->addFlash('alert','Accès refusé');
            return $this->redirectToRoute('home');
        }

        $form=$this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($user, $form->get('plainPassword')->getData())){
                $user=$form->getData();
                $user->setUpdatedAt(new \DateTimeImmutable());
                //$user->setCity('Myleschester');

                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success','Les informations de votre compte ont été modifiées.');
                return $this->redirectToRoute('home');
            }else{
                $this->addFlash('alert','Le mot de passe renseigné est incorrect.');
            }


        }
        return $this->render('pages/user/edit.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }
    #[Route('/utilisateur/edition-mdp/{id}', name: 'app_user_edit', methods: ['GET','POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager): Response{
        $form=$this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($user, $form->get('plainPassword')->getData())){
                $user->setUpdatedAt(new DateTimeImmutable());
                //dd($user);
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->get('newPassword')->getData()
                    )
                );

                //$user->setCity('Myleschester');
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success','Le mot de passe a été bien modifié.');
                return $this->redirectToRoute('home');
            }else{
                $this->addFlash('alert','Le mot de passe renseigné est incorrect.');
            }
        }
        return $this->render('pages/user/edit_password.html.twig',['userPasswordForm'=>$form->createView()]);
    }
}