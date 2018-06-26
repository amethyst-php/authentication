<?php

namespace Railken\LaraOre\Concerns\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Railken\LaraOre\User\User as BaseUser;

class User extends BaseUser implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
}
