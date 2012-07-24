<?php
/**
 * AOP of logging and tracking.
 *
 * ����Ҫ��һЩͳ�ƴ��룬ͳһ������ʵ�֣������׹���
 *
 * ���⣬����ӡ��user renew time�ȶ���������
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo ���ڶ�����ٲ����û�����ģ���˹���1�����ڣ��������ǵ���Ϊ
 */
class KHttp_Middleware_Tracker extends DHttp_Middleware
{

    public function call()
    {
        $this->_prepareUserContext();

        if (!$this->_uid)
        {
            // δ��¼
            $req = $this->getRequest();

            $this->_getLogger()->statLog2(
                $req->getBrowserUid(),
                KBiz_Util_LogTagger::openHalfLogin($req->getSimplifiedHost())
            );
        }

        // �����г��Ч��������
        CCampaignTracker::start();

        // ����ӡ
        $cvisit = CServiceFactory::getVisitService();
        $cvisit->visit($this->_uid, $this->uid, true);

        // renew user time ?
        if ($this->_uid)
        {
            $cuser = CServiceFactory::getUserService();
            $cuser->updateTime($this->_uid);
        }

        // ��¼���û����ܵ��л�web server���¼�
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
