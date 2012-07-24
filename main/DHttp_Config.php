<?php
/**
 * 应用的配置信息.
 *
 * 它相当于struts2里的struts.xml，但我们没有使用配置文件，因为
 * servlet container是long run process，而php不是。
 * 如果弄个大的配置文件，对它的read/parsing的开销会得不偿失。
 *
 * 因此，我们采用定义配置对象的方法，交给每个页面自己配置，而不是集中配置，但本框架
 * 也提供了很多适合不同场景的bundled config classes，因此，client program也不
 * 会很累。
 *
 * 支持chain method
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo 自动计算module name, convention over configuration
 *
 */
abstract class DHttp_Config implements DHttp_Result
{

    const
        MODE_DEBUG = 'debug',
        MODE_DEV = 'dev',
        MODE_RELEASE = 'release';

    const
        MAP_TYPE = 'type',
        MAP_VALUE = 'value';

    /**
     * 运行模式.
     *
     * @var string dev|release|debug
     */
    private $_mode = self::MODE_DEV;

    /**
     * Debug level.
     *
     * @var int
     */
    private $_debug = 0;

    /**
     * @var string
     */
    private $_module;

    /**
     * Action class name.
     *
     * @var string
     */
    private $_actionClass;

    /**
     * Action method name.
     *
     * @var string
     */
    private $_actionMethod;

    /**
     * 自定义参数.
     *
     * @var array
     */
    private $_attributes = array();

    /**
     * 对于某种HTTP METHOD，自动重定向到某个url.
     *
     * @var array [httpMethod: targetUrl, ...]
     */
    private $_redirects;


    /**
     * 每个用户每分钟刷新页面的次数限制.
     *
     * 每分钟为单位
     *
     * @var int
     */
    private $_refreshRatePerUser = 60;

    /**
     * 是否登录后才能访问?
     *
     * @var bool
     */
    private $_requireLogin = false;

    /**
     * result映射表.
     *
     * 在此已经定了全站的global mapping，具体的应用在此基础上定义
     * 自己的映射关系
     *
     * @var array {name: {type:y, value:z}, ...}
     */
    private $_resultMappings = array(
        self::RESULT_GLOBAL_INVALID_PARAM =>
        array(
            self::MAP_VALUE  => 'invalidParam.html',
            self::MAP_TYPE   => self::TYPE_SMARTY
        ),

        self::RESULT_GLOBAL_DATAERROR     =>
        array(
            self::MAP_VALUE  => 'dataError.html',
            self::MAP_TYPE   => self::TYPE_SMARTY
        ),

        self::RESULT_GLOBAL_LOGIN         =>
        array(
            self::MAP_VALUE  => '/login.php',
            self::MAP_TYPE   => self::TYPE_REDIRECT
        ),

    );

    /**
     * Constructor.
     *
     * @param string $module 该页面应用所属的模块名称
     * @param string $actionClass
     * @param string $actionMethodOrFilename
     */
    public function __construct($module = null, $actionClass = null, $actionMethodOrFilename = null)
    {
        $this->_actionClass = $actionClass;

        if (!is_null($actionMethodOrFilename))
        {
            $this->setActionMethod($actionMethodOrFilename);
        }

        if (!is_null($module))
        {
            $this->setModule($module);
        }

        $this->_init();
    }

    /**
     * 预留给子类覆盖的初始化方法.
     */
    protected function _init()
    {

    }

    /**
     * @param int $debug
     *
     * @return DHttp_Config
     */
    public final function setDebug($debug)
    {
        $this->_debug = $debug;

        return $this;
    }

    /**
     * @return int Debug level
     */
    public final function getDebug()
    {
        return $this->_debug;
    }

    /**
     * @param string $module 某个应用所属的模块名称
     *
     * @return DHttp_Config
     */
    public final function setModule($module)
    {
        $this->_module = ltrim($module, '/');

        return $this;
    }

    /**
     * 某个应用所属的模块名称.
     *
     * @return string
     */
    public final function getModule()
    {
        return $this->_module;
    }

    /**
     * @param string $mode
     *
     * @return DHttp_Config
     */
    public final function setMode($mode)
    {
        $this->_mode = $mode;

        return $this;
    }

    /**
     * @return string Run mode
     */
    public final function getMode()
    {
        return $this->_mode;
    }

