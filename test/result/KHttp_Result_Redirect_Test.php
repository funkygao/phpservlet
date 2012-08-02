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

class MyActionForRedirectTest extends DHttp_Action
{

    public function on_index(DHttp_Request $req, DHttp_Response $res)
    {
        return DHttp_Result::RESULT_GLOBAL_LOGIN;
    }

}

class KHttp_Result_Redirect_Test extends DHttp_TestBase
{

    public function testExecute()
    {
        $config = new KHttp_Config_Default('blah', 'MyActionForRedirectTest', 'index');
        $app = new DHttp_App($config, DHttp_Env::mock());

        $app->call();

        $this->assertEquals(302, $app->response()->status());
        $this->assertEquals('/login.php', $app->response()->header('Location'));
        $this->assertEquals('', $app->response()->body());
    }

}
