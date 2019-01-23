<?php

require_once 'TestIncludes.php';

/**
 * @TODO: Add closure tests. Refactor original tests for included classes.
 */

class TransformerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_it_sets_params_and_returns_an_array()
    {
        $foo = new Foo;

        $transformer = new TransformerDummy;
        $transformer->setProperties([
            'foo',
            'far',
            'faz',
        ]);

        $transformation = $transformer->transform($foo);

        foreach ($foo->getAttributes() as $key => $value) {
            $this->assertEquals($value, $transformation[$key]);
        }
    }

    public function test_it_builds_only_existing_properties()
    {
        $foo = new Foo;
        unset($foo->far);

        $transformer = new TransformerDummy;
        $transformer->setProperties([
            'foo',
            'far',
        ]);

        $transformation = $transformer->transform($foo);

        $this->assertEquals(true, isset($transformation['foo']));
        $this->assertEquals(false, isset($transformation['far']));
        $this->assertEquals(false, isset($transformation['faz']));
    }

    public function test_basic_type_transformation()
    {
        $foo = new Foo;
        $foo->foo = '1';
        $foo->far = 'false';
        $foo->faz = '.5';
        $foo->fo  = 1;
        $foo->bar = 0;
        $foo->baz = 1;
        $foo->biz = '1';

        $expectations = [
            'foo' => 1,
            'far' => false,
            'faz' => .5,
            'fo'  => '1',
            'bar' => false,
            'baz' => true,
            'biz' => true,
        ];

        $transformer = new TransformerDummy;
        $transformer->setProperties([
            'foo' => 'int',
            'far' => 'bool',
            'faz' => 'float',
            'fo'  => 'string',
            'bar' => 'bool',
            'baz' => 'bool',
            'biz' => 'bool',
        ]);

        $transformation = $transformer->transform($foo);

        // var_dump(print_r([
        //     'file'           => __FILE__ . ' line ' . __LINE__,
        //     'foo'            => $foo,
        //     'expectations'   => $expectations,
        //     'transformation' => $transformation,
        // ], true));

        foreach ($expectations as $key => $value) {
            $this->assertSame($value, $transformation[$key]);
        }
    }

    public function test_it_sets_and_creates_and_checks_relations()
    {
        $foo  = new Foo;
        $bar  = new Bar;
        $bars = new \ArrayIterator([$bar]);

        $foo->setRelation('bars', $bars);

        $barTransformer = new TransformerDummy;
        $barTransformer->setProperties(['bar', 'baz', 'boo']);

        $transformer = new TransformerDummy;
        $transformer->setProperties(['foo', 'far', 'faz'])->setRelation('bars', $barTransformer);

        $transformation = $transformer->transform($foo);

        $this->assertTrue($transformer->relationLoaded('bars', $foo));
        $this->assertTrue(!$transformer->relationLoaded('foo', $foo));

        $this->assertEquals($foo->foo, $transformation['foo']);
        $this->assertEquals($foo->far, $transformation['far']);
        $this->assertEquals($foo->faz, $transformation['faz']);

        $this->assertEquals(true, isset($transformation['bars']));
        $this->assertEquals(true, count($transformation['bars']) > 0);

        $this->assertEquals($foo->bars[0]->bar, $transformation['bars'][0]['bar']);
        $this->assertEquals($foo->bars[0]->baz, $transformation['bars'][0]['baz']);
        $this->assertEquals($foo->bars[0]->boo, $transformation['bars'][0]['boo']);
    }

    public function test_it_gets_relations()
    {
        $transformer = new TransformerDummy;
        $foo = (object) ['test' => 1];

        $transformer->setRelation('foo', $foo);
        $transformer->getRelation('foo')->test = 2;

        $this->assertEquals(2, $transformer->getRelation('foo')->test);
    }

    public function test_eloquent_security()
    {
        $foo = new Foo;
        $foo->setRelation('bar', new Bar);

        // var_dump(print_r([
        //     'file' => __FILE__ . ' line ' . __LINE__,
        //     'foo' => $foo,
        // ], true));

        $security    = new SecurityService;
        $transformer = new FooTransformer;
        $transformer->setRelation('bar', new BarTransformer)->setSecure()->setSecurity($security);

        $expectation = [
            'foo' => 'Foo',
            'faz' => 'Faz',
            'bar' => [
                'bar' => 'Bar',
            ],
        ];

        $transformation = $transformer->transform($foo);

        // var_dump(print_r([
        //     'file' => __FILE__ . ' line ' . __LINE__,
        //     'foo' => $foo,
        //     'expectation' => $expectation,
        //     'transformation' => $transformation,
        // ], true));

        $this->assertSame($expectation, $transformation);
    }

    public function test_additional_transforms()
    {
        $foo = new Foo;
        $transformer = new FooTransformer;
        $transformer->addTransform(function($model, &$array) {
            $array['foo_tastic'] = $model->foo . '-tastic!';
        });

        $expectation = 'Foo-tastic!';
        $transformation = $transformer->transform($foo);

        $this->assertEquals($expectation, $transformation['foo_tastic']);

        $bar = new Bar;
        $transformer = new BarTransformer;
        $transformer->addTransform(function($model, $array) {
            $array['boo_tastic'] = $model->boo . '-tastic!';

            return $array;
        });

        $expectation = 'Boo-tastic!';
        $transformation = $transformer->transform($bar);

        $this->assertEquals($expectation, $transformation['boo_tastic']);
    }

    public function test_named_transform()
    {
        $foo = new Foo;
        $transformer = new FooTransformer;
        $transformer->addTransform(function($model, &$array) {
            $array['foo_tastic'] = $model->foo . '-tastic!';
        }, 'footastic');

        $expectation    = 'Foo-tastic!';
        $transformation = $transformer->transform($foo);

        $this->assertEquals($expectation, $transformation['foo_tastic']);
        $this->assertEquals(true, $transformer->hasTransform('footastic'));
    }

    public function test_nonmodel_object_transform()
    {
        $foo = (object) [
            'name' => 'Foo',
            'bar' => [(object) [
                'name' => 'Bar',
            ]]
        ];

        $relation = 'bar';

        $transformer = new TransformerDummy;
        $transformer->setProperties([
            'name',
        ]);

        $transformer->setRelation('bar', $transformer);
        $transformed = $transformer->transform($foo);

        // var_dump(print_r([
        //     'loc' => __FILE__ . ' @ ' . __LINE__,
        //     'foo' => $foo,
        //     'transformed' => $transformed,
        // ], true));

        $this->assertTrue($transformed['name'] == 'Foo');
        $this->assertTrue($transformed['bar'][0]['name'] == 'Bar');
    }

    public function test_null_transform()
    {
        $transformer = new BarTransformer;
        $result      = $transformer->transform(NULL);

        $this->assertEquals([], $result);
    }
}

/* End of file */