    /**
     * @return bool
     */
    public final function isDebugMode()
    {
        return self::MODE_DEBUG === $this->_mode;
    }

    /**
     * @param string $actionClass
     *
     * @return DHttp_Config
     */
    public final function setActionClass($actionClass)
    {
        $this->_actionClass = $actionClass;

        return $this;
    }

    /**
     * @return string Action class name
     */
    public final function getActionClass()
    {
        return $this->_actionClass;
    }

    /**
     * 通过文件名或方法名设置action method.
     *
     * 可以利用__FILE__简化setActionMethod的方法.
     *
     * 因为我们没有使用urlrewrite，当前的文件名与请求的URI是一致的
     *
     * <code>
     * // userList.php
     * $cfg = new DHttp_Config();
     * $cfg->setActionMethod(__FILE__)->setActionClass('DUser_Action');
     * </code>
     *
     * @param string $actionMethodOrFilename Virtual action method name or __FILE__
     *
     * @return DHttp_Config
     */
    public final function setActionMethod($actionMethodOrFilename)
    {
        $phpFileExtention = '.php';
        if (CStr::endsWith($actionMethodOrFilename, $phpFileExtention))
        {
            $this->_actionMethod = basename($actionMethodOrFilename, $phpFileExtention);
        }
        else
        {
            $this->_actionMethod = $actionMethodOrFilename;
        }

        return $this;
    }

    /**
     * @return string Virtual action method name
     */
    public final function getActionMethod()
    {
        return $this->_actionMethod;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return DHttp_Config
     */
    public final function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
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
     * 设置result.
     *
     * @param string $name
     * @param string $type Result type
     * @param string $value Result value
     *
     * @return DHttp_Config
     * @deprecated
     */
    public final function mapResult($name, $type = self::TYPE_SMARTY, $value = null)
    {
        if (isset($this->_resultMappings[$name]))
        {
            // declare mapping for a name twice or more
            DLogger_Facade::getLogger()->error_log('duplicated result map: ' . $name);
        }

        $this->_resultMappings[$name] = array(
            self::MAP_VALUE => $value, // result value
            self::MAP_TYPE  => $type, // result type
        );

        return $this;
    }

    /**
     * 根据result名称取得result value, result type
     *
     * @param string $name
     * @return array [value, type], Null if not found.
     */
    public final function getResultConfiguration($name)
    {
        if (!isset($this->_resultMappings[$name]))
        {
            return array(null, null);
        }

        $map = $this->_resultMappings[$name];
        return array($map[self::MAP_VALUE], $map[self::MAP_TYPE]);
    }

    /**
     * 设置或取得针对某个HTTP METHOD对应的跳转URL.
     *
     * @param string $httpMethod
     * @param null|string $targetUrl If null, getter; else setter
     *
     * @return DHttp_Config|null|string
     */
    public final function redirect($httpMethod, $targetUrl = null)
    {
        if (!is_null($targetUrl))
        {
            // setter
            if (is_null($this->_redirects))
            {
                $this->_redirects = array();
            }

            $this->_redirects[$httpMethod] = $targetUrl;

            return $this;
        }

        if (is_null($this->_redirects) || !isset($this->_redirects[$httpMethod]))
        {
            return null;
        }

        return $this->_redirects[$httpMethod];
    }

    /**
     * 取得或设置每个用户每分钟刷新页面次数的限制.
     *
     * @param int $rate
     *
     * @return DHttp_Config|int
     */
    public final function refreshRatePerUser($rate = null)
    {
        if (is_null($rate))
        {
            return $this->_refreshRatePerUser;
        }
        else
        {
            $this->_refreshRatePerUser = $rate;

            return $this;
        }
    }

    /**
     * 设置为必须登录后才能访问.
     *
     * 默认是不需要的
     *
     * @param bool $require
     * @return bool|DHttp_Config
     */
    public final function requireLogin($require = null)
    {
        if (is_null($require))
        {
            return $this->_requireLogin;
        }
        else
        {
            $this->_requireLogin = $require;

            return $this;
        }
    }

    /**
     * 供子类定义自己预安装的中间件.
     *
     * 注意顺序：越靠后，越早被执行！
     *
     * @return array List of string
     */
    public abstract function getMiddlewareNames();

}
