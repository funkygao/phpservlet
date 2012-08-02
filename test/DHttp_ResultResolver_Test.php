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

class MyActionForResolverTest extends KHttp_Action_Base
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

}

class DHttp_ResultResolver_Test extends KxTestCaseBase
{

    /**
     * @var DHttp_ResultResolver
     */
    private $resolver;

    /**
     * @var DHttp_Config
     */
    private $config;

    /**
     * @var DHttp_Action
     */
    private $action;

    /**
     * @var array Map
     */
    private $mapSettings;

    protected function setUp()
    {
        $this->config = new KHttp_Config_Default('samples', 'MyActionForResolverTest', 'index');
        $app = new DHttp_App($this->config, DHttp_Env::mock());
        $this->action = new MyActionForResolverTest($app);

        $this->mapSettings = array(
            array(DHttp_Result::RESULT_SUCCESS, DHttp_Result::TYPE_SMARTY, 'samples/http/test.html'),

        );
    }

    private function _createResolver($mapSettings, $resultName)
    {
        foreach ($mapSettings as $mapping)
        {
            list($name, $type, $value) = $mapping;

            $this->config->mapResult($name, $type, $value);
        }

        $this->resolver = new DHttp_ResultResolver($this->config, $this->action, $resultName);
    }

    public function testResolve()
    {
        $this->_createResolver($this->mapSettings, DHttp_Result::RESULT_SUCCESS);

        $this->assertEquals('we are here!gaopeng,197', $this->resolver->resolve());
        $this->assertEquals(200, $this->action->getResponse()->status());
        $this->assertEquals('text/html; charset=UTF-8', $this->action->getResponse()->contentType());
    }

    public function testResolveJsonConventionOverConfiguration()
    {
        list($config, $action) = $this->_createActionWithEmptyConfig();

        $data = array(
            'name' => 'gaopeng',
            'sex' => 'm',
        );
        $resultName = json_encode($data) . '@j'; // value@type
        $resolver = new DHttp_ResultResolver($config, $action, $resultName);

        $this->assertEquals('"{\"name\":\"gaopeng\",\"sex\":\"m\"}"', $resolver->resolve());
        $this->assertEquals('application/json; charset=UTF-8', $action->getResponse()->contentType());
        $this->assertEquals(200, $action->getResponse()->status());
    }

    private function _createActionWithEmptyConfig()
    {
        $config = new KHttp_Config_Default();
        $app = new DHttp_App($config, DHttp_Env::mock());
        $action = new MyActionForResolverTest($app);

        return array($config, $action);
    }

    public function testResolveSmartyConventionOverConfiguration()
    {
        list($config, $action) = $this->_createActionWithEmptyConfig();

        $resultName = 'samples/http/test.html@s'; // value@type
        $resolver = new DHttp_ResultResolver($config, $action, $resultName);

        $this->assertEquals('we are here!gaopeng,197', $resolver->resolve());
        $this->assertEquals('text/html; charset=UTF-8', $action->getResponse()->contentType());
        $this->assertEquals(200, $action->getResponse()->status());
    }


}
