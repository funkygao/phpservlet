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

class MyConfigForTest extends DHttp_Config
{

    public function getMiddlewareNames()
    {
        return array();
    }
    
}

class DHttp_Config_Test extends DHttp_TestBase
{

    /**
     * @var DHttp_Config
     */
    private $config;

    protected function setUp()
    {
        $this->config = new MyConfigForTest();
    }

    public function testConstruct()
    {
        $config = new MyConfigForTest();
        $this->assertNull($config->getActionMethod());
        $this->assertNull($config->getActionClass());
        $this->assertNull($config->getModule());

        $config = new MyConfigForTest('', 'KSamples_Http_Action');
        $this->assertNull($config->getActionMethod());
        $this->assertEquals('KSamples_Http_Action', $config->getActionClass());

        $config = new MyConfigForTest('', 'non-exist-class');
        $this->assertEquals('non-exist-class', $config->getActionClass());

        $config = new MyConfigForTest('', 'KSamples_Http_Action', __FILE__);
        $this->assertEquals('DHttp_Config_Test', $config->getActionMethod());

        $config = new MyConfigForTest('', 'KSamples_Http_Action', 'index');
        $this->assertEquals('index', $config->getActionMethod());

        $config = new MyConfigForTest('photo', 'KSamples_Http_Action', 'index');
        $this->assertEquals('photo', $config->getModule());
    }

    public function testGetAndSetActionClass()
    {
        $this->assertInstanceOf('DHttp_Config', $this->config->setActionClass('foo'));
        $this->assertEquals('foo', $this->config->getActionClass());

        $this->config->setActionClass('non-exist');
        $this->assertEquals('non-exist', $this->config->getActionClass());
    }

    public function testGetAndSetActionMethod()
    {
        $this->assertInstanceOf('DHttp_Config', $this->config->setActionMethod('on_home'));
        $this->assertEquals('on_home', $this->config->getActionMethod());

        $this->config->setActionMethod('home_foo_bar');
        $this->assertEquals('home_foo_bar', $this->config->getActionMethod());

        $this->config->setActionMethod(__FILE__);
        $this->assertEquals('DHttp_Config_Test', $this->config->getActionMethod());
    }

    public function testGetAndSetDebug()
    {
        $this->assertInstanceOf('DHttp_Config', $this->config->setDebug(100));
        $this->assertEquals(100, $this->config->getDebug());

        $this->config->setDebug(-99);
        $this->assertEquals(-99, $this->config->getDebug());
    }

    public function testGetAndSetMode()
    {
        $this->assertInstanceOf('DHttp_Config', $this->config->setMode('dev'));
        $this->assertEquals('dev', $this->config->getMode());

        $this->config->setMode('non-exist');
        $this->assertEquals('non-exist', $this->config->getMode());
    }

    public function testSetAndGetAttribute()
    {
        $this->config->setAttribute('foo', 'bar');
        $this->assertEquals('bar', $this->config->getAttribute('foo'));

        $this->config->setAttribute('foo', $this);
        $this->assertEquals($this, $this->config->getAttribute('foo'));

        $this->config->setAttribute('spam', null);
        $this->assertNull($this->config->getAttribute('spam'));

        $this->config->setAttribute('spam', -1);
        $this->assertInternalType('int', $this->config->getAttribute('spam'));
        $this->assertEquals(-1, $this->config->getAttribute('spam'));
    }

    public function testSetAndGetModule()
    {
        $this->config->setModule('repaste');
        $this->assertEquals('repaste', $this->config->getModule());

        $this->config->setModule('/repaste');
        $this->assertEquals('repaste', $this->config->getModule());

        $this->config->setModule('repaste/detail');
        $this->assertEquals('repaste/detail', $this->config->getModule());

        $this->config->setModule('/repaste/detail');
        $this->assertEquals('repaste/detail', $this->config->getModule());
    }

    public function testInvalidResultConfiguration()
    {
        list($value, $type) = $this->config->getResultConfiguration('non-exist-map');
        $this->assertNull($value);
        $this->assertNull($type);
    }

    public function testGetMiddlewareNames()
    {
        $this->assertEquals(array(), $this->config->getMiddlewareNames());
    }

    public function testChainMethod()
    {
        $this->assertInstanceOf('DHttp_Config', $this->config->setDebug(5));
        $this->assertInstanceOf('DHttp_Config', $this->config->setMode('dev'));
        $this->assertInstanceOf('DHttp_Config', $this->config->setActionClass('foo'));
        $this->assertInstanceOf('DHttp_Config', $this->config->setActionMethod('bar'));
    }

    public function testRedirect()
    {
        $this->assertNull($this->config->redirect('GET'));

        $this->config->redirect('GET', '/foo/bar.php');
        $this->assertEquals('/foo/bar.php', $this->config->redirect('GET'));
        $this->assertNull($this->config->redirect('POST'));

        $this->assertInstanceOf('DHttp_Config', $this->config->redirect('POST', '/'));
    }

    public function testRefreshRatePerUser()
    {
        // default 60
        $this->assertEquals(60, $this->config->refreshRatePerUser());

        $this->config->refreshRatePerUser(10);
        $this->assertEquals(10, $this->config->refreshRatePerUser());

        $this->assertInstanceOf('DHttp_Config', $this->config->refreshRatePerUser(20));
        $this->assertInternalType('int', $this->config->refreshRatePerUser());
    }

    public function testRequireLogin()
    {
        // default is false
        $this->assertFalse($this->config->requireLogin());

        // set true
        $this->config->requireLogin(true);
        $this->assertTrue($this->config->requireLogin());
    }

}
