<?php
/**
 * HTTP Request����.
 *
 * ����java��ServletRequest��Ŀ����Ҫʵ������java servlet��python WSGI����
 * ��webӦ�ù淶��ͬʱҲΪ������RESTful OpenAPI��׼��
 *
 * �� $_SERVER, $_GET, $_POST, $_COOKIES�ķ�װ��ͬʱ���ڲ�ʵ��session����
 *
 * <pre>
 *
 *                          -- DHttp_Env
 *                         |
 *        DHttp_Request ---|-- DHttp_Session
 *              ^          |
 *              |          |-- DHttp_UserAgent
 *              |          |
 *              |           -- DHttp_Cookie
 *              |
 *        DHttp_KxRequest
 *             
 * </pre>
 *
 * @category http
 * @package http
 * @version $Id$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 * @todo file upload, cookie encrypted, $this->_request = $_GETΪʲô����?, isUrlRewritten
 */
class DHttp_Request
{

    const
        METHOD_HEAD = 'HEAD',
        METHOD_GET = 'GET',
        METHOD_POST = 'POST',
        METHOD_PUT = 'PUT',
        METHOD_DELETE = 'DELETE',
        METHOD_OPTIONS = 'OPTIONS';

    /**
     * ������url�ϼ�?_ajax=1�������ʶ���Ǹ�AJAX����.
     *
     * ������������������ڷ���AJAX����ʱ��û�аѱ�Ҫ��ͷ������
     * ������������Ǿ���ȷ���κ�����������Ƕ���׼ȷ�ж��Ƿ�AJAX����
     *
     * Ŀǰ���û��ƻ�û�б�ʹ��
     */
    const AJAX_REQUEST_KX = '_kxajax';

    /**
     * ����session��cookie key.
     *
     */
    const SESSIONID_KEY = '_kxsess_';

    /**
     * The request data(params).
     *
     * ����$_GET �� $_POST
     *
     * @var array Map
     */
    private $_request;

    /**
     * ��������������.
     *
     * @var array|DHttp_Env
     */
    private $_env;

    /**
     * @var DHttp_UserAgent
     */
    private $_userAgent;

    /**
     * Session across requests.
     *
     * ��ȫin-house implementation����php��session�޹ء�
     * ��ˣ�ʹ��ʱ����session_start()����session_XXX()����Ҳ������ʹ��
     *
     * @var DHttp_Session
     */
    private $_session;

    /**
     * An array for controller and view variables exchange area.
     *
     * ������һ�������ڱ����м�״ֵ̬��ʹ�ø���Э����֮����Թ���һЩ״ֵ̬
     *
     * @var array
     */
    private $_attributes = array();

    /**
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute �����������Ϊ������ĳ�Ա����? Default false
     */
    public function __construct($env = null, $paramAsInstanceAttribute = false)
    {
        $this->_env = is_null($env) ? DHttp_Env::getInstance() : $env;
        $this->_userAgent = new DHttp_UserAgent($this->getUserAgent());

        // ���������GET��POST����
        // $this->_request = $_GET;
        $this->_request = &$_GET;
        if ($this->isPostMethod())
        {
            // POST�����ȼ� > GET
            $this->_request = array_merge($this->_request, $_POST);
        }

        /*
         * Ϊ����������Աһ��������Щ���ʲ���
         *
         * ���磺http://www.kaixin001.com/foo.php?bar=spam
         *
         * $request = new DHttp_Request();
         * echo $request->bar; // spam
         */
        if ($paramAsInstanceAttribute)
        {
            foreach ($this->_request as $k => $v)
            {
                $this->_addProperty($k, $v);
            }
        }

    }

    /**
     * �Ѳ�������Ϊ������ĳ�Ա����.
     *
     * @param string $param
     * @param mixed $value
     */
    private function _addProperty($param, $value)
    {
        if ($param[0] == '_')
        {
            // _ is reserved
            return;
        }

        $this->$param = $value;
    }

