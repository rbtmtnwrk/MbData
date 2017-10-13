<?php

namespace MbData;

trait EloquentFiltersTrait
{
    protected $filterJoinsKeys = [];

    private function filterWhere($query, $table, $field, $filter)
    {
        $prefix = $table ? $table . '.' : '';

        if (is_array($field)) {
            $query->where(function($query) use($prefix, $field, $filter) {
                foreach ($field as $i => $fld) {
                    if ($i) {
                        $query->orWhere($prefix . $fld, 'LIKE', "%$filter%");

                        continue;
                    }

                    $query->where($prefix . $fld, 'LIKE', "%$filter%");
                }
            });

            return;
        }

        $query->where($prefix . $field, 'LIKE', "%$filter%");
    }

    private function filterOrderBy($query, $table, $field, $direction)
    {
        $prefix = $table ? $table . '.' : '';

        if (is_array($field)) {
            foreach ($field as $fld) {
                $query->orderBy($prefix . $fld, $direction);
            }

            return;
        }

        $query->orderBy($prefix . $field, $direction);
    }

    private function filter($query, $relation, $filter, $direction = null)
    {
        $relations = explode('.', $relation);
        $field     = array_pop($relations);

        strpos($field, ',') !== false && $field = explode(',', $field);

        if (strtoupper($filter) == 'ASC' || strtoupper($filter) == 'DESC') {
            $direction = $filter;
            $filter    = null;
        }

        if (! $relations) {
            $filter && $this->filterWhere($query, null, $field, $filter);
            $direction && $this->filterOrderBy($query, null, $field, $direction);

            return;
        }

        /**
         * Add a join for each level of relations.
         */
        $model = $this;

        foreach ($relations as $name) {
            $relation   = $model->{$name}();
            $related    = $relation->getRelated();
            $table      = $related->getTable();
            $foreignKey = $relation->getForeignKey();

            /**
             * Creating a kvp for joins to make sure it is unique.
             */
            $joinKey = $table . $foreignKey . $model->getTable() . $related->getForeignKey();

            if (! isset($this->filterJoinsKeys[$joinKey])) {
                $query->join($table, $foreignKey, '=', $model->getTable() . '.' . $related->getForeignKey());
            }

            $this->filterJoinsKeys[$joinKey] = 1;
            $model = $related;
        }

        $direction && $this->filterOrderBy($query, $table, $field, $direction);

        $filter && $this->filterWhere($query, $table, $field, $filter);

        $query->select($this->getTable() . '.*'); // Get this model's properties
    }

    public function scopeFilter($query, $param, $filter = null, $direction = null)
    {
        if (! is_array($param)) {
            $this->filter($query, $param, $filter, $direction);

            return;
        }

        foreach ($param as $key => $value) {
            if (! is_array($value)) {
                $this->filter($query, $key, $value);

                continue;
            }

            $this->filter($query, $key, $f = $value[0], $d = $value[1]);
        }
    }
}

/* End of file */