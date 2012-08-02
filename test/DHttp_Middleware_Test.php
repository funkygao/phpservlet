<?php
/**
 * HTTP中间件的单元测试.
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class MyMiddleware extends DHttp_Middleware
{

    public function call()
    {
        echo "Before";
        $this->_callNextMiddleware();
        echo "After";
    }

    public function prepareUserContext()
    {
        $this->_prepareUserContext();
    }

    public function uid()
    {
        return $this->uid;
    }

    public function _uid()
    {
        return $this->_uid;
    }

}

// 既当controller又当middleware stub
class MyController extends DHttp_Middleware
{
    public function call()
    {
        echo "Blah";
    }
}

class MyMiddlewareForForwardTest extends DHttp_Middleware
{
    public function call()
    {
        $this->_forward('KSamples_Http_Action', 'index');
    }
}

class DHttp_Middleware_Test extends DHttp_TestBase
{

    /**
     * @var MyController
     */
    private $controller;

    /**
     * @var DHttp_Middleware
     */
    private $middleware;

    protected function setUp()
    {
        $this->controller = new MyController();
        $this->middleware = new MyMiddleware();
    }

    public function testGetAndSetController()
    {
        $this->middleware->setApp($this->controller);
        $this->assertSame($this->controller, $this->middleware->getApp());
    }

    public function testGetAndSetNextMiddleware()
    {
        $m1 = new MyMiddleware();
        $m2 = new MyMiddleware();

        $m1->setNext($m2);
        $this->assertSame($m2, $m1->getNext());
    }

    public function testCall()
    {
        $this->expectOutputString('BeforeBlahAfter');

        $this->middleware->setNext($this->controller);
        $this->middleware->call();
    }

    public function testForward()
    {
        $this->markTestIncomplete();
    }

    public function testPrepareUserContext()
    {
        $app = new DHttp_App(new KHttp_Config_Default());
        $_GET['uid'] = self::UID_GAOPENG;
        $now = time();
        $checksum = CPlatApp::getLoginHash(9940649, $now);
        $_COOKIE['_user'] = $checksum . '_9940649_' . $now;

        $m = new MyMiddleware();
        $m->setApp($app);
        // before prepare
        $this->assertNull($m->uid());
        $this->assertNull($m->_uid());

        $m->prepareUserContext();
        // after prepare
        $this->assertEquals(self::UID_GAOPENG, $m->uid());
        $this->assertEquals(9940649, $m->_uid());

    }

}
