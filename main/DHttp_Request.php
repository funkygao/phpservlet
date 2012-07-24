<?php
/**
 * HTTP Request对象.
 *
 * 仿造java的ServletRequest，目的是要实现类似java servlet和python WSGI那样
 * 的web应用规范，同时也为真正的RESTful OpenAPI做准备
 *
 * 对 $_SERVER, $_GET, $_POST, $_COOKIES的封装；同时，内部实现session机制
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
 * @todo file upload, cookie encrypted, $this->_request = $_GET为什么不行?, isUrlRewritten
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
     * 可以在url上加?_ajax=1来特殊标识这是个AJAX请求.
     *
     * 这是怕有特殊浏览器在发送AJAX请求时，没有把必要的头发过来
     * 有了这个，我们就能确保任何浏览器，我们都能准确判断是否AJAX请求
     *
     * 目前，该机制还没有被使用
     */
    const AJAX_REQUEST_KX = '_kxajax';

    /**
     * 用于session的cookie key.
     *
     */
    const SESSIONID_KEY = '_kxsess_';

    /**
     * The request data(params).
     *
     * 包括$_GET 和 $_POST
     *
     * @var array Map
     */
    private $_request;

    /**
     * 环境变量的容器.
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
     * 完全in-house implementation，与php的session无关。
     * 因此，使用时不必session_start()，而session_XXX()方法也都不能使用
     *
     * @var DHttp_Session
     */
    private $_session;

    /**
     * An array for controller and view variables exchange area.
     *
     * 用于在一个请求内保存中间状态值，使得各个协作类之间可以共享一些状态值
     *
     * @var array
     */
    private $_attributes = array();

    /**
     *
     * @param DHttp_Env $env
     * @param bool $paramAsInstanceAttribute 把请求参数作为本对象的成员属性? Default false
     */
    public function __construct($env = null, $paramAsInstanceAttribute = false)
    {
        $this->_env = is_null($env) ? DHttp_Env::getInstance() : $env;
        $this->_userAgent = new DHttp_UserAgent($this->getUserAgent());

        // 构造请求的GET、POST数据
        // $this->_request = $_GET;
        $this->_request = &$_GET;
        if ($this->isPostMethod())
        {
            // POST的优先级 > GET
            $this->_request = array_merge($this->_request, $_POST);
        }

        /*
         * 为了能像对象成员一样访问这些访问参数
         *
         * 例如：http://www.kaixin001.com/foo.php?bar=spam
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
     * 把参数设置为本对象的成员属性.
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
     * 这些都是HTTP request headers，是不该被信任的.
     *
     * @param string $header
     *
     * @return string 没有定义则返回''
     */
    public final function header($header)
    {
        return $this->_env->getHttpHeader($header);
    }

    /**
     * Return the value of all HTTP headers.
     *
     * 这些都是HTTP request headers，是不该被信任的.
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
     * 只能用于一个请求内(一个进程)，如果要跨请求，必须使用{@link DHttp_Session}
     *
     * @param string $name
     * @param mixed $value 可以是对象
     */
    public final function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     *
     * 只能用于一个请求内(一个进程)，如果要跨请求，必须使用{@link DHttp_Session}
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
     * 只能用于一个请求内(一个进程)，如果要跨请求，必须使用{@link DHttp_Session}
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
     * 获取全部的请求参数.
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
     * 如果没有，返回''
     *
     * @return string
     */
    public final function getQueryString()
    {
        return $this->_env->getQueryString();
    }

    /**
     * 获取某个int型的用户输入数据.
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
     * 请求里是否传入了某个参数?
     *
     * 不关心值是什么
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
     * 没有该参数，或者empty($thisParam)，都返回true
     *
     * @param string $name 请求参数，包括POST
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
     * 在apache里配置: ServerAdmin=you@example.com
     *
     * @return string e.g webmaster@corp.kaixin001.com
     */
    public final function getServerAdmin()
    {
        return $this->_env->getServerAdmin();
    }

    /**
     * 取得原始的POST数据.
     *
     * 读取没有处理过的POST数据
     *
     * 相较于$HTTP_RAW_POST_DATA而言，它给内存带来的压力较小，并且不需要特殊的php.ini设置
     *
     * 限制条件：
     * <ul>
     * <li>readable one time only</li>
     * <li>not available for mutipart/form-data requests</li>
     * </ul>
     *
     * @return string 空的时候返回''
     */
    public final function getRawPostBody()
    {
        return $this->_env->getRawPostBody();
    }

    /**
     * Get the HTTP method of this request.
     *
     * @param bool $lowercase 是否把结果转换成小写
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
        // 先判断我们自己的特定ajax的请求参数
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
     * iPad不算
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
     * 当前请求是否来自iPad?
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
     * 获取android客户端的版本号.
     *
     * 只返回major.minor version number
     * e.g.
     * <p>
     * 如果版本是2.1-update1，则返回2.1
     * 如果版本是2.2.1，则返回2.2
     * </p>
     *
     * @return string False if not android client request
     */
    public final function getAndroidVersion()
    {
        return $this->_userAgent->getAndroidVersion();
    }

    /**
     * 当前请求是否来自iPhone?
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
     * 目前支持：google, msn, yahoo, etc
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
     * 根据HTTP_ACCEPT_LANGUAGE取得当前浏览器的语言.
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
     * 获取当前请求的浏览器支持的所有语言.
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
     * 使用的HTTP协议和版本信息.
     *
     * 实际上是浏览器在请求里指定的，虽然它的名字里有SERVER:
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
     * 实际请求的端口号.
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
     * 获取请求发起的时间戳.
     *
     * 精确到秒级
     *
     * 如果要更精确的，需要等php5.4.0+，REQUEST_TIME_FLOAT
     *
     * @return int Returns the current time measured in the number of seconds since the Unix Epoch
     */
    public final function getRequestTime()
    {
        return $this->_env->getRequestTime();
    }

    /**
     * 远程用户的IP地址.
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
     * hacker的真实IP是： 200.200.200.200
     * 现在想通过X-Forwarded-Fo伪造成：100.100.100.100
     *
     * 那么，php端拿到的X-Forwarded-For的值应该是：
     * X-Forwarded-For: 100.100.100.100, 200.200.200.200
     *
     * 其中200.200.200.200是haproxy添加的
     * </p>
     *
     * @param bool $antiFake 是否打开防伪造功能? 打开后，如果用户通过代理服务器，则只能拿掉代理服务器的ip
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
     * 远程用户的IP地址.
     *
     * Alias of getRemoteIp().
     *
     * @param bool $antiFake 是否打开防伪造功能? 打开后，如果用户通过代理服务器，则只能拿掉代理服务器的ip
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
     * 取得refer里的主机名信息.
     *
     * @return string e,g. www.baidu.com
     */
    public final function getRefererHost()
    {
        return KBiz_Util_Uri::getHost($this->getReferer());
    }

    /**
     * 当前执行的脚本.
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
     * 当前访问的web主机名.
     *
     * 返回的都是小写字母，即使用户这样访问：http://WWW.KAIXIN001.COM
     *
     * @return string i,e www.kaixin001.com
     */
    public final function getHost()
    {
        if (isset($this->_env['HTTP_HOST']))
        {
            if (strpos($this->_env['HTTP_HOST'], ':') !== false)
            {
                // HTTP_HOST头里包含了端口号
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
     * 当前访问的web主机名的简称.
     *
     * @return string  e,g www or music if www.kaixin001.com
     */
    public final function getSimplifiedHost()
    {
        $host = $this->getHost();
        return substr($host, 0, strpos($host, '.'));
    }

    /**
     * 当前访问的web主机名.
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
     * 主机名加端口号，中间用:分隔.
     *
     * @return string i,e www.kaixin001.com:80
     */
    public function getHostWithPort()
    {
        return $this->getHost() . ':' . $this->getPort();
    }

    /**
     *
     * @return string 'http' 或 'https'
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
     * 该session与php session机制无关，完全是我们自己实现的.
     * 它使得我们可以像使用本地数组一样来实现跨请求的session功能
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
