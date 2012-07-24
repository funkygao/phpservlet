<?php
/**
 * ʹ��Smartyģ�������result.
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
         * ʵ������struts2��OGNL����
         *
         * Object Graph Navigation Language
         *
         * ����annotation�ҳ���Ҫ���ݸ�ģ��ı���ֵ
         */
        $action->getApp()->enableAnnotationFeature();

        $clsname = $action->getClassName();
        $reflection = KBase_Reflections::getInstance()->register($clsname)->get($clsname);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            $methodName = $method->name;
            if (strpos($methodName, 'get') !== 0)
            {
                // ֻ��public getXXX�ķ���������
                continue;
            }

            $m = new ReflectionAnnotatedMethod($method->class, $methodName);
            $ognl = $m->getAnnotation(self::ANNOTATION_OGNL);
            if (!$ognl)
            {
                // ֻ����@KHttp_Annotation_Ognlע�͵�������
                continue;
            }

            $attrName = $ognl->name;
            if (!$attrName)
            {
                $attrName = substr($methodName, 3); // ȥ��'get'�����
                $attrName[0] = strtolower($attrName[0]);
            }

            // ��smartyģ�������ֵ
            $smarty->assign($attrName, $action->$methodName());
        }

        /*
         * ����actionͨ��request.setAttribute�ķ�ʽ��ģ�洫ֵ
         */
        foreach ($action->getRequest()->getAttributes() as $attrName => $attrValue)
        {
            $smarty->assign($attrName, $attrValue);
        }

        // ģ����GB18030�ģ�Ҫת����UTF-8
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
