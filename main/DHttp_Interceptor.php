<?php
/**
 * ���������м����һ����ʽ.
 *
 * ʹ��������������ֻ������ǰ���º�Ĵ���������������������м��
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo ����������before/after���з���ֵ:action result���������Լ����ܾ����Ƿ���������
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
