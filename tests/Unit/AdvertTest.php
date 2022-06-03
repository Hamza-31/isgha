<?php

namespace App\Tests\Unit;

use App\Entity\Advert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdvertTest extends KernelTestCase
{
    public function testAdvertCreation(): void
    {
        $kernel = self::bootKernel();

        //$this->assertSame('test', $kernel->getEnvironment());
        // $routerService = static::getContainer()->get('router');
        // $myCustomService = static::getContainer()->get(CustomService::class);
        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();
        $advert = new Advert();
        $advert->setTitle('Annonce')
        ->setDescription('Une annonce')
        ->setPrice(43.3);

        $errors=$container->get('validator')->validate($advert);
        $this->assertCount(0,$errors);

    }
    public function testInvalidName():void{
        $kernel = self::bootKernel();
        $container=static::getContainer();
        $advert=new Advert();
        $advert->setTitle('');
        $errors=$container->get('validator')->validate($advert);
        $this->assertCount(0,$errors);
    }
}
