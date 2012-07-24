<?php
/**
 * 开心网访问保护的中间件.
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo 在跳转时支持forward, 降低逻辑复杂度，或者移动到业务逻辑层实现
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

        // 用户伪造IP
        if ($ip != $ip2)
        {
            $logger->logIpForgery($ip, $ip2);
        }

        // 非法HTTP请求方法
        if (!$req->isMethodAllowed())
        {
            $logger->addSysDebugLog("invalid_http_method " . $ip
                . " " . $script
                . " " . $refer);

            $this->_app->halt(self::SC_METHOD_NOT_ALLOWED);
        }

        // 某些页面只允许POST或GET方法
        $redirectTarget = $this->getConfig()->redirect($req->getMethod());
        if (!is_null($redirectTarget))
        {
            // TODO log
            // TODO 支持forward
            $this->_redirect($redirectTarget);
        }

        // 对于POST请求只允许内部Ref发起
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

        //对于reg/login域名，单独处理
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
