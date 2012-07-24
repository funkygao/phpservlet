<?php
/**
 * 请求的环境信息.
 *
 * 对 $_SERVER 的封装
 *
 * 只有{@link DHttp_Request}会使用我，应用程序别理我！
 *
 * 在一个请求内，是单例
 *
 * $_SERVER 是个array，里面包括header, path, script name等信息，里面的内容是web server
 * 传递过来的，但不同的web server可能传递的item不同，有的item可能在部分web server里不传
 * 它们遵循的是 {@link http://www.faqs.org/rfcs/rfc3875.html} CGI/1.1 标准
 *
 * 故意去掉的头，包括
 * <ul>
 * <li>SCRIPT_FILENAME</li>
 * <li>GATEWAY_INTERFACE</li>
 * <li>SERVER_ADDR</li>
 * <li>SERVER_SOFTWARE</li>
 * </ul>
 *
 * 为什么不在代码里直接使用 $_SERVER，而要使用本类?
 * <ul>
 * <li>便于进行单元测试</li>
 * <li>把$_SERVER里的key magic number封装起来</li>
 * <li>把部分信息隐藏，例如SERVER_SOFTWARE</li>
 * <li>增加我们特定的头信息</li>
 * <li>便于扩充</li>
 * <li>由于用方法实现，更灵活</li>
 * </ul>
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * @see http://www.php.net/manual/en/reserved.variables.server.php
 *
 * vim: set sw=4 ts=4 et:
 */
class DHttp_Env implements ArrayAccess, IteratorAggregate, Countable
{

    /**
     * 当前环境变量的所有内容.
     *
     * Array of key/value pairs
     *
     * @var array [{x: y}, ...]
     */
    protected $_properties;

    /**
     * For singleton.
     *
     * 子类也会是单例，不过需要php 5.3+
     * 因为get_called_class这个方法
     *
     * @var DHttp_Env
     */
    protected static $_instance;

    /**
     * 私有的构造器.
     *
     * @param array $settings Map
     */
    private function __construct($settings = null)
    {
        if ($settings)
        {
            // 用户指定属性内容
            $this->_properties = $settings;
            $this->_addExtraEnv();
            return;
        }

        $this->_properties = array();
        $this->_properties['REQUEST_METHOD'] = $this->_safeGetServerVariable('REQUEST_METHOD', null);
        $this->_properties['SERVER_NAME'] = $this->_safeGetServerVariable('SERVER_NAME', '');
        $this->_properties['SERVER_PORT'] = (int)$this->_safeGetServerVariable('SERVER_PORT', 0);
        $this->_properties['SERVER_ADMIN'] = $this->_safeGetServerVariable('SERVER_ADMIN', '');
        $this->_properties['DOCUMENT_ROOT'] = $this->_safeGetServerVariable('DOCUMENT_ROOT', '');
        $this->_properties['SERVER_PROTOCOL'] = $this->_safeGetServerVariable('SERVER_PROTOCOL', 'HTTP/1.1');
        $this->_properties['REMOTE_ADDR'] = $this->_safeGetServerVariable('REMOTE_ADDR', '');
        $this->_properties['REMOTE_PORT'] = (int)$this->_safeGetServerVariable('REMOTE_PORT', 0);
        $this->_properties['REQUEST_URI'] = $this->_safeGetServerVariable('REQUEST_URI', '');
        $this->_properties['SCRIPT_NAME'] = $this->_safeGetServerVariable('SCRIPT_NAME', '');
        $this->_properties['PHP_SELF'] = $this->_safeGetServerVariable('PHP_SELF', '');
        $this->_properties['QUERY_STRING'] = $this->_safeGetServerVariable('QUERY_STRING', '');
        $this->_properties['REQUEST_TIME'] = (int)$this->_safeGetServerVariable('REQUEST_TIME', 0);
        $this->_properties['HTTPS'] = $this->_safeGetServerVariable('HTTPS', null);
        $this->_properties['CONTENT_TYPE'] = $this->_safeGetServerVariable('CONTENT_TYPE', null);
        $this->_properties['CONTENT_LENGTH'] = $this->_safeGetServerVariable('CONTENT_LENGTH', null);

        // HTTP request headers
        $specialHeaders = array(
            'PHP_AUTH_USER',
            'PHP_AUTH_PW',
            'PHP_AUTH_DIGEST',
            'AUTH_TYPE'
        );
        foreach ($_SERVER as $key => $value)
        {
            if (strpos($key, 'HTTP_') === 0
                || strpos($key, 'X_') === 0
                || in_array($key, $specialHeaders))
            {
                $value = is_string($value) ? trim($value) : $value;
                $this->_properties[$key] = $value; // TODO 弄个大小写不区分的
            }
        }

        $this->_addExtraEnv();
    }

