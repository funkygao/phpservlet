<?php
/**
 * HTTP�����ĵĴ�������.
 *
 * ��ȡ������Ӧ�ȶ���
 *
 * ȡ�õĶ����ǵ�������˿��Է��ĵض�ε���
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
     * ��ǰweb server������IP.
     *
     * ��̬����Ϊ�˼��ٶ�fs������
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
     * �������ⲿ��new
     */
    private function __construct()
    {

    }

    /**
     * ��ȡ�ĵ�ǰ�����ĵ�HTTP request����.
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute �����������Ϊ������ĳ�Ա����? Default false
     *
     * @return DHttp_Request
     */
    public static function getRequest($env = null, $paramAsInstanceAttribute = false)
    {
        return self::_getRequest(self::$_request, 'DHttp_Request',
            $env, $paramAsInstanceAttribute);
    }

    /**
     * ȡ�õ�ǰ�����ĵ��뿪����ҵ����ص�HTTP request����.
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute �����������Ϊ������ĳ�Ա����? Default false
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
     * ��ȡ��ǰ�����ĵ�HTTP response����.
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
     * ��ǰ�û��Ƿ��Ѿ���¼?
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
     * ���ص�ǰweb��������������ַ.
     *
     * @return string e,g 192.168.16.89
     */
    public static function getLocalIp()
    {
        if (is_null(self::$_localIp))
        {
            // OPS��ά��Ա��Ϊÿ��web������ά�����ļ�
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
     * ���ص�ǰweb��������������ַHEXֵ.
     *
     * ȡip��ַ��2���֣��������䵹��16����ֵ
     *
     * e,g 192.168.0.142���򷵻� E800
     *
     * @return string
     */
    public static function getHexLocalIp()
    {
        return KBiz_Util_Formatter::createWebHexIp(self::getLocalIp());
    }

    /**
     * php�����汾��.
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        $version = phpversion();
        return substr($version, 0, strpos($version, '.'));
    }

    /**
     * php�Ĵΰ汾��.
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        $version = phpversion();
        return substr($version, strpos($version, '.') + 1);
    }

    /**
     * ĳ����������ַ�Ƿ���ͼƬ?
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
     * ĳ�������Ƿ��ǿ������ڲ�����?
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
     * �Ƿ��ǿ�����ͼƬ����?
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
     * �Ƿ��ǿ�����webӦ�õ�����?
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
