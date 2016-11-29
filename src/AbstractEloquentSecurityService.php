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

    public function secureData($class, array $data)
    {
        $permissions = isset($this->permissions[$class]) ? $this->permissions[$class] : null;

        foreach ($data as $key => $value) {
            if (! $this->secureAttribute($class, $key, $permissions)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * The following "can" functions are only an example scaffold of an
     * implementation for testing and sample purposes only. Override
     * them to suit your own permissions lookup facilities.
     */

    private function can($action, $class)
    {
        if (! isset($this->permissions[$class]) || ! isset($this->permissions[$class][$action])) {
            return true;
        }

        return $this->permissions[$class][$action];
    }

    public function canCreate($class)
    {
        return $this->can('_create', $class);
    }

    public function canUpdate($class)
    {
        return $this->can('_update', $class);
    }

    public function canDelete($class)
    {
        return $this->can('_delete', $class);
    }
}

/* End of file */
