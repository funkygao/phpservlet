<?php
/**
 * ��������.
 *
 * ͨ��ע�룬��������ͬһ���ִ���
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
     * ȡ��ĳ���û�����չ��Ϣ.
     *
     * s_user_exinfo
     *
     * �ñ�������ݾ����仯������ó����ŵ���������.
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
     * @return int 0û��ϵ/1ż��/2��˿/3��Ϊ����
     */
    public function isFriend($uid, $friend_uid, $lcache_time = CFriend::FRIEND_CACHE_TIME)
    {
        return $this->getCFriend()->isFriend($uid, $friend_uid, $lcache_time);
    }

    /**
     * ��׵ȼ�.
     *
     * Խ��Խ�ǻ��ˣ�0��ʾ��׵�
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