    // 不这么干，单元测试时会出错，因为$_SERVER里是空的，Undefined index error
    private function _safeGetServerVariable($item, $default)
    {
        return isset($_SERVER[$item]) ? $_SERVER[$item] : $default;
    }

    /**
     * 开心网额外增加的环境信息.
     *
     */
    private function _addExtraEnv()
    {
        // Customized attribute
        $this->_properties['URL_SCHEME'] =
            (empty($this->_properties['HTTPS']) || $this->_properties['HTTPS'] === 'off') ?
            'http' : 'https';

        $this->_properties['kx.version'] = '1.0a';
        $this->_properties['kx.multithread'] = false;
        $this->_properties['kx.multiprocess'] = true;
        $this->_properties['kx.run_once'] = true;
    }

    /**
     * Get the env instance(singleton).
     *
     * @param bool $refresh
     *
     * @return DHttp_Env
     */
    public final static function getInstance($refresh = false)
    {
        if (is_null(self::$_instance) || $refresh)
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Mock.
     *
     * 用于单元测试，来手工构造HTTP请求.
     *
     * @param array $userSettings Map，array of key/value pairs
     *
     * @return DHttp_Env
     */
    public static function mock($userSettings = array())
    {
        self::$_instance = new self(
            array_merge(
                array(
                    'REQUEST_METHOD'          => 'GET',
                    'SCRIPT_NAME'             => '/home/index.php',
                    'QUERY_STRING'            => '',
                    'REMOTE_ADDR'             => '127.0.0.1',
                    'SERVER_NAME'             => 'localhost',
                    'SCRIPT_FILENAME'         => __FILE__,
                    'SERVER_PROTOCOL'         => 'HTTP/1.1',
                    'SERVER_PORT'             => 80,

                    'HTTP_REFERER'            => 'http://www.kaixin001.com/home/?uid=12345',
                    'HTTP_ACCEPT'             => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'HTTP_ACCEPT_ENCODING'    => 'gzip,deflate,sdch',
                    'HTTP_ACCEPT_LANGUAGE'    => 'zh-CN,zh;q=0.8',
                    'HTTP_ACCEPT_CHARSET'     => 'GBK,utf-8;q=0.7,*;q=0.3',
                    'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.52 Safari/536.5',
                    'HTTP_CONNECTION'         => "keep-alive",
                    'HTTP_HOST'               => 'www.kaixin001.com',
                    'HTTP_CACHE_CONTROL'      => "max-age=0",
                ),
                $userSettings
            )
        );

        return self::$_instance;
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
        $rawInput = @file_get_contents('php://input');
        if (!$rawInput)
        {
            $rawInput = '';
        }

        return $rawInput;
    }

    /**
     *
     * e.g. POST
     *
     * @return string
     */
    public final function getMethod()
    {
        return $this->_properties['REQUEST_METHOD'];
    }

    /**
     *
     * e.g. www.kaixin001.com
     *
     * @return string
     */
    public final function getServerName()
    {
        return $this->_properties['SERVER_NAME'];
    }

    /**
     *
     * e.g. 80
     *
     * @return int
     */
    public final function getServerPort()
    {
        return $this->_properties['SERVER_PORT'];
    }

    /**
     * IP of remote user.
     *
     * 但由于开心网HAProxy的存在，该地址永远是HAProxy的ip地址
     *
     * e.g. 123.125.220.50
     *
     * @return string
     */
    public final function getRemoteAddr()
    {
        return $this->_properties['REMOTE_ADDR'];
    }

    /**
     * 取得远程用户的端口号.
     *
     * 不过由于我们是在HAProxy后面，该值都是HAProxy的代理端口号，而非远程用户的端口号
     *
     * @return int e.g. 80
     */
    public final function getRemotePort()
    {
        return $this->_properties['REMOTE_PORT'];
    }

    /**
     *
     * e.g. /!farm/?t=3910
     *
     * e.g. /!farm/index.php?t=3910
     *
     * @return string
     */
    public final function getRequestUri()
    {
        return $this->_properties['REQUEST_URI'];
    }

    /**
     *
     * e.g. /!farm/index.php
     *
     * @return string
     *
     * @todo 需要处理在一个进程内多个php文件之间切换的场景，例如开心网的组件机制
     */
    public final function getScriptName()
    {
        return $this->_properties['SCRIPT_NAME'];
    }

    /**
     *
     * php的$_SERVER['PHP_SELF'] 会对网站进行urldecode
     *
     * /home/index.php/%22%3E%3Cscript%3Ealert(’xss’)%3C/script%3E%3Cfoo
     * 服务器端的$_SERVER['PHP_SELF']拿到的是：
     * /home/index.php/"><script>alert(’xss’)</script><foo
     * 这就产生了安全漏洞: <form action="<?php echo $_SERVER['PHP_SELF']; ?>">
     *
     * 因此，本方法会通过{@link htmlentities}解决该漏洞
     *
     * @return string e.g. /home/index.php
     */
    public final function getPhpSelf()
    {
        return htmlentities($this->_properties['PHP_SELF']);
    }

    /**
     *
     * Without ending slash
     *
     * @return string e.g. /testkx/htdocs
     */
    public final function getDocumentRoot()
    {
        return $this->_properties['DOCUMENT_ROOT'];
    }

    /**
     * URL?后面的查询字符串.
     *
     * e.g. aid=1160&en=farm&url=index.php&t=3910
     *
     * @return string
     */
    public final function getQueryString()
    {
        return $this->_properties['QUERY_STRING'];
    }

    /**
     * Request time measured in the number of seconds since the Unix Epoch.
     *
     * Since php5.1.0
     *
     * e.g 1338862306
     *
     * 精确度到秒
     *
     * 如果要更精确的，需要等php5.4.0+，REQUEST_TIME_FLOAT
     *
     * @return int
     */
    public final function getRequestTime()
    {
        return $this->_properties['REQUEST_TIME'];
    }

    /**
     *
     * e.g. http or https in lowercase
     *
     * @return string
     */
    public final function getUrlScheme()
    {
        return $this->_properties['URL_SCHEME'];
    }

    /**
     * 在POST时会出现本属性.
     *
     * @return string Null if not set
     */
    public final function getContentType()
    {
        return $this->_properties['CONTENT_TYPE'];
    }

    /**
     * 在POST时会出现本属性.
     *
     * @return int
     */
    public final function getContentLength()
    {
        return (int)$this->_properties['CONTENT_LENGTH'];
    }

    /**
     *
     * e.g webmaster@corp.kaixin001.com
     *
     * 在apache里配置: ServerAdmin=you@example.com
     *
     * @return string
     */
    public final function getServerAdmin()
    {
        return $this->_properties['SERVER_ADMIN'];
    }

    /**
     * 使用的HTTP协议和版本信息.
     *
     * 实际上是浏览器在请求里指定的，虽然它的名字里有SERVER
     *
     * @return string e.g HTTP/1.1
     */
    public final function getServerProtocol()
    {
        return $this->_properties['SERVER_PROTOCOL'];
    }

    /**
     * 专门获取HTTP_开头的请求信息的方法.
     *
     * 这些都是HTTP request headers，是不该被信任的.
     *
     * @param string $header 不区分大小写
     *
     * 下面的效果是一样的:
     * <code>
     * $env = DHttp_Env::getInstance();
     * $env->getHttpHeader('HTTP_ACCEPT_ENCODING');
     * $env->getHttpHeader('HTTP-ACCEPT-ENCODING');
     * $env->getHttpHeader('ACCEPT_ENCODING');
     * $env->getHttpHeader('ACCEPT-ENCODING');
     * </code>
     *
     * @return string Null if not set
     */
    public final function getHttpHeader($header)
    {
        $header = strtoupper(str_replace('-', '_', $header));
        if (strpos($header, 'HTTP_') !== 0)
        {
            // header不以HTTP_开头，那么就补上
            $header = 'HTTP_' . $header;
        }

        return isset($this->_properties[$header]) ? $this->_properties[$header] : null;
    }

    public function offsetExists($offset)
    {
        return isset($this->_properties[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_properties[$offset]) ? $this->_properties[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->_properties[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_properties[$offset]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_properties);
    }

    public function count()
    {
        return count($this->_properties);
    }

}
