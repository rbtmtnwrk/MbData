<?php

class DummyService
{
    use \MbData\PageableFilteringTrait;
}

class PageableFilteringTraitTest extends TestCase
{
    public function test_params()
    {
        $service = new DummyService;

        $params = [
            'page'   => 2,
            'start'  => 200,
            'limit'  => 200,
            'sort'   => '[{"property":"name","direction":"ASC"}]',
            'filter' => 'test',
        ];

        $service->addSearchParams($params);

        $paging = $service->getPaging();
        $sort   = json_decode($params['sort'])[0];

        // var_dump(print_r([
        //         'file'   => __FILE__ . ' on ' . __LINE__,
        //         'paging' => $paging,
        //         'sort'   => $sort,
        //         'filter' => $service->getFilter(),
        //     ], true));

        $this->assertEquals($params['page'], $paging->page);
        $this->assertEquals($params['start'], $paging->start);
        $this->assertEquals($params['limit'], $paging->limit);
        $this->assertEquals($sort, $service->getSort());
        $this->assertEquals($params['filter'], $service->getFilter());
    }

    public function test_page_results()
    {
        $service = new DummyService;

        $params = [
            'page'   => 2,
            'start'  => 10,
            'limit'  => 10,
        ];

        /**
         * Test array results
         */
        $range = range(0, 100);
        $paged = $service->addSearchParams($params)->pageResults($range);

        $this->assertEquals(10, count($paged));
        $this->assertEquals(10, $paged[0]);
        $this->assertEquals(19, $paged[9]);

        /**
         * Test collection results
         */
        $range = collect($range);
        $paged = $service->addSearchParams($params)->pageResults($range);

        $this->assertEquals(10, count($paged));
        $this->assertEquals(10, $paged[0]);
        $this->assertEquals(19, $paged[9]);
    }

    public function test_filter_select()
    {
        $service = new DummyService;

        $params = [
            'page'   => 2,
            'start'  => 200,
            'limit'  => 200,
            'sort'   => '[{"property":"name","direction":"ASC"}]',
            'filter' => 'test',
        ];

        $service->addFilterSelect(['table.column'])->addSearchParams($params);

        // var_dump(print_r([
        //         'file'   => __FILE__ . ' on ' . __LINE__,
        //         'paging' => $paging,
        //         'sort'   => $sort,
        //         'filter' => $service->getFilter(),
        //     ], true));

        $this->assertEquals(['table.column'], $service->getFilteringSelect());
    }
}

/* End of file */
