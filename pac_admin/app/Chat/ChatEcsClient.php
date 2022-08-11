<?php

namespace App\Chat;

use App\Chat\Properties\ChatAwsProperties;
use App\Chat\Properties\ChatRegisteredTaskDefinitionProperties;
use App\Chat\Properties\ChatTaskEnvironmentValues;
use App\Chat\Properties\ChatTaskProperties;
use App\Chat\Properties\ChatEcsServiceProperties;
use App\Chat\Properties\ChatTaskRegisterProperties;
use App\Chat\Properties\PlainProperties;
use Aws\Ecs\EcsClient;

/* No Singleton */

class ChatEcsClient
{

    private $props = null;
    private $awsprops = null;


    public function __construct(ChatAwsProperties $awsprops)
    {
        $this->props = new PlainProperties();
        $this->awsprops = $awsprops;
    }


    /**
     *
     */
    private function client()
    {
        $aws = $this->awsprops;
        return $this->props->getIfNullSet(__FUNCTION__, function () use ($aws) {
            return $this->makeClient($aws);
        });
    }

    private function makeClient(ChatAwsProperties $aws)
    {
        return new EcsClient([
            // "api_provider" => "api",
            "credentials" => [
                "key" => $aws->access_key_id(),
                "secret" => $aws->secret_access_key()
            ],
            // "csm" => false,
            // "debug" => false,
            // "stats" => false,
            // "disable_host_prefix_injection" => false,
            // "endpoint" =>
            // "endpoint_discovery" =>
            // "endpoint_provider" =>
            // "handler" =>
            // "http" =>
            // "http_handler" =>
            // "idempotency_auto_fill" =>
            // "profile" =>
            "region" => $aws->region(),
            // "retries" =>
            // "scheme" =>
            // "signature_provider" =>
            // "signature_version" =>
            // "use_aws_shared_config_files" =>
            // "validate" =>
            "version" => "latest" // ?
        ]);
    }

    public static function makeEnvironmentArray(ChatTaskEnvironmentValues $values)
    {
        $ret = [];
        $envs = $values->toArray();
        foreach ($envs as $key => $val) {
            $ret[] = ["name" => $key, "value" => $val];
        }
        return $ret;
    }



    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-ecs-2014-11-13.html#registertaskdefinition
     */
    public function registerTaskDefinition(ChatTaskRegisterProperties $params)
    {

        $client = $this->client();
        $envs = $params->environments();
        //
        $rsp = $client->registerTaskDefinition([
            'containerDefinitions' => [ // REQUIRED
                [
                    'cpu' => 0,
                    'disableNetworking' => false,
                    'environment' => self::makeEnvironmentArray($envs),
                    'essential' => true,
                    'healthCheck' => [
                        'command' => ['CMD-SHELL', 'curl -f http://localhost:3000/ || exit 1'],
                        'interval' => 30,
                        'retries' => 3,
                        'startPeriod' => 300,
                        'timeout' => 5,
                    ],
                    'image' => $params->image(),
                    'logConfiguration' => [
                        "logDriver" => $params->awslog_driver(),
                        "options" => [
                            "awslogs-group" => $params->awslogs_group(),
                            "awslogs-region" => $params->awslogs_region(),
                            "awslogs-stream-prefix" => $params->awslogs_stream_prefix(),
                        ]
                    ],
                    'memory' => $params->memory_reservation(),
                    'mountPoints' => [],
                    'name' => $params->container_name(),
                ],
            ],
            'executionRoleArn' => $params->execution_role_arn(),
            'family' => $params->family(), // REQUIRED
            'networkMode' => 'bridge',
            'requiresCompatibilities' => ['EC2'],
            'runtimePlatform' => [
                'operatingSystemFamily' => 'LINUX',
            ],
            'taskRoleArn' => $params->task_role_arn() // ,
        ]);

        $ret = new ChatRegisteredTaskDefinitionProperties();
        $ret->task_definition_arn($rsp["taskDefinition"]["taskDefinitionArn"]);
        return $ret;
    }


    /**
     *
     * @return ChatEcsServiceProperties
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-ecs-2014-11-13.html#createservice
     */
    public function craeteService(ChatEcsServiceProperties $params)
    {
        //
        $client = $this->client();
        $res = $client->createService([
            //'clientToken' => '<string>',
            'cluster' => $params->cluster(),
            'deploymentController' => [
                'type' => 'ECS', // REQUIRED
            ],
            'desiredCount' => 1,
            'launchType' => 'EC2',
            //'propagateTags' => 'TASK_DEFINITION|SERVICE',
            //'role' => '<string>',
            'schedulingStrategy' => 'REPLICA',
            'serviceName' => $params->service_name(), // REQUIRED
            'taskDefinition' => $params->task_definition()
        ]);

        $svr = $res["service"];

        $ret = new ChatEcsServiceProperties();
        $ret->cluster($svr["clusterArn"]);
        $ret->service_arn($svr["serviceArn"]);
        $ret->task_definition($svr["taskDefinition"]);
        return $ret;
    }
}
