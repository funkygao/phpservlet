<?php
/**
 * 拦截器，中间件的一种形式.
 *
 * 使用拦截器，我们只关心事前、事后的处理，不关心如何引导其他中间件
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo 让拦截器的before/after能有返回值:action result，这样它自己就能决定是否往下走了
 */
abstract class DHttp_Interceptor extends DHttp_Middleware
{

    public final function call()
    {
        $req = $this->getRequest();
        $res = $this->getResponse();

        $this->_before($req, $res);

        $this->_callNextMiddleware();

        $this->_after($req, $res);
    }

    /**
     * @param DHttp_Request $req
     * @param DHttp_Response $res
     *
     * @return string
     */
    protected abstract function _before(DHttp_Request $req, DHttp_Response $res);

    /**
     * @param DHttp_Request $req
     * @param DHttp_Response $res
     *
     * @return string
     */
    protected abstract function _after(DHttp_Request $req, DHttp_Response $res);

}
