<?php
/**
 * HTTP 中间件.
 *
 * Simulation of WSGI's middleware mechanism: 著名的洋葱卷, named after that!
 *
 * AOP模式! 也就是plugin! 也被成为拦截器!
 *
 * 它能干的事情就是当你的项目写了一半时发现缺少啥全局要做的事情(比如需要验证权限),不用担心,搞一个中间件就是了。
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
     * 应用程序.
     *
     * @var DHttp_App
     */
    protected $_app;

    /**
     * 下一个中间件.
     *
     * @var DHttp_IMiddleware
     */
    private $_next;

    /**
     * 当前用户在访问哪个用户的信息.
     *
     * 该值是通过url的uid参数传递进来的
     *
     * @var int
     */
    protected $uid;

    /**
     * 当前登录用户的uid.
     *
     * 该值保存在session里
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
     * 把应用注入到本中间件里.
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
     * 设置下一个接力棒.
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
     * 给$this->uid, _uid赋值.
     *
     * 没有放到constructor里，是因为有很多中间件，而很多中间件不需要uid/_uid值，不需要
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
     * 立即结束，并给浏览器发送状态码和正文.
     *
     * 它会覆盖现有上下文里的{@link DHttp_Response}对象
     *
     * 供{@link DHttp_Action}使用.
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
     * 中间件控制转发到指定的action.
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
     * 让下一个中间件执行.
     */
    protected final function _callNextMiddleware()
    {
        $this->_next->call();
    }

}
