<?php

namespace Stylers\Taxonomy\Models;


use Stylers\Taxonomy\Manipulators\DescriptionSetter;

trait DescriptionTrait
{

    public function descriptionTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function description()
    {
        return $this->hasOne(Description::class, 'id', 'description_id');
    }

    public function setDescription($columnName, $objectId, $taxonomyId, $description, $database = null)
    {
        $object = call_user_func([get_class($this), 'on'], $database)
            ->where($columnName, $objectId)->where('taxonomy_id', $taxonomyId)->first();

        if ($object) {
            $description = (new DescriptionSetter($description,
                $object->description_id))->setConnection($database)->set();
        } else {
            $description = (new DescriptionSetter($description))->setConnection($database)->set();

            $object = new self();
            $object->setConnection($database);
            $object->{$columnName} = $objectId;
            $object->taxonomy_id = $taxonomyId;
            $object->description_id = $description->id;
            $object->save();
        }
    }

}