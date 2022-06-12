<?php

namespace App\Controller;

use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use App\Entity\Category;
use App\Entity\City;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\RegionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;

class SearchController extends AbstractController
{
    /*
    private $advertFinder;

    public function __construct($advertFinder)
    {
        $this->advertFinder = $advertFinder;
    }
    */
    #[Route('/search', name: 'search',methods: ['GET','POST'])]
    public function search(Request $request,AdvertRepository $advertRepository,PaginatorInterface $paginator, RegionRepository $regionRepository,CityRepository $cityRepository,LocationRepository $locationRepository): Response
    {
        global $adverts;
       //var_dump($request->request->all('form'));
       //exit();

        $query= $request->request->all('form')['query'];
        $idCategory = $request->request->all('form')['idCategory'];
        $cityQuery= $request->request->all('form')['city'];
        $city=$cityRepository->findOneBy(['id'=>$cityQuery]);

        $idRegion=$city->getIdRegion()->getId();
        //$location=$locationRepository->findOneBy(['idRegion'=>$idRegion]);
        $locations=$locationRepository->findBy(['city'=>$city->getName()]);
        $idLocations=[];
        foreach ($locations as $location){
            $idLocations[]=$location->getId();
        }
        $adverts=[];
        $advertsLocation=[];

                if(!$query && $idCategory==162 && in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAll();
                }
                if($query && $idCategory==162 && in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts($query,162,[17]);
                }
                if(!$query && $idCategory!=162 && in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts('',$idCategory,17);
                }
                if(!$query && $idCategory==162 && !in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts('',162,$idLocations);
                }
                if($query && $idCategory!=162 && in_array(17,$idLocations)){

                    $adverts=$advertRepository->findAdverts($query,$idCategory,[17]);
                }
                if($query && $idCategory==162 && !in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts($query,162,$idLocations);
                }
                if(!$query && $idCategory!=162 && !in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts('',$idCategory,$idLocations);
                }
                if($query && $idCategory!=162 && !in_array(17,$idLocations)){
                    $adverts=$advertRepository->findAdverts($query,$idCategory,$idLocations);
                }

        return $this->render('pages/search_results.html.twig', [
            'adverts'=>$adverts,'query'=>$query
        ]);
    }

    #[Route('/searchBar', name: 'searchBar',methods: ['GET'])]
    public function searchBar(Request $request): Response
    {

        $form = $this->createFormBuilder(null)
            ->setAction($this->generateUrl('search'))
            ->setMethod('POST')
            ->add('query', TextType::class,['required'=>false,'label' =>'Que recherchez-vous?','label_attr'=>['class'=>'form-label'],'attr'=>['class'=>'form-control']])
            ->add('idCategory',EntityType::class, [
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->getName();
                },'choice_value' => function (?Category $choice) {
                    return $choice ? $choice->getId() : '';
                },
                'attr'=>[
                    'class'=>'form-select dropdown-primary'
                ],
                'label'=>'Catégorie',
                'label_attr'=>['class'=>'form-label']])
            ->add('city',EntityType::class,[
                'class'=>City::class,
                'choice_label' => function ($city) {
                    return $city->getName();
                },'choice_value' => function (?City $city) {
                    return $city ? $city->getId() : '';
                },
                'attr'=>[
                    'class'=>'form-select dropdown-primary'
                ],
                'label'=>'Choisissez une ville',
                'label_attr'=>['class'=>'form-label']
            ])
            ->add('search',SubmitType::class,['label' =>'Rechercher','attr'=>['class'=>'text-light btn btn-primary mt-4 px-5']])
            ->getForm();



        return $this->render('components/search_bar.html.twig',['searchForm'=>$form->createView()]);
    }

}
