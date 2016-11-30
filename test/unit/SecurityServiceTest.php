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
        $this->assertEquals(true, $service->secureAttribute('Foo', 'foo', $fooPermissions, 'read'));

        // Attribute not in permissions, so pass
        $this->assertEquals(true, $service->secureAttribute('Foo', 'not_in_permissions', $fooPermissions, 'read'));

        // bar in permissions as a not pass
        $this->assertEquals(false, $service->secureAttribute('Foo', 'bar', $fooPermissions, 'read'));

        // Class does not exist in permissions
        $this->assertEquals(true, $service->secureAttribute('Gleep', 'glop', null, 'read'));
    }

    public function test_secureModel()
    {
        $service = new SecurityService;
        $foo = new Foo;
        $foo = $service->secureModel($foo, 'read');

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
        $data    = $service->secureData('Foo', $data, 'read');

        $expectation = [
            'foo' => 'Foo',
            'baz' => 'Baz',
        ];

        $this->assertEquals($expectation, $data);
    }

    public function test_create_delete()
    {
        $service = new SecurityService;

        $this->assertEquals(true, $service->can('create', 'Foo'));
        $this->assertEquals(true, $service->can('read', 'Foo'));
        $this->assertEquals(true, $service->can('update', 'Foo'));
        // Permissions does not exist so pass.
        $this->assertEquals(true, $service->can('delete', 'Foo'));

        $this->assertEquals(false, $service->can('create', 'Bar'));
        $this->assertEquals(false, $service->can('read', 'Bar'));
        $this->assertEquals(false, $service->can('update', 'Bar'));
        $this->assertEquals(false, $service->can('delete', 'Bar'));
    }
}

/* End of file */
