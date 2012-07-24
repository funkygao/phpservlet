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

class IResultBuilderClassForTest implements DHttp_ResultBuilder
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

    public function execute(DHttp_Action $action, $value = null)
    {

    }

}

class DHttp_ResultBuilder_Test extends KxTestCaseBase
{

    public function testInheritance()
    {
        $child = new IResultBuilderClassForTest();
        $this->assertEquals('json', $child->getTypeJson());
        $this->assertEquals('success', $child->getResultSuccess());
        $this->assertEquals('login', $child->getResultLogin());
    }

}
