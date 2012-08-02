<?php
/**
 *
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class DHttp_ServiceHelper_Test extends DHttp_TestBase implements DHttp_Constant
{

    /**
     * @var DHttp_ServiceHelper
     */
    private $serviceHelper;

    /**
     * @var int
     */
    private $uid;

    protected function setUp()
    {
        $this->serviceHelper = new DHttp_ServiceHelper();
        $this->uid = self::UID_GAOPENG;
    }

    private function _testForService($clazz)
    {
        $method = 'get' . $clazz;
        $service = $this->serviceHelper->$method();
        $this->assertInstanceOf($clazz, $service);
        $this->assertNotEmpty($service);

        $this->assertSame($service, $this->serviceHelper->$method());
    }

    public function testGetCUser()
    {
        $this->_testForService('CUser');
    }

    public function testCUserStar()
    {
        $this->_testForService('CUserStar');
    }

    public function testCFriend()
    {
        $this->_testForService('CFriend');
    }

    public function testCApp()
    {
        $this->_testForService('CApp');
    }

    public function testCVisit()
    {
        $this->_testForService('CVisit');
    }

    public function testGetUserInfo()
    {
        $info = $this->serviceHelper->getCUserInfo($this->uid);

        $this->assertEquals($this->uid, $info->getFirst('uid'));
        $this->assertEquals('gaopeng@corp.kaixin001.com', $info->getFirst('email'));
    }

    public function testGetUserMoreInfo()
    {
        $info = $this->serviceHelper->getCUserMoreInfo($this->uid);

        $this->assertEquals(1, $info->rowNum());
        $this->assertEquals('2012-02-22 13:02:21', $info->getFirst('ctime'));
        $this->assertEquals('593547964183', $info->getFirst('verifycode'));
        $this->assertStringStartsWith('1=1&2=1&1266=1&1018=1&1088=1&1002=1&1226=1&1282=0&1160=1&',
            $info->getFirst('app_config'));
    }

    public function testGetUserInfoExtra()
    {
        $info = $this->serviceHelper->getCUserInfoExtra($this->uid);

        $this->assertEquals($this->uid, $info->getFirst('uid'));
        $this->assertEquals(0, $info->getFirst('forbidsound'));
    }

    public function testIsFriend()
    {
        $this->assertInternalType('int', $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_GAOPENG_NOT_FRIEND));

        $this->assertEquals(0, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_GAOPENG_NOT_FRIEND));

        $this->assertEquals(1, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_GAOPENG_FRIEND));

        $this->assertEquals(1, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_STAR_FOLLOWED));
        $this->assertEquals(0, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_STAR_NOT_FOLLOWED));

        $this->assertEquals(0, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_ORG_NOT_FOLLOWED));
        $this->assertEquals(1, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_ORG_FOLLOWED));

        // 自己是自己的好友吗？是，互为好友
        $this->assertEquals(3, $this->serviceHelper->isFriend(self::UID_GAOPENG, self::UID_GAOPENG));
    }

    public function testIsStar()
    {
        $this->assertInternalType('int', $this->serviceHelper->isStar(self::UID_GAOPENG));

        $this->assertEquals(0, $this->serviceHelper->isStar(self::UID_GAOPENG));

        $this->assertEquals(1, $this->serviceHelper->isStar(self::UID_STAR_NOT_FOLLOWED));
        $this->assertEquals(self::STAR_PERSON, $this->serviceHelper->isStar(self::UID_STAR_FOLLOWED));

        $this->assertEquals(2, $this->serviceHelper->isStar(self::UID_ORG_FOLLOWED));
        $this->assertEquals(self::STAR_ORG, $this->serviceHelper->isStar(self::UID_ORG_NOT_FOLLOWED));
    }

    public function testGetFakeLevel()
    {
        $this->assertInternalType('int', $this->serviceHelper->getFakeLevel(self::UID_GAOPENG_NOT_FRIEND));
        $this->assertEquals(0, $this->serviceHelper->getFakeLevel(self::UID_GAOPENG));
    }

}
