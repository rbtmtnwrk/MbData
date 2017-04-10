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
                    'far'     => $crudFalse,
                    'faz'     => $crudTrue,
                ],
            ],
            'Bar' => [
                'row' => $crudFalse,
                'column' => [
                    'bar' => $crudTrue,
                ],
            ]
        ];
    }
}

class ModelBase
{
    public $relations = [];
    /**
     * Simulate the getAttributes() method.
     * @return array
     */
    public function getAttributes()
    {
        $array = [];

        foreach ($this as $key => $value) {
            /**
             * Skip the relations array or any property that is a relation.
             */
            if ($key == 'relations' || isset($this->getRelations()[$key])) {
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Simulate relations methods
     */
    public function getRelations()
    {
        return $this->relations;
    }

    public function setRelation($name, $mixed)
    {
        $this->relations[$name] = true;
        $this->$name = $mixed;
    }
}

class Foo extends ModelBase
{
    public $foo = 'Foo';
    public $far = 'Far';
    public $faz = 'Faz';
}

class Bar extends ModelBase
{
    public $bar = 'Bar';
    public $baz = 'Baz';
    public $boo = 'Boo';
}

class FooTransformer extends \MbData\AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties([
            'foo',
            'far',
            'faz',
        ]);
    }
}

class BarTransformer extends \MbData\AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties([
            'bar',
            'baz',
            'boo',
        ]);
    }
}

/* End of file */
