<?php
namespace MbData;

trait PageableFilteringTrait
{
    protected $paging;
    protected $sort;
    protected $filter;
    protected $filterProperties;
    protected $filterColumns;
    protected $searchParams;
    protected $strict;
    protected $searchParamsTransformer;

    public function getPaging()
    {
        return $this->paging;
    }

    public function setPaging($page = 1, $start = 0, $limit = 50)
    {
        $this->paging = (object) compact('page', 'start', 'limit');

        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilterProperties()
    {
        return $this->filterProperties;
    }

    public function setFilterProperties($filterProperties)
    {
        $this->filterProperties = $filterProperties;

        return $this;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    public function addFilterSelect($columns)
    {
        $this->filterColumns = $columns;

        return $this;
    }

    public function getFilteringSelect()
    {
        return $this->filterColumns;
    }

    /**
     * Extracts filtering paging values from the given params array.
     * @return this
     */
    public function addSearchParams(array $params)
    {
        $this->searchParams = $params;

        if ($this->searchParamsTransformer) {
            $this->searchParamsTransformer->transform($this, $params);

            return $this;
        }

        isset($params['sort']) && $this->setSort($params['sort']);

        isset($params['filter']) && $this->setFilter($params['filter']);

        $paging = ['page', 'start', 'limit'];

        foreach ($paging as $param) {
            isset($params[$param]) ? $$param = $params[$param] : $$param = null;
        }

        $this->setPaging($page, $start, $limit);

        return $this;
    }

    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * Applies filters for relational property values and sorting to the repository query.
     * @return this
     */
    public function applyFiltering()
    {
        /**
         * Set up filters if there are any, and set the sort direction if the property name matches.
         */
        $filters = $this->createFiltersArray();

        if (! $filters) {
            /**
             * Validate sort property is a filter in local properties array before adding.
             */
            $property  = isset($this->filterProperties[$this->sort->property]) ? $this->filterProperties[$this->sort->property] : $this->sort->property;
            $property && $filters[] = [$property, $this->sort->direction];
        }

        $method = $this->strict ? 'filterStrict' : 'filter';

        $filters && $this->repository->{$method}($filters, $this->filterColumns);

        return $this;
    }

    /**
     * Set to filter strict by using this method.
     * @return this
     */
    public function applyFilteringStrict()
    {
        $this->strict = true;

        return $this->applyFiltering();
    }

    /**
     * Creates a filters array to be consumed by the repository filters methods.
     */
    protected function createFiltersArray()
    {
        $filters = [];

        /**
         * Filter single values across properties in the local filter properties array.
         */
        if (! is_array($this->filter)) {
            foreach ($this->filterProperties as $property) {
                $sort = ($property == $this->sort->property) ? $this->sort->direction : null;

                $filters[] = [$property, $this->filter, $sort];
            }

            return $filters;
        }

        /**
         * When filtering on multiple values, the query is more specific - filter on the properties given.
         * Consumer will have to do work here to make sure it is a valid column before hand.
         */
        $this->strict = true;

        foreach ($this->filter as $filter) {
            $sort = ($filter->property == $this->sort->property) ? $this->sort->direction : null;
            $operator = property_exists($filter, 'operator') ? $filter->operator : null;

            $filters[] = [$filter->property, $filter->value, $operator, $sort];
        }

        return $filters;
    }

    /**
     * Applies the paging config to a result set.
     * @param  array|Illuminate\Support\Collection $results
     * @return array|Illuminate\Support\Collection
     */
    public function pageResults($results)
    {
        if (is_array($results)) {
            $paged = array_slice($results, $this->paging->start, $this->paging->limit);

            return $paged;
        }

        if (! ($results instanceof \Illuminate\Support\Collection)) {
            throw new \Exception('Only Collections is supported for pageResults');
        }

        $paged = $results->slice($this->paging->start, $this->paging->limit);

        return $paged;
    }

    /**
     * Applies the paging values to the repository query.
     * @return this
     */
    protected function applyPaging()
    {
        $this->paging->start && $this->repository->skip($this->paging->start);
        $this->paging->limit && $this->repository->take($this->paging->limit);

        return $this;
    }
}

/* End of file */
