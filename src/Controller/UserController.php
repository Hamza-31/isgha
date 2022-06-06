<?php

namespace App\Controller;


use App\Entity\Note;
use App\Entity\User;
use App\Form\NoteType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    #[Route('/user/edit/{id}', name: 'app_user', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request,User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher,int $id): Response
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
        return $this->render('pages/user/edit.html.twig', ['id' => $id,
            'userForm' => $form->createView(),
        ]);
    }

    /**
     * Cette méthode permet la modification du mot de passe de l'utilisateur.
     * @param User $user
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/user/edit-mdp/{id}', name: 'app_user_edit_password', methods: ['GET','POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager, int $id): Response{

        if (!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() !== $user){
            $this->addFlash('alert','Accès refusé');
            return $this->redirectToRoute('home');
        }
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
        return $this->render('pages/user/edit_password.html.twig',['id' => $id,'userPasswordForm'=>$form->createView()]);
    }
    #[Route('/user/{id}', name: 'app_user_details', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function userDetails( EntityManagerInterface $manager,NoteRepository $noteRepository,Request $request, UserRepository $userRepository, User $user, int $id): Response
    {

        $note =new Note();
        $form=$this->createForm(NoteType::class,$note);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $note->setUser($this->getUser())
                ->setUserNoted($user);
            $existingNote=$noteRepository->findOneBy(['userNoted'=>$user,'user'=>$this->getUser()]);
            if(!$existingNote){
            $manager->persist($note);
            }else{

                $existingNote->setNote($form->getData()->getNote());
                $manager->persist($note);
            }
            $manager->flush();
            $this->addFlash('success','Votre note a bien été prise.');
                return $this->redirectToRoute('app_user_details',['id'=>$user->getId()]);





        }
        return $this->render('pages/user/details.html.twig', [
            'user' => $userRepository->findOneBy(['id'=>$id]),
            'userDetailsForm'=> $form->createView()
        ]);
    }
}
