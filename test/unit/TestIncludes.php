<?php

class TransformerTest extends \MbData\AbstractTransformer
{
 //
}

class SecurityService extends \MbData\AbstractEloquentSecurityService
{
    public function __construct()
    {
        $this->permissions = [
            'Foo' => [
                '_create' => true,
                '_update' => true,
                'foo'     => true,
                'bar'     => false,
            ],
            'Bar' => [
                '_create' => false,
                '_update' => false,
                '_delete' => false,
                'boo'     => false,
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
