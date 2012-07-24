<?php
/**
 * AOP of logging and tracking.
 *
 * 经常要加一些统计代码，统一在这里实现，就容易管理咯
 *
 * 此外，留脚印、user renew time等都放在这里
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo 长期定向跟踪部分用户，规模不宜过大，1万以内，跟踪他们的行为
 */
class KHttp_Middleware_Tracker extends DHttp_Middleware
{

    public function call()
    {
        $this->_prepareUserContext();

        if (!$this->_uid)
        {
            // 未登录
            $req = $this->getRequest();

            $this->_getLogger()->statLog2(
                $req->getBrowserUid(),
                KBiz_Util_LogTagger::openHalfLogin($req->getSimplifiedHost())
            );
        }

        // 启动市场活动效果跟踪器
        CCampaignTracker::start();

        // 留脚印
        $cvisit = CServiceFactory::getVisitService();
        $cvisit->visit($this->_uid, $this->uid, true);

        // renew user time ?
        if ($this->_uid)
        {
            $cuser = CServiceFactory::getUserService();
            $cuser->updateTime($this->_uid);
        }

        // 记录下用户可能的切换web server的事件
        $this->_getLogger()->addSysLog(
            $req->getRemoteAddr()
                . " " . $this->_uid
                . " " . KBiz_Util_Formatter::resolveWebHexIp($req->getLastWebHexIp())
                . " " . KBiz_Util_Formatter::resolveWebHexIp($req->getCurrentWebHexIp())
                . " " . $req->getMethod()
                . " " . $req->getRequestUri()
        );

        $this->_callNextMiddleware();

    }

}
