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

class SearchController extends AbstractController
{
    private $advertFinder;

    public function __construct($advertFinder)
    {
        $this->advertFinder = $advertFinder;
    }
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

        $region=$city->getIdRegion();
        $location=$locationRepository->findOneBy(['idRegion'=>$region]);
        $idLocation = $location->getId();

        $adverts=[];
        $advertsCat=[];
        $advertsLocation=[];
        $advertsQuery=[];
        if ($query){
            $advertsQuery=$advertRepository->findAdvertsByName($query);
        }
        if(!$query && $idCategory === '162'){
            $advertsCat=$advertRepository->findAll();
        }else{
            $advertsCat=$advertRepository->findBy(['idCategory'=>$idCategory]);
        }
        /*
        if(!$query && $idLocation == '17'){
            $advertsLocation=$advertRepository->findAll();
        }else{
            $advertsLocation=$advertRepository->findBy(['id'=>$idLocation]);
        }
        */
        if($advertsCat){
        $adverts=array_merge($advertsCat);
        }
        if($advertsQuery){
        $adverts=array_merge($advertsQuery);
        }
        /*
        if($advertsLocation){
        $adverts=array_merge($advertsLocation);
        }
        */
        //$adverts = $paginator->paginate($adverts, $request->query->getInt('page',1),6);
//var_dump($adverts);
  //      exit();

        //$wordToSearch = $request->request->all('form')['query'];
        //$query = new TypesenseQuery( 	$wordToSearch, 'title');

        // Get Doctrine Hydrated objects
        //$results = $this->advertFinder->query($query)->getResults();
        // Get raw results from Typesence
        // $rawResults = $this->advertFinder->rawQuery($query)->getResults();
        // Return 'adverts'=>$rawResults

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
            ->add('search',SubmitType::class,['label' =>'Rechercher','attr'=>['class'=>'text-light btn btn-primary mt-4 w-100']])
            ->getForm();



        return $this->render('components/search_bar.html.twig',['searchForm'=>$form->createView()]);
    }

}
