<?php
use \MbData\CarbonDateTransformableTrait;

class CarbonTransformable
{
    use CarbonDateTransformableTrait;
}

class CarbonTransformableTest extends \Tests\TestCase
{
    public function test_it_resets()
    {
        $transformable = new CarbonTransformable;
        $dateString    = '2016-01-01 00:00:00';
        $dummyData     = [
            'created_at' => \Carbon\Carbon::parse($dateString),
            'updated_at' => \Carbon\Carbon::parse($dateString),
        ];

        $array = $transformable->transformCarbonDates((object) $dummyData, []);

        $this->assertEquals($dateString, $array['created_at']);
        $this->assertEquals($dateString, $array['updated_at']);
    }
}

/* End of file */
