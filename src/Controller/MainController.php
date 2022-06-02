<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(RegionRepository $regionRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('main/index.html.twig',[
            'regions'=>$regionRepository->findAll(),
            'categories'=>$categoryRepository->findAll()
        ]);
    }
}
