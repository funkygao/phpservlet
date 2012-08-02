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

class MyActionForControllerTest extends DHttp_Action
{
    /**
     * @KHttp_Annotation_Param(type='int', default=98)
     */
    public $id;

    /**
     * @KHttp_Annotation_Param(type='str', default='blah')
     */
    public $photoName;

    /**
     * @KHttp_Annotation_Param(type='str', validators='email')
     */
    public $email;

    public function on_foo(DHttp_KxRequest $request, DHttp_Response $response)
    {
        return DHttp_Result::RESULT_SUCCESS;
    }

    public function on_forward(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('override', 19);
        return $this->_forward($this->getClassName(), 'forwarded');
    }

    public function on_forwarded(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('override', 97);
        return self::RESULT_INPUT;
    }
}

class DHttp_Controller_Test extends DHttp_TestBase implements DHttp_Constant
{

    /**
     * @var DHttp_Controller
     */
    private $controller;

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
        $this->config = new KHttp_Config_Default('', 'MyActionForControllerTest', 'foo');
        $this->app = new DHttp_App($this->config);
        $this->controller = new DHttp_Controller($this->app);
    }

    public function testDispatch()
    {
        list($action, $result) = $this->controller->dispatch();
        $this->assertEquals('success', $result);
        $this->assertInstanceOf('DHttp_Action', $action);
        $this->assertNotNull($action);

        $this->assertSame($action->getRequest(), $this->app->request());

        // test action
        $this->assertEquals('MyActionForControllerTest', $this->app->action()->getClassName());
    }

    /**
     * @runInSeparateProcess
     * @expectedException RuntimeException
     * @expectedExceptionMessage MyActionForControllerTest::on_invalidMethod() not found
     */
    public function testDispatchInvalidMethod()
    {
        $this->config->setActionMethod('invalidMethod');
        $this->controller->dispatch();
    }

    public function testGetRealActionMethodName()
    {
        $this->assertEquals('on_Foo', $this->controller->getRealActionMethodName('Foo'));
        $this->assertEquals('on__foo', $this->controller->getRealActionMethodName('_foo'));
        $this->assertEquals('on_aj_recommand_users',
            $this->controller->getRealActionMethodName('aj_recommand_users'));

    }

    public function testActionMethodPrefix()
    {
        $this->assertEquals('on_', DHttp_Controller::ACTION_METHOD_PREFIX);
    }

    public function testForward()
    {
        $config = new KHttp_Config_Default('', 'MyActionForControllerTest', 'foo');
        $app = new DHttp_App($config);
        $controller = new DHttp_Controller($app);

        $config->setActionMethod('forward');

        $result = $controller->forward('MyActionForControllerTest', 'forwarded');
        $this->assertEquals('input', $result);

        // test action setter
        $this->assertEquals('MyActionForControllerTest', $app->action()->getClassName());
    }

    /**
     * @runInSeparateProcess
     */
    public function testForwardAfterDispatch()
    {
        $config = new KHttp_Config_Default('', 'MyActionForControllerTest', 'foo');
        $app = new DHttp_App($config);
        $controller = new DHttp_Controller($app);

        $config->setActionMethod('forward');

        list($action, $result) = $controller->dispatch();
        $this->assertEquals('input', $result);
        $this->assertEquals(97, $app->request()->getAttribute('override'));
        $this->assertInstanceOf('MyActionForControllerTest', $action);
    }

    /**
     * @param array $params
     * @return MyActionForControllerTest
     */
    private function _prepareForParam($params)
    {
        foreach ($params as $name => $value)
        {
            $_GET[$name] = $value;
        }

        $conf = $this->app->config();
        $conf->setActionMethod('forward');

        list($action, $result) = $this->controller->dispatch();

        return $action;
    }

    public function testAnnotationParamWithDefaultValue()
    {
        $action = $this->_prepareForParam(array());

        $this->assertInternalType('int', $action->id);
        $this->assertEquals(98, $action->id);
        $this->assertInternalType('string', $action->photoName);
        $this->assertEquals('blah', $action->photoName);
    }

    public function testAnnotationParamWithInputValue()
    {
        $action = $this->_prepareForParam(array('id' => 1949, 'photoName' => 'OhMyDog'));

        $this->assertInternalType('int', $action->id);
        $this->assertEquals(1949, $action->id);
        $this->assertInternalType('string', $action->photoName);
        $this->assertEquals('OhMyDog', $action->photoName);
    }

    public function testAnnotationParamWithValidatorsOfInvalidValue()
    {
        $action = $this->_prepareForParam(array('email' => 'OhMyDog'));

        $this->assertEquals(self::PARAM_INVALID_VALUE, $action->email);
    }

    public function testAnnotationParamWithValidatorsOfValidValue()
    {
        $action = $this->_prepareForParam(array('email' => 'gaopeng@corp.kaixin001.com'));

        $this->assertEquals('gaopeng@corp.kaixin001.com', $action->email);
    }

}
