<?php
/**
 * 专门用于调试的拦截器(中间件).
 *
 * 在非线上环境下，通过给url参数追加&_xhprof_=1，就可以在返回的html尾部追加一个xhprof
 * 报告的热链接。点击该链接，就能看到当前请求的各个call profiler！
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_Debug extends DHttp_Interceptor
{

    private function _isXhprofEnabled()
    {
        return DHttp_Debug::isDebugTurnedOn(self::REQ_PARAM_DEBUG_XHPROF)
            && function_exists('xhprof_enable');
    }

    protected function _before(DHttp_Request $req, DHttp_Response $res)
    {
        if ($this->_isXhprofEnabled())
        {
            // profiler starts here
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
    }

    protected function _after(DHttp_Request $req, DHttp_Response $res)
    {
        if ($this->_isXhprofEnabled())
        {
            // profiler ends here
            $xhprof_data = xhprof_disable();

            $xhprofRoot = ROOT_DIR . 'htdocs/prof/xhprof_lib/utils/';
            include_once $xhprofRoot . 'xhprof_lib.php';
            include_once $xhprofRoot . 'xhprof_runs.php';

            $xhprof_runs = new XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, 'txt');

            // append to http body
            $res->write('<br/>');
            $res->write("<a href='/test/xhprof_html/index.php?run=$run_id&source=txt'>xhprof</a>");
        }

    }
}
