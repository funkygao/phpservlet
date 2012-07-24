<?php
/**
 * ��ѭaction�淶�Ķ����Զ��ӳټ��ط����.
 *
 * ��ͬʱʵ���˺ܶ࿪����ҵ����صĸ���������Ҳ�ѵ�ǰ��������Ϣ
 * ����ɿ��Է�����õĳ�Ա�����ͷ���
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
     * ��ǰ�û��ڷ����ĸ��û�����Ϣ.
     *
     * ��ֵ��ͨ��url��uid�������ݽ�����
     *
     * @var int ���δ����uid��������0
     */
    protected $uid;

    /**
     * ��ǰ��¼�û���uid.
     *
     * ��ֵ������session��
     *
     * @var int ���δ��¼����0
     */
    protected $_uid;

    /**
     * @var DLogger_Facade
     */
    protected $_logger;

    /**
     *
     * ���뱩¶��������
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

        // ����ÿ��action�����õ�������Щֵ�����eager fetch
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
     * ���������ύPOST���󵽿�����.
     *
     */
    public final function enableExternalPost()
    {
        $this->getRequest()->setAttribute(self::BIZ_EXTERNAL_POST_ENABLED, true);
    }

}
