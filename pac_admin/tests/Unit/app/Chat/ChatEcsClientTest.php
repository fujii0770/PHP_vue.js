<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatEcsClient;
use App\Chat\Properties\ChatAwsProperties;
use App\Chat\Properties\ChatTaskEnvironmentValues;
use App\Chat\Properties\ChatEcsServiceProperties;
use App\Chat\Properties\ChatTaskRegisterProperties;
use Aws\Ecs\EcsClient;
use Mockery;
use stdClass;

class ChatEcsClientTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_registerTaskDefinition()
    {

        // args
        $envs = new ChatTaskEnvironmentValues();
        $envs->mongo_oplog_url("mongo:oplog");
        $envs->mongo_url("mongo:mydb");
        $envs->root_url("https://www.exmple.com");
        $envs->virtual_host("www.example.com");
        $envs->virtual_port(3333);
        $envs->opening_callback_url("https://callback.example.com");
        $envs->fileupload_s3_bucket("chat/c001");
        $envs->timezone("Asia/Tokyo");
        $envs->virtual_port(3000);
        $envs->fileupload_storage_type("AmazonS3");
        $envs->fileupload_s3_awsaccesskeyid("s3asdf1234");
        $envs->fileupload_s3_awssecretaccesskey("s3qwer1234");
        $envs->fileupload_s3_region("ap-notheast-23456");
        $envs->admin_email("admin01@example.com");
        $envs->admin_password("pass@1234");
        $envs->admin_username("admin01");

        $params = new ChatTaskRegisterProperties();
        $params->image("image:version");
        $params->awslogs_group("logsgroup");
        $params->container_name("mycontainer");
        $params->execution_role_arn("arn:executionRoleArn");
        $params->task_role_arn("arn:taskRoleArn");
        $params->family("mytaskDefinition");
        $params->environments($envs);

        // mock
        $mock = Mockery::mock("overload:" . EcsClient::class);
        $mock->shouldReceive("registerTaskDefinition")
            ->withArgs(function ($o) use ($params) {
                $ps = $params;
                $envs = $params->environments();

                $ok = true;
                $ct = $o['containerDefinitions'][0];
                $ctenvs = $ct["environment"];

                $exps = [
                    "ADMIN_EMAIL" => "admin01@example.com",
                    "ADMIN_PASS" => "pass@1234",
                    "ADMIN_USERNAME" => "admin01",
                    "MONGO_OPLOG_URL" => $envs->mongo_oplog_url(),
                    "MONGO_URL" => $envs->mongo_url(),
                    "OPENING_CALLBACK_URL" => $envs->opening_callback_url(),
                    "TZ" => "Asia/Tokyo",
                    "ROOT_URL" => $envs->root_url(),
                    "VIRTUAL_HOST" => $envs->virtual_host(),
                    "VIRTUAL_PORT" => $envs->virtual_port(),
                    "FileUpload_Storage_Type" => "AmazonS3",
                    "FileUpload_S3_Bucket" => "chat/c001",
                    "FileUpload_S3_AWSAccessKeyId" => "s3asdf1234",
                    "FileUpload_S3_AWSSecretAccessKey" => "s3qwer1234",
                    "FileUpload_S3_Region" => "ap-notheast-23456",
                ];

                foreach ($exps as $key => $val) {
                    $eok = false;
                    foreach ($ctenvs as $env) {
                        if ($env["name"] === $key) {
                            $eok = $env["value"] === $val;
                            if (!$eok) break;
                        }
                        if ($eok) break;
                    }
                    $ok = $eok;
                    if (!$ok) break;
                }

                if ($ok) $ok = $ct["image"] === $ps->image();
                if ($ok) $ok = $ct["logConfiguration"]["options"]["awslogs-group"] === $ps->awslogs_group();
                if ($ok) $ok = $ct["name"] === $ps->container_name();

                if ($ok) $ok = $o["executionRoleArn"] === $ps->execution_role_arn();
                if ($ok) $ok = $o["taskRoleArn"] === $ps->task_role_arn();
                if ($ok) $ok = $o["family"] === $ps->family();

                return $ok;
            })
            ->andReturn(["taskDefinition" => ["taskDefinitionArn" => "arn:testTaskDefinition"]]);

        // target
        $target = new TestChatEscClient(new ChatAwsProperties());

        // exec
        $act = $target->registerTaskDefinition($params);

        // assert
        $this->assertEquals("arn:testTaskDefinition", $act->task_definition_arn());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_craeteService()
    {
        $exp = new stdClass;
        $exp->clusterArn = "arn:cluster001Arn";
        $exp->service_name = "testservice";
        $exp->serviceArn = "arn:service001Arn";
        $exp->taskDefinition = "task001:5";

        // mock
        $mock = Mockery::mock("overload:" . EcsClient::class);
        $mock->shouldReceive("createService")
            ->withArgs(function ($o) use ($exp) {
                $ok = true;
                //The short name or full Amazon Resource Name (ARN) of the cluster that you run your service on
                if ($ok) $ok = $o["cluster"] === $exp->clusterArn;
                if ($ok) $ok = $o["deploymentController"]["type"] === "ECS";
                if ($ok) $ok = $o["launchType"] === "EC2";
                //The name of your service. Up to 255 letters (uppercase and lowercase), numbers, underscores, and hyphens are allowed.
                if ($ok) $ok = $o["serviceName"] === $exp->service_name;
                //The family and revision (family:revision) or full ARN of the task definition to run in your service
                if ($ok) $ok = $o["taskDefinition"] === $exp->taskDefinition;
                return $ok;
            })
            ->andReturn([
                "service" => [
                    "clusterArn" => $exp->clusterArn,
                    "serviceArn" => $exp->serviceArn,
                    "taskDefinition" => $exp->taskDefinition,
                ]
            ]);

        // target
        $target = new TestChatEscClient(new ChatAwsProperties());

        // args
        $params = new ChatEcsServiceProperties();
        $params->cluster($exp->clusterArn);
        $params->service_name($exp->service_name);
        $params->task_definition($exp->taskDefinition);

        // exec
        $act = $target->craeteService($params);

        // assert
        $this->assertIsObject($act);
        $this->assertEquals($exp->clusterArn, $act->cluster());
        $this->assertEquals($exp->serviceArn, $act->service_arn());
        $this->assertEquals($exp->taskDefinition, $act->task_definition());
    }
}


class TestChatEscClient extends ChatEcsClient
{
}
