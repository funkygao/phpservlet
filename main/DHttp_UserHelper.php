<?php
/**
 * 当前登录用户在访问另外一个用户的上下文信息.
 *
 * 主要为了lazy loading，替代CPlatApp::__get
 *
 * @package http
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo isMe, _uid if uid is empty
 */
final class DHttp_UserHelper implements DHttp_Constant
{

    /**
     * @var DHttp_ServiceHelper
     */
    private $_serviceHelper;

    /**
     * @var int
     */
    private $uid;

    /**
     * @var int
     */
    private $_uid;

    /**
     * @var array Map {functionName: result, ...}
     */
    private static $_cachedResults = array();

    /**
     * @param int $_uid
     * @param int $uid
     * @param DHttp_ServiceHelper $serviceHelper
     */
    public function __construct($_uid, $uid, DHttp_ServiceHelper $serviceHelper)
    {
        $this->_serviceHelper = $serviceHelper;

        $this->_uid = $_uid;
        $this->uid = $uid;
        if (empty($this->uid))
        {
            $this->uid = $this->_uid; // FIXME
        }
    }

    /**
     * TA的uid.
     *
     * @param int $val
     *
     * @return int 0 if not param 'uid' passed in
     */
    public function uid($val = null)
    {
        if (!is_null($val))
        {
            $this->uid = $val;
        }

        return $this->uid;
    }

    /**
     * 我的uid.
     *
     * @param int $val
     *
     * @return int 0 if not logged in
     */
    public function _uid($val = null)
    {
        if (!is_null($val))
        {
            $this->_uid = $val;
        }

        return $this->_uid;
    }

    /**
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function isMe($refresh = false)
    {
        return $this->_callCachedMethod('_is_me', $refresh);
    }

    private function _is_me()
    {
        return $this->_uid == $this->uid;
    }

    /**
     *
     * @param bool $refresh
     *
     * @return int 0没关系/1偶像/2粉丝/3互为好友
     */
    public function isFriend($refresh = false)
    {
        return $this->_callCachedMethod('_is_friend', $refresh);
    }

    private function _is_friend()
    {
        $f = $this->_serviceHelper->isFriend($this->_uid, $this->uid);
        if ($f == self::FRIEND_FANS && $this->_isStar())
        {
            $f = self::FRIEND_NONE;
        }

        return $f;
    }

    /**
     * 我们互为好友?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function isFriendMutual($refresh = false)
    {
        return $this->isFriend($refresh) == self::FRIEND_MUTUAL;
    }

    public function isFriendFans($refresh = false)
    {
        return $this->isFriend($refresh) == self::FRIEND_FANS;
    }

    /**
     * 我已经把TA加为粉丝?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function isFriendIdol($refresh = false)
    {
        return $this->isFriend($refresh) == self::FRIEND_IDOL;
    }

    /**
     *
     * @param bool $refresh
     *
     * @return int 0-2, 0 if not star
     */
    public function _isStar($refresh = false)
    {
        return $this->_callCachedMethod('_i_am_star', $refresh);
    }

    private function _i_am_star()
    {
        return $this->_serviceHelper->isStar($this->_uid);
    }

    /**
     * 我是明星帐号?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function _isStarPerson($refresh = false)
    {
        return $this->_isStar($refresh) == self::STAR_PERSON;
    }

    /**
     * 我是机构帐号?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function _isStarOrg($refresh = false)
    {
        return $this->_isStar($refresh) == self::STAR_ORG;
    }

    /**
     *
     * @param bool $refresh
     *
     * @return int 0-2, 0 if not star
     */
    public function isStar($refresh = false)
    {
        return $this->_callCachedMethod('_ta_is_star', $refresh);
    }

    private function _ta_is_star()
    {
        return $this->_serviceHelper->isStar($this->uid);
    }

    /**
     * TA是明星帐号?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function isStarPerson($refresh = false)
    {
        return $this->isStar($refresh) == self::STAR_PERSON;
    }

    /**
     * TA是机构帐号?
     *
     * @param bool $refresh
     *
     * @return bool
     */
    public function isStarOrg($refresh = false)
    {
        return $this->isStar($refresh) == self::STAR_ORG;
    }

    /**
     *
     * @param bool $refresh
     *
     * @return array
     */
    public function _appList($refresh = false)
    {
        return $this->_callCachedMethod('_my_applist', $refresh);
    }

    private function _my_applist()
    {
        $app_config = CApp::getAll($this->_userMoreInfo()->getFirst('app_config'));
        $appids = array_keys($app_config);
        $this->_serviceHelper->getCApp()->getDatas($appids, $applist);
        return $applist;
    }

    /**
     * 当前登录用户对某个组件的设置.
     *
     * @param int $appid
     * @param bool $refresh
     *
     * @return string 0-2
     * @see PRIVACY_OPEN
     */
    public function _app_config($appid, $refresh = false)
    {
        static $cachedValue = array();
        if (!isset($cachedValue[$appid]) || $refresh)
        {
            $app_config = CApp::getAll($this->_userMoreInfo()->getFirst('app_config'));
            $cachedValue[$appid] = $app_config[$appid];
        }

        return $cachedValue[$appid];
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CDBResult False if fails
     */
    public function _userInfo($refresh = false)
    {
        return $this->_callCachedMethod('_my_userinfo', $refresh);
    }

    private function _my_userinfo()
    {
        return $this->_serviceHelper->getCUserInfo($this->_uid);
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CDBResult False if fails
     */
    public function userInfo($refresh = false)
    {
        return $this->_callCachedMethod('_ta_userinfo', $refresh);
    }

    private function _ta_userinfo()
    {
        return $this->_serviceHelper->getCUserInfo($this->uid);
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CUObjectResult
     */
    public function _userMoreInfo($refresh = false)
    {
        return $this->_callCachedMethod('_my_moreinfo', $refresh);
    }

    private function _my_moreinfo()
    {
        return $this->_serviceHelper->getCUserMoreInfo($this->_uid);
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CUObjectResult
     */
    public function userMoreInfo($refresh = false)
    {
        return $this->_callCachedMethod('_ta_moreinfo', $refresh);
    }

    private function _ta_moreinfo()
    {
        return $this->_serviceHelper->getCUserMoreInfo($this->uid);
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CDBResult
     */
    public function _userExtraInfo($refresh = false)
    {
        return $this->_callCachedMethod('_my_extrainfo', $refresh);
    }

    private function _my_extrainfo()
    {
        return $this->_serviceHelper->getCUserInfoExtra($this->_uid);
    }

    /**
     *
     * @param bool $refresh
     *
     * @return CDBResult
     */
    public function userExtraInfo($refresh = false)
    {
        return $this->_callCachedMethod('_ta_extrainfo', $refresh);
    }

    private function _ta_extrainfo()
    {
        return $this->_serviceHelper->getCUserInfoExtra($this->uid);
    }

    /**
     * @param bool $refresh
     *
     * @return int 0-3
     */
    public function _fakeLevel($refresh = false)
    {
        return $this->_callCachedMethod('_my_fake_level', $refresh);
    }

    private function _my_fake_level()
    {
        return $this->_serviceHelper->getFakeLevel($this->_uid);
    }

    private function _callCachedMethod($internalMethodName, $refresh)
    {
        if (!isset(self::$_cachedResults[$internalMethodName]) || $refresh)
        {
            self::$_cachedResults[$internalMethodName] = call_user_func(
                array(
                    $this,
                    $internalMethodName
                )
            );
        }

        return self::$_cachedResults[$internalMethodName];
    }

}
