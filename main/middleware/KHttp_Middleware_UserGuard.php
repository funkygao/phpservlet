<?php
/**
 * �����Ѿ���¼�û��ı���������.
 *
 * ʵ����CKxApp::limitUid()
 *
 * <ul>
 * <li>��״���</li>
 * <li>����Ƶ��ˢ��ҳ��</li>
 * </ul>
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_UserGuard extends DHttp_Middleware
{

    public function call()
    {
        $req = $this->getRequest();

        $script = $req->getScriptName();
        $this->_prepareUserContext();

        // TODO ÿ��ҳ�����Ƶ���ȵĶ��岻ͬ
        if ($this->_uid && !CPageLimit::check($this->_uid, $time))
        {
            if ($time == 60)
            {
                // ������ÿ���ӵ����ƹ���
                throw new Exception('����������ҳ����࣬���һ���Ӻ�ˢ�±�ҳ�档', 0);
            }
            else
            {
                // ������ÿ�յ����ƹ���
                $this->_redirect(self::URL_REFRESH_LIMITED);
            }
        }

        // ���Ǻ��ѵ�ʱ�򣬲鿴�û���Ϣҳ��Ҫ�ܵ�����
        if (!CServiceFactory::getFriendService()->isFriend($this->_uid, $this->uid)
            && $this->_isFriendPageLimited($script))
        {
            $strangerlimit = CLimit::checkLimit($this->_uid, "strangervisit_limit");
            if ($strangerlimit <= 0)
            {
                $target = self::URL_STRANGER_LIMITED;
                if ($req->isFromWap())
                {
                    $target = '/';
                }

                $this->_redirect($target);
            }
        }

        // �Ƿ��û������ڷ��ʷǷ��û�ʱ����ת
        foreach (array($this->_uid, $this->uid) as $uid)
        {
            $target = $this->_getInvalidUserRedirectTarget($uid);
            if ($target)
            {
                $this->_redirect($target);
            }
        }

        $blacked = new CUserBlack();
        if ($blacked->isBlackList($this->uid, $this->_uid))
        {
            $this->_redirect(self::URL_BLACKED_OUT);
        }

        $this->_callNextMiddleware();

    }

    private function _getInvalidUserRedirectTarget($uid)
    {
        $userConfig = KBiz_User_Config::getInstance($uid);
        if ($userConfig->isAccountDropped())
        {
            return self::URL_ACCOUNT_DROPPED;
        }

        if ($userConfig->getFakeLevel() == 3)
        {
            return self::URL_LOGOUT;
        }

        return '';
    }

    private function _isFriendPageLimited($script)
    {
        $limitedPages = array(
            '/home/index.php',
            '/set/detail.php',
            '/blog/index.php',
            '/blog/detail.php',
            '/home/detail.php',
        );

        return in_array($script, $limitedPages);
    }

}
