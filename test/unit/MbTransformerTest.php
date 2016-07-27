<?php

/**
 * @TODO: Add closure tests
 */

class TransformerTest extends \MbData\AbstractTransformer
{
 //
}

class MbTransformerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_it_sets_params_and_returns_an_array()
    {
        $modelData = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz',
        ];

        $mockModel = (object) $modelData;

        $modelData['bazzle'] = 'Baz';
        unset($modelData['baz']);

        $transformer = new TransformerTest;
        $transformer->setProperties([
            'foo',
            'bar',
            'baz' => 'bazzle',
        ]);

        $transformation = $transformer->transform($mockModel);

        $this->assertEquals($modelData, $transformation);
    }

    public function test_it_builds_only_existing_properties()
    {
        $modelData = [
            'foo' => 'Foo',
        ];

        $mockModel = (object) $modelData;

        $transformer = new TransformerTest;
        $transformer->setProperties([
            'foo',
            'bar',
        ]);

        $transformation = $transformer->transform($mockModel);

        $this->assertEquals($modelData, $transformation);
    }

    public function test_it_sets_and_creates_relations()
    {
        $modelData = [
            'foo'     => 'Foo',
            'bar'     => 'Bar',
            'burgers' => [[
                'bun'     => 1,
                'pickles' => 0,
            ]]
        ];

        $mockModel          = (object) $modelData;
        $mockModel->burgers = new \ArrayIterator([(object) $modelData['burgers'][0]]);

        $bugerTransformer = new TransformerTest;
        $bugerTransformer->setProperties([
            'bun',
            'pickles',
        ]);

        $transformer = new TransformerTest;
        $transformer->setProperties([
            'foo',
            'bar',
        ])->setRelation('burgers', $bugerTransformer);

        $transformation = $transformer->transform($mockModel);

        $this->assertEquals($modelData, $transformation);
    }
}

/* End of file */
