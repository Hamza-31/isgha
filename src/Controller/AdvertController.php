<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\User;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/advert')]
class AdvertController extends AbstractController
{
    #[Route('/list', name: 'app_advert_index_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function indexUser(AdvertRepository $advertRepository): Response
    {
        return $this->render('advert/index.html.twig', [
            'adverts' => $advertRepository->findBy(['idUser'=>$this->getUser()])
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_advert_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AdvertRepository $advertRepository, CityRepository $cityRepository, LocationRepository $locationRepository, CategoryRepository $categoryRepository): Response
    {
        $advert = new Advert();
        $cities = $cityRepository->findAll();
        $form = $this->createForm(AdvertType::class, $advert, [
            'cities' => $cities
        ]);
        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le nom de la ville
            $city = $form->get("city")->getData();
            // Récupérer la région de la ville
            $region = $city->getIdRegion();
            //$location = $locationRepository->findOneBy(['idRegion' => $region->getId()]);
            $location = new Location();
            $location->setIdRegion($region);
            $location->setCity($city->getName());

            $advert->setIdLocation($location);
            $advert->setIdUser($user);
            $advert->setIsValid(false);
            // Récupérer le fichier image.
            $imageFile=$request->files->get('advert')['imageFile']['file'];
            $imageName=$request->files->get('advert')['imageFile']['file']->getClientOriginalName();

            $uploadedFile = new UploadedFile($imageFile->getPathName(), $imageName, "image/jpg", null, true);
            $advert->setImageFile($uploadedFile);
            $advert->setImageName($imageName);

            $locationRepository->add($location, true);
            $advertRepository->add($advert, true);

            $this->addFlash('success', 'Votre annonce a été ajouté avec succès.');

            return $this->redirectToRoute('app_advert_index_user');
        }

        return $this->renderForm('advert/new.html.twig', [
            'advert' => $advert,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_advert_show', methods: ['GET'])]
    #[ParamConverter('advert', class: Advert::class)]
    public function show(Advert $advert, int $id, AdvertRepository $advertRepository, UserRepository $userRepository,NoteRepository $noteRepository): Response
    {
        $advertUser=$advertRepository->findOneBy(['id'=>$advert->getId()]);
        $user=$userRepository->findOneBy(['id'=>$advertUser->getIdUser()]);
        $note=$user->getAverage();
        return $this->render('advert/show.html.twig', [
            'advert' => $advert,
            'id'=>$id,
            'user'=>$user,

        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_advert_edit', methods: ['GET', 'POST'])]
    #[ParamConverter('advert', class: Advert::class)]
    public function edit(Request $request, Advert $advert, AdvertRepository $advertRepository): Response
    {

        if (!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() !== $advert->getIdUser()){
            $this->addFlash('alert','Accès refusé');
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertRepository->add($advert, true);

            return $this->redirectToRoute('app_advert_index_user', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('advert/edit.html.twig', [
            'advert' => $advert,
            'form' => $form,
            'id' => $advert->getId()
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/delete', name: 'app_advert_delete', methods: ['POST','GET'])]
    #[ParamConverter('advert', class: Advert::class)]
    public function delete(Request $request, Advert $advert, AdvertRepository $advertRepository): Response
    {

        if (!$this->getUser()){
            $this->addFlash('alert', 'Votre devez avoir un compte pour ajouter une annonce.');
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() !== $advert->getIdUser()){
            $this->addFlash('alert', 'Vous n\'êtes pas l\'auteur de cette annonce.');
            return $this->redirectToRoute('home');
        }
        if($this->getUser() === $advert->getIdUser()){

            $advert->setIsValid(false);
            $advertRepository->add($advert,true);
        }else{
            $this->addFlash('Erreur', "Vous n'êtes pas l'auteur de cette annonce !" );
            return $this->redirectToRoute('app_advert_show', ['id' => $advert->getId()]);
        }

        $this->addFlash('success','Votre annonce a été supprimée.');

        return $this->redirectToRoute('app_advert_index_user', [], Response::HTTP_SEE_OTHER);
    }
}
