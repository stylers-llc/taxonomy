<?php

namespace Stylers\Taxonomy\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface EntityInterface
{
    public static function getCollection(Collection $collection, array $additions = []): array;

    public function getFrontendData(array $additions = []): array;
}