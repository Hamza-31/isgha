<?php

namespace App\Component;

use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent('searchLive')]
class SearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $wordToSearch ='';

    private $advertFinder;
    public function __construct($advertFinder){
        $this->advertFinder=$advertFinder;
    }
    public function getData(): array{
        if(empty($this->wordToSearch)){
            return [];
        }

        $query = new TypesenseQuery( 	$this->wordToSearch, 'title');

        // Get Doctrine Hydrated objects
        return $this->advertFinder->query($query)->getResults();
    }
}