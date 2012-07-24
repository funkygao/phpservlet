<?php
/**
 * 服务辅助类.
 *
 * 通过注入，这样共享同一部分代码
 *
 * @package http
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_ServiceHelper
{

    /**
     * @return CUser
     */
    public function getCUser()
    {
        return CServiceFactory::getUserService();
    }

    /**
     * @return CUserStar
     */
    public function getCUserStar()
    {
        return CServiceFactory::getUserStarService();
    }

    /**
     * @return CFriend
     */
    public function getCFriend()
    {
        return CServiceFactory::getFriendService();
    }

    /**
     * @return CApp
     */
    public function getCApp()
    {
        return CServiceFactory::getAppService();
    }

    /**
     * @return CVisit
     */
    public function getCVisit()
    {
        return CServiceFactory::getVisitService();
    }

    /**
     *
     * s_user_info
     *
     * @param int $uid
     *
     * @return CDBResult False if fails
     */
    public function getCUserInfo($uid)
    {
        return $this->getCUser()->getDetail($uid);
    }

    /**
     *
     * s_user_moreinfo
     *
     * @param int $uid
     *
     * @return CUObjectResult
     */
    public function getCUserMoreInfo($uid)
    {
        return $this->getCUser()->getMoreInfo($uid);
    }

    /**
     * 取得某个用户的扩展信息.
     *
     * s_user_exinfo
     *
     * 该表里的内容经常变化，因此拿出来放到单独表里.
     *
     * @param int $uid
     *
     * @return CDBResult
     */
    public function getCUserInfoExtra($uid)
    {
        return $this->getCUser()->getDetailEx($uid);
    }

    /**
     * @param int $uid
     *
     * @return int
     */
    public function isStar($uid)
    {
        return KBiz_User_Config::getInstance($uid)->isStar();
    }

    /**
     * @param int $uid
     * @param int $friend_uid
     * @param int $lcache_time
     *
     * @return int 0没关系/1偶像/2粉丝/3互为好友
     */
    public function isFriend($uid, $friend_uid, $lcache_time = CFriend::FRIEND_CACHE_TIME)
    {
        return $this->getCFriend()->isFriend($uid, $friend_uid, $lcache_time);
    }

    /**
     * 马甲等级.
     *
     * 越大越是坏人，0表示清白的
     *
     * @param int $uid
     *
     * @return int 0-3
     */
    public function getFakeLevel($uid)
    {
        return KBiz_User_Config::getInstance($uid)->getFakeLevel();
    }

}
