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

class MyActionForSmartyTest extends DHttp_Action
{

    public function on_index(DHttp_KxRequest $req, DHttp_Response $res)
    {
        return DHttp_Result::RESULT_NONE;
    }

    /** @KHttp_Annotation_Ognl */
    public function getUsername()
    {
        return 'gaopeng';
    }

    /** @KHttp_Annotation_Ognl */
    public function getUid()
    {
        return 197;
    }

    public function getBlah()
    {

    }

    public function getFoo($uid)
    {

    }

}

class KHttp_Result_Smarty_Test extends DHttp_TestBase
{

    /**
     * @var KHttp_Result_Smarty
     */
    private $engine;

    /**
     * @var DHttp_Action
     */
    private $action;

    protected function setUp()
    {
        $config = new KHttp_Config_Default('blah', 'MyActionForSmartyTest', 'index');
        $app = new DHttp_App($config);
        $this->action = new MyActionForSmartyTest($app);

        $this->engine = new KHttp_Result_Smarty();
    }

    public function testExecuteWithActionAssignValues()
    {
        `rm -rf /kx/smarty_writable/compile/blah/`;
        $this->assertFileNotExists('/kx/smarty_writable/compile/blah/');

        $this->assertInstanceOf('DHttp_Result', $this->engine);
        $this->assertInstanceOf('DHttp_ResultBuilder', $this->engine);

        $this->assertEquals('we are here!gaopeng,197',
            $this->engine->execute($this->action, 'samples/http/test.html'));

        // 会把模版编译成php文件
        $this->assertFileExists('/kx/smarty_writable/compile/blah/');
    }

    public function testGlobalResultNames()
    {
        $this->assertEquals('dataError', KHttp_Result_Smarty::RESULT_GLOBAL_DATAERROR);
        $this->assertEquals('invalidParameter', KHttp_Result_Smarty::RESULT_GLOBAL_INVALID_PARAM);
        $this->assertEquals('login', KHttp_Result_Smarty::RESULT_GLOBAL_LOGIN);
    }

}
