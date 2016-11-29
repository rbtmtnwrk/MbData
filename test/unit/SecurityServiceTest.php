<?php

require 'TestIncludes.php';

class SecurityServiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_secureAttribute()
    {
        $service = new SecurityService;
        $fooPermissions = $service->getPermissions()['Foo'];

        // foo in permissions and true
        $this->assertEquals(true, $service->secureAttribute('Foo', 'foo', $fooPermissions));

        // Attribute not in permissions, so pass
        $this->assertEquals(true, $service->secureAttribute('Foo', 'not_in_permissions', $fooPermissions));

        // bar in permissions as a not pass
        $this->assertEquals(false, $service->secureAttribute('Foo', 'bar', $fooPermissions));

        // Class does not exist in permissions
        $this->assertEquals(true, $service->secureAttribute('Gleep', 'glop', null));
    }

    public function test_secureModel()
    {
        $service = new SecurityService;
        $foo = new Foo;
        $foo = $service->secureModel($foo);

        // foo in permissions and true
        $this->assertEquals(true, isset($foo->foo));

        // bar in permissions as a not pass
        $this->assertEquals(false, isset($foo->bar));

        // baz not in permission, so pass
        $this->assertEquals(true, isset($foo->baz));
    }

    public function test_secureData()
    {
        $service = new SecurityService;
        $foo     = new Foo;
        $data    = $foo->getAttributes();
        $data    = $service->secureData('Foo', $data);

        $expectation = [
            'foo' => 'Foo',
            'baz' => 'Baz',
        ];

        $this->assertEquals($expectation, $data);
    }

    public function test_create_delete()
    {
        $service = new SecurityService;

        $this->assertEquals(true, $service->canCreate('Foo'));
        $this->assertEquals(true, $service->canUpdate('Foo'));
        // Permissions does not exist so pass.
        $this->assertEquals(true, $service->canDelete('Foo'));

        $this->assertEquals(false, $service->canCreate('Bar'));
        $this->assertEquals(false, $service->canUpdate('Bar'));
        $this->assertEquals(false, $service->canDelete('Bar'));
    }
}

/* End of file */
