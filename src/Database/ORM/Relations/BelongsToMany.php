<?php

namespace Volcano\Database\ORM\Relations;

use Volcano\Database\ORM\Model;
use Volcano\Database\ORM\Builder;
use Volcano\Database\Query\Expression;
use Volcano\Database\ORM\Collection;
use Volcano\Database\ORM\ModelNotFoundException;
use Volcano\Support\Str;


class BelongsToMany extends Relation
{
    /**
     * The intermediate table for the relation.
     *
     * @var string
     */
    protected $table;

    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key of the relation.
     *
     * @var string
     */
    protected $otherKey;

    /**
     * The "name" of the relationship.
     *
     * @var string
     */
    protected $relationName;

    /**
     * The pivot table columns to retrieve.
     *
     * @var array
     */
    protected $pivotColumns = array();

    /**
     * Any pivot table restrictions.
     *
     * @var array
     */
    protected $pivotWheres = array();

    /**
     * The class name of the custom pivot model to use for the relationship.
     *
     * @var string
     */
    protected $using;


    /**
     * Create a new has many relationship instance.
     *
     * @param  \Volcano\Database\ORM\Builder  $query
     * @param  \Volcano\Database\ORM\Model  $parent
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $relationName
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $table, $foreignKey, $otherKey, $relationName = null)
    {
        $this->table = $table;
        $this->otherKey = $otherKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relationName;

        parent::__construct($query, $parent);
    }

    /**
     * Specify the custom Pivot Model to use for the relationship.
     *
     * @param  string  $className
     * @return \Volcano\Database\ORM\Relations\BelongsToMany
     */
    public function using($className)
    {
        $this->using = $className;

        return $this;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    /**
     * Set a where clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return \Volcano\Database\ORM\Relations\BelongsToMany
     */
    public function wherePivot($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->pivotWheres[] = func_get_args();

        return $this->where($this->table.'.'.$column, $operator, $value, $boolean);
    }

    /**
     * Set an or where clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @return \Volcano\Database\ORM\Relations\BelongsToMany
     */
    public function orWherePivot($column, $operator = null, $value = null)
    {
        return $this->wherePivot($column, $operator, $value, 'or');
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array   $columns
     * @return mixed
     */
    public function first($columns = array('*'))
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \Volcano\Database\ORM\Model|static
     *
     * @throws \Database\ORM\ModelNotFoundException
     */
    public function firstOrFail($columns = array('*'))
    {
        if (! is_null($model = $this->first($columns))) return $model;

        throw new ModelNotFoundException;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Volcano\Database\ORM\Collection
     */
    public function get($columns = array('*'))
    {
        $columns = $this->query->getQuery()->columns ? array() : $columns;

        $select = $this->getSelectColumns($columns);

        $models = $this->query->addSelect($select)->getModels();

        $this->hydratePivotRelation($models);

        if (count($models) > 0) {
            $models = $this->query->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Volcano\Pagination\Paginator
     */
    public function paginate($perPage = null, $columns = array('*'), $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        $paginator = $this->query->paginate($perPage, $columns, $pageName, $page);

        $this->hydratePivotRelation($paginator->items());

        return $paginator;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Volcano\Pagination\SimplePaginator
     */
    public function simplePaginate($perPage = null, $columns = array('*'), $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        $paginator = $this->query->simplePaginate($perPage, $columns, $pageName, $page);

        $this->hydratePivotRelation($paginator->items());

        return $paginator;
    }

    /**
     * Hydrate the pivot table relationship on the models.
     *
     * @param  array  $models
     * @return void
     */
    protected function hydratePivotRelation(array $models)
    {
        foreach ($models as $model) {
            $pivot = $this->newExistingPivot($this->cleanPivotAttributes($model));

            $model->setRelation('pivot', $pivot);
        }
    }

    /**
     * Get the pivot attributes from a model.
     *
     * @param  \Volcano\Database\ORM\Model  $model
     * @return array
     */
    protected function cleanPivotAttributes(Model $model)
    {
        $values = array();

        foreach ($model->getAttributes() as $key => $value) {
            if (strpos($key, 'pivot_') === 0) {
                $values[substr($key, 6)] = $value;

                unset($model->$key);
            }
        }

        return $values;
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $this->setJoin();

        if (static::$constraints) {
            $this->setWhere();
        }
    }

    /**
     * Add the constraints for a relationship count query.
     *
     * @param  \Volcano\Database\ORM\Builder  $query
     * @param  \Volcano\Database\ORM\Builder  $parent
     * @return \Volcano\Database\ORM\Builder
     */
    public function getRelationCountQuery(Builder $query, Builder $parent)
    {
        if ($parent->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationCountQueryForSelfJoin($query, $parent);
        }

        $this->setJoin($query);

        return parent::getRelationCountQuery($query, $parent);
    }

    /**
     * Add the constraints for a relationship count query on the same table.
     *
     * @param  \Volcano\Database\ORM\Builder  $query
     * @param  \Volcano\Database\ORM\Builder  $parent
     * @return \Volcano\Database\ORM\Builder
     */
    public function getRelationCountQueryForSelfJoin(Builder $query, Builder $parent)
    {
        $query->select(new Expression('count(*)'));

        $tablePrefix = $this->query->getQuery()->getConnection()->getTablePrefix();

        $query->from($this->table.' as '.$tablePrefix .$hash = $this->getRelationCountHash());

        $key = $this->wrap($this->getQualifiedParentKeyName());

        return $query->where($hash.'.'.$this->foreignKey, '=', new Expression($key));
    }

    /**
     * Get a relationship join table hash.
     *
     * @return string
     */
    public function getRelationCountHash()
    {
        return 'self_' .md5(microtime(true));
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param  array  $columns
     * @return \Volcano\Database\ORM\Relations\BelongsToMany
     */
    protected function getSelectColumns(array $columns = array('*'))
    {
        if ($columns == array('*')) {
            $columns = array($this->related->getTable().'.*');
        }

        return array_merge($columns, $this->getAliasedPivotColumns());
    }

    /**
     * Get the pivot columns for the relation.
     *
     * @return array
     */
    protected function getAliasedPivotColumns()
    {
        $defaults = array($this->foreignKey, $this->otherKey);

        $columns = array();

        foreach (array_merge($defaults, $this->pivotColumns) as $column) {
            $columns[] = $this->table.'.'.$column.' as pivot_'.$column;
        }

        return array_unique($columns);
    }

    /**
     * Determine whether the given column is defined as a pivot column.
     *
     * @param  string  $column
     * @return bool
     */
    protected function hasPivotColumn($column)
    {
        return in_array($column, $this->pivotColumns);
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param  \Volcano\Database\ORM\Builder|null
     * @return $this
     */
    protected function setJoin($query = null)
    {
        $query = $query ?: $this->query;

        $baseTable = $this->related->getTable();

        $key = $baseTable.'.'.$this->related->getKeyName();

        $query->join($this->table, $key, '=', $this->getOtherKey());

        return $this;
    }

    /**
     * Set the where clause for the relation query.
     *
     * @return $this
     */
    protected function setWhere()
    {
        $foreign = $this->getForeignKey();

        $this->query->where($foreign, '=', $this->parent->getKey());

        return $this;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn($this->getForeignKey(), $this->getKeys($models));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Volcano\Database\ORM\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $collection = $this->related->newCollection($dictionary[$key]);

                $model->setRelation($relation, $collection);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Volcano\Database\ORM\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $foreign = $this->foreignKey;

        $dictionary = array();

        foreach ($results as $result) {
            $dictionary[$result->pivot->$foreign][] = $result;
        }

        return $dictionary;
    }

    /**
     * Touch all of the related models for the relationship.
     *
     * E.g.: Touch all roles associated with this user.
     *
     * @return void
     */
    public function touch()
    {
        $key = $this->getRelated()->getKeyName();

        $columns = $this->getRelatedFreshUpdate();

        $ids = $this->getRelatedIds();

        if (count($ids) > 0) {
            $this->getRelated()->newQuery()->whereIn($key, $ids)->update($columns);
        }
    }

    /**
     * Get all of the IDs for the related models.
     *
     * @return array
     */
    public function getRelatedIds()
    {
        $related = $this->getRelated();

        $fullKey = $related->getQualifiedKeyName();

        return $this->getQuery()->select($fullKey)->lists($related->getKeyName());
    }

    /**
     * Save a new model and attach it to the parent model.
     *
     * @param  \Volcano\Database\ORM\Model  $model
     * @param  array  $joining
     * @param  bool   $touch
     * @return \Volcano\Database\ORM\Model
     */
    public function save(Model $model, array $joining = array(), $touch = true)
    {
        $model->save(array('touch' => false));

        $this->attach($model->getKey(), $joining, $touch);

        return $model;
    }

    /**
     * Save an array of new models and attach them to the parent model.
     *
     * @param  array  $models
     * @param  array  $joinings
     * @return array
     */
    public function saveMany(array $models, array $joinings = array())
    {
        foreach ($models as $key => $model) {
            $this->save($model, (array) array_get($joinings, $key), false);
        }

        $this->touchIfTouching();

        return $models;
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @param  array  $joining
     * @param  bool   $touch
     * @return \Volcano\Database\ORM\Model
     */
    public function create(array $attributes, array $joining = array(), $touch = true)
    {
        $instance = $this->related->newInstance($attributes);

        $instance->save(array('touch' => false));

        $this->attach($instance->getKey(), $joining, $touch);

        return $instance;
    }

    /**
     * Create an array of new instances of the related models.
     *
     * @param  array  $records
     * @param  array  $joinings
     * @return \Volcano\Database\ORM\Model
     */
    public function createMany(array $records, array $joinings = array())
    {
        $instances = array();

        foreach ($records as $key => $record) {
            $instances[] = $this->create($record, (array) array_get($joinings, $key), false);
        }

        $this->touchIfTouching();

        return $instances;
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param  array  $ids
     * @param  bool   $detaching
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        $changes = array(
            'attached' => array(), 'detached' => array(), 'updated' => array()
        );

        if ($ids instanceof Collection) $ids = $ids->modelKeys();

        $current = $this->newPivotQuery()->lists($this->otherKey);

        $records = $this->formatSyncList($ids);

        $detach = array_diff($current, array_keys($records));

        if ($detaching && count($detach) > 0) {
            $this->detach($detach);

            $changes['detached'] = (array) array_map(function($v) { return (int) $v; }, $detach);
        }

        $changes = array_merge(
            $changes, $this->attachNew($records, $current, false)
        );

        if (count($changes['attached']) || count($changes['updated'])) {
            $this->touchIfTouching();
        }

        return $changes;
    }

    /**
     * Format the sync list so that it is keyed by ID.
     *
     * @param  array  $records
     * @return array
     */
    protected function formatSyncList(array $records)
    {
        $results = array();

        foreach ($records as $id => $attributes) {
            if (! is_array($attributes)) {
                list($id, $attributes) = array($attributes, array());
            }

            $results[$id] = $attributes;
        }

        return $results;
    }

    /**
     * Attach all of the IDs that aren't in the current array.
     *
     * @param  array  $records
     * @param  array  $current
     * @param  bool   $touch
     * @return array
     */
    protected function attachNew(array $records, array $current, $touch = true)
    {
        $changes = array('attached' => array(), 'updated' => array());

        foreach ($records as $id => $attributes) {
            if (! in_array($id, $current)) {
                $this->attach($id, $attributes, $touch);

                $changes['attached'][] = (int) $id;
            } else if ((count($attributes) > 0) && $this->updateExistingPivot($id, $attributes, $touch)) {
                $changes['updated'][] = (int) $id;
            }
        }

        return $changes;
    }

    /**
     * Update an existing pivot record on the table.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        if (in_array($this->updatedAt(), $this->pivotColumns)) {
            $attributes = $this->setTimestampsOnAttach($attributes, true);
        }

        $updated = $this->newPivotStatementForId($id)->update($attributes);

        if ($touch) $this->touchIfTouching();

        return $updated;
    }

    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function attach($id, array $attributes = array(), $touch = true)
    {
        if ($id instanceof Model) $id = $id->getKey();

        $query = $this->newPivotStatement();

        $query->insert($this->createAttachRecords((array) $id, $attributes));

        if ($touch) $this->touchIfTouching();
    }

    /**
     * Create an array of records to insert into the pivot table.
     *
     * @param  array  $ids
     * @param  array  $attributes
     * @return array
     */
    protected function createAttachRecords($ids, array $attributes)
    {
        $records = array();

        $timed = ($this->hasPivotColumn($this->createdAt()) || $this->hasPivotColumn($this->updatedAt()));

        foreach ($ids as $key => $value) {
            $records[] = $this->attacher($key, $value, $attributes, $timed);
        }

        return $records;
    }

    /**
     * Create a full attachment record payload.
     *
     * @param  int    $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @param  bool   $timed
     * @return array
     */
    protected function attacher($key, $value, $attributes, $timed)
    {
        list($id, $extra) = $this->getAttachId($key, $value, $attributes);

        $record = $this->createAttachRecord($id, $timed);

        return array_merge($record, $extra);
    }

    /**
     * Get the attach record ID and extra attributes.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    protected function getAttachId($key, $value, array $attributes)
    {
        if (is_array($value)) {
            return array($key, array_merge($value, $attributes));
        }

        return array($value, $attributes);
    }

    /**
     * Create a new pivot attachment record.
     *
     * @param  int   $id
     * @param  bool  $timed
     * @return array
     */
    protected function createAttachRecord($id, $timed)
    {
        $record[$this->foreignKey] = $this->parent->getKey();

        $record[$this->otherKey] = $id;

        if ($timed) {
            $record = $this->setTimestampsOnAttach($record);
        }

        return $record;
    }

    /**
     * Set the creation and update timestamps on an attach record.
     *
     * @param  array  $record
     * @param  bool   $exists
     * @return array
     */
    protected function setTimestampsOnAttach(array $record, $exists = false)
    {
        $fresh = $this->parent->freshTimestamp();

        if (! $exists && $this->hasPivotColumn($this->createdAt())) {
            $record[$this->createdAt()] = $fresh;
        }

        if ($this->hasPivotColumn($this->updatedAt())) {
            $record[$this->updatedAt()] = $fresh;
        }

        return $record;
    }

    /**
     * Detach models from the relationship.
     *
     * @param  int|array  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($ids = array(), $touch = true)
    {
        if ($ids instanceof Model) $ids = (array) $ids->getKey();

        $query = $this->newPivotQuery();

        $ids = (array) $ids;

        if (count($ids) > 0) {
            $query->whereIn($this->otherKey, (array) $ids);
        }

        if ($touch) $this->touchIfTouching();

        $results = $query->delete();

        return $results;
    }

    /**
     * If we're touching the parent model, touch.
     *
     * @return void
     */
    public function touchIfTouching()
    {
        if ($this->touchingParent()) $this->getParent()->touch();

        if ($this->getParent()->touches($this->relationName)) $this->touch();
    }

    /**
     * Determine if we should touch the parent on sync.
     *
     * @return bool
     */
    protected function touchingParent()
    {
        return $this->getRelated()->touches($this->guessInverseRelation());
    }

    /**
     * Attempt to guess the name of the inverse of the relation.
     *
     * @return string
     */
    protected function guessInverseRelation()
    {
        $relation = Str::plural(class_basename($this->getParent()));

        return Str::camel($relation);
    }

    /**
     * Create a new query builder for the pivot table.
     *
     * @return \Volcano\Database\Query\Builder
     */
    protected function newPivotQuery()
    {
        $query = $this->newPivotStatement();

        foreach ($this->pivotWheres as $whereArgs) {
            call_user_func_array([$query, 'where'], $whereArgs);
        }

        return $query->where($this->foreignKey, $this->parent->getKey());
    }

    /**
     * Get a new plain query builder for the pivot table.
     *
     * @return \Volcano\Database\Query\Builder
     */
    public function newPivotStatement()
    {
        return $this->query->getQuery()->newQuery()->from($this->table);
    }

    /**
     * Get a new pivot statement for a given "other" ID.
     *
     * @param  mixed  $id
     * @return \Volcano\Database\Query\Builder
     */
    public function newPivotStatementForId($id)
    {
        return $this->newPivotQuery()->where($this->otherKey, $id);
    }

    /**
     * Create a new pivot model instance.
     *
     * @param  array  $attributes
     * @param  bool   $exists
     * @return \Volcano\Database\ORM\Relations\Pivot
     */
    public function newPivot(array $attributes = array(), $exists = false)
    {
        $pivot = $this->related->newPivot($this->parent, $attributes, $this->table, $exists, $this->using);

        return $pivot->setPivotKeys($this->foreignKey, $this->otherKey);
    }

    /**
     * Create a new existing pivot model instance.
     *
     * @param  array  $attributes
     * @return \Volcano\Database\ORM\Relations\Pivot
     */
    public function newExistingPivot(array $attributes = array())
    {
        return $this->newPivot($attributes, true);
    }

    /**
     * Set the columns on the pivot table to retrieve.
     *
     * @param  mixed  $columns
     * @return $this
     */
    public function withPivot($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->pivotColumns = array_merge($this->pivotColumns, $columns);

        return $this;
    }

    /**
     * Specify that the pivot table has creation and update timestamps.
     *
     * @param  mixed  $createdAt
     * @param  mixed  $updatedAt
     * @return \Volcano\Database\ORM\Relations\BelongsToMany
     */
    public function withTimestamps($createdAt = null, $updatedAt = null)
    {
        return $this->withPivot($createdAt ?: $this->createdAt(), $updatedAt ?: $this->updatedAt());
    }

    /**
     * Get the related model's updated at column name.
     *
     * @return string
     */
    public function getRelatedFreshUpdate()
    {
        return array($this->related->getUpdatedAtColumn() => $this->related->freshTimestamp());
    }

    /**
     * Get the key for comparing against the parent key in "has" query.
     *
     * @return string
     */
    public function getHasCompareKey()
    {
        return $this->getForeignKey();
    }

    /**
     * Get the fully qualified foreign key for the relation.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->table.'.'.$this->foreignKey;
    }

    /**
     * Get the fully qualified "other key" for the relation.
     *
     * @return string
     */
    public function getOtherKey()
    {
        return $this->table .'.' .$this->otherKey;
    }

    /**
     * Get the intermediate table for the relationship.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the relationship name for the relationship.
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relationName;
    }

}
