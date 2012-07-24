<?php
/**
 * Controller.
 *
 * 负责调度{@link DHttp_Action}.
 *
 * 日后，如果有url rewrite，只需要修改本类
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_Controller implements DHttp_Constant
{

    /**
     * Action方法名称的前缀.
     */
    const ACTION_METHOD_PREFIX = 'on_';

    /**
     * @var DHttp_App
     */
    private $_app;

    /**
     * 为了防止同个action被创建多次.
     *
     * 实际上创建多次也无所谓，只是性能考虑
     *
     * @var array {actionClassName: actionObject, ...}
     */
    private static $_actionInstances = array();

    /**
     * @var array {validatorClassName, validatorInstance, ...}
     */
    private static $_validatorInstances = array();

    public function __construct(DHttp_App $app)
    {
        $this->_app = $app;
    }

    /**
     * 获取真正的action里定义的method name.
     *
     * @param string $actionMethod 虚拟的action method name
     *
     * @return string
     */
    public function getRealActionMethodName($actionMethod)
    {
        return $method = self::ACTION_METHOD_PREFIX . $actionMethod;
    }

    /**
     * @param string $actionClass
     *
     * @return DHttp_Action
     *
     * @throws RuntimeException When class not exists
     */
    private function _getActionInstance($actionClass)
    {
        if (!class_exists($actionClass))
        {
            throw new RuntimeException($actionClass . " not found");
        }

        if (!isset(self::$_actionInstances[$actionClass]))
        {
            self::$_actionInstances[$actionClass] = new $actionClass($this->_app);
        }

        return self::$_actionInstances[$actionClass];
    }

    private function _getActionAndMethod($actionClass, $actionMethod)
    {
        $action = $this->_getActionInstance($actionClass);
        $method = $this->getRealActionMethodName($actionMethod);
        if (!method_exists($action, $method))
        {
            throw new RuntimeException($action->getClassName() . "::$method() not found");
        }

        // notify app the current action
        $this->_app->action($action);

        return array($action, $method);
    }

    /**
     * 调度action执行指定action method.
     *
     * @return array [actionObject, resultName]
     *
     * @throws RuntimeException 如果该$actionClass没有定义对应的$actionMethod
     */
    public function dispatch()
    {
        list($actionClass, $actionMethod) = DHttp_Coc::getInstance($this->_app->config())
            ->resolveActionClassAndMethod($this->_app->request());

        list($action, $method) = $this->_getActionAndMethod(
            $actionClass,
            $actionMethod
        );

        // 把页面GET/POST传入的参数注入到action
        $this->_prepareRequestParameters($action);

        return array(
            $action,
            $action->$method($action->getRequest(), $action->getResponse())
        );
    }

    /**
     * 省却了原来 CBaseApp::getPara 步骤.
     *
     * 不再需要action主动去取参数的值，容器会自动注入
     *
     * The so called meta-programming.
     * Programming by declaration
     *
     * @param DHttp_Action $action
     */
    private function _prepareRequestParameters(DHttp_Action $action)
    {
        // 手工require annotation库文件
        $this->_app->enableAnnotationFeature();

        $req = $action->getRequest();

        $clsname = $action->getClassName();
        $reflection = KBase_Reflections::getInstance()->register($clsname)->get($clsname);
        // 遍历该action的所有property
        foreach ($reflection->getProperties() as $prop)
        {
            $p = new ReflectionAnnotatedProperty($prop->class, $prop->name);
            $paramAnnotation = $p->getAnnotation(self::ANNOTATION_PARAM);
            if (!$paramAnnotation)
            {
                // 只取用 KHttp_Annotation_Param 声明的 property
                continue;
            }

            $paramName = $prop->name;
            $decode = $paramAnnotation->decode; // TODO

            $getter = 'get' . $paramAnnotation->type;
            $paramValue = $req->$getter($prop->name, $paramAnnotation->default);
            $this->_validateParam($paramValue, $paramAnnotation->validators);

            /*
             * 把参数值注入到action对象里
             *
             * action在声明这些参数属性时，必须public，不能private+setter/getter
             *
             * TODO forward怎么办
             */
            $action->$paramName = $paramValue;
        }
    }

    /**
     * @param string $value
     * @param string $validators e,g 'email, required'
     */
    private function _validateParam(&$value, $validators)
    {
        if (!$validators)
        {
            return;
        }

        foreach (explode(',', $validators) as $validator)
        {
            $validator = trim($validator);
            if (!$validator)
            {
                continue;
            }

            $validatorClassName = 'KHttp_Validator_' . ucfirst($validator);
            if (!isset(self::$_validatorInstances[$validatorClassName]))
            {
                self::$_validatorInstances[$validatorClassName] = new $validatorClassName();
            }

            $validatorInstance = self::$_validatorInstances[$validatorClassName];
            if (!$validatorInstance->validate($value))
            {
                $value = self::PARAM_INVALID_VALUE;

                // 已经非法了，就不必继续校验咯
                return;
            }
        }

    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    public function isParamValid($paramValue)
    {
        return self::PARAM_INVALID_VALUE != $paramValue;
    }

    /**
     * 转发给某个action处理请求.
     *
     * @param string $actionClass
     * @param string $actionMethod
     *
     * @return string Result name
     */
    public function forward($actionClass, $actionMethod)
    {
        list($action, $method) = $this->_getActionAndMethod($actionClass, $actionMethod);

        return $action->$method($action->getRequest(), $action->getResponse());
    }

}
