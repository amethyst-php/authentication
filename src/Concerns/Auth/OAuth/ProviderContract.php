<?php

namespace Railken\LaraOre\Concerns\Auth\OAuth;

use Illuminate\Http\Request;

interface ProviderContract
{
    public function getName();

    public function setClientId($client_id);

    public function setClientSecret($client_secret);

    public function getClientId();

    public function getClientSecret();

    public function issueAccessToken(Request $request);

    public function getUser($token);
}
