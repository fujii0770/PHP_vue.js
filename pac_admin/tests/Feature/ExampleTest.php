<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Utils\IdAppApiUtils;
use Illuminate\Support\Facades\Log;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $client = IdAppApiUtils::getAuthorizeClient();

        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        $params = [
            'company_id' => 1,
            'company_name' => "test",
            'contract_app' => 0,
            'app_env' => 1,
            'contract_server'=> 1,
            'plan' => 1,
            'subdomain' => "testdomain",
        ];

        $result = $client->post("sasattotalk.available", [
            RequestOptions::JSON =>$params
        ]);

        log::debug($result);

    }

    public function domaincheck()
    {
        $client = IdAppApiUtils::getAuthorizeClient();

        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        $params = [
            'subdomain' => "testdomain",
        ];

        $result = $client->post("sasattotalk.available", [
            RequestOptions::JSON => $params
        ]);

        log::debug($result);

        //$response = $this->get('/');

        //$response->assertStatus(200);
    }
}
