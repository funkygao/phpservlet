<?php
/**
 * Convention over configuration.
 *
 * 首先找{@link DHttp_Config}里的配置信息，如果没有，则
 * 按照潜规则自动计算
 *
 * @package http
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_Coc
{

    /**
     * result type简称与全称的映射.
     *
     * @var array
     */
    private static $_resultTypeCanonicalMappings = array(
        'j' => 'json',
        's' => 'smarty',
        'r' => 'redirect',
    );

    /**
     * @var DHttp_Coc
     */
    private static $_instance;

    /**
     * @var DHttp_Config
     */
    private $_config;

    private function __construct(DHttp_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @param DHttp_Config $config
     *
     * @return DHttp_Coc
     */
    public static function getInstance(DHttp_Config $config)
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    /**
     *
     * action method可以这样: return value@type
     *
     * e.g.
     * <code>
     * return json_encode($mydata) . '@j';
     * </code>
     *
     * @param string $resultName
     *
     * @return array [resultValue, resultType]
     *
     * @throws KHttp_Exception_InvalidConfig
     */
    public function resolveResultName($resultName)
    {
        list($value, $type) = explode('@', $resultName);
        if (!empty($value) && !empty($type))
        {
            $type = self::$_resultTypeCanonicalMappings[$type];
        }
        else
        {
            list($value, $type) = $this->_config->getResultConfiguration($resultName);
            if (!$type)
            {
                throw new KHttp_Exception_InvalidConfig("Null result type");
            }
        }

        return array($value, $type);
    }

    /**
     * @param DHttp_Request $req
     *
     * @return array [actionClass, actionMethod]
     */
    public function resolveActionClassAndMethod(DHttp_Request $req)
    {
        list($actionClass, $actionMethod) = array(
            $this->_config->getActionClass(),
            $this->_config->getActionMethod()
        );

        $scriptName = $req->getScriptName();

        if (!$actionClass)
        {
            /*
             * convention over configuration
             *
             * /samples/diary/fdiarylist.php -> KSamples_Diary_Action
             */
            $dirs = explode('/', trim(dirname($scriptName), '/'));
            $actionClass = 'K';
            foreach ($dirs as $dir)
            {
                $actionClass .= ucfirst($dir) . '_';
            }
            $actionClass .= 'Action';

        }

        if (!$actionMethod)
        {
            /*
             * convention over configuration
             *
             * /samples/diary/fdiarylist.php -> fdiarylist
             */
            $actionMethod = basename($scriptName, '.php');
        }

        return array($actionClass, $actionMethod);
    }

}
