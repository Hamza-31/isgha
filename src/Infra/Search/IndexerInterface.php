<?php
declare(strict_types=1);

namespace App\Infra\Search;

use App\Entity\Advert;

interface IndexerInterface
{
public function index(Advert $advert): bool;
}