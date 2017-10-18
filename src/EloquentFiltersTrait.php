<?php

namespace MbData;

trait EloquentFiltersTrait
{
    protected $filterJoinsKeys = [];

    private function filterWhere($query, $table, $field, $filter, $operator)
    {
        $prefix   = $table ? $table . '.' : '';
        $operator = $operator ?: 'LIKE';
        $filter   = $operator == 'LIKE' ? "%$filter%" : $filter;

        if (is_array($field)) {
            $query->where(function($query) use($prefix, $field, $filter) {
                foreach ($field as $i => $fld) {
                    if ($i) {
                        $query->orWhere($prefix . $fld, $operator, $filter);
                        continue;
                    }

                    $query->where($prefix . $fld, $operator, $filter);
                }
            });

            return;
        }

        $query->where($prefix . $field, $operator, $filter);
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

    private function filter($query, $relation, $operator, $filter, $direction)
    {
        $relations = explode('.', $relation);
        $field     = array_pop($relations);

        strpos($field, ',') !== false && $field = explode(',', $field);

        if (! $relations) {
            $filter && $this->filterWhere($query, $this->getTable(), $field, $filter, $operator);
            $direction && $this->filterOrderBy($query, $this->getTable(), $field, $direction);

            return;
        }

        /**
         * Add a join for each level of relations.
         */
        $model = $this;

        foreach ($relations as $name) {
            /**
             * Default relation is HasOne
             */
            $relation     = $model->{$name}();
            $related      = $relation->getRelated();
            $relatedTable = $related->getTable();
            $foreignKey   = $relation->getForeignKey();
            $localKey     = $relation->getQualifiedParentKeyName();

            /**
             * Inspect relation type and adjust foreign/local keys
             */
            $relationClass = explode('\\', get_class($relation));
            $relationType  = array_pop($relationClass);

            switch($relationType) {
                case 'BelongsTo':
                        $foreignKey = $relatedTable . '.' . $related->getKeyName();
                        $localKey   = $model->getTable() . '.' . $relation->getForeignKey();
                    break;
                default:
            }

            /**
             * Creating a kvp for joins to make sure it is unique.
             */
            $joinKey = $relatedTable . $foreignKey . $localKey;

            if (! isset($this->filterJoinsKeys[$joinKey])) {
                $query->join($relatedTable, $foreignKey, '=', $localKey);
            }

            $this->filterJoinsKeys[$joinKey] = 1;
            $model = $related;
        }

        $direction && $this->filterOrderBy($query, $relatedTable, $field, $direction);

        $filter && $this->filterWhere($query, $relatedTable, $field, $filter, $operator);

        $query->select($this->getTable() . '.*'); // Get this model's properties
    }

    /**
     * Arguments are variable and optional. This method shift arguments
     * to the named parameter and calls the filter method.
     * @param  Illuminate\Database\Query\Builder $query
     * @param  string $relation
     * @param  string $operator
     * @param  string $filter
     * @param  string $direction
     */
    private function parseArguments($query, $relation, $operator, $filter = null, $direction = null)
    {
        /**
         * When there is an operation in the operator param.
         */
        if (in_array($operator, ['LIKE', '>', '<', '=', '!=', '<>'])) {
            /**
             * Adjust for the filter param having a direction value
             */
            if (strtolower($filter == 'asc') || strtolower($filter) == 'desc') {
                $this->filter($query, $relation, $operator, $f = null, $d = $filter);

                return;
            }

            /**
             * Otherwise all the params line up
             */
            $this->filter($query, $relation, $operator, $filter, $direction);

            return;
        }

        /**
         * Adjust for the operator having a direction value
         */
        if ((strtolower($operator) == 'asc') || (strtolower($operator) == 'desc')) {
            $this->filter($query, $relation, $o = null, $f = null, $d = $operator);

            return;
        }

        /**
         * Otherwise the operator param is the filter.
         */
        $this->filter($query, $relation, $o = null, $f = $operator, $d = $filter);
    }

    /**
     * Scope method for filtering and ordering relations. Method parameters are variable and can be mixed:
     * scopeFilter($query, [array of params])
     * scopeFilter($query, 'posts.title', 'asc') // Sort only on posts.name
     * scopeFilter($query, 'posts.comments.comment', 'great') // filter posts comments for 'great'
     * scopeFilter($query, 'posts.id', '>', 50, 'desc') // filter post for ids > 50 and sort them desc
     * @param  Illuminate\Database\Query\Builder $query
     * @param  mixed $relation
     * @param  string $operator
     * @param  string $filter
     * @param  string $direction
     */
    public function scopeFilter($query, $relation, $operator = null, $filter = null, $direction = null)
    {
        if (! is_array($relation)) {
            $this->parseArguments($query, $relation, $operator, $filter, $direction);

            return;
        }

        foreach ($relation as $args) {
            array_unshift($args, $query);
            call_user_func_array([$this, 'parseArguments'], $args);
        }
    }
}

/* End of file */