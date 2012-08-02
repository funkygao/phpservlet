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

class KHttp_Exception_InvalidConfig_Test extends DHttp_TestBase
{

    private $code = 19;

    private $msg = 'fooBarSpam';

    /**
     * @var KHttp_Exception_InvalidConfig
     */
    private $exception;

    protected function setUp()
    {
        parent::setUp();

        $this->exception = new KHttp_Exception_InvalidConfig($this->msg, $this->code);
    }

    public function testToString()
    {
        $this->assertEquals('KHttp_Exception_InvalidConfig [19]: fooBarSpam', $this->exception->toString());
    }

    public function testGetClassName()
    {
        $this->assertEquals('KHttp_Exception_InvalidConfig', $this->exception->getClassName());
    }

}
