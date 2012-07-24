<?php
/**
 * HTTP Response.
 *
 * ��HTTP response�ķ�װ�����php�����setcookie()��header()�Ⱥ���
 *
 * �������ɣ�[int status, array headers, string body]��������Ҫ���
 * �����ݣ�����������IO���
 *
 * <pre>
 *
 *                        -- DHttp_Header(response header)
 *     DHttp_Response ---|
 *                        -- DHttp_Cookie
 *
 * </pre>
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

class DHttp_Response implements DHttp_Constant, ArrayAccess, Countable, IteratorAggregate
{

    /**
     * HTTP response codes and messages.
     *
     * @var array
     */
    protected static $_messages = array(
        // Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',

        // Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',

        // Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',

        // Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',

        // Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
    );

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $_status;

    /**
     * List of HTTP response headers.
     *
     * ����������һ������
     *
     * @var DHttp_Header
     */
    protected $_header;

    /**
     * HTTP response body.
     *
     * @var string
     */
    protected $_body;

    /**
     * Length of HTTP response body.
     *
     * @var int
     */
    protected $_length;

    /**
     * @var array
     */
    private $_cookies;

    /**
     * @param string $body
     * @param int $status Status code
     * @param array $headers
     */
    public function __construct($body = '', $status = self::SC_OK, $headers = array())
    {
        $this->_status = (int)$status;

        $this->_header = new DHttp_Header(
            array(
                self::HDR_CONTENT_TYPE => self::DEFAULT_CONTENT_TYPE . '; charset=' . self::DEFAULT_CHARSET,
            )
        );
        foreach($headers as $name => $value)
        {
            $this->_header->merge($name, $value);
        }

        $this->body($body);
    }

    /**
     * Get or set http status code.
     *
     * @param null|int $status HTTP status code, Ϊ�ձ�ʾȡ״̬��, �ǿձ�ʾ����״̬��
     *
     * @return int|DHttp_Response
     * @throws InvalidArgumentException
     */
    public final function status($status = null)
    {
        if (!is_null($status))
        {
            $this->_status = (int)$status;
            if ($this->_status > self::SC_HTTP_VERSION_NOT_SUPPORTED
                || $this->_status < self::SC_OK)
            {
                throw new InvalidArgumentException('Invalid status code');
            }
            return $this;
        }

        return $this->_status;
    }

    /**
     * ȡ��Ҫ����������͵�status����ͷ����.
     *
     * @param string $sapiName {@link PHP_SAPI}
     * @param string $protocol e.g HTTP/1.1
     * @param int $code Status code
     *
     * @return string
     */
    public final function renderStatus($sapiName, $protocol, $code)
    {
        $msg = self::$_messages[$code];
        if (strpos($sapiName, 'cgi') === 0)
        {
            return 'Status: ' . $msg; // e.g Status: 200 OK
        }
        else
        {
            /*
             * PHP_SAPI
             *
             * ������Ŀǰ�ǣ�apache2handler
             * �������php-fpm����ֵ���ǣ�fpm-fcgi
             */
            return "$protocol $msg"; // e.g HTTP/1.1 200 OK
        }

    }

    /**
     * @param int $statusCode
     *
     * @return string
     */
    public final function getStatusMessage($statusCode)
    {
        $msg = self::$_messages[$statusCode];
        return substr($msg, 4); // ȥ��3λ��״̬���1���ո�
    }

    /**
     * Get or set header.
     *
     * �Ƕ�php header()���滻�����������Ͳ��������Ĵ����ˣ�
     * PHP Warning: Cannot modify header information - headers already sent by ...
     *
     * @param string $name
     * @param string $value Null if getter, else setter
     * @param bool $replace
     *
     * @return string|DHttp_Response
     */
    public final function header($name, $value = null, $replace = true)
    {
        if (!is_null($value))
        {
            $this->_header->merge($name, $value, $replace);

            return $this;
        }

        return $this->_header[$name];
    }

    /**
     * Get all the headers.
     *
     * @return DHttp_Header
     */
    public final function headers()
    {
        return $this->_header;
    }

    /**
     *
     * @return array Array of string
     */
    public final function headersArray()
    {
        return $this->_header->toArray();
    }

    /**
     * Get and set body.
     *
     * @param null|string $body
     *
     * @return DHttp_Response|string
     */
    public final function body($body = null)
    {
        if (!is_null($body))
        {
            $this->write($body, true);

            return $this;
        }

        return $this->_body;
    }

    /**
     * Get or set content(body) length.
     *
     * @param null|int $length
     *
     * @return DHttp_Response|int
     */
    public final function length($length = null)
    {
        if (!is_null($length))
        {
            $this->_length = (int)$length;
            if ($this->_length < 0)
            {
                $this->_length = 0;
            }

            return $this;
        }

        return $this->_length;
    }

    /**
     * @param string $name
     * @param string $value
     * @return DHttp_Response|string
     */
    private function _getOrSetHeader($name, $value)
    {
        if (!is_null($value))
        {
            // set
            return $this->header($name, $value);
        }
        else
        {
            // get
            return $this->_header[$name];
        }
    }

    /**
     * Get or set content type header.
     *
     * ����charsetһ��
     *
     * @param string $charset
     * @param string $contentType
     *
     * @return DHttp_Response|string
     */
    public final function contentType($contentType = null, $charset = null)
    {
        if (!is_null($charset) || !is_null($contentType))
        {
            // setter for contentType or charset
            if (is_null($contentType))
            {
                $contentType = self::DEFAULT_CONTENT_TYPE;
            }
            if (is_null($charset))
            {
                $charset = self::DEFAULT_CHARSET;
            }
            $charset = '; charset=' . $charset;

            $contentType = $contentType . $charset;
        }

        return $this->_getOrSetHeader(self::HDR_CONTENT_TYPE, $contentType);
    }

    /**
     * Append to HTTP response body.
     *
     * û��ʹ��append()���ƣ�����Ϊ����linux fs�����ƣ����԰�body�����stream
     * shell$ man 2 write
     *
     * @param string $body
     * @param bool $replace
     *
     * @return DHttp_Response
     */
    public final function write($body, $replace = false)
    {
        if (!is_string($body))
        {
            throw new InvalidArgumentException('DHttp_Response::write(), body must be string');
        }

        if ($replace)
        {
            $this->_body = (string)$body;
        }
        else
        {
            $this->_body .= (string)$body;
        }

        // ����Content-Length
        $this->length(strlen($this->_body));

        return $this;
    }

    /**
     * Finalize.
     *
     * This prepares this response and returns an array
     * of [status, headers, body].
     *
     * This array is passed to outer middleware if available
     * or directly to the app run method.
     *
     * @return array [int status, DHttp_Header header, string body]
     */
    public final function finalize()
    {
        if (in_array($this->_status, array(self::SC_NO_CONTENT, self::SC_NOT_MODIFIED)))
        {
            unset($this[self::HDR_CONTENT_TYPE], $this[self::HDR_CONTENT_LENGTH]);
            return array($this->_status, $this->_header, '');
        }
        else
        {
            return array($this->_status, $this->_header, $this->_body);
        }
    }

    /**
     * Set cookie.
     *
     * û��ʹ��php�Դ���setcookie()�������ֹ�����HTTP 'Set-Cookie' header
     *
     * �����һ��response����2����ͬname��cookie������ĸ���ǰ��cookie
     *
     * �����޸�$this->_headerֵ
     * �������������м��{@link DHttp_Middleware}�ڷ��ظ�browser֮ǰ���в���
     *
     * @param DHttp_Cookie $cookie
     */
    public final function setCookie(DHttp_Cookie $cookie)
    {
        $this->_cookies[$cookie->getName()] = $cookie->renderHeader();
        $this->header(self::HDR_SET_COOKIE, implode("\n", array_values($this->_cookies)));
    }

    /**
     * @param string $name
     */
    public final function deleteCookie($name)
    {
        $cookie = DHttp_Cookie::create($name, '');
        $cookie->expireAfter(-100);

        $this->setCookie($cookie);
    }

    /**
     *
     * @return bool
     */
    public final function isEmpty()
    {
        return in_array(
            $this->_status,
            array(
                self::SC_CREATED,
                self::SC_NO_CONTENT,
                self::SC_NOT_MODIFIED
            )
        );
    }

    /**
     *
     * @return bool
     */
    public final function isOk()
    {
        return self::SC_OK === $this->_status;
    }

    /**
     *
     * @return bool
     */
    public final function isForbidden()
    {
        return self::SC_FORBIDDEN === $this->_status;
    }

    /**
     *
     * @return bool
     */
    public final function isNotFound()
    {
        return self::SC_NOT_FOUND === $this->_status;
    }

    /**
     *
     * @return bool
     */
    public final function isClientError()
    {
        return self::SC_BAD_REQUEST <= $this->_status && $this->_status < self::SC_INTERNAL_SERVER_ERROR;
    }

    /**
     *
     * @return bool
     */
    public final function isServerError()
    {
        return self::SC_INTERNAL_SERVER_ERROR <= $this->_status && $this->_status <= self::SC_HTTP_VERSION_NOT_SUPPORTED;
    }

    /**
     * Redirect to a url.
     *
     * �Զ��ж��Ƿ�ò���header�������window.location
     *
     * @param string $url
     * @param bool $useJs
     *
     * @todo security check against XSS
     * @deprecated
     */
    public function redirect($url, $useJs = false)
    {
        // if (headers_sent()) Ӧ�õ�include virtual��ʹ�ø÷�������ֵ����
        if ($useJs)
        {
            echo '<script lanuage="JavaScript"> window.location = "' . $url . '"; </script>';
        }
        else
        {
            header('Location: ' . $url);
        }

        exit;
    }

    /**
     * @param string $url
     * @param int $status
     *
     * @todo �ȱ�������ɺ�rename to redirect
     */
    public final function redirectFinal($url, $status = self::SC_MOVED_TEMPORARILY)
    {
        $req = DHttp_ContextUtil::getRequest();
        if ($req->getScriptName() != KBiz_Util_Uri::getPath($url))
        {
            // �ض����Ŀ�겻�ǵ�ǰҳ�棬��������������ѭ�����ᱻ��������
            $this->_status = $status;
            $this->header('Location', $url);
        }
    }

    /**
     * ȡ�����кϷ���HTTP status code.
     *
     * @return array List, [200, 304, ...]
     */
    public final function getStatusCodes()
    {
        return array_keys(self::$_messages);
    }

    /**
     * The response has any redirects?
     *
     * @return bool
     */
    public final function hasRedirects()
    {
        return isset($this->_header[self::HDR_LOCATION]);
    }

    /**
     * ����Cache-Control����Ӧͷ����ֹ�����ʹ�û���.
     *
     */
    public function disableBrowserCache()
    {
        $this->header(self::HDR_EXPIRES, 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->header(self::HDR_LAST_MODIFIED, gmdate("D, d M Y H:i:s") . ' GMT');
        $this->header(self::HDR_CACHE_CONTROL, 'no-store, no-cache, must-revalidate');
        $this->header(self::HDR_CACHE_CONTROL, 'post-check=0, pre-check=0', false);
        $this->header(self::HDR_PRAGMA, 'no-cache');
    }

    /**
     * Reset response header and body completely.
     *
     * ��HTTP status codeû�б�reset
     */
    public function reset()
    {
        $this->_header->reset();

        $this->body('');
    }

    public function offsetExists($offset)
    {
        return isset($this->_header[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_header[$offset]) ? $this->_header[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->_header[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_header[$offset]);
    }

    public function count()
    {
        return count($this->_header);
    }

    /**
     * Get Iterator.
     *
     * @return DHttp_Header
     */
    public function getIterator()
    {
        return $this->_header;
    }

}
