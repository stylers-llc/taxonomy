<?php

namespace Stylers\Taxonomy\Models;

use Stylers\Taxonomy\Entities\DescriptionEntity;

/**
 * @require protected parentTaxonomyId;
 */
trait ClassificationTrait
{
    public function classificationTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'classification_taxonomy_id');
    }

    public function valueTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'value_taxonomy_id');
    }

    public function priceTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'price_taxonomy_id');
    }

    public function additionalDescription()
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }

    public function getClassificationObjects($columnName, $objectId, $orderBy = null)
    {
        $query = call_user_func(array(get_class($this), 'where'), [[$columnName, $objectId]]);
        if (!is_null($orderBy)) {
            $query->orderBy($orderBy);
        }
        return $query->get();
    }

    public function getClassification($columnName, $objectId, $classificationTaxonomyId)
    {
        return call_user_func(array(get_class($this), 'where'), [[$columnName, $objectId]])
            ->where('classification_taxonomy_id', $classificationTaxonomyId)->first();
    }

    public function getClassificationWithTrashed($columnName, $objectId, $classificationTaxonomyId)
    {
        return call_user_func(array(get_class($this), 'withTrashed'), [])
            ->where($columnName, $objectId)
            ->where('classification_taxonomy_id', $classificationTaxonomyId)
            ->first();
    }

    public function getActiveClassificationIds($columnName, $objectId)
    {
        $return = [];
        $objects = call_user_func(array(get_class($this), 'where'), [[$columnName, $objectId]])->get();
        $numObjects = count($objects);
        for ($i = 0; $i < $numObjects; $i++) {
            $return[] = $objects[$i]->id;
        }
        return $return;
    }

    public function getClassificationEntities($columnName, $objectId)
    {
        $return = [];
        $classifications = $this->getClassificationObjects($columnName, $objectId);
        if ($classifications) {
            foreach ($classifications as $classification) {
                $data = [
                    'name' => $classification->classificationTaxonomy->name,
                    'isset' => true
                ];

                if ($classification->value_taxonomy_id) {
                    $data['value'] = $classification->valueTaxonomy->name;
                }

                if ($classification->additional_description_id) {
                    $data['description'] = (new DescriptionEntity($classification->additionalDescription))->getFrontendData();
                }

                if (isset($classification->is_highlighted)) {
                    $data['highlighted'] = $classification->is_highlighted;
                }

                $return[] = $data;
            }
        }
        return $return;
    }

    public function insertOrUpdateClassification($columnName, $objectId, $nameTxId, $value)
    {
        $valueTxId = is_null($value) ? null : Taxonomy::getTaxonomy($value, $nameTxId)->id;

        $classification = $this->getClassificationWithTrashed($columnName, $objectId, $nameTxId);
        if ($classification) {
            if ($classification->trashed()) {
                $classification->restore();
            }
            $classification->value_taxonomy_id = $valueTxId;
            $classification->setConnection($this->getConnectionName());
            return $classification;
        }

        $newClassification = new self();
        $newClassification->{$columnName} = $objectId;
        $newClassification->classification_taxonomy_id = $nameTxId;
        $newClassification->value_taxonomy_id = $valueTxId;
        $newClassification->setConnection($this->getConnectionName());
        $newClassification->saveOrFail();
        return $newClassification;
    }

    public function setClassifications($columnName, $objectId, $parentId, array $classifications)
    {
        $classificationIds = $this->getActiveClassificationIds($columnName, $objectId);
        $numClassifications = count($classifications);
        for ($i = 0; $i < $numClassifications; $i++) {
            $classificationId = $this->insertOrUpdateClassification($columnName, $objectId, $parentId,
                $classifications[$i]['value'])->id;
            $classificationIds = array_diff($classificationIds, [$classificationId]);
        }
        $this->deleteUnusedClassifications($classificationIds);
    }

    public function deleteUnusedClassifications(array $unusedIds)
    {
        $numUnused = count($unusedIds);
        $class = get_class($this);
        for ($i = 0; $i < $numUnused; $i++) {
            call_user_func(array($class, 'destroy'), [[$unusedIds[$i]]]);
        }
    }

    public function clearClassifications($columnName, $objectId)
    {
        $classificationIds = $this->getActiveClassificationIds($columnName, $objectId);
        $this->deleteUnusedClassifications($classificationIds);
    }
}