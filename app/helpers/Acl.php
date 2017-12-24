<?php

class Acl
{
    private $rules = [];
    private $user_roles = [];

    public function allow($role, $resource = null, $permission = null)
    {
        $this->allowDeny($role, $resource, $permission, 1);
    }

    public function deny($role, $resource = null, $permission = null)
    {
        $this->allowDeny($role, $resource, $permission, 0);
    }

    public function allowDeny($role, $resource = null, $permission = null, $allow)
    {
        if (!isset($this->rules[$role])) {
            $this->rules[$role] = [
                'all_resources' => [
                    'all_permissions' => null,
                    'by_permission' => [],
                ],
                'by_resource' => [],
            ];
        }

        if (is_null($resource) && is_null($permission)) {
            $this->rules[$role]['all_resources']['all_permissions'] = $allow;
        } else if (is_null($resource) && !is_null($permission)) {
            if (is_array($permission)) {
                foreach ($permission as $p) {
                    $this->allow($role, null, $p);
                }
            } else if (is_string($permission)) {
                $this->rules[$role]['all_resources']['by_permission'][$permission] = $allow;
            }
        } else if (!is_null($resource) && is_null($permission)) {
            if (is_array($resource)) {
                foreach ($resource as $r) {
                    $this->allow($role, $r);
                }
            } else if (is_string($resource)) {
                if (!isset($this->rules[$role]['by_resource'][$resource])) {
                    $this->rules[$role]['by_resource'][$resource] = [
                        'all_permissions' => null,
                        'by_permission' => [],
                    ];
                }

                $this->rules[$role]['by_resource'][$resource]['all_permissions'] = $allow;
            }
        } else if (!is_null($resource) && !is_null($permission)) {
            if (is_array($resource)) {
                foreach ($resource as $r) {
                    $this->allow($role, $r, $permission);
                }
            } else if (is_string($resource) && is_array($permission)) {
                foreach ($permission as $p) {
                    $this->allow($role, $resource, $p);
                }
            } else if (is_string($resource) && is_string($permission)) {
                // printa($role . '::' . $resource . '::' . $permission);

                if (!isset($this->rules[$role]['by_resource'][$resource])) {
                    $this->rules[$role]['by_resource'][$resource] = [
                        'all_permissions' => null,
                        'by_permission' => [],
                    ];
                }

                $this->rules[$role]['by_resource'][$resource]['by_permission'][$permission] = $allow;
            }
        }
    }

    public function addUserRole($role)
    {
        if (is_array($role)) {
            foreach ($role as $r) {
                $this->addUserRole($r);
            }
        } else if (is_string($role)) {
            $this->user_roles[] = $role;
        }
    }

    public function isAllowed($resource, $permission)
    {
        $has_permission = null;

        foreach ($this->user_roles as $user_role) {
            if (isset($this->rules[$user_role]['by_resource'][$resource]['by_permission'][$permission])) {
                $has_permission = $this->rules[$user_role]['by_resource'][$resource]['by_permission'][$permission];
            } else if (isset($this->rules[$user_role]['by_resource'][$resource]['all_permissions'])) {
                $has_permission = $this->rules[$user_role]['by_resource'][$resource]['all_permissions'];
            } else if (isset($this->rules[$user_role]['all_resources']['by_permission'][$permission])) {
                $has_permission = $this->rules[$user_role]['all_resources']['by_permission'][$permission];
            } else if (isset($this->rules[$user_role]['all_resources']['all_permissions'])) {
                $has_permission = $this->rules[$user_role]['all_resources']['all_permissions'];
            }
        }

        return (bool) $has_permission;
    }
}