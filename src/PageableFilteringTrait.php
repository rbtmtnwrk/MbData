<?php
namespace MbData;

trait PageableFilteringTrait
{
    private $paging;
    private $sort;
    private $filter;
    private $filterProperties;

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

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Extracts filtering paging values from the given params array.
     * @return this
     */
    public function addSearchParams(array $params)
    {
        if (isset($params['sort'])) {
            $sort = json_decode($params['sort']);
            is_array($sort) && $sort = $sort[0];

            $this->setSort($sort);
        }

        $paging = ['page', 'start', 'limit'];

        foreach ($paging as $param) {
            isset($params[$param]) ? $$param = $params[$param] : $$param = null;
        }

        $this->setPaging($page, $start, $limit);

        isset($params['filter']) && $this->filter = $params['filter'];

        return $this;
    }

    /**
     * Applies filters for relational property values and sorting to the repository query.
     * @return this
     */
    public function applyFiltering()
    {
        /**
         * Set null default sort if none
         */
        (! $this->sort) && $this->sort = (object) ['property' => null];

        /**
         * Set up filters if there are any, and set the sort direction if the property name matches.
         */
        $filters = [];

        if ($this->filter) {
            foreach ($this->filterProperties as $key => $property) {
                if ($key == $this->sort->property || $property == $this->sort->property) {
                    $filters[] = [$property, $this->filter, $this->sort->direction];
                    continue;
                }

                $filters[] = [$property, $this->filter];
            }
        } else {
            /**
             * If the property has a key value pair map the property name
             */
            $property  = isset($this->filterProperties[$this->sort->property]) ? $this->filterProperties[$this->sort->property] : $this->sort->property;
            $filters[] = [$property, $this->sort->direction];
        }

        $filters && $this->repository->filter($filters);

        return $this;
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

        if (get_class($results) != 'Illuminate\Support\Collection') {
            throw new Exception('Only arrays or Illuminate\Support\Collection is supported for pageResults');
        }

        $paged = $results->slice($this->paging->start, $this->paging->limit);

        return $paged;
    }

    /**
     * Applies the paging values to the repository query.
     * @return this
     */
    private function applyPaging()
    {
        $this->paging->start && $this->repository->skip($this->paging->start - 1);
        $this->paging->limit && $this->repository->take($this->paging->limit);

        return $this;
    }
}

/* End of file */
