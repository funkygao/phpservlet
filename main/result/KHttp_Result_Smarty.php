<?php
/**
 * 使用Smarty模版输出的result.
 *
 * @category
 * @package http
 * @subpackage result
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo lazy load to action attributes instead of eager assign values
 */

require_once dirname(__FILE__) . '/../../ko/vendor/Smarty-3.0.7/libs/Smarty.class.php';

class KHttp_Result_Smarty implements DHttp_ResultBuilder
{

    const
        LEFT_DELEMITER = '{{',
        RIGHT_DELEMITER = '}}';

    public function execute(DHttp_Action $action, $value = null)
    {
        // get configurations
        $module = $action->getConfig()->getModule();
        $smartyDir = ROOT_DIR . '/smarty_writable';
        $compileDir = $smartyDir . '/compile/' . $module;
        $cacheDir = $smartyDir . '/cache/' . $module;
        $configDir = $smartyDir . '/config/' . $module;

        /*
         * Prepare smarty engine
         */
        $smarty = new Smarty();
        $smarty->compile_dir = $compileDir;
        $smarty->cache_dir = $cacheDir;
        $smarty->config_dir = $configDir;
        $smarty->left_delimiter = self::LEFT_DELEMITER;
        $smarty->right_delimiter = self::RIGHT_DELEMITER;

        /*
         * 实现类似struts2的OGNL功能
         *
         * Object Graph Navigation Language
         *
         * 根据annotation找出需要传递给模版的变量值
         */
        $action->getApp()->enableAnnotationFeature();

        $clsname = $action->getClassName();
        $reflection = KBase_Reflections::getInstance()->register($clsname)->get($clsname);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            $methodName = $method->name;
            if (strpos($methodName, 'get') !== 0)
            {
                // 只对public getXXX的方法起作用
                continue;
            }

            $m = new ReflectionAnnotatedMethod($method->class, $methodName);
            $ognl = $m->getAnnotation(self::ANNOTATION_OGNL);
            if (!$ognl)
            {
                // 只对有@KHttp_Annotation_Ognl注释的起作用
                continue;
            }

            $attrName = $ognl->name;
            if (!$attrName)
            {
                $attrName = substr($methodName, 3); // 去掉'get'这个词
                $attrName[0] = strtolower($attrName[0]);
            }

            // 给smarty模版变量赋值
            $smarty->assign($attrName, $action->$methodName());
        }

        /*
         * 允许action通过request.setAttribute的方式给模版传值
         */
        foreach ($action->getRequest()->getAttributes() as $attrName => $attrValue)
        {
            $smarty->assign($attrName, $attrValue);
        }

        // 模版是GB18030的，要转换成UTF-8
        $html = $smarty->fetch($this->_getTemplateBaseDir() . $value);
        $html = iconv(DB_CHARSET, SYS_CHARSET . '//IGNORE', $html);
        return $html;
    }

    /**
     * @return string
     */
    protected function _getTemplateBaseDir()
    {
        return ROOT_DIR . '/template/';
    }

}
