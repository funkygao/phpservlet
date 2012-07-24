<?php
/**
 * ����Ļ�����Ϣ.
 *
 * �� $_SERVER �ķ�װ
 *
 * ֻ��{@link DHttp_Request}��ʹ���ң�Ӧ�ó�������ң�
 *
 * ��һ�������ڣ��ǵ���
 *
 * $_SERVER �Ǹ�array���������header, path, script name����Ϣ�������������web server
 * ���ݹ����ģ�����ͬ��web server���ܴ��ݵ�item��ͬ���е�item�����ڲ���web server�ﲻ��
 * ������ѭ���� {@link http://www.faqs.org/rfcs/rfc3875.html} CGI/1.1 ��׼
 *
 * ����ȥ����ͷ������
 * <ul>
 * <li>SCRIPT_FILENAME</li>
 * <li>GATEWAY_INTERFACE</li>
 * <li>SERVER_ADDR</li>
 * <li>SERVER_SOFTWARE</li>
 * </ul>
 *
 * Ϊʲô���ڴ�����ֱ��ʹ�� $_SERVER����Ҫʹ�ñ���?
 * <ul>
 * <li>���ڽ��е�Ԫ����</li>
 * <li>��$_SERVER���key magic number��װ����</li>
 * <li>�Ѳ�����Ϣ���أ�����SERVER_SOFTWARE</li>
 * <li>���������ض���ͷ��Ϣ</li>
 * <li>��������</li>
 * <li>�����÷���ʵ�֣������</li>
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
     * ��ǰ������������������.
     *
     * Array of key/value pairs
     *
     * @var array [{x: y}, ...]
     */
    protected $_properties;

    /**
     * For singleton.
     *
     * ����Ҳ���ǵ�����������Ҫphp 5.3+
     * ��Ϊget_called_class�������
     *
     * @var DHttp_Env
     */
    protected static $_instance;

    /**
     * ˽�еĹ�����.
     *
     * @param array $settings Map
     */
    private function __construct($settings = null)
    {
        if ($settings)
        {
            // �û�ָ����������
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
                $this->_properties[$key] = $value; // TODO Ū����Сд�����ֵ�
            }
        }

        $this->_addExtraEnv();
    }

    // ����ô�ɣ���Ԫ����ʱ�������Ϊ$_SERVER���ǿյģ�Undefined index error
    private function _safeGetServerVariable($item, $default)
    {
        return isset($_SERVER[$item]) ? $_SERVER[$item] : $default;
    }

    /**
     * �������������ӵĻ�����Ϣ.
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
     * ���ڵ�Ԫ���ԣ����ֹ�����HTTP����.
     *
     * @param array $userSettings Map��array of key/value pairs
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
     * �����ڿ�����HAProxy�Ĵ��ڣ��õ�ַ��Զ��HAProxy��ip��ַ
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
     * ȡ��Զ���û��Ķ˿ں�.
     *
     * ����������������HAProxy���棬��ֵ����HAProxy�Ĵ���˿ںţ�����Զ���û��Ķ˿ں�
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
     * @todo ��Ҫ������һ�������ڶ��php�ļ�֮���л��ĳ��������翪�������������
     */
    public final function getScriptName()
    {
        return $this->_properties['SCRIPT_NAME'];
    }

    /**
     *
     * php��$_SERVER['PHP_SELF'] �����վ����urldecode
     *
     * /home/index.php/%22%3E%3Cscript%3Ealert(��xss��)%3C/script%3E%3Cfoo
     * �������˵�$_SERVER['PHP_SELF']�õ����ǣ�
     * /home/index.php/"><script>alert(��xss��)</script><foo
     * ��Ͳ����˰�ȫ©��: <form action="<?php echo $_SERVER['PHP_SELF']; ?>">
     *
     * ��ˣ���������ͨ��{@link htmlentities}�����©��
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
     * URL?����Ĳ�ѯ�ַ���.
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
     * ��ȷ�ȵ���
     *
     * ���Ҫ����ȷ�ģ���Ҫ��php5.4.0+��REQUEST_TIME_FLOAT
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
     * ��POSTʱ����ֱ�����.
     *
     * @return string Null if not set
     */
    public final function getContentType()
    {
        return $this->_properties['CONTENT_TYPE'];
    }

    /**
     * ��POSTʱ����ֱ�����.
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
     * ��apache������: ServerAdmin=you@example.com
     *
     * @return string
     */
    public final function getServerAdmin()
    {
        return $this->_properties['SERVER_ADMIN'];
    }

    /**
     * ʹ�õ�HTTPЭ��Ͱ汾��Ϣ.
     *
     * ʵ�������������������ָ���ģ���Ȼ������������SERVER
     *
     * @return string e.g HTTP/1.1
     */
    public final function getServerProtocol()
    {
        return $this->_properties['SERVER_PROTOCOL'];
    }

    /**
     * ר�Ż�ȡHTTP_��ͷ��������Ϣ�ķ���.
     *
     * ��Щ����HTTP request headers���ǲ��ñ����ε�.
     *
     * @param string $header �����ִ�Сд
     *
     * �����Ч����һ����:
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
            // header����HTTP_��ͷ����ô�Ͳ���
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
