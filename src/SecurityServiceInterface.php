<?php

namespace MbData;

interface SecurityServiceInterface
{
    public function getPermissions();

    public function setPermissions($permissions);

    public function secureAttribute($class, $attribute, $permissions);

    public function secureModel($model);

    public function secureData($class, array $data);

    public function canCreate($class);

    public function canUpdate($class);

    public function canDelete($class);
}

/* End of file */
