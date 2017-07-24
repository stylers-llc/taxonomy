<?php

namespace Stylers\Taxonomy\Models;


use Stylers\Taxonomy\Entities\DescriptionEntity;

trait MetaTrait
{
    public function metaTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function additionalDescription()
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }

    public function getMetaObjects($columnName, $objectId)
    {
        return call_user_func(array(get_class($this), 'where'), [[$columnName, $objectId]])->get();
    }

    public function getMetaEntities($columnName, $objectId)
    {
        $return = [];
        $metas = $this->getMetaObjects($columnName, $objectId);
        if ($metas) {
            foreach ($metas as $meta) {
                $tmpMeta = [
                    'name' => $meta->metaTaxonomy->name,
                    'value' => $meta->value
                ];

                if ($meta->additionalDescription) {
                    $tmpMeta['description'] = (new DescriptionEntity($meta->additionalDescription))->getFrontendData();
                }

                $return[] = $tmpMeta;
            }
        }
        return $return;
    }

    public function setMetas($columnName, $objectId, $parentId, array $metas)
    {
        $metaIds = $this->getActiveMetaIds($columnName, $objectId);
        $numMetas = count($metas);
        for ($i = 0; $i < $numMetas; $i++) {
            $metaId = $this->insertOrUpdateMeta($columnName, $objectId, $parentId, $metas[$i]);
            $metaIds = array_diff($metaIds, [$metaId]);
        }
        $this->deleteUnusedMetas($metaIds);
    }

    public function clearMetas($columnName, $objectId)
    {
        $metaIds = $this->getActiveMetaIds($columnName, $objectId);
        $this->deleteUnusedMetas($metaIds);
    }

    public function insertOrUpdateMeta($columnName, $objectId, $parentId, $meta, $listable = null)
    {
        $taxonomy = Taxonomy::getTaxonomy($meta['name'], $parentId, $this->getConnectionName());
        $object = $this->getMetaWithTrashed($columnName, $objectId, $taxonomy->id);
        if ($object) {
            if ($object->trashed()) {
                $object->restore();
            }
            $object->value = $meta['value'];
            $object->save();
            return $object->id;
        }

        $object = new self();
        $object->{$columnName} = $objectId;
        $object->taxonomy_id = $taxonomy->id;
        $object->value = $meta['value'];
        if (!is_null($listable)) {
            $object->is_listable = $listable;
        }
        $object->setConnection($this->getConnectionName());
        $object->saveOrFail();

        return $object->id;
    }

    public function getActiveMetaIds($columnName, $objectId)
    {
        $return = [];
        $objects = call_user_func(array(get_class($this), 'where'), [[$columnName, $objectId]])->get();
        $numObjects = count($objects);
        for ($i = 0; $i < $numObjects; $i++) {
            $return[] = $objects[$i]->id;
        }
        return $return;
    }

    public function deleteUnusedMetas(array $unusedIds)
    {
        $class = get_class($this);
        foreach ($unusedIds as $unused) {
            call_user_func(array($class, 'destroy'), [[$unused]]);
        }
    }

    public function getMetaWithTrashed($columnName, $objectId, $taxonomyId)
    {
        return call_user_func(array(get_class($this), 'withTrashed'), [])
            ->where($columnName, $objectId)
            ->where('taxonomy_id', $taxonomyId)
            ->first();
    }
}