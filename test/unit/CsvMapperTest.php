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


        $mapper->map(['first_name', 'last_name', 'phone', 'street']);

        $properties = $mapper->getColumnProperties();

        // var_dump(print_r([
        //         'file' => __FILE__ . ' on ' . __LINE__,
        //         'properties' => $mapper->getColumnProperties(),
        //     ], true));

        $this->assertEquals($properties['first']->csvColumn, 'first_name');
        $this->assertEquals($properties['last']->csvColumn, 'last_name');
        $this->assertEquals($properties['zip']->csvColumn, '');
        $this->assertEquals($properties['address']->csvColumn, 'street');
    }
}

/* End of file */
