<?php
/**
 * HTTP上下文的纯工具类.
 *
 * 获取请求、响应等对象
 *
 * 取得的对象都是单例，因此可以放心地多次调用
 *
 * @category http
 * @package http
 * @version $Id$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_ContextUtil
{
    /**
     * 当前web server的内网IP.
     *
     * 静态化是为了减少对fs的请求
     *
     * @var string
     */
    private static $_localIp;

    /**
     * @var DHttp_Request
     */
    private static $_request = null;

    /**
     * @var DHttp_KxRequest
     */
    private static $_kxRequest = null;

    /**
     * @var DHttp_Response
     */
    private static $_response = null;

    /**
     * 不允许外部来new
     */
    private function __construct()
    {

    }

    /**
     * 获取的当前上下文的HTTP request对象.
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute 把请求参数作为本对象的成员属性? Default false
     *
     * @return DHttp_Request
     */
    public static function getRequest($env = null, $paramAsInstanceAttribute = false)
    {
        return self::_getRequest(self::$_request, 'DHttp_Request',
            $env, $paramAsInstanceAttribute);
    }

    /**
     * 取得当前上下文的与开心网业务相关的HTTP request对象.
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute 把请求参数作为本对象的成员属性? Default false
     *
     * @return DHttp_KxRequest
     */
    public static function getKxRequest($env = null, $paramAsInstanceAttribute = false)
    {
        return self::_getRequest(self::$_kxRequest, 'DHttp_KxRequest',
            $env, $paramAsInstanceAttribute);
    }

    private static function _getRequest(&$instance, $class, $env, $paramAsInstanceAttribute)
    {
        if (is_null($instance) || !is_null($env))
        {
            $instance = new $class($env, $paramAsInstanceAttribute);
        }

        return $instance;
    }

    /**
     * 获取当前上下文的HTTP response对象.
     *
     * @param bool $refresh
     *
     * @return DHttp_Response
     */
    public static function getResponse($refresh = false)
    {
        if (is_null(self::$_response) || $refresh)
        {
            self::$_response = new DHttp_Response();
        }

        return self::$_response;
    }

    /**
     * 当前用户是否已经登录?
     *
     * @return bool
     */
    public static function isUserLoggedIn()
    {
        // cache result
        static $loggedIn = null;
        if (!is_null($loggedIn))
        {
            return $loggedIn;
        }

        $uid = self::getKxRequest()->getLoggedInUid();
        $loggedIn = !empty($uid);

        return $loggedIn;
    }

    /**
     * 返回当前web服务器的内网地址.
     *
     * @return string e,g 192.168.16.89
     */
    public static function getLocalIp()
    {
        if (is_null(self::$_localIp))
        {
            // OPS运维人员会为每个web服务器维护该文件
            $ipFilename = DATA_PATH . "/localip";
            if (is_file($ipFilename))
            {
                self::$_localIp = trim(file_get_contents($ipFilename));
            }
            else
            {
                self::$_localIp = '127.0.0.1';
            }
        }

        return self::$_localIp;
    }

    /**
     * 返回当前web服务器的内网地址HEX值.
     *
     * 取ip地址后2部分，并返回其倒序16进制值
     *
     * e,g 192.168.0.142，则返回 E800
     *
     * @return string
     */
    public static function getHexLocalIp()
    {
        return KBiz_Util_Formatter::createWebHexIp(self::getLocalIp());
    }

    /**
     * php的主版本号.
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        $version = phpversion();
        return substr($version, 0, strpos($version, '.'));
    }

    /**
     * php的次版本号.
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        $version = phpversion();
        return substr($version, strpos($version, '.') + 1);
    }

    /**
     * 某个开心网地址是否是图片?
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function isKxPicUri($uri)
    {
        return substr($uri, 0, 5) == "/pic/"
            || substr($uri, 0, 9) == "/privacy/"
            || substr($uri, 0, 6) == "/logo/";
    }

    /**
     * 某个域名是否是开心网内部域名?
     *
     * @param string $hostname
     *
     * @return bool
     */
    public static function isInnerDomain($hostname)
    {
        return self::isKxImgHost($hostname)
            || self::isKxWebHost($hostname)
            || $hostname == SINAMUSIC_HOST;
    }

    /**
     * 是否是开心网图片域名?
     *
     * @param string $hostname
     *
     * @return bool
     */
    public static function isKxImgHost($hostname)
    {
        $hostname = rtrim($hostname, '.');
        return substr($hostname, 0 - strlen(COMMON_IMGHOST)) == COMMON_IMGHOST
            || substr($hostname, 0 - strlen(N1_COMMON_IMGHOST)) == N1_COMMON_IMGHOST;
    }

    /**
     * 是否是开心网web应用的域名?
     *
     * @param string $hostname
     *
     * @return bool
     */
    public static function isKxWebHost($hostname)
    {
        $hostname = rtrim($hostname, '.');
        return substr($hostname, 0 - strlen(COMMON_HOST)) == COMMON_HOST
            || substr($hostname, 0 - strlen(N1_COMMON_HOST)) == N1_COMMON_HOST;
    }

}
