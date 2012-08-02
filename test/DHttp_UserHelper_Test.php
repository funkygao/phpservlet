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
 *
 * @todo isFriend的返回值不大理解
 * @todo isStar的返回值不大理解
 */

require_once('DHttp_TestBase.php');

class DHttp_UserHelper_Test extends KxTestCaseBase implements DHttp_Constant
{

    /**
     * @var DHttp_UserHelper
     */
    private $emptyUserHelper;

    /**
     * @var DHttp_UserHelper
     */
    private $fullUserHelper;

    protected function setUp()
    {
        $service = new DHttp_ServiceHelper();
        $this->emptyUserHelper = new DHttp_UserHelper(0, 0, $service);
        $this->fullUserHelper = new DHttp_UserHelper(self::UID_GAOPENG, self::UID_GAOPENG_FRIEND, $service);
    }

    public function testEmptyUidWhileNotEmpty_Uid()
    {
        $u = new DHttp_UserHelper(10, 0, new DHttp_ServiceHelper());
        $this->assertEquals(10, $u->_uid());
        $this->assertEquals(10, $u->uid());
    }

    public function testGetUidAnd_Uid()
    {
        $this->assertInternalType('int', $this->emptyUserHelper->uid());
        $this->assertInternalType('int', $this->emptyUserHelper->_uid());
        $this->assertEquals(0, $this->emptyUserHelper->uid());
        $this->assertEquals(0, $this->emptyUserHelper->_uid());

        $this->assertInternalType('int', $this->fullUserHelper->uid());
        $this->assertInternalType('int', $this->fullUserHelper->_uid());
        $this->assertEquals(self::UID_GAOPENG_FRIEND, $this->fullUserHelper->uid());
        $this->assertEquals(self::UID_GAOPENG, $this->fullUserHelper->_uid());
    }

    public function testIsMeAndCachedResult()
    {
        $u = new DHttp_UserHelper(10, 10, new DHttp_ServiceHelper());
        $this->assertTrue($u->isMe());
        // 该结果现在被缓存了

        $u = new DHttp_UserHelper(10, 40, new DHttp_ServiceHelper());
        $this->assertTrue($u->isMe());  // 虽然不该是isMe，但这时候取的是缓存的结果
        $this->assertFalse($u->isMe(true)); // 不用缓存的结果

        $u = new DHttp_UserHelper(10, 0, new DHttp_ServiceHelper());
        $this->assertTrue($u->isMe(true));
    }

    public function testIsFriend()
    {
        // 自己是自己的双向好友
        $u = new DHttp_UserHelper(self::UID_GAOPENG, self::UID_GAOPENG, new DHttp_ServiceHelper());
        $this->assertEquals(self::FRIEND_MUTUAL, $u->isFriend(true));
        $this->assertTrue($u->isFriendMutual(true));

        $u->uid(self::UID_GAOPENG_FRIEND);
        $this->assertEquals(self::FRIEND_IDOL, $u->isFriend(true));
        $this->assertTrue($u->isFriendIdol(true));

        $u = new DHttp_UserHelper(self::UID_GAOPENG, 3, new DHttp_ServiceHelper());

        $u->uid(self::UID_GAOPENG_NOT_FRIEND);
        $this->assertEquals(self::FRIEND_NONE, $u->isFriend(true));

        $u->uid(self::UID_STAR_FOLLOWED);
        $this->assertEquals(self::FRIEND_IDOL, $u->isFriend(true));
        $this->assertTrue($u->isFriendIdol(true));

        $u->uid(self::UID_STAR_NOT_FOLLOWED);
        $this->assertEquals(self::FRIEND_NONE, $u->isFriend(true));
        $this->assertFalse($u->isFriendIdol(true));
        $this->assertFalse($u->isFriendMutual(true));
        $this->assertFalse($u->isFriendFans(true));

        $u->uid(self::UID_ORG_NOT_FOLLOWED);
        $this->assertEquals(self::FRIEND_NONE, $u->isFriend(true));

        $u->uid(self::UID_ORG_FOLLOWED);
        $this->assertEquals(self::FRIEND_IDOL, $u->isFriend(true));
        $this->assertTrue($u->isFriendIdol(true));

        // 把这2个uid反转，结果就不一样了
        $u->_uid(self::UID_ORG_FOLLOWED);
        $u->uid(self::UID_GAOPENG);
        $this->assertEquals(self::FRIEND_NONE, $u->isFriend(true));
    }

    public function testAppList()
    {
        $u = new DHttp_UserHelper(self::UID_GAOPENG, self::UID_GAOPENG_FRIEND, new DHttp_ServiceHelper());

        $this->assertGreaterThanOrEqual(10, count($u->_appList(true)));
        $this->assertInternalType('array', $u->_appList(true));

        foreach ($u->_appList(true) as $appId => $appInfo)
        {
            $this->assertGreaterThan(0, $appId);
            $this->assertLessThan(PHP_INT_MAX, $appId);

            $this->assertNotEmpty($appInfo['aid']);
            $this->assertNotEmpty($appInfo['name']);
            $this->assertNotEmpty($appInfo['ename']);
            $this->assertNotEmpty($appInfo['url']);

            // verify 是固定长度的
            if (isset($appInfo['verifycode']) && !empty($appInfo['verifycode']))
            {
                $this->assertEquals(12, strlen($appInfo['verifycode']));
            }
        }
    }

    public function testIsStar()
    {
        $this->assertEquals(0, $this->fullUserHelper->_isStar(true));

        $this->fullUserHelper->_uid(self::UID_GAOPENG_FRIEND);
        $this->assertEquals(0, $this->fullUserHelper->_isStar(true));
        $this->assertFalse($this->fullUserHelper->_isStarOrg(true));
        $this->assertFalse($this->fullUserHelper->_isStarPerson(true));
        $this->fullUserHelper->_uid(self::UID_GAOPENG_NOT_FRIEND);
        $this->assertEquals(0, $this->fullUserHelper->_isStar(true));

        $this->fullUserHelper->_uid(self::UID_STAR_FOLLOWED);
        $this->assertEquals(1, $this->fullUserHelper->_isStar(true));
        $this->assertTrue($this->fullUserHelper->_isStarPerson(true));
        $this->assertFalse($this->fullUserHelper->_isStarOrg(true));

        $this->fullUserHelper->uid(self::UID_STAR_NOT_FOLLOWED);
        $this->assertEquals(1, $this->fullUserHelper->isStar(true));
        $this->assertTrue($this->fullUserHelper->isStarPerson(true));
        $this->assertFalse($this->fullUserHelper->isStarOrg(true));

        $this->fullUserHelper->uid(self::UID_ORG_FOLLOWED);
        $this->assertEquals(2, $this->fullUserHelper->isStar(true));
        $this->assertTrue($this->fullUserHelper->isStarOrg(true));
        $this->assertFalse($this->fullUserHelper->isStarPerson(true));

        $this->fullUserHelper->uid(self::UID_ORG_NOT_FOLLOWED);
        $this->assertEquals(2, $this->fullUserHelper->isStar(true));
        $this->assertTrue($this->fullUserHelper->isStarOrg(true));
        $this->assertFalse($this->fullUserHelper->isStarPerson(true));
    }

    public function testFakeLevel()
    {
        $this->assertEquals(0, $this->fullUserHelper->_fakeLevel(true));
    }

    public function testAppConfig()
    {
        $diaryConfig = $this->fullUserHelper->_app_config(APP_DIARY_ID);
        $this->assertInternalType('string', $diaryConfig);
        $this->assertEquals('1', $diaryConfig);
    }

}