    /**
     * Return the value of the given HTTP header.
     *
     * ��Щ����HTTP request headers���ǲ��ñ����ε�.
     *
     * @param string $header
     *
     * @return string û�ж����򷵻�''
     */
    public final function header($header)
    {
        return $this->_env->getHttpHeader($header);
    }

    /**
     * Return the value of all HTTP headers.
     *
     * ��Щ����HTTP request headers���ǲ��ñ����ε�.
     *
     * @return array Map
     */
    public final function headers()
    {
        $headers = array();
        foreach ($this->_env as $key => $value)
        {
            if (strpos($key, 'HTTP_') === 0)
            {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * The transport channel between controller and view template.
     *
     * ֻ������һ��������(һ������)�����Ҫ�����󣬱���ʹ��{@link DHttp_Session}
     *
     * @param string $name
     * @param mixed $value �����Ƕ���
     */
    public final function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     *
     * ֻ������һ��������(һ������)�����Ҫ�����󣬱���ʹ��{@link DHttp_Session}
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public final function getAttribute($name, $default = null)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : $default;
    }

    /**
     * @param string $name
     * @param bool $default
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public final function getBoolAttribute($name, $default = false)
    {
        $result = isset($this->_attributes[$name]) ? $this->_attributes[$name] : $default;
        if (!is_bool($result))
        {
            throw new InvalidArgumentException("$name is not bool attribute");
        }

        return (bool)$result;
    }

    /**
     * @return array List of string i,e. ['uid', 'username', ...]
     */
    public final function getAttributeNames()
    {
        return array_keys($this->_attributes);
    }

    /**
     * @return array Map
     */
    public final function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     *
     * ֻ������һ��������(һ������)�����Ҫ�����󣬱���ʹ��{@link DHttp_Session}
     *
     * @param string $name
     */
    public final function removeAttribute($name)
    {
        if (isset($this->_attributes[$name]))
        {
            unset($this->_attributes[$name]);
        }
    }

    /**
     * ��ȡȫ�����������.
     *
     * @return array Map
     */
    public final function getParams()
    {
        return $this->_request;
    }

    /**
     * The portion of the request URI following the '?'
     *
     * ���û�У�����''
     *
     * @return string
     */
    public final function getQueryString()
    {
        return $this->_env->getQueryString();
    }

    /**
     * ��ȡĳ��int�͵��û���������.
     *
     * @param string $name
     * @param int $default
     *
     * @return int
     */
    public function getInt($name, $default = null)
    {
        return isset($this->_request[$name]) ?
            (int)$this->_request[$name] : $default;
    }

    /**
     * @param $name
     * @param float $default
     *
     * @return float
     */
    public function getFloat($name, $default = null)
    {
        return isset($this->_request[$name]) ?
            (float)$this->_request[$name] : $default;
    }

    /**
     * Get a boolean value from http request.
     *
     * @param string $name
     * @param bool $default
     *
     * @return bool
     */
    public function getBool($name, $default = null)
    {
        if (!isset($this->_request[$name]))
        {
            return $default;
        }

        if ('true' === strtolower($this->_request[$name]))
        {
            return true;
        }
        elseif ('false' === strtolower($this->_request[$name]))
        {
            return false;
        }
        else
        {
            return (bool)$this->_request[$name];
        }

    }

    /**
     * @param string $name
     * @param string $default
     * @param bool $xssClean
     *
     * @return string
     */
    public function getStr($name, $default = null, $xssClean = false)
    {
        $ret = isset($this->_request[$name]) ?
            (string)$this->_request[$name] : $default;

        if ($xssClean)
        {
            $ret = filter_var($ret, FILTER_SANITIZE_STRING);
        }

        return $ret;
    }

    /**
     * @param string $name
     * @param array $default
     *
     * @return array
     */
    public function getArray($name, $default = array())
    {
        if (isset($this->_request[$name])
            && is_array($this->_request[$name]))
        {
            return $this->_request[$name];
        }
        else
        {
            return $default;
        }
    }

    /**
     * �������Ƿ�����ĳ������?
     *
     * ������ֵ��ʲô
     *
     * @param string $name
     *
     * @return bool
     */
    public final function exists($name)
    {
        return array_key_exists($name, $this->_request);
    }

    /**
     * Determine whether a request param is empty.
     *
     * û�иò���������empty($thisParam)��������true
     *
     * @param string $name �������������POST
     *
     * @return bool
     */
    public final function isEmpty($name)
    {
        return !isset($this->_request[$name])
            || empty($this->_request[$name]);
    }

    /**
     *
     * @return string i,e. /home/index.php?uid=1
     */
    public final function getRequestUri()
    {
        return $this->_env->getRequestUri();
    }

    /**
     * Get Content-Type.
     *
     * @return string Null if not set
     */
    public final function getContentType()
    {
        return $this->_env->getContentType();
    }

    /**
     * Get Content-Length.
     *
     * @return int
     */
    public final function getContentLength()
    {
        return $this->_env->getContentLength();
    }

    /**
     * Get Media Type (type/subtype within Content Type header).
     *
     * @return string Null if not set
     */
    public final function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType)
        {
            $parts = preg_split('/\s*[;,]\s*/', $contentType);
            return strtolower($parts[0]);
        }
        else
        {
            return null;
        }
    }

