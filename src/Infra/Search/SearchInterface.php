<?php
declare(strict_types=1);
namespace App\Infra\Search;
interface SearchInterface
{
public function search(string $query,$options):array;
}