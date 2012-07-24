<?php
/**
 * 对php cookie的内容和其meta info的封装.
 *
 * 支持chain method
 * <code>
 * $cookie = new DHttp_Cookie('_user', 'anonymouse');
 * $cookie->domain('.kaixin001.com')->expire(time() + 1)->httponly(true)->secure(true);
 * </code>
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_Cookie
{

    /**
     * Cookie name.
     *
     * @var string
     */
    private $_name;

    /**
     * Cookie value.
     *
     * @var string
     */
    private $_value;

    /**
     * This is the server timestamp.
     *
     * Defaults to 0, meaning "until the browser is closed".
     *
     * @var int
     */
    private $_expire;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var string
     */
    private $_domain;

    /**
     * Whether cookie should be sent via secure connection.
     *
     * @var bool
     */
    private $_secure;

    /**
     * Whether the cookie should be accessible only through the HTTP protocol.
     *
     * By setting this property to true, the cookie will not be accessible by scripting languages,
     * such as JavaScript, which can effectly help to reduce identity theft through XSS attacks.
     *
     * This property is only effective for PHP 5.2.0 or above
     *
     * @var bool
     */
    private $_httponly;

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public function __construct($name, $value, $expire = 0, $path = '/',
                                $domain = COMMON_HOST, $secure = false, $httponly = false)
    {
        $this->_name = (string)$name;
        $this->_value = (string)$value;

        $this->_expire = $expire;
        $this->_path = $path;
        $this->_domain = $domain;
        $this->_secure = $secure;
        $this->_httponly = $httponly;
    }

    /**
     * 产生一个cookie对象.
     *
     * 退化的静态工厂方法
     *
     * @param string $name
     * @param string $value
     *
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     *
     * @return DHttp_Cookie
     */
    public static function create($name, $value, $expire = 0, $path = '/',
                                  $domain = COMMON_HOST, $secure = false, $httponly = false)
    {
        return new self($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    private function _getOrSetAttribute($name, $attr)
    {
        $name = '_' . $name;
        if (is_null($attr))
        {
            // getter
            return $this->$name;
        }
        else
        {
            // setter
            $this->$name = $attr;
            return $this;
        }

    }

    /**
     * Get or set domain.
     *
     * @param string $domain
     * @return DHttp_Cookie|string
     */
    public function domain($domain = null)
    {
        return $this->_getOrSetAttribute(__FUNCTION__, $domain);
    }

    /**
     *
     * @param bool $httponly
     * @return DHttp_Cookie|bool
     */
    public function httponly($httponly = null)
    {
        return $this->_getOrSetAttribute(__FUNCTION__, $httponly);
    }

    /**
     *
     * @param string $path
     * @return DHttp_Cookie|string
     */
    public function path($path = null)
    {
        return $this->_getOrSetAttribute(__FUNCTION__, $path);
    }

    /**
     *
     * @param bool $secure
     * @return DHttp_Cookie|bool
     */
    public function secure($secure = null)
    {
        return $this->_getOrSetAttribute(__FUNCTION__, $secure);
    }

    /**
     *
     * @param int $expire This is a Unix timestamp so is in number of seconds since the epoch.
     * @return DHttp_Cookie|int
     */
    public function expire($expire = null)
    {
        return $this->_getOrSetAttribute(__FUNCTION__, $expire);
    }

    /**
     * 从现在起多少秒后expire.
     *
     * @param int $seconds
     * @return DHttp_Cookie
     */
    public function expireAfter($seconds)
    {
        $this->_expire = time() + $seconds;

        return $this;
    }

    /**
     *
     * @return string 可以给Set-Cookie header使用的expire字符串
     */
    private function _renderExpire()
    {
        return $this->_expire ? gmdate('D, d-M-Y H:i:s e', $this->_expire) : '';
    }

    /**
     * 取得直接输出给HTTP header的cookie头内容.
     *
     * 头内容是被{@link urlencode}过的
     *
     * @return string e.g '_kxsess_=4b87c37e8834a6a83fe828f67f395ca0; domain=.vm142.kaixin009.com; path=/; HttpOnly'
     */
    public function renderHeader()
    {
        $headerValue = '';
        $headerValue .= '; domain=' . $this->_domain;
        $headerValue .= '; path=' . $this->_path;
        if ($this->_expire)
        {
            $headerValue .= '; expires=' . $this->_renderExpire();
        }
        if ($this->_secure)
        {
            $headerValue .= '; secure';
        }
        if ($this->_httponly)
        {
            $headerValue .= '; httponly';
        }

        $headerValue = sprintf('%s=%s%s',
            urlencode($this->_name),
            urlencode($this->_value),
            $headerValue);
        return $headerValue;
    }

}
