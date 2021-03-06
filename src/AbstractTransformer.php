<?php

namespace MbData;

abstract class AbstractTransformer implements TransformerInterface
{
    protected $model      = null;
    protected $properties = [];
    protected $relations  = [];
    protected $transforms = [];
    protected $secure     = false;
    protected $security;

    private $conversions;

    private function getConversions()
    {
        (! $this->conversions) && ($this->conversions = [
            'bool' => function($mixed) {
                if ($mixed === 'true' || intval($mixed) === 1) {
                    return true;
                }
                return false;
            },
            'string' => function($mixed) {
                settype($mixed, 'string');
                return $mixed;
            },
            'int' => function($mixed) {
                settype($mixed, 'int');
                return $mixed;
            },
            'float' => function($mixed) {
                settype($mixed, 'float');
                return $mixed;
            }
        ]);

        return $this->conversions;
    }

    /**
     * For adding transforms during runtime. Closure must accept the
     * model and a reference to the transformed array. Adding a
     * name useful for checking if an added transform exists.
     * @param \Closure $transform
     * @param string   $name
     * @return this
     */
    public function addTransform(\Closure $transform, $name = null)
    {
        $name ? $this->transforms[$name] = $transform : $this->transforms[] = $transform;

        return $this;
    }

    public function hasTransform($name)
    {
        return isset($this->transforms[$name]);
    }

    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    public function getRelation($name)
    {
        if (! isset($this->relations[$name])) {
            throw new \Exception('No transfromer relation found for ' .  $name);
        }

        return $this->relations[$name];
    }

    public function setRelation($name, $transformer)
    {
        $this->relations[$name] = $transformer;

        return $this;
    }

    public function setSecure($secure = true)
    {
        $this->secure = $secure;

        return $this;
    }

    public function setSecurity($security)
    {
        $this->security = $security;

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
        $this->model = $model;

        $this->secure && $this->security && ($model = $this->security->secureModel($model, 'read'));

        $convert = $this->getConversions();
        $array   = [];

        foreach ($this->properties as $key => $value) {
            $property = is_int($key) ? $value : $key;

            if (! isset($model->$property)) {
                continue;
            }

            $array[$property] = is_int($key) ? $model->$property : $convert[$value]($model->$property);
        }

        foreach ($this->relations as $relation => $transformer) {
            if (! $this->relationLoaded($relation)) {
                continue;
            }

            $snakeName         = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $relation));
            $array[$snakeName] = [];
            $transform         = $this->closure($transformer->setSecure($this->secure)->setSecurity($this->security));

            // Has many relations.
            if ($model->$relation instanceof \Traversable || is_array($model->$relation)) {
                foreach ($model->$relation as $related) {
                    $array[$snakeName][] = $transform($related);
                }

                continue;
            }

            // One to one relation.
            $array[$snakeName] = $transform($model->$relation);
        }

        foreach ($this->transforms as $transform) {
            $result = $transform($model, $array);

            is_array($result) && $array = array_merge($array, $result);
        }

        return $array;
    }

    public function relationLoaded($relation, $model = null)
    {
        if (! $model) {
            if (! $this->model) {
                throw new \Exception('No model provided for relationLoaded');
            }

            $model = $this->model;
        }

        if (method_exists($model, 'relationLoaded')) {
            $loaded = $model->relationLoaded($relation);

            if (!$loaded) {
                return false;
            }

            if ($model->{$relation} == null) {
                return false;
            }

            if (method_exists($model->{$relation}, 'toArray')) {
                return !!count($model->{$relation}->toArray());
            }

            if ($model->{$relation} instanceof \Countable) {
                return !!count($model->{$relation});
            }

            return !empty($model->{$relation});
        }

        /**
         * For basic objects not implementing MbData/ModelInterface.
         */
        if (isset($model->{$relation})) {
            return is_array($model->{$relation}) || ($model->{$relation} instanceof \Countable && count($model->{$relation})) || !empty($model->{$relation});
        }

        return false;
    }
}

/* End of file */
