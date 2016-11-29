<?php

namespace MbData;

abstract class AbstractEloquentSecurityService implements SecurityServiceInterface
{
    protected $permissions = [];

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Does a basic lookup in a permissions array. Override
     * this method to add your own secret security sauce.
     * @param  string       $class
     * @param  string       $attribute
     * @param  array|object $permissions
     * @return bool
     */
    public function secureAttribute($class, $attribute, $permissions)
    {
        /**
         * If there are no permissions for this class, just pass it.
         */
        if (! $permissions) {
            return true;
        }

        /**
         * Also pass if the attribute is not set.
         */
        return isset($permissions[$attribute]) ? $permissions[$attribute] : true;
    }

    /**
     * Secures a model. Assumes there is a permissions array indexed by the class name of your model.
     * @param  object $model
     * @return object
     */
    public function secureModel($model)
    {
        $class = get_class($model);
        $permissions = isset($this->permissions[$class]) ? $this->permissions[$class] : null;

        if ($permissions) {
            $attributes = $model->getAttributes();
            foreach ($attributes as $key => $value) {
                if (! $this->secureAttribute($class, $key, $permissions)) {
                    unset($model->$key);
                }
            }
        }

        return $model;
    }

    public function secureData($data)
    {
        // @TODO: Implement.
    }
}

/* End of file */
