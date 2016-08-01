<?php

namespace MbData;

interface RepositoryInterface
{
    public function setColumns($columns);

    public function getColumns();

    public function getTransformer();

    public function setTransformer($transformer);

    public function transform();

    public function newQuery();

    public function find($id, $columns = null);

    public function all($columns = null);

    public function first($columns = null);

    public function where($column, $operator = null, $value = null);

    public function whereIn($column, $values);

    public function orWhere($column, $operator = null, $value = null);

    public function whereHas($relation, \Closure $callback);

    public function whereDoesntHave($relation, \Closure $callback = null);

    public function orWhereHas($relation, \Closure $callback);

    public function orderBy($column, $direction = 'asc');

    public function with($relations);

    public function create($data);

    public function save($data);

    public function thenTransform();

    public function thenGet();

    public function update($data);

    public function delete();
}

/* End of file */
