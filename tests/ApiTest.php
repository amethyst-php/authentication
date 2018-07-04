<?php

namespace Railken\LaraOre\Auth\Tests;

use Illuminate\Support\Facades\Config;
use Railken\LaraOre\Support\Testing\ApiTestableTrait;
use donatj\MockWebServer\MockWebServer;
use duncan3dc\Laravel\Dusk;

class ApiTest extends BaseTest
{
    use ApiTestableTrait;
    
    /**
     * Retrieve basic url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return Config::get('ore.api.router.prefix').Config::get('ore.auth.http.router.prefix');
    }

    /**
     * Test common requests.
     *
     * @return void
     */
    public function testSignIn()
    {
        $response = $this->post($this->getBaseUrl(), [
            'username' => 'admin@admin.com',
            'password' => 'vercingetorige',
        ]);
        $this->assertOrPrint($response, 200);

        $access_token = json_decode($response->getContent())->data->access_token;
        $this->withHeaders(['Authorization' => 'Bearer '.$access_token]);

        return $response;
    }

    /**
     * Test common requests.
     *
     * @return void
     */
    public function testSignInGithub()
    {
        $client_id = env('TEST_GITHUB_OAUTH_CLIENT_ID');
        $client_secret = env('TEST_GITHUB_OAUTH_CLIENT_SECRET');

        $server = new MockWebServer(env('TEST_MOCK_WEB_SERVER_PORT'));
        $server->start();
        $server_url = $server->getServerRoot();

        $github_url = "https://github.com/login/oauth/authorize?scope=user:email&client_id={$client_id}&client_secret={$client_secret}";

        $dusk = new Dusk;
        $dusk->visit($github_url);
                
        $dir = __DIR__."/../build/";
        $this->prepareDir($dir);
        
        $dusk->getDriver()->takeScreenshot($dir."/1.png");

        # Authenticate
        $dusk->value('#login_field', env('TEST_GITHUB_USERNAME'));
        $dusk->type('#password', env('TEST_GITHUB_PASSWORD'));
        $dusk->click(".auth-form-body .btn-primary");
        $dusk->pause(3000);

        $url = $dusk->getDriver()->getCurrentURL();
        $host = parse_url($url, PHP_URL_HOST);
        $dusk->getDriver()->takeScreenshot($dir."/2.png");

        # Authorize
        if ($host === 'github.com') {
            $dusk->click("#js-oauth-authorize-btn");
            $dusk->pause(5000);
        }

        $dusk->getDriver()->takeScreenshot($dir."/3.png");

        $url = $dusk->getDriver()->getCurrentURL();

        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $params);
        $code = $params['code'];

        $response = $this->post($this->getBaseUrl() . "/github", [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code
        ]);

        $this->assertOrPrint($response, 200);
    }

    public function prepareDir($dir)
    {
        $dir = dirname($dir);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
