<?php
/**
 * 优雅降级的实现.
 *
 * 目前只支持某个模块的全部下线、上线
 *
 * @category
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo 实现更细的粒度
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

        // 就当作什么都没发生，该干嘛干嘛，呵呵
        $this->_callNextMiddleware();

    }

    /**
     *
     * 通常，redirectTarget是：/interface/maintain.php
     *
     * @return array {moduleName: redirectTarget, ...}
     */
    protected function _getDisabledModules()
    {
        return array(

        );
    }

}
