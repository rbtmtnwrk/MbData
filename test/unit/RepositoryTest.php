<?php

class TransformerDummy extends \MbData\AbstractTransformer
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

class RepositoryDummy extends \MbData\AbstractEloquentRepository
{
    //
}

class RepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_call_transformer_method()
    {
        $repository = new RepositoryDummy;
        $repository->setTransformer(new TransformerDummy);
        $foo = 'Foo!';

        $repository->callTransformerMethod('setFoo', [$foo]);

        $this->assertEquals($foo, $repository->getTransformer()->getFoo());
    }
}

/* End of file */
