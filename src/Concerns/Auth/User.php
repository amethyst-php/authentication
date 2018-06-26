<?php

namespace Railken\LaraOre\Concerns\Auth;

use Railken\LaraOre\User\User as BaseUser;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends BaseUser implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
}
