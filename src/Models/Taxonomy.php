<?php


namespace Stylers\Taxonomy\Models;


use Baum\Node;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\MessageBag;

class Taxonomy extends Node
{

    use SoftDeletes,
        ModelValidatorTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'taxonomies';

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $appends = ['has_descendants'];

    /**
     * Column name which stores reference to parent's node.
     *
     * @var string
     */
    protected $parentColumn = 'parent_id';

    /**
     * Column name for the left index.
     *
     * @var string
     */
    protected $leftColumn = 'lft';

    /**
     * Column name for the right index.
     *
     * @var string
     */
    protected $rightColumn = 'rgt';

    /**
     * Column name for the depth field.
     *
     * @var string
     */
    protected $depthColumn = 'depth';

    /**
     * Column to perform the default sorting
     *
     * @var string
     */
    protected $orderColumn = 'priority';

    /**
     * With Baum, all NestedSet-related fields are guarded from mass-assignment
     * by default.
     *
     * @var array
     */
    protected $guarded = ['id', 'lft', 'rgt', 'depth'];
    protected $fillable = [
        'name',
        'parent_id',
        'priority',
        'is_active',
        'is_required',
        'is_merchantable',
        'type',
        'relation',
        'icon'
    ];

    /*
      This is to support "scoping" which may allow to have multiple nested
      set trees in the same database table.
      You should provide here the column names which should restrict Nested
      Set queries. f.ex: company_id, etc.
     */

    /**
     * Columns which restrict what we consider our Nested Set list
     *
     * @var array
     */
    protected $scoped = [];

    /*
      Baum makes available two model events to application developers:

      1. `moving`: fired *before* the a node movement operation is performed.
      2. `moved`: fired *after* a node movement operation has been performed.

      In the same way as Eloquent's model events, returning false from the
      `moving` event handler will halt the operation.

      Please refer the Laravel documentation for further instructions on how
      to hook your own callbacks/observers into this events:
      http://laravel.com/docs/5.0/eloquent#model-events
     */

    /**
     * Model Validation rules for ModelValidatorTrait
     */
    protected $rules = [];

    /**
     * Error Message for ModelValidatorTrait
     *
     * @var MessageBag
     */
    protected $errorMessages;

    /**
     * Relation class
     * @return App\Relation\Relation
     */
    protected $taxonomyRelation;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new PriorityOrderScope);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function translations()
    {
        return $this->hasMany(TaxonomyTranslation::class, 'taxonomy_id', 'id');
    }

    public function getHasDescendantsAttribute()
    {
        return !$this->isLeaf();
    }

    public function getChildren()
    {
        return $this->getDescendants(1);
    }

    public function listTaxonomyRelationDependencies()
    {
        $relationClassName = $this->relation;
        if (!$relationClassName) {
            return null;
        }
        return $relationClassName::listDependencies();
    }

    public function getTaxonomyRelation(array $dependencies)
    {
        $relationClassName = $this->relation;
        if (!$relationClassName) {
            return null;
        }
        if (empty($this->taxonomyRelation)) {
            $this->taxonomyRelation = $relationClassName::getInstance($dependencies);
        }
        return $this->taxonomyRelation;
    }

    public function getOptions() {
        $children = $this->getChildren();

        $options = [];
        foreach ($children as $child) {
            $temp = [];
            $temp['value'] = $child->name;
            $temp['translations']['en'] = $child->name;
            $translations = $child->translations;
            foreach ($translations as $translation) {
                $key = Language::findOrFail($translation->language_id)->iso_code;
                $temp['translations'][$key] = $translation->name;
            }
            $options[] = $temp;
        }
        return $options;
    }

    static public function getRoots()
    {
        return self::whereNull('parent_id')->get();
    }

    static public function taxonomyExists($name, $parent_id, $database = null)
    {
        $tx = new Taxonomy();
        $tx->setConnection($database);
        return (bool)$tx->where(['name' => $name, 'parent_id' => $parent_id])->count();
    }

    static public function getTaxonomy($name, $parent_id, $database = null)
    {
        $tx = new Taxonomy();
        $tx->setConnection($database);
        return $tx->where(['name' => $name, 'parent_id' => $parent_id])->firstOrFail();
    }
    
    static public function getTaxonomyById($id, $parent_id, $database = null)
    {
        $tx = new Taxonomy();
        $tx->setConnection($database);
        return $tx->where(['id' => $id, 'parent_id' => $parent_id])->firstOrFail();
    }
    
    static public function getTaxonomyOfGranny($name, $grandparent_id, $database = null)
    {
        $tx = new Taxonomy();
        $tx->setConnection($database);
        return $tx
            ->select('taxonomies.*')
            ->join('taxonomies AS parents', 'parents.id', '=', 'taxonomies.parent_id')
            ->where(['taxonomies.name' => $name, 'parents.parent_id' => $grandparent_id])
            ->firstOrFail();
    }

    static public function getOrCreateTaxonomy($name, $parentTxId = null, $database = null)
    {
        try {
            return self::getTaxonomy($name, $parentTxId, $database);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $tx = new Taxonomy();
            $tx->setConnection($database);
            $tx->name = $name;
            $tx->saveOrFail();

            if ($parentTxId) {
                $parentTx = (new Taxonomy())->setConnection($database)->findOrFail($parentTxId);
                $tx->makeChildOf($parentTx);
            }

            return $tx;
        }
    }

    static public function loadTaxonomy($id = null, $database = null)
    {
        try {
            $taxonomy = Taxonomy::on($database)->findOrFail($id);
        } catch (\Exception $e) {
            $taxonomy = new Taxonomy();
            $taxonomy->id = $id;
        }
        $taxonomy->setConnection($database);
        return $taxonomy;
    }
}
