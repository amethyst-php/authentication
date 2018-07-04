<?php

namespace Railken\LaraOre\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Railken\LaraOre\Api\Http\Controllers\Controller;
use Laravel\Socialite\Two\GithubProvider;
use Railken\LaraOre\User\UserManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * @var UserManager
     */
    protected $manager;

    /**
     * Create a new controller instance.
     *
     * @param UserManager
     *
     * @return void
     */
    public function __construct(UserManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Serialize token.
     *
     * @param \Laravel\Passport\Token $token
     *
     * @return array
     */
    public function serializeToken($token)
    {
        return [
            'access_token' => $token->accessToken,
            'token_type'   => 'Bearer',
            'expire_in'    => 0,
        ];
    }

    /**
     * List of all providers.
     *
     * @var array
     */
    protected $providers = [
        'github'   => GithubProvider::class,
    ];

    /**
     * Get provider.
     *
     * @param string $name
     * @param Request $request
     *
     * @return \Railken\LaraOre\Concerns\Auth\OAuth\Provider
     */
    public function getProvider($name, $request)
    {
        $class = isset($this->providers[$name]) ? $this->providers[$name] : null;

        if (!$class) {
            return;
        }

        return new $class($request, $request->input('client_id'), $request->input('client_secret'), $request->input('redirect_url'));
    }

    /**
     * Sign in a user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(Request $request)
    {
        $oauth_client = DB::table('oauth_clients')->where('password_client', 1)->first();

        if (!$oauth_client) {
            return $this->error([
                'code' => 'CLIENT_NOT_FOUND',
            ]);
        }

        $request->request->add([
            'username'      => $request->input('username'),
            'password'      => $request->input('password'),
            'scope'         => '*',
            'grant_type'    => 'password',
            'client_id'     => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
        ]);
        $request = Request::create('/oauth/token', 'POST', []);

        $response = Route::dispatch($request);

        $body = json_decode($response->getContent());

        if ($response->getStatusCode() === 200) {
            return $this->success(['data' => $body]);
        }

        if ($response->getStatusCode() === 401) {
            return $this->error(['code' => 'CREDENTIALS_NOT_VALID', 'message' => $body->error]);
        }

        if ($response->getStatusCode() === 500) {
            return $response;
        }

        throw new \Exception('Uhm...');
    }

    /**
     * Request token and generate a new one.
     *
     * @param string  $provider_name
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signInWithProvider($provider_name, Request $request)
    {
        $provider = $this->getProvider($provider_name, $request);
        $provider->stateless();

        if (!$provider) {
            return $this->error(['errors' => [
                'code'    => 'PROVIDER_NOT_FOUND',
                'message' => 'No provider found',
            ]]);
        }

        if ($request->input('code') !== null) {

            try {
                return $this->authenticateByCode($provider, $request->input('code'));
            } catch (\Exception $e) {
                return $this->error([
                    'code'    => 'CODE_NOT_VALID',
                    'message' => 'Code invalid or expired' . $e->getMessage(),
                ]);
            }
        }

    }

    public function authenticateByCode($provider, string $code)
    {
        $provider_user = $provider->user();

        $user = $this->manager->getRepository()->findOneByEmail($provider_user->getEmail());

        if (!$user) {
            $result = $this->manager->create([
                'name'     => $provider_user->getNickname() ? $provider_user->getNickname() : $provider_user->getName(),
                'role'     => 'user',
                'password' => str_random(32),
                'email'    => $provider_user->getEmail(),
            ]);

            if (!$result->ok()) {
                return $this->error(['errors' => $result->getSimpleErrors()]);
            }

            $user = $result->getResource();
        }

        
        $token = $user->createToken('login');

        return $this->success([
            'token_type'   => 'Bearer',
            'expires_in'   => 0,
            'access_token' => $token->accessToken,
        ]);

    }
}
