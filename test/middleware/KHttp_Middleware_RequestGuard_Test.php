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

class KHttp_Middleware_RequestGuard_Test extends KxTestCaseBase
{

    /**
     * @var KHttp_Middleware_RequestGuard
     */
    private $guard;

    /**
     * @return DHttp_Request
     */
    private function _mockFakeIpRequest()
    {
        $this->_mockRequest(array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_CLIENT_IP'       => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '100.100.100.100, 200.200.200.200, 200.200.200.201',
        ));
    }

    /**
     * @param array $settings Map
     * @param array $unsets List
     *
     */
    private function _mockRequest(array $settings = array(), $unsets = array())
    {
        // 为了让mt_rand()的值可以预测
        mt_srand(0);

        $env = DHttp_Env::mock($settings);
        foreach ($unsets as $key)
        {
            unset($env[$key]);
        }

        $cfg = new KHttp_Config_Default('', 'KSamples_Http_Action', 'index');
        $cfg->mapResult(DHttp_Result::RESULT_SUCCESS, DHttp_Result::TYPE_SMARTY, 'samples/http/index.html')
            ->mapResult(DHttp_Result::RESULT_FAIL, DHttp_Result::TYPE_SMARTY, 'samples/http/fail.html');

        $app = new DHttp_App($cfg, $env);
        $this->guard = new KHttp_Middleware_RequestGuard();
        $app->register($this->guard);
    }


    public function testFakeIp()
    {
        $this->markTestIncomplete();
    }

    public function testMethodNotAllowed()
    {
        $thrown = false;

        try
        {
            $this->_mockRequest(array('REQUEST_METHOD' => 'HEAD'));
            $this->guard->call();
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $thrown = true;
        }

        if (!$thrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }

        $this->assertEquals(DHttp_Response::SC_METHOD_NOT_ALLOWED, $this->guard->getResponse()->status());
        $this->assertStringEndsWith(
            "invalid_http_method 127.0.0.1 /home/index.php http://www.kaixin001.com/home/?uid=12345\n",
            $this->getLastLine(self::DEBUG_LOGFILE)
        );
    }

    public function testConst()
    {
        $this->assertEquals('.baidu.com', KHttp_Middleware_RequestGuard::BAIDU_HOST);
    }

}
