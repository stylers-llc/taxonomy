<?php

namespace Stylers\Taxonomy\Entities\Traits;

use Illuminate\Database\Eloquent\Collection;

trait Collectionable
{
    public static function getCollection(Collection $collection, array $additions = []): array
    {
        $list = [];
        foreach ($collection as $model) {
            $entity = new self($model);
            $data = $entity->getFrontendData($additions);
            array_push($list, $data);
        }

        return $list;
    }
}