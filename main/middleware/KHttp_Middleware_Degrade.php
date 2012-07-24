<?php
/**
 * ���Ž�����ʵ��.
 *
 * Ŀǰֻ֧��ĳ��ģ���ȫ�����ߡ�����
 *
 * @category
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo ʵ�ָ�ϸ������
 */
class KHttp_Middleware_Degrade extends DHttp_Middleware
{

    public function call()
    {
        $moduleName = $this->getRequest()->getModuleName();
        foreach ($this->_getDisabledModules() as $module => $target)
        {
            if ($module == $moduleName)
            {
                $this->_redirect($target);
            }
        }

        // �͵���ʲô��û�������ø������Ǻ�
        $this->_callNextMiddleware();

    }

    /**
     *
     * ͨ����redirectTarget�ǣ�/interface/maintain.php
     *
     * @return array {moduleName: redirectTarget, ...}
     */
    protected function _getDisabledModules()
    {
        return array(

        );
    }

}
