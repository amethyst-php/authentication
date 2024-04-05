<?php

namespace Amethyst\Tests;

use Illuminate\Support\Facades\Config;

class ApiTest extends BaseTest
{
    /**
     * Retrieve basic url.
     *
     * @return string
     */
    public function getResourceUrl()
    {
        return Config::get('amethyst.api.http.app.router.prefix').Config::get('amethyst.authentication.http.app.authentication.router.prefix');
    }

    /**
     * Test common requests.
     */
    public function testSignInBasic()
    {
        $response = $this->post($this->getResourceUrl(), [
            'username' => 'admin@admin.com',
            'password' => 'vercingetorige',
        ]);
        $response->assertStatus(200);

        $access_token = json_decode($response->getContent())->access_token;
        $this->withHeaders(['Authorization' => 'Bearer '.$access_token]);

        return $response;
    }

    public function prepareDir($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
