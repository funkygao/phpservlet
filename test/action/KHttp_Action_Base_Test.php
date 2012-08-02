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

class ActionForActionBaseTest extends KHttp_Action_Base
{
    public function getUid()
    {
        return $this->uid;
    }

    public function get_uid()
    {
        return $this->_uid;
    }

    public function get_logger()
    {
        return $this->_logger;
    }

    public function getServiceHelper()
    {
        return $this->_serviceHelper;
    }

}

class KHttp_Action_Base_Test extends DHttp_TestBase
{

    /**
     * @var ActionForActionBaseTest|KHttp_Action_Base
     */
    private $action;

    /**
     * @var int
     */
    private $uid;

    protected function setUp()
    {
        $cfg = new KHttp_Config_Default('', 'ActionForActionBaseTest');
        $app = new DHttp_App($cfg);
        $this->action = new ActionForActionBaseTest($app);

        $this->uid = self::UID_GAOPENG;
    }

    public function testServiceHelper()
    {
        $this->assertNotNull($this->action->getServiceHelper());
        $this->assertInstanceOf('DHttp_ServiceHelper', $this->action->getServiceHelper());
    }

    /**
     * @runInSeparateProcess
     */
    public function testEnableExternalPost()
    {
        $this->assertFalse($this->action->getRequest()->getBoolAttribute(DHttp_Constant::BIZ_EXTERNAL_POST_ENABLED));

        $this->action->enableExternalPost();
        $this->assertTrue($this->action->getRequest()->getBoolAttribute(DHttp_Constant::BIZ_EXTERNAL_POST_ENABLED));
    }

    private function mockEmptyUserContextAction()
    {
        $cfg = new KHttp_Config_Default('', 'ActionForActionBaseTest');
        $app = new DHttp_App($cfg);
        return new ActionForActionBaseTest($app);
    }

    private function mockFullUserContextAction()
    {
        $_GET['uid'] = self::UID_GAOPENG;
        $now = time();
        $checksum = CPlatApp::getLoginHash(9940649, $now);
        $_COOKIE['_user'] = $checksum . '_9940649_' . $now;
        $cfg = new KHttp_Config_Default('', 'ActionForActionBaseTest');
        $app = new DHttp_App($cfg);
        return new ActionForActionBaseTest($app);
    }

    public function testCompleteConstructor()
    {
        $action = $this->mockEmptyUserContextAction();

        // constructor 为3个protected属性赋值，在此一一测试
        $this->assertEquals(0, $action->get_uid());
        $this->assertEquals(0, $action->getUid());
        $this->assertInstanceOf('DLogger_Facade', $action->get_logger());

        $action = $this->mockFullUserContextAction();

        // constructor 为3个protected属性赋值，在此一一测试
        $this->assertInstanceOf('DLogger_Facade', $action->get_logger());
        $this->assertEquals(self::UID_GAOPENG, $action->getUid());
        $this->assertEquals(9940649, $action->get_uid());
    }

    public function testGetLogger()
    {
        $this->assertInstanceOf('DLogger_Facade', $this->action->get_logger());
        $this->assertSame($this->action->get_logger(), $this->action->get_logger());
    }

}
