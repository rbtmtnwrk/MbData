<?php

namespace MbData;

abstract class AbstractEloquentRepository implements RepositoryInterface, EloquentRepositoryInterface
{
    protected $model;
    protected $entity;
    protected $transformer;
    protected $columns;
    protected $transform = false;
    protected $secure    = false;
    protected $security;
    protected $calls     = [];

    protected $notResettable    = ['model', 'transformer', 'security'];

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

    public function setSecurity(\MbData\SecurityServiceInterface $security)
    {
        $this->security = $security;

        return $this;
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function callTransformerMethod($method, $params = [])
    {
        call_user_func_array([$this->transformer, $method], $params);

        return $this;
    }

    public function transform()
    {
        $this->transform = true;

        return $this;
    }

    public function secure()
    {
        $this->secure = true;

        return $this;
    }

    public function newQuery()
    {
        $this->reset();

        return $this;
    }

    public function find($id, $columns = null)
    {
        $this->where('id', $id);

        return $this->first($columns);
    }

    public function first($columns = null)
    {
        $columns      = $this->getColumns($columns);
        $this->entity = $this->getBuilder()->first($columns);

        return $this->transform ? $this->doTransformation($this->entity) : $this->entity;
    }

    public function get($columns = null)
    {
        return $this->all($columns);
    }

    public function all($columns = null)
    {
        $columns      = $this->getColumns($columns);
        $this->entity = $this->getBuilder()->get($columns);

        return $this->transform ? $this->doTransformation($this->entity) : $this->entity;
    }

    private function addCall($name, $params)
    {
        (! isset($this->calls[$name])) && $this->calls[$name] = [];

        $this->calls[$name][] = $params;
    }

    public function where($column, $operator = null, $value = null)
    {
        $params = func_get_args();
        $params['boolean'] = 'and';

        $this->addCall('where', $params);

        return $this;
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->addCall('whereIn', func_get_args());

        return $this;
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $this->addCall('whereNotIn', func_get_args());

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $params = func_get_args();
        $params['boolean'] = 'or';

        $this->addCall('orWhere', $params);

        return $this;
    }

    public function whereHas($relation, \Closure $callback)
    {
        $this->addCall('whereHas', func_get_args());

        return $this;
    }

    public function whereDoesntHave($relation, \Closure $callback = null)
    {
        $this->addCall('whereDoesntHave', func_get_args());

        return $this;
    }

    public function orWhereHas($relation, \Closure $callback)
    {
        $this->addCall('orWhereHas', func_get_args());

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->addCall('orderBy', func_get_args());

        return $this;
    }

    public function with($with)
    {
        $this->addCall('with', func_get_args());

        return $this;
    }

    public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
    {
        $this->addCall('join', func_get_args());

        return $this;
    }

    public function joinWhere($table, $one, $operator, $two, $type = 'inner')
    {
        return $this->join($table, $one, $operator, $two, $type, true);
    }

    public function leftJoin($table, $one, $operator = null, $two = null)
    {
        return $this->join($table, $one, $operator, $two, 'left');
    }

    public function leftJoinWhere($table, $one, $operator, $two)
    {
        return $this->joinWhere($table, $one, $operator, $two, 'left');
    }

    public function rightJoin($table, $one, $operator = null, $two = null)
    {
        return $this->join($table, $one, $operator, $two, 'right');
    }

    public function rightJoinWhere($table, $one, $operator, $two)
    {
        return $this->joinWhere($table, $one, $operator, $two, 'right');
    }

    public function skip($value)
    {
        $this->addCall('skip', func_get_args());

        return $this;
    }

    public function take($value)
    {
        $this->addCall('take', func_get_args());

        return $this;
    }

    public function load($relations)
    {
        if (! $this->entity) {
            throw new \Exception('No entity found for load operation.');
        }

        $relations = is_array($relations) ? $relations : func_get_args();
        $this->entity->load($relations);

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

        foreach ($this->calls as $name => $call) {
            foreach ($call as $params) {
                $builder = call_user_func_array([$builder, $name], $params);
            }
        }

        // When extending, call parent::getBuilder() and then add secret sauce if needed.
        return $builder;
    }

    protected function doTransformation($transformable)
    {
        if (! $transformable) {
            return null;
        }

        /**
         * Create a closure of the transformer if not already one. If not one, set the security attributes and then get the closure.
         */
        $transform = $this->transformer instanceof \Closure ? $this->transformer : $this->transformer->setSecure($this->secure)->setSecurity($this->security)->closure();

        if ($transformable instanceof \Traversable) {
            $array = [];

            foreach ($transformable as $model) {
                $array[] = $transform($model);
            }

            return $array;
        }

        return $transform($transformable);
    }

    private function secureData($data, $action)
    {
        if ($this->secure && $this->security) {
            $data = $this->security->secureData(get_class($this->getModel()), $data, $action);
        }

        return $data;
    }

    public function create($data)
    {
        $data  = $this->secureData($data, 'create');
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
        $data   = $this->secureData($data, 'update');
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

    public function thenTransform($with = null)
    {
        if (! $this->entity) {
            throw new \Exception('No entity to transform');
        }

        if ($with) {
            $with = is_array($with) ? $with : func_get_args();
            $this->load($with);
        }

        return $this->doTransformation($this->entity);
    }

    public function thenTransformWith($with)
    {
        $with = is_array($with) ? $with : func_get_args();

        return $this->thenTransform($with);
    }

    public function thenGet($with = null)
    {
        if (! $this->entity) {
            throw new \Exception('No entity to get');
        }

        if ($with) {
            $with = is_array($with) ? $with : func_get_args();
            $this->load($with);
        }

        return $this->entity;
    }

    public function thenGetWith($with = null)
    {
        $with = is_array($with) ? $with : func_get_args();

        return $this->thenGet($with);
    }

    /**
     * Perform a model update.
     * @param  array $data
     * @return int
     */
    public function update($data)
    {
        $data = $this->secureData($data, 'update');

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
