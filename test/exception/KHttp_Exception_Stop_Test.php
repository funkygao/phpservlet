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

class KHttp_Exception_Stop_Test extends KxTestCaseBase
{

    private $code = 19;

    private $msg = 'fooBarSpam';

    /**
     * @var KHttp_Exception_Stop
     */
    private $exception;

    protected function setUp()
    {
        parent::setUp();

        $this->exception = new KHttp_Exception_Stop($this->msg, $this->code);
    }

    public function testToString()
    {
        $this->assertEquals('KHttp_Exception_Stop [19]: fooBarSpam', $this->exception->toString());
    }

    public function testGetClassName()
    {
        $this->assertEquals('KHttp_Exception_Stop', $this->exception->getClassName());
    }

    public function testGetMessage()
    {
        $this->assertEquals($this->msg, $this->exception->getMessage());
    }

    public function testGetCode()
    {
        $this->assertEquals($this->code, $this->exception->getCode());
    }

}
