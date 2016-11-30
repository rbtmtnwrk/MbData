<?php

namespace MbData;

interface SecurityServiceInterface
{
    public function getPermissions();

    public function setPermissions($permissions);

    public function secureAttribute($class, $attribute, $permissions, $action);

    public function secureModel($model, $action);

    public function secureData($class, array $data, $action);
}

/* End of file */
