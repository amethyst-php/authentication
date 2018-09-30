<?php

namespace Railken\Amethyst\Http\Controllers\App;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Two\BitbucketProvider;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\LinkedInProvider;
use Railken\Amethyst\Api\Http\Controllers\Controller;
use Railken\Amethyst\Managers\UserManager;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @var UserManager
     */
    protected $manager;

    /**
     * List of all providers.
     *
     * @var array
     */
    protected $providers = [
        'github'    => GithubProvider::class,
        'bitbucket' => BitbucketProvider::class,
        'google'    => GoogleProvider::class,
        'facebook'  => FacebookProvider::class,
        'linkedin'  => LinkedInProvider::class,
    ];

    /**
     * Create a new controller instance.
     *
     * @param UserManager $manager
     */
    public function __construct(UserManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get provider.
     *
     * @param string  $name
     * @param Request $request
     *
     * @return \Laravel\Socialite\Two\AbstractProvider|null
     */
    public function getProvider($name, $request)
    {
        $class = isset($this->providers[$name]) ? $this->providers[$name] : null;

        if (!$class) {
            return null;
        }

        return new $class($request, $request->input('client_id'), $request->input('client_secret'), $request->input('redirect_url'));
    }

    /**
     * Sign in a user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signIn(Request $request)
    {
        $oauth_client = DB::table('oauth_clients')->where('password_client', 1)->first();

        if (!$oauth_client) {
            return $this->response([
                'code' => 'CLIENT_NOT_FOUND',
            ], Response::HTTP_BAD_REQUEST);
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
            return $this->response(['data' => $body], Response::HTTP_OK);
        }

        if ($response->getStatusCode() === 401) {
            return $this->response(['code' => 'CREDENTIALS_NOT_VALID', 'message' => $body->error], Response::HTTP_BAD_REQUEST);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signInWithProvider($provider_name, Request $request)
    {
        $provider = $this->getProvider($provider_name, $request);

        if (!$provider) {
            return $this->response(['errors' => [
                'code'    => 'PROVIDER_NOT_FOUND',
                'message' => 'No provider found',
            ]], Response::HTTP_BAD_REQUEST);
        }

        $provider->stateless();

        if ($request->input('code') !== null) {
            try {
                return $this->authenticateByCode($provider, $request->input('code'));
            } catch (\Exception $e) {
                return $this->response([
                    'code'    => 'CODE_NOT_VALID',
                    'message' => 'Code invalid or expired'.$e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Authenticate a user by the "code" of oauth2.
     *
     * @param \Laravel\Socialite\Two\AbstractProvider $provider
     * @param string                                  $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authenticateByCode($provider, string $code)
    {
        $provider_user = $provider->user();

        /** @var \Railken\Amethyst\Repositories\UserRepository */
        $repository = $this->manager->getRepository();

        $user = $repository->findOneByEmail($provider_user->getEmail());

        if (!$user) {
            $result = $this->manager->create([
                'name'     => $provider_user->getNickname() ? $provider_user->getNickname() : $provider_user->getName(),
                'role'     => 'user',
                'password' => str_random(32),
                'email'    => $provider_user->getEmail(),
            ]);

            if (!$result->ok()) {
                return $this->response(['errors' => $result->getSimpleErrors()], Response::HTTP_BAD_REQUEST);
            }

            $user = $result->getResource();
        }

        $token = Container::getInstance()->make(\Laravel\Passport\PersonalAccessTokenFactory::class)->make(
            $user->getKey(), 'login', []
        );

        return $this->response([
            'token_type'   => 'Bearer',
            'expires_in'   => 0,
            'access_token' => $token->accessToken,
        ], Response::HTTP_OK);
    }
}
