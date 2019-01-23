<?php

namespace MbData;

trait CarbonDateTransformableTrait
{
    public function transformCarbonDates($model, $array, $fields = ['created_at', 'updated_at'])
    {
        if (! $model) {
            return $array;
        }

        foreach ($fields as $field) {
            if (! is_object($model->$field)) {
                $array[$field] = $model->$field;

                continue;
            }

            $array[$field] = $model->$field->toDateTimeString();
        }

        return $array;
    }
}

/* End of file */
