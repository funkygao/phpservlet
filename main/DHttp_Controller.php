<?php
/**
 * Controller.
 *
 * �������{@link DHttp_Action}.
 *
 * �պ������url rewrite��ֻ��Ҫ�޸ı���
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
     * Action�������Ƶ�ǰ׺.
     */
    const ACTION_METHOD_PREFIX = 'on_';

    /**
     * @var DHttp_App
     */
    private $_app;

    /**
     * Ϊ�˷�ֹͬ��action���������.
     *
     * ʵ���ϴ������Ҳ����ν��ֻ�����ܿ���
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
     * ��ȡ������action�ﶨ���method name.
     *
     * @param string $actionMethod �����action method name
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
     * ����actionִ��ָ��action method.
     *
     * @return array [actionObject, resultName]
     *
     * @throws RuntimeException �����$actionClassû�ж����Ӧ��$actionMethod
     */
    public function dispatch()
    {
        list($actionClass, $actionMethod) = DHttp_Coc::getInstance($this->_app->config())
            ->resolveActionClassAndMethod($this->_app->request());

        list($action, $method) = $this->_getActionAndMethod(
            $actionClass,
            $actionMethod
        );

        // ��ҳ��GET/POST����Ĳ���ע�뵽action
        $this->_prepareRequestParameters($action);

        return array(
            $action,
            $action->$method($action->getRequest(), $action->getResponse())
        );
    }

    /**
     * ʡȴ��ԭ�� CBaseApp::getPara ����.
     *
     * ������Ҫaction����ȥȡ������ֵ���������Զ�ע��
     *
     * The so called meta-programming.
     * Programming by declaration
     *
     * @param DHttp_Action $action
     */
    private function _prepareRequestParameters(DHttp_Action $action)
    {
        // �ֹ�require annotation���ļ�
        $this->_app->enableAnnotationFeature();

        $req = $action->getRequest();

        $clsname = $action->getClassName();
        $reflection = KBase_Reflections::getInstance()->register($clsname)->get($clsname);
        // ������action������property
        foreach ($reflection->getProperties() as $prop)
        {
            $p = new ReflectionAnnotatedProperty($prop->class, $prop->name);
            $paramAnnotation = $p->getAnnotation(self::ANNOTATION_PARAM);
            if (!$paramAnnotation)
            {
                // ֻȡ�� KHttp_Annotation_Param ������ property
                continue;
            }

            $paramName = $prop->name;
            $decode = $paramAnnotation->decode; // TODO

            $getter = 'get' . $paramAnnotation->type;
            $paramValue = $req->$getter($prop->name, $paramAnnotation->default);
            $this->_validateParam($paramValue, $paramAnnotation->validators);

            /*
             * �Ѳ���ֵע�뵽action������
             *
             * action��������Щ��������ʱ������public������private+setter/getter
             *
             * TODO forward��ô��
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

                // �Ѿ��Ƿ��ˣ��Ͳ��ؼ���У�鿩
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
     * ת����ĳ��action��������.
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
