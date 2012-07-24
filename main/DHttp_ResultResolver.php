<?php
/**
 * 对{@link DHttp_Action}的结果进行处理的类.
 *
 * <pre>
 *
 *          DHttp_Config    DHttp_Action
 *             |                 |
 *              -----------------
 *                     |
 *                     ◇
 *             DHttp_ResultResolver
 *                     |
 *                     | resolve() <---------
 *                     V                     |
 *                     |                     ^ return HTTP body content
 *                     |                     |
 *               new KHttp_Result_XX ->- execute()
 * </pre>
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class DHttp_ResultResolver implements DHttp_Result
{

    /**
     * @var DHttp_Config
     */
    private $_config;

    /**
     * @var DHttp_Action
     */
    private $_action;

    /**
     * @var string
     */
    private $_resultName;

    /**
     *
     * @param DHttp_Config $config
     * @param DHttp_Action $action
     * @param string $resultName
     */
    public function __construct(DHttp_Config $config, DHttp_Action $action, $resultName)
    {
        $this->_config = $config;
        $this->_action = $action;
        $this->_resultName = $resultName;
    }

    /**
     * @return string
     *
     * @throws KHttp_Exception_InvalidConfig
     */
    public function resolve()
    {
        if (self::RESULT_NONE === $this->_resultName || is_null($this->_resultName))
        {
            // 不用我来生成其最终的结果了，它自己负责echo
            return '';
        }

        list($value, $type) = DHttp_Coc::getInstance($this->_config)
            ->resolveResultName($this->_resultName);

        $resultEngineClass = 'KHttp_Result_' . ucfirst($type);
        $resultEngine = new $resultEngineClass();

        return $resultEngine->execute(
            $this->_action,
            $value
        );
    }

}