    /**
     * ��apache������: ServerAdmin=you@example.com
     *
     * @return string e.g webmaster@corp.kaixin001.com
     */
    public final function getServerAdmin()
    {
        return $this->_env->getServerAdmin();
    }

    /**
     * ȡ��ԭʼ��POST����.
     *
     * ��ȡû�д������POST����
     *
     * �����$HTTP_RAW_POST_DATA���ԣ������ڴ������ѹ����С�����Ҳ���Ҫ�����php.ini����
     *
     * ����������
     * <ul>
     * <li>readable one time only</li>
     * <li>not available for mutipart/form-data requests</li>
     * </ul>
     *
     * @return string �յ�ʱ�򷵻�''
     */
    public final function getRawPostBody()
    {
        return $this->_env->getRawPostBody();
    }

    /**
     * Get the HTTP method of this request.
     *
     * @param bool $lowercase �Ƿ�ѽ��ת����Сд
     *
     * @return string
     */
    public final function getMethod($lowercase = false)
    {
        $method = $this->_env->getMethod();;
        if ($lowercase)
        {
            $method = strtolower($method);
        }

        return $method;
    }

    private function _isSomeMethod($some)
    {
        return $some === $this->getMethod(false);
    }

    /**
     * Is this a GET request?
     *
     * @return bool
     */
    public final function isGetMethod()
    {
        return $this->_isSomeMethod(self::METHOD_GET);
    }

    /**
     * Is this a POST request?
     *
     * @return bool
     */
    public final function isPostMethod()
    {
        return $this->_isSomeMethod(self::METHOD_POST);
    }

    /**
     * Is this a HEAD request?
     *
     * @return bool
     */
    public final function isHeadMethod()
    {
        return $this->_isSomeMethod(self::METHOD_HEAD);
    }

    /**
     * Is this a OPTIONS request?
     *
     * @return bool
     */
    public final function isOptionsMethod()
    {
        return $this->_isSomeMethod(self::METHOD_OPTIONS);
    }

    /**
     * Is this a DELETE method?
     *
     * For RESTful API
     *
     * @return bool
     */
    public final function isDeleteMethod()
    {
        return $this->_isSomeMethod(self::METHOD_DELETE);
    }

    /**
     * Is this a PUT request?
     *
     * For RESTful API
     *
     * @return bool
     */
    public final function isPutMethod()
    {
        return $this->_isSomeMethod(self::METHOD_PUT);
    }

    /**
     * @return bool
     */
    public final function isUrlRewritten()
    {
        $realScriptName = $this->_env->getScriptName();
        $virtualScriptName = reset(explode('?', $this->_env->getRequestUri()));
        if (CStr::endsWith($virtualScriptName, '/'))
        {
            // i,e. /home/?uid=1212
            $virtualScriptName .= 'index.php';
        }

        return !($realScriptName == $virtualScriptName);
    }

