<?php

namespace MbData;

abstract class AbstractEloquentRepository implements RepositoryInterface, EloquentRepositoryInterface
{
    protected $model;
    protected $entity;
    protected $transformer;
    protected $columns;
    protected $transform        = false;
    protected $wheres           = [];
    protected $whereIns         = [];
    protected $relationalWheres = [];
    protected $with             = [];
    protected $orderBys         = [];
    protected $notResettable     = ['model', 'transformer'];

    use \MbSupport\ResettableTrait;

    public function getColumns($columns = null)
    {
        $columns = $columns ?: ['*'];
        $columns = $this->columns ? $this->columns : $columns;

        return $columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function getModel()
    {
        return $this->model->replicate();
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function getTransformer()
    {
        return $this->transformer;
    }

     public function setTransformer($transformer)
     {
        $this->transformer = $transformer;
     }

    public function transform()
    {
        $this->transform = true;

        return $this;
    }

    public function find($id, $columns = null)
    {
        $columns = $this->getColumns($columns);
        $this->where('id', $id);

        return $this->transform ? $this->getTransformed($columns, 0) : $this->getBuilder()->first($columns);
    }

    public function all($columns = null)
    {
        $columns = $this->getColumns($columns);

        return $this->transform ? $this->getTransformed($columns) : $this->getBuilder()->get($columns);
    }

    public function first($columns = null)
    {
        $columns = $this->getColumns($columns);

        return $this->transform ? $this->getTransformed($columns, 0) : $this->getBuilder()->first($columns);
    }

    public function where($column, $operator = null, $value = null)
    {
        $boolean        = 'and';
        $this->wheres[] = (object) compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    public function whereIn($column, $values)
    {
        $this->whereIns[] = (object) compact('column', 'values');

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $boolean        = 'or';
        $this->wheres[] = (object) compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    public function whereHas($relation, \Closure $callback)
    {
        $args = compact('relation', 'callback');
        $args['type'] = 'whereHas';
        $this->relationalWheres[] = (object) $args;

        return $this;
    }

    public function whereDoesntHave($relation, \Closure $callback = null)
    {
        $args = compact('relation', 'callback');
        $args['type'] = 'whereDoesntHave';
        $this->relationalWheres[] = (object) $args;

        return $this;
    }

    public function orWhereHas($relation, \Closure $callback)
    {
        $args = compact('relation', 'callback');
        $args['type'] = 'orWhereHas';
        $this->relationalWheres[] = (object) $args;

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBys[] = (object) compact('column', 'direction');

        return $this;
    }

    public function with($with)
    {
        $this->with[] = is_array($with) ? $with : func_get_args();

        return $this;
    }

    /**
     * Extend this method with your own builder logic.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getBuilder()
    {
        $builder = $this->getModel();

        if (count($this->with)) {
            foreach ($this->with as $with) {
                $builder = $builder->with($with);
            }
        }

        if (count($this->wheres)) {
            foreach ($this->wheres as $where) {
                $builder = $builder->where($where->column, $where->operator, $where->value, $where->boolean);
            }
        }

        if (count($this->whereIns)) {
            foreach ($this->whereIns as $whereIn) {
                $builder = $builder->whereIn($whereIn->column, $whereIn->values);
            }
        }

        if (count($this->relationalWheres)) {
            foreach ($this->relationalWheres as $where) {
                $builder = $builder->{$where->type}($where->relation, $where->callback);
            }
        }

        if (count($this->orderBys)) {
            foreach ($this->orderBys as $orderBy) {
                $builder = $builder->orderBy($orderBy->column, $orderBy->direction);
            }
        }

        // When extending, call parent::getBuilder() and then add secret sauce if needed.
        return $builder;
    }

    protected function getTransformed($columns = null, $index = null)
    {
        $builder = $this->getBuilder();
        $models  = $builder->get($this->getColumns($columns));

        if (! $models) {
            return null;
        }

        $array = $this->doTransformation($models);

        return is_null($index) ? $array : $array[$index];
    }

    protected function doTransformation($transformable)
    {
        $transform = $this->transformer instanceof \Closure ? $this->transformer : $this->transformer->closure();

        if ($transformable instanceof \Traversable) {
            $array = [];

            foreach ($transformable as $model) {
                $array[] = $transform($model);
            }

            return $array;
        }

        return $transform($transformable);
    }

    public function create($data)
    {
        $model = $this->getModel()->create($data);

        if (! $model) {
            throw new \Exception('Create operation failed.');
        }

        $this->entity = $model;

        return $this;
    }

    /**
     * Save operation.
     * @param  array  $data
     * @param  object $entity Pass in a loaded model to save.
     * @return this
     */
    public function save($data, $entity = null)
    {
        $model  = $this->getModel();
        $entity = $entity ?: $this->getBuilder()->first($this->getColumns());

        if (! $entity) {
            throw new \Exception('No entity found for save operation.');
        }

        /**
         * Restrict to model columns.
         */
        $columns = $model->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($model->getTable());

        unset($data['id']); // Make sure the id is not updated.

        foreach ($columns as $column) {
            if (isset($data[$column])) {
                $entity->$column = $data[$column];
            }
        }

        if (! $entity->save()) {
            throw new \Exception('Save operation failed.');
        }

        $this->entity = $entity;

        return $this;
    }

    public function thenTransform()
    {
        if (! $this->entity) {
            throw new \Exception('No entity to transform');
        }

        return $this->doTransformation($this->entity);
    }

    public function thenGet()
    {
        if (! $this->entity) {
            throw new \Exception('No entity to get');
        }

        return $this->entity;
    }

    /**
     * Perform a model update.
     * @param  array $data
     * @return int
     */
    public function update($data)
    {
        return $this->getBuilder()->update($data);
    }

    /**
     * Perform a delete of the model.
     * @return bool
     */
    public function delete()
    {
        return $this->getBuilder()->delete();
    }
}

/* End of file */
