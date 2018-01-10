<?php

class DummyCsvMapper extends \MbData\CsvMapper
{
    //
}

class CsvMapperTest extends TestCase
{
    public function dummy_csv()
    {
        return [
            ['first_name', 'last_name'],
            ['Mary'], ['Samples'],
        ];
    }

    public function test_add_col_prop()
    {
        $mapper = new DummyCsvMapper;

        $mapper->addColumnProperty('first', 'First Name', ['first']);

        $property = $mapper->getColumnProperties()['first'];

        $expected = (object) [
                'column'    => 'first',
                'label'     => 'First Name',
                'keywords'  => ['first'],
                'csvColumn' => '',
                'index'     => null,
            ];

        // var_dump(print_r([
        //         'file' => __FILE__ . ' on ' . __LINE__,
        //         'property' => $property,
        //     ], true));

        $this->assertEquals($expected, $property);
    }

    public function test_parse_columns()
    {
        $mapper = new DummyCsvMapper;

        $mapper->addColumnProperty('first', 'First Name', ['first'])
            ->addColumnProperty('last', 'Last Name', ['last'])
            ->addColumnProperty('zip', 'Zip Code', ['zip', 'postal', 'code'])
            ->addColumnProperty('address', 'Address', ['address', 'street']);


        $map = $mapper->map(['first_name', 'last_name', 'phone', 'street']);

        // var_dump(print_r([
        //         'file' => __FILE__ . ' on ' . __LINE__,
        //         'properties' => $mapper->getColumnProperties(),
        //     ], true));

        $this->assertEquals($map['first']->csvColumn, 'first_name');
        $this->assertEquals($map['first']->index, 0);
        $this->assertEquals($map['last']->csvColumn, 'last_name');
        $this->assertEquals($map['last']->index, 1);
        $this->assertEquals($map['zip']->csvColumn, '');
        $this->assertEquals($map['address']->csvColumn, 'street');
        $this->assertEquals($map['address']->index, 3);

        /**
         * Test get by index
         */
        $this->assertEquals('address', $mapper->getByIndex(3)->column);
    }
}

/* End of file */