    /**
     * Is this a XMLHttpRequest request?
     *
     * @return bool
     */
    public final function isXmlHttpRequest()
    {
        // ���ж������Լ����ض�ajax���������
        if (isset($this->_request[self::AJAX_REQUEST_KX])
            && $this->_request[self::AJAX_REQUEST_KX])
        {
            return true;
        }

        return $this->_env->getHttpHeader('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * Is this a XHR(XMLHttpRequest) request?
     *
     * Alias of {@link self::isXmlHttpRequest}
     *
     * @return bool
     */
    public final function isXhr()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Is this a Javascript XMLHttpRequest?
     *
     * Alias of {@link self::isXmlHttpRequest}
     *
     * @return bool
     */
    public final function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Is this request coming from a mobile device?
     *
     * iPad����
     *
     * @return bool
     */
    public final function isMobile()
    {
        if ($this->_env->getHttpHeader('HTTP_X_OPERAMINI_PHONE'))
        {
            return true;
        }

        return $this->_userAgent->isMobile();
    }

    /**
     * ��ǰ�����Ƿ�����iPad?
     *
     * @return bool
     */
    public final function isIpad()
    {
        return $this->_userAgent->isIpad();
    }

    /**
     *
     * @return bool
     */
    public final function isIpod()
    {
        return $this->_userAgent->isIpod();
    }

    /**
     *
     * @return bool
     */
    public final function isAndroid()
    {
        return $this->_userAgent->isAndroid();
    }

    /**
     * ��ȡandroid�ͻ��˵İ汾��.
     *
     * ֻ����major.minor version number
     * e.g.
     * <p>
     * ����汾��2.1-update1���򷵻�2.1
     * ����汾��2.2.1���򷵻�2.2
     * </p>
     *
     * @return string False if not android client request
     */
    public final function getAndroidVersion()
    {
        return $this->_userAgent->getAndroidVersion();
    }

    /**
     * ��ǰ�����Ƿ�����iPhone?
     *
     * @return bool
     */
    public final function isIphone()
    {
        return $this->_userAgent->isIphone();
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public final function isFlash()
    {
        return $this->_userAgent->isFlash();
    }

    /**
     * @return bool
     */
    public final function isMeeGo()
    {
        return $this->_userAgent->isMeeGo();
    }

    /**
     * @return bool
     */
    public final function isUcWebClient()
    {
        return $this->_userAgent->isUcWebClient();
    }

    /**
     * Is this request coming from a bot or spider?
     *
     * Ŀǰ֧�֣�google, msn, yahoo, etc
     *
     * @return bool
     */
    public final function isSpider()
    {
        return $this->_userAgent->isSpider();
    }

    /**
     * Is the request from CLI?
     *
     * @return bool
     */
    public final function isCli()
    {
        return 'cli' === PHP_SAPI;
    }

    /**
     * Is this a https request?
     *
     * @return bool
     */
    public final function isSsl()
    {
        return $this->getScheme() === 'https';
    }

    /**
     * Alias of isSsl().
     *
     * @return bool
     */
    public final function isSecure()
    {
        return $this->isSsl();
    }

    /**
     * ����HTTP_ACCEPT_LANGUAGEȡ�õ�ǰ�����������.
     *
     * @param string $default
     *
     * @return string i,e zh_CN
     */
    public final function getLang($default = 'zh_CN')
    {
        $browserLang = $this->_env->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        if (is_null($browserLang))
        {
            return $default;
        }

        $parts = explode(',', $browserLang);
        return trim($parts[0]);
    }

    /**
     * ��ȡ��ǰ����������֧�ֵ���������.
     *
     * @return array ['zh-CN', 'zh', ...]
     */
    public final function getLangs()
    {
        $browserLang = $this->_env->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        if (is_null($browserLang))
        {
            return array();
        }

        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $browserLang, $langParsed);
        if (count($langParsed[1]))
        {
            // create a list like "en" => 0.8
            $langs = array_combine($langParsed[1], $langParsed[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val)
            {
                if ($val === '')
                {
                    $langs[$lang] = 1;
                }
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }

        return array_keys($langs);
    }

    /**
     * ʹ�õ�HTTPЭ��Ͱ汾��Ϣ.
     *
     * ʵ�������������������ָ���ģ���Ȼ������������SERVER:
     * GET /index.php HTTP/1.1
     *
     * @param bool $lowercase Default false
     *
     * @return string e.g HTTP/1.1
     */
    public final function getServerProcotol($lowercase = false)
    {
        $protocol = $this->_env->getServerProtocol();
        if ($lowercase)
        {
            $protocol = strtolower($protocol);
        }

        return $protocol;
    }

    /**
     * ʵ������Ķ˿ں�.
     *
     * @return int i,e 80
     */
    public final function getPort()
    {
        return $this->_env->getServerPort();
    }

    /**
     * Get a cookie by name supporting default value.
     *
     * @param string $name
     * @param string $default
     *
     * @return string Null if non-exists
     */
    public final function getCookie($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * ��ȡ�������ʱ���.
     *
     * ��ȷ���뼶
     *
     * ���Ҫ����ȷ�ģ���Ҫ��php5.4.0+��REQUEST_TIME_FLOAT
     *
     * @return int Returns the current time measured in the number of seconds since the Unix Epoch
     */
    public final function getRequestTime()
    {
        return $this->_env->getRequestTime();
    }

    /**
     * Զ���û���IP��ַ.
     *
     * X-Forwarded-For: client1, proxy1, proxy2
     *
     * <p>
     * Since it is easy to forge an X-Forwarded-For field the given information should be used with care.
     * The last IP address is always the IP address that connects to the last proxy, which means it
     * is the most reliable source of information. X-Forwarded-For data can be used in a forward or
     * reverse proxy scenario.
     * </p>
     *
     * <p>
     * hacker����ʵIP�ǣ� 200.200.200.200
     * ������ͨ��X-Forwarded-Foα��ɣ�100.100.100.100
     *
     * ��ô��php���õ���X-Forwarded-For��ֵӦ���ǣ�
     * X-Forwarded-For: 100.100.100.100, 200.200.200.200
     *
     * ����200.200.200.200��haproxy��ӵ�
     * </p>
     *
     * @param bool $antiFake �Ƿ�򿪷�α�칦��? �򿪺�����û�ͨ���������������ֻ���õ������������ip
     * @param string $default
     *
     * @return string IP address of remote user
     */
    public function getRemoteIp($antiFake = false, $default = '0.0.0.0')
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key)
        {
            if (empty($this->_env[$key]))
            {
                continue;
            }

            $ips = explode(',', $this->_env[$key]);
            if ($antiFake)
            {
                $ips = array_reverse($ips);
            }

            $ip = trim($ips[0]);
            if (false != ip2long($ip) && long2ip(ip2long($ip) === $ip))
            {
                return $ip;
            }
        }

        return $default;
    }

    /**
     * Զ���û���IP��ַ.
     *
     * Alias of getRemoteIp().
     *
     * @param bool $antiFake �Ƿ�򿪷�α�칦��? �򿪺�����û�ͨ���������������ֻ���õ������������ip
     * @param string $default
     *
     * @return string IP address of remote user
     */
    public final function getRemoteAddr($antiFake = false, $default = '0.0.0.0')
    {
        return $this->getRemoteIp($antiFake, $default);
    }

    /**
     *
     * @return string e,g http://www.baidu.com/?q=foo
     */
    public final function getReferer()
    {
        return $this->_env->getHttpHeader('HTTP_REFERER');
    }

    /**
     * ȡ��refer�����������Ϣ.
     *
     * @return string e,g. www.baidu.com
     */
    public final function getRefererHost()
    {
        return KBiz_Util_Uri::getHost($this->getReferer());
    }

    /**
     * ��ǰִ�еĽű�.
     *
     * @return string
     */
    public final function getScriptName()
    {
        return $this->_env->getScriptName();
    }

    /**
     * User Agent.
     *
     * @param bool $lowercase Defaults false
     *
     * @return string
     */
    public final function getUserAgent($lowercase = false)
    {
        $ua = trim($this->_env->getHttpHeader('HTTP_USER_AGENT'));
        if ($lowercase)
        {
            $ua = strtolower($ua);
        }

        return $ua;
    }

    /**
     * ��ǰ���ʵ�web������.
     *
     * ���صĶ���Сд��ĸ����ʹ�û��������ʣ�http://WWW.KAIXIN001.COM
     *
     * @return string i,e www.kaixin001.com
     */
    public final function getHost()
    {
        if (isset($this->_env['HTTP_HOST']))
        {
            if (strpos($this->_env['HTTP_HOST'], ':') !== false)
            {
                // HTTP_HOSTͷ������˶˿ں�
                $hostParts = explode(':', $this->_env['HTTP_HOST']);
                return strtolower($hostParts[0]);
            }
            return strtolower($this->_env['HTTP_HOST']);
        }
        else
        {
            return strtolower($this->_env['SERVER_NAME']);
        }

    }

    /**
     * ��ǰ���ʵ�web�������ļ��.
     *
     * @return string  e,g www or music if www.kaixin001.com
     */
    public final function getSimplifiedHost()
    {
        $host = $this->getHost();
        return substr($host, 0, strpos($host, '.'));
    }

    /**
     * ��ǰ���ʵ�web������.
     *
     * Alias of getHost().
     *
     * @return string
     */
    public final function getServerName()
    {
        return $this->getHost();
    }

    /**
     * �������Ӷ˿ںţ��м���:�ָ�.
     *
     * @return string i,e www.kaixin001.com:80
     */
    public function getHostWithPort()
    {
        return $this->getHost() . ':' . $this->getPort();
    }

    /**
     *
     * @return string 'http' �� 'https'
     */
    public final function getScheme()
    {
        return $this->_env->getUrlScheme();
    }

    /**
     * @param DHttp_Cookie $cookie
     */
    protected function _setCookie(DHttp_Cookie $cookie)
    {
        DHttp_ContextUtil::getResponse()->setCookie($cookie);
    }

    /**
     * Session across requests.
     *
     * ��session��php session�����޹أ���ȫ�������Լ�ʵ�ֵ�.
     * ��ʹ�����ǿ�����ʹ�ñ�������һ����ʵ�ֿ������session����
     *
     * @param bool $autocreate Auto create if non-exists?
     * @param string $memcacheGroup Defaults 'plat'
     *
     * @return DHttp_Session Null if not exists and don't want to autocreate
     */
    public final function getSession($autocreate = true, $memcacheGroup = 'plat')
    {
        if (!is_null($this->_session))
        {
            return $this->_session;
        }

        $sessionId = $this->_getSessionId($autocreate);
        if (is_null($sessionId))
        {
            return null;
        }

        $this->_session = new DHttp_Session($sessionId, CMemCacheEx::getInstance($memcacheGroup));
        return $this->_session;
    }

    /**
     * @param bool $autocreate
     *
     * @return string  Null if session not exists and don't want to autocreate
     */
    private function _getSessionId($autocreate)
    {
        $sessionId = $this->getCookie(self::SESSIONID_KEY, null);
        if (!is_null($sessionId))
        {
            return $sessionId;
        }

        // session not created yet
        if (!$autocreate)
        {
            return null;
        }

        $sessionId = md5(uniqid(mt_rand(), TRUE));
        $this->_setCookie(
            new DHttp_Cookie(
                self::SESSIONID_KEY,
                $sessionId,
                0,
                '/',
                COMMON_HOST,
                $this->isSecure(),
                true
            )
        );

        return $sessionId;
    }

}
