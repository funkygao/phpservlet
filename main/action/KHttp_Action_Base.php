<?php
/**
 * 遵循action规范的而且自动延迟加载服务层.
 *
 * 它同时实现了很多开心网业务相关的辅助方法，也把当前上下文信息
 * 抽象成可以方便调用的成员变量和方法
 *
 * @package http
 * @subpackage action
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
abstract class KHttp_Action_Base extends DHttp_Action
{

    /**
     * 当前用户在访问哪个用户的信息.
     *
     * 该值是通过url的uid参数传递进来的
     *
     * @var int 如果未传入uid参数，则0
     */
    protected $uid;

    /**
     * 当前登录用户的uid.
     *
     * 该值保存在session里
     *
     * @var int 如果未登录，则0
     */
    protected $_uid;

    /**
     * @var DLogger_Facade
     */
    protected $_logger;

    /**
     *
     * 不想暴露给子类了
     *
     * @var DHttp_UserHelper
     */
    private $_userHelper;

    /**
     * @var DHttp_ServiceHelper
     */
    protected $_serviceHelper;

    /**
     * @param DHttp_App $app
     */
    public function __construct(DHttp_App $app)
    {
        parent::__construct($app);

        // 几乎每个action都会用到下面这些值，因此eager fetch
        $this->_logger = $this->getLogger();

        $req = $this->getRequest();
        $this->_uid = $req->getLoggedInUid();
        $this->uid = $req->getCalleeUid();

        $this->_serviceHelper = new DHttp_ServiceHelper();
        $this->_userHelper = new DHttp_UserHelper($this->_uid, $this->uid, $this->_serviceHelper);
    }

    /**
     * @return bool
     */
    public final function isUserLoggedIn()
    {
        return !empty($this->_uid);
    }

    /**
     * @return bool
     */
    public final function isMe()
    {
        return $this->_userHelper->isMe();
    }

    /**
     * @return int
     */
    public final function isFriend()
    {
        return $this->_userHelper->isFriend();
    }

    /**
     * @return int
     */
    public final function isStar()
    {
        return $this->_userHelper->isStar();
    }

    /**
     * @return bool
     */
    public final function isStarOrg()
    {
        return $this->_userHelper->isStarOrg();
    }

    /**
     * @return bool
     */
    public final function isStarPerson()
    {
        return $this->_userHelper->isStarPerson();
    }

    /**
     * @return int
     */
    public final function _isStar()
    {
        return $this->_userHelper->_isStar();
    }

    /**
     *
     * @return bool
     */
    public final function _isStarOrg()
    {
        return $this->_userHelper->isStarOrg();
    }

    /**
     *
     * @return bool
     */
    public final function _isStarPerson()
    {
        return $this->_userHelper->_isStarPerson();
    }

    /**
     * @return array
     */
    public final function _appList()
    {
        return $this->_userHelper->_appList();
    }

    /**
     * @param int $appid
     *
     * @return string
     */
    public final function _app_config($appid)
    {
        return $this->_userHelper->_app_config($appid);
    }

    /**
     * @return CDBResult
     */
    public final function _userInfo()
    {
        return $this->_userHelper->_userInfo();
    }

    /**
     * @return CDBResult
     */
    public final function userInfo()
    {
        return $this->_userHelper->userInfo();
    }

    /**
     * @return CUObjectResult
     */
    public final function _userMoreInfo()
    {
        return $this->_userHelper->_userMoreInfo();
    }

    /**
     * @return CUObjectResult
     */
    public final function userMoreInfo()
    {
        return $this->_userHelper->userMoreInfo();
    }

    /**
     * @return CDBResult
     */
    public final function _userExtraInfo()
    {
        return $this->_userHelper->_userExtraInfo();
    }

    /**
     * @return CDBResult
     */
    public final function userExtraInfo()
    {
        return $this->_userHelper->userExtraInfo();
    }

    /**
     * @return int
     */
    public final function _fakeLevel()
    {
        return $this->_userHelper->_fakeLevel();
    }

    /**
     * 允许外链提交POST请求到开心网.
     *
     */
    public final function enableExternalPost()
    {
        $this->getRequest()->setAttribute(self::BIZ_EXTERNAL_POST_ENABLED, true);
    }

}
