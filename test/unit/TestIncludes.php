<?php

class TransformerTest extends \MbData\AbstractTransformer
{
 //
}

class SecurityService extends \MbData\AbstractEloquentSecurityService
{
    public function __construct()
    {
        $crudTrue = [
            'create' => true,
            'read'   => true,
            'update' => true,
            'delete' => true,
        ];
        $crudFalse = [
            'create' => false,
            'read'   => false,
            'update' => false,
            'delete' => false,
        ];

        $this->permissions = [
            'Foo' => [
                'row' => $crudTrue,
                'column' => [
                    'foo'     => $crudTrue,
                    'bar'     => $crudFalse,
                    'baz'     => $crudTrue,
                ],
            ],
            'Bar' => [
                'row' => $crudFalse,
                'column' => [
                    'boo' => $crudTrue,
                ],
            ]
        ];
    }
}

class ModelBase
{
    /**
     * Simulate the getAttributes() method.
     * @return array
     */
    public function getAttributes()
    {
        $array = [];

        foreach ($this as $key => $value) {
            if ($key == 'relation') {
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }
}

class Foo extends ModelBase
{
    public $foo = 'Foo';
    public $bar = 'Bar';
    public $baz = 'Baz';
}

class Bar extends ModelBase
{
    public $boo = 'Boo';
    public $far = 'Far';
    public $faz = 'Faz';
}

class FooTransformer extends \MbData\AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties([
            'foo',
            'bar',
            'baz',
        ]);
    }
}

class BarTransformer extends \MbData\AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties([
            'boo',
            'far',
            'faz',
        ]);
    }
}

/* End of file */
