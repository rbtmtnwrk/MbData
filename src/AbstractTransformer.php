<?php

namespace MbData;

abstract class AbstractTransformer implements TransformerInterface
{
    protected $properties;
    protected $relations = [];
    protected $factories = [];

    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    public function setRelation($name, $transformer)
    {
        $this->relations[$name] = $transformer;

        return $this;
    }

    public function closure($transformer = null)
    {
        $transformer = $transformer ? $transformer : $this;

        // Convert transformer to closure.
        $funcTransform = function ($model) use ($transformer) {
            return $transformer->transform($model);
        };

        return $transformer instanceof \Closure ? $transformer : $funcTransform;
    }

    public function transform($model)
    {
        $array = [];

        foreach ($this->properties as $property => $name) {
            $property = is_int($property) ? $name : $property;
            isset($model->$property) && $array[$name] = $model->$property;
        }

        foreach ($this->relations as $relation => $transformer) {
            if (! isset($model->$relation)) {
                continue;
            }

            $snakeName         = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $relation));
            $array[$snakeName] = [];
            $transform         = $this->closure($transformer);

            // Has many relations.
            if ($model->$relation instanceof \Traversable) {
                foreach ($model->$relation as $related) {
                    $array[$snakeName][] = $transform($related);
                }

                continue;
            }

            // One to one relation.
            $array[$snakeName] = $transform($model->$relation);
        }

        return $array;
    }
}

/* End of file */
