<?php

namespace Amethyst\Concerns\Auth;

use Amethyst\Models\User as BaseUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\HasApiTokens;
use Railken\Lem\Contracts\AgentContract;

class User extends BaseUser implements AuthenticatableContract, AuthorizableContract, AgentContract
{
    use Authenticatable;
    use Authorizable;
    use HasApiTokens;

    /**
     * Retrieve user for passport oauth.
     *
     * @param string $identifier
     *
     * @return User
     */
    public function findForPassport($identifier)
    {
        return (new static())->newQuery()->orWhere(function ($q) use ($identifier) {
            return $q->orWhere('email', $identifier)->orWhere('name', $identifier);
        })->where('enabled', 1)->first();
    }

    public function can($ability, $arguments = [])
    {
        return true;
    }
}
