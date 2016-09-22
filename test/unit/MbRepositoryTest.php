<?php

class TransformerTest extends \MbData\AbstractTransformer
{
    private $foo;

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;

        return $this;
    }
}

class RepositoryTest extends \MbData\AbstractEloquentRepository
{
    //
}

class MbRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_call_transformer_method()
    {
        $repository = new RepositoryTest;
        $repository->setTransformer(new TransFormerTest);
        $foo = 'Foo!';

        $repository->callTransformerMethod('setFoo', [$foo]);

        $this->assertEquals($foo, $repository->getTransformer()->getFoo());
    }
}

/* End of file */
