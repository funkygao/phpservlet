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

class ResultClassForTest implements DHttp_Result
{
    public function getTypeJson()
    {
        return self::TYPE_JSON;
    }

    public function getResultLogin()
    {
        return self::RESULT_GLOBAL_LOGIN;
    }

    public function getResultSuccess()
    {
        return self::RESULT_SUCCESS;
    }

}

class DHttp_Result_Test extends DHttp_TestBase
{

    public function testResultTypes()
    {
        $this->assertEquals('json', DHttp_Result::TYPE_JSON);
        $this->assertEquals('redirect', DHttp_Result::TYPE_REDIRECT);
        $this->assertEquals('smarty', DHttp_Result::TYPE_SMARTY);
        $this->assertEquals('xml', DHttp_Result::TYPE_XML);
    }

    public function testResultNames()
    {
        $this->assertEquals('none', DHttp_Result::RESULT_NONE);
        $this->assertEquals('success', DHttp_Result::RESULT_SUCCESS);
        $this->assertEquals('fail', DHttp_Result::RESULT_FAIL);
        $this->assertEquals('input', DHttp_Result::RESULT_INPUT);
    }

    public function testGlobalResultNames()
    {
        $this->assertEquals('dataError', DHttp_Result::RESULT_GLOBAL_DATAERROR);
        $this->assertEquals('invalidParameter', DHttp_Result::RESULT_GLOBAL_INVALID_PARAM);
        $this->assertEquals('login', DHttp_Result::RESULT_GLOBAL_LOGIN);
    }

    public function testInheritance()
    {
        $child = new ResultClassForTest();
        $this->assertEquals('json', $child->getTypeJson());
        $this->assertEquals('success', $child->getResultSuccess());
        $this->assertEquals('login', $child->getResultLogin());
    }

}
