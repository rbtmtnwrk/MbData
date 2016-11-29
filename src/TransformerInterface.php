<?php

namespace MbData;

interface TransformerInterface
{
    public function setProperties($properties);

    public function setRelation($name, $transformer);

    public function setSecure($secure = true);

    public function setSecurity($security);

    public function transform($model);
}

/* End of file */
