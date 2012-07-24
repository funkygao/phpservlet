<?php
/**
 * HTTP �м��.
 *
 * Simulation of WSGI's middleware mechanism: ��������о�, named after that!
 *
 * AOPģʽ! Ҳ����plugin! Ҳ����Ϊ������!
 *
 * ���ܸɵ�������ǵ������Ŀд��һ��ʱ����ȱ��ɶȫ��Ҫ��������(������Ҫ��֤Ȩ��),���õ���,��һ���м�������ˡ�
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
abstract class DHttp_Middleware implements DHttp_IMiddleware, DHttp_Constant
{
    /**
     * Ӧ�ó���.
     *
     * @var DHttp_App
     */
    protected $_app;

    /**
     * ��һ���м��.
     *
     * @var DHttp_IMiddleware
     */
    private $_next;

    /**
     * ��ǰ�û��ڷ����ĸ��û�����Ϣ.
     *
     * ��ֵ��ͨ��url��uid�������ݽ�����
     *
     * @var int
     */
    protected $uid;

    /**
     * ��ǰ��¼�û���uid.
     *
     * ��ֵ������session��
     *
     * @var int
     */
    protected $_uid;

    /**
     * @return DLogger_Facade
     */
    protected function _getLogger()
    {
        return DLogger_Facade::getLogger();
    }

    /**
     * ��Ӧ��ע�뵽���м����.
     *
     * @param DHttp_App $app
     */
    public final function setApp($app)
    {
        $this->_app = $app;
    }

    /**
     *
     * @return DHttp_App
     */
    public final function getApp()
    {
        return $this->_app;
    }

    /**
     * ������һ��������.
     *
     * @param DHttp_IMiddleware $nextMiddleware
     */
    public final function setNext(DHttp_IMiddleware $nextMiddleware)
    {
        $this->_next = $nextMiddleware;
    }

    /**
     *
     * @return DHttp_IMiddleware
     */
    public final function getNext()
    {
        return $this->_next;
    }

    /**
     * @return DHttp_KxRequest
     */
    public final function getRequest()
    {
        return $this->_app->request();
    }

    /**
     * @return DHttp_Response
     */
    public final function getResponse()
    {
        return $this->_app->response();
    }

    /**
     * @return DHttp_Config
     */
    public final function getConfig()
    {
        return $this->_app->config();
    }

    /**
     *
     * @return DHttp_Action Null if not dispatched yet
     */
    public final function getAction()
    {
        return $this->_app->action();
    }

    /**
     * ��$this->uid, _uid��ֵ.
     *
     * û�зŵ�constructor�����Ϊ�кܶ��м�������ܶ��м������Ҫuid/_uidֵ������Ҫ
     * eager fetch.
     */
    protected final function _prepareUserContext()
    {
        if (is_null($this->_uid) || is_null($this->uid))
        {
            $req = $this->getRequest();

            $this->_uid = $req->getLoggedInUid();
            $this->uid = $req->getCalleeUid();
        }
    }

    /**
     * @param string $url
     * @param int $status
     */
    protected final function _redirect($url, $status = self::SC_MOVED_TEMPORARILY)
    {
        $this->_app->redirect($url, $status);
    }

    /**
     * �����������������������״̬�������.
     *
     * ���Ḳ���������������{@link DHttp_Response}����
     *
     * ��{@link DHttp_Action}ʹ��.
     *
     * @param int $statusCode HTTP status code
     * @param string $body HTTP body content
     *
     * @throws KHttp_Exception_Stop
     */
    protected final function _halt($statusCode, $body = '')
    {
        $this->_app->halt($statusCode, $body);
    }

    /**
     * �м������ת����ָ����action.
     *
     * @param string $actionClass
     * @param string $actionMethod
     */
    protected final function _forward($actionClass, $actionMethod)
    {
        $config = $this->getConfig();
        $config->setActionClass($actionClass)->setActionMethod($actionMethod);
    }

    /**
     * ����һ���м��ִ��.
     */
    protected final function _callNextMiddleware()
    {
        $this->_next->call();
    }

}
