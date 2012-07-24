<?php
/**
 * 页面重定向的结果类.
 *
 * @category
 * @package http
 * @subpackage result
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Result_Redirect implements DHttp_ResultBuilder
{
    public function execute(DHttp_Action $action, $value = null)
    {
        $action->getApp()->redirect($value);
    }

}
