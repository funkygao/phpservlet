<?php
/**
 * 对于已经登录用户的保护和限制.
 *
 * 实现了CKxApp::limitUid()
 *
 * <ul>
 * <li>马甲处理</li>
 * <li>过度频繁刷新页面</li>
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

        // TODO 每个页面对于频繁度的定义不同
        if ($this->_uid && !CPageLimit::check($this->_uid, $time))
        {
            if ($time == 60)
            {
                // 触发了每分钟的限制规则
                throw new Exception('你连续访问页面过多，请等一分钟后刷新本页面。', 0);
            }
            else
            {
                // 触发了每日的限制规则
                $this->_redirect(self::URL_REFRESH_LIMITED);
            }
        }

        // 不是好友的时候，查看用户信息页面要受到限制
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

        // 非法用户、或在访问非法用户时的跳转
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
