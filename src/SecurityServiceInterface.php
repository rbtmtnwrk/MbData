<?php

namespace MbData;

interface SecurityServiceInterface
{
    public function getPermissions();

    public function setPermissions($permissions);

    public function secureAttribute($class, $attribute, $permissions);

    public function secureModel($model);

    public function secureData($data);
}

/* End of file */
