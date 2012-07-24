<?php
/**
 * Ӧ�õ�������Ϣ.
 *
 * ���൱��struts2���struts.xml��������û��ʹ�������ļ�����Ϊ
 * servlet container��long run process����php���ǡ�
 * ���Ū����������ļ���������read/parsing�Ŀ�����ò���ʧ��
 *
 * ��ˣ����ǲ��ö������ö���ķ���������ÿ��ҳ���Լ����ã������Ǽ������ã��������
 * Ҳ�ṩ�˺ܶ��ʺϲ�ͬ������bundled config classes����ˣ�client programҲ��
 * ����ۡ�
 *
 * ֧��chain method
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo �Զ�����module name, convention over configuration
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
     * ����ģʽ.
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
     * �Զ������.
     *
     * @var array
     */
    private $_attributes = array();

    /**
     * ����ĳ��HTTP METHOD���Զ��ض���ĳ��url.
     *
     * @var array [httpMethod: targetUrl, ...]
     */
    private $_redirects;


    /**
     * ÿ���û�ÿ����ˢ��ҳ��Ĵ�������.
     *
     * ÿ����Ϊ��λ
     *
     * @var int
     */
    private $_refreshRatePerUser = 60;

    /**
     * �Ƿ��¼����ܷ���?
     *
     * @var bool
     */
    private $_requireLogin = false;

    /**
     * resultӳ���.
     *
     * �ڴ��Ѿ�����ȫվ��global mapping�������Ӧ���ڴ˻����϶���
     * �Լ���ӳ���ϵ
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
     * @param string $module ��ҳ��Ӧ��������ģ������
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
     * Ԥ�������า�ǵĳ�ʼ������.
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
     * @param string $module ĳ��Ӧ��������ģ������
     *
     * @return DHttp_Config
     */
    public final function setModule($module)
    {
        $this->_module = ltrim($module, '/');

        return $this;
    }

    /**
     * ĳ��Ӧ��������ģ������.
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
     * ͨ���ļ����򷽷�������action method.
     *
     * ��������__FILE__��setActionMethod�ķ���.
     *
     * ��Ϊ����û��ʹ��urlrewrite����ǰ���ļ����������URI��һ�µ�
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
     * ����result.
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
     * ����result����ȡ��result value, result type
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
     * ���û�ȡ�����ĳ��HTTP METHOD��Ӧ����תURL.
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
     * ȡ�û�����ÿ���û�ÿ����ˢ��ҳ�����������.
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
     * ����Ϊ�����¼����ܷ���.
     *
     * Ĭ���ǲ���Ҫ��
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
     * �����ඨ���Լ�Ԥ��װ���м��.
     *
     * ע��˳��Խ����Խ�类ִ�У�
     *
     * @return array List of string
     */
    public abstract function getMiddlewareNames();

}
