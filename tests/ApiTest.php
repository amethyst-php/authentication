<?php

namespace Railken\LaraOre\Auth\Tests;

use Illuminate\Support\Facades\Config;

class ApiTest extends BaseTest
{
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
        $response = $this->post($this->getBaseUrl().'/sign-in', [
            'username' => 'admin@admin.com',
            'password' => 'vercingetorige',
        ]);
        $response->assertStatus(200);

        $access_token = json_decode($response->getContent())->data->access_token;
        $this->withHeaders(['Authorization' => 'Bearer '.$access_token]);

        return $response;
    }
}
