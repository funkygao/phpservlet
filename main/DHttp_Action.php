<?php
/**
 * Action.
 *
 * Action的工作就是通过访问 HTTP 会话、HTTP 请求和表单参数等调用业务
 * 逻辑,最后把响应数据映射到VO上,来完成特定的功能。
 *
 * 通常，我们把一组相关的页面，做成一个action，其中的每个特定方法on_XXX()
 * 对应一个页面(即用户看到的url)
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo remove the deprecated
 */
abstract class DHttp_Action implements DHttp_Result, DHttp_Constant
{

    /**
     * @var DHttp_App
     */
    protected $_app;

    public function __construct(DHttp_App $app)
    {
        $this->_app = $app;
    }

    /**
     * 获取当前{@link DHttp_Action}的类名.
     *
     * @return string
     */
    public final function getClassName()
    {
        return get_class($this);
    }

    /**
     * @return DHttp_App
     */
    public final function getApp()
    {
        return $this->_app;
    }

    /**
     * @return DHttp_KxRequest
     */
    public final function getRequest()
    {
        return $this->_app->request();
    }

    /**
     * @return DHttp_Controller
     */
    public final function getController()
    {
        return $this->_app->controller();
    }

    /**
     * @param bool $autocreate
     * @param string $memcacheGroup
     *
     * @return DHttp_Session
     */
    public final function getSession($autocreate = true, $memcacheGroup = 'plat')
    {
        return $this->getRequest()->getSession($autocreate, $memcacheGroup);
    }

    /**
     * @return DHttp_Response
     */
    public final function getResponse()
    {
        return $this->_app->response();
    }

    /**
     * @return DLogger_Facade
     */
    public final function getLogger()
    {
        return DLogger_Facade::getLogger();
    }

    /**
     * @return DHttp_Config
     */
    public final function getConfig()
    {
        return $this->_app->config();
    }

    /**
     * 立即结束.
     *
     */
    protected final function _stop()
    {
        $this->_app->stop();
    }

    /**
     * 立即结束，并给浏览器发送状态码和正文.
     *
     * @param int $statusCode
     * @param string $body
     */
    protected final function _halt($statusCode, $body = '')
    {
        $this->_app->halt($statusCode, $body);
    }

    /**
     * 页面重定向.
     *
     * @param string $url
     * @param int $status
     */
    protected final function _redirect($url, $status = self::SC_MOVED_TEMPORARILY)
    {
        $this->_app->redirect($url, $status);
    }

    /**
     * 转发给某个action处理请求.
     *
     * 注意与`_redirect()`的不同：
     * 内部跳转和外部跳转的区别，前者在跳转时保留request/response信息
     *
     * @param string $actionClass
     * @param string $actionMethod
     *
     * @return string Result name
     */
    protected final function _forward($actionClass, $actionMethod)
    {
        return $this->_app->forward($actionClass, $actionMethod);
    }

    /**
     * Set Last-Modified HTTP Response Header.
     *
     * @param int $time Timestamp
     *
     * @return void
     * @deprecated
     */
    protected final function _lastModified($time)
    {
        if (!is_integer($time))
        {
            return;
        }

        $this->getResponse()->header(self::HDR_LAST_MODIFIED, date(DATE_RFC1123, $time));

        if ($time == strtotime($this->getRequest()->header('If-Modified-Since')))
        {
            $this->_halt(self::SC_NOT_MODIFIED);
        }
    }

    /**
     * Set Expires HTTP response header.
     *
     * @param int|string $time
     * @deprecated
     */
    protected final function _expires($time)
    {
        if (is_string($time))
        {
            $time = strtotime($time);
        }

        $this->getResponse()->header(self::HDR_EXPIRES, gmdate(DATE_RFC1123, $time));
    }

    /**
     * 页面直接输出"另存为"的对话框让用户下载.
     *
     * @param string $filename 用户对话框里的文件名
     * @param string $content 文件内容
     * @param string $encoding 文件编码
     */
    protected final function _exportFile($filename, $content, $encoding = SYS_CHARSET)
    {
        $res = $this->getResponse();

        $res->reset();
        $res->contentType(self::CONTENT_TYPE_STREAM, $encoding);
        $res->header('Content-Disposition', 'attachment;filename=' . urlencode($filename));
        $res->body($content);
    }

    /**
     * @param string $body HTTP body
     * @param string $contentType
     * @param string $encoding
     */
    private function _renderByContentType($body, $contentType, $encoding)
    {
        $res = $this->getResponse();
        $res->reset();
        $res->disableBrowserCache();

        $res->status(self::SC_OK);
        $res->contentType($contentType, $encoding);
        $res->body($body);
    }

    /**
     * @param string $jsCode
     */
    protected function _renderJs($jsCode)
    {
        $this->_renderByContentType($jsCode, self::CONTENT_TYPE_JS, self::DEFAULT_CHARSET);
    }

    /**
     * @param string $txt
     */
    protected function _renderText($txt)
    {
        $this->_renderByContentType($txt, self::CONTENT_TYPE_TXT, self::DEFAULT_CHARSET);
    }

    /**
     * @param string $encodedJson 已经经过json_encode过的json值
     */
    protected function _renderJson($encodedJson)
    {
        $this->_renderByContentType($encodedJson, self::CONTENT_TYPE_JSON, self::DEFAULT_CHARSET);
    }

    /**
     * 把数组输出成json作为HTTP response.
     *
     * 通常用于AJAX处理
     *
     * 如果使用本方法，那么该action的result name就得是RESULT_NONE了
     *
     * @param array $arr
     */
    protected function _renderArrayAsJson($arr)
    {
        $json = json_encode(DUtil_String::toUTF8($arr));
        $this->_renderJson($json);
    }

    /**
     * @param string $xml
     */
    protected function _renderXml($xml)
    {
        $this->_renderByContentType($xml, self::CONTENT_TYPE_XML, self::DEFAULT_CHARSET);
    }

}
