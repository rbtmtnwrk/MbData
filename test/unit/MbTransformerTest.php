<?php

require 'TestIncludes.php';

/**
 * @TODO: Add closure tests. Refactor original tests for included classes.
 */

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

    public function test_basic_type_transformation()
    {
        $modelData = [
            'foo' => '1',
            'bar' => 'false',
            'baz' => '.5',
            'fub' => 1
        ];

        $mockModel = (object) $modelData;

        $modelData['foo'] = 1;
        $modelData['bar'] = false;
        $modelData['baz'] = .5;
        $modelData['fub'] = '1';

        $transformer = new TransformerTest;
        $transformer->setProperties([
            'foo' => 'int',
            'bar' => 'bool',
            'baz' => 'float',
            'fub' => 'string',
        ]);

        $transformation = $transformer->transform($mockModel);

        foreach ($modelData as $key => $value) {
            $this->assertSame(gettype($value), gettype($transformation[$key]));
        }
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

    public function test_eloquent_security()
    {
        $foo = new Foo;
        $foo->relation = new Bar;

        $security = new SecurityService;

        $transformer = new FooTransformer;
        $transformer->setRelation('relation', new BarTransformer)
            ->setSecure()
            ->setSecurity($security);

        $expectation = [
            'foo' => 'Foo',
            'baz' => 'Baz',
            'relation' => [
                'boo' => 'Boo',
            ],
        ];

        $transformation = $transformer->transform($foo);

        $this->assertEquals($expectation, $transformation);
    }
}

/* End of file */
