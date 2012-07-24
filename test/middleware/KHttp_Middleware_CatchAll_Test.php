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

require_once('/kx/tests/KxTestCaseBase.php');

class MyActionForCatchAllTest extends DHttp_Action
{
    public function on_test(DHttp_Request $req, DHttp_Response $res)
    {
        print_r(DLogger_Facade::getLogger()->traceCallStack('', false));
        throw new Exception('blah', 19);
    }
}

class KHttp_Middleware_CatchAll_Test extends KxTestCaseBase
{

    /**
     * @var DHttp_App
     */
    private $app;

    /**
     * @var DHttp_Config
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->middleware = new KHttp_Middleware_CatchAll();

        $this->config = new KHttp_Config_Default('', 'MyActionForCatchAllTest', 'test');
        $this->config->setMode('debug');
    }

    /**
     * @runInSeparateProcess
     */
    public function testCatchException()
    {
        $this->app = new DHttp_App($this->config, DHttp_Env::mock());
        $this->app->run();

        $this->assertFalse(headers_sent());

        $errlog = $this->getLastLine('/tmp/phperror', 16);
        //$this->assertEquals('', $errlog);
    }

}
