<?php

namespace MbData;

interface TransformerInterface
{
    public function addTransform(\Closure $transform, $name = null);

    public function hasTransform($name);

    public function setProperties($properties);

    public function getRelation($name);

    public function setRelation($name, $transformer);

    public function setSecure($secure = true);

    public function setSecurity($security);

    public function transform(\MbData\ModelInterface $model);
}

/* End of file */
