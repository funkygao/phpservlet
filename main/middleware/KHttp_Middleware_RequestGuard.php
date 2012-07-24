<?php
/**
 * ���������ʱ������м��.
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo ����תʱ֧��forward, �����߼����Ӷȣ������ƶ���ҵ���߼���ʵ��
 */
class KHttp_Middleware_RequestGuard extends DHttp_Middleware
{

    const BAIDU_HOST = '.baidu.com';

    public function call()
    {
        $req = $this->getRequest();
        $script = $req->getScriptName();
        $refer = $req->getReferer();
        $logger = $this->_getLogger();

        $ip = $req->getRemoteAddr();
        $ip2 = $req->getRemoteAddr($antiFake = true);

        // �û�α��IP
        if ($ip != $ip2)
        {
            $logger->logIpForgery($ip, $ip2);
        }

        // �Ƿ�HTTP���󷽷�
        if (!$req->isMethodAllowed())
        {
            $logger->addSysDebugLog("invalid_http_method " . $ip
                . " " . $script
                . " " . $refer);

            $this->_app->halt(self::SC_METHOD_NOT_ALLOWED);
        }

        // ĳЩҳ��ֻ����POST��GET����
        $redirectTarget = $this->getConfig()->redirect($req->getMethod());
        if (!is_null($redirectTarget))
        {
            // TODO log
            // TODO ֧��forward
            $this->_redirect($redirectTarget);
        }

        // ����POST����ֻ�����ڲ�Ref����
        if ($req->isPostMethod()
            && $refer
            && !$req->getBoolAttribute(self::BIZ_EXTERNAL_POST_ENABLED))
        {
            $host = $req->getRefererHost();
            if (CStr::contains($host, self::BAIDU_HOST))
            {
                $logger->statLogWithRequest($req, 'baidu_open');
            }
            elseif (!$req->isInnerDomain($host))
            {
                $logger->addSysDebugLog("wrongref_post " . $ip . " " . $script . " " . $refer);

                $this->_redirect('/');
            }
        }

        //����reg/login��������������
        if ($req->isGetMethod())
        {
            $host = $req->getHost();
            if (($host == "reg" . COMMON_HOST && $script != "/" && substr($script, 0, 11) != "/interface/")
                || ($host == "login" . COMMON_HOST && $script != "/"))
            {
                $this->_redirect('http://' . WWW_HOST . $script);
            }
        }

        $this->_callNextMiddleware();

    }

}
