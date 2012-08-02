<?php
/**
 *
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class MyActionForJsonTest extends DHttp_Action
{

    public function on_index(DHttp_Request $req, DHttp_Response $res)
    {
        $req->setAttribute('json',
            array(
                'name'=> 'gaopeng',
                'sex' => 'm'
            )
        );
        return DHttp_Result::RESULT_SUCCESS;
    }

}

class KHttp_Result_Json_Test extends KxTestCaseBase
{

    public function testExecute()
    {
        $config = new KHttp_Config_Default('blah', 'MyActionForJsonTest', 'index');
        $config->mapResult(DHttp_Result::RESULT_SUCCESS, DHttp_Result::TYPE_JSON);

        $app = new DHttp_App($config, DHttp_Env::mock());
        $action = new MyActionForJsonTest($app);

        $engine = new KHttp_Result_Json();
        $this->assertInstanceOf('DHttp_Result', $engine);
        $this->assertInstanceOf('DHttp_ResultBuilder', $engine);
        $app->call();

        $this->assertEquals('application/json; charset=UTF-8', $action->getResponse()->contentType());
        $this->assertEquals('{"name":"gaopeng","sex":"m"}', $action->getResponse()->body());
    }

}
