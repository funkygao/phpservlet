<?php
/**
 * 执行时间统计的profiler中间件.
 *
 * @category
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_Profiler extends DHttp_Middleware
{

    /**
     * 中间件的真正执行逻辑.
     *
     * 从app里获取request/response，并可以更改其值
     *
     * 自主决定是否调用下一个中间件接力棒，如果不调，则表示直接输出了
     */
    public function call()
    {
        $this->_app->hook(DHttp_App::HOOK_BEFORE, array($this, 'beginProfiler'));
        $this->_app->hook(DHttp_App::HOOK_AFTER, array($this, 'endProfiler'));

        $this->_callNextMiddleware();
    }

    public function beginProfiler()
    {
        $this->getRequest()->setAttribute('profiler_begin',
            CTime::getMilliSeconds());
    }

    public function endProfiler()
    {
        $used = CTime::getMilliSeconds()
            - $this->getRequest()->getAttribute('profiler_begin');
        $threshold = $this->getConfig()->getAttribute('profiler.threshold', 0);
        if (!$threshold || $used > $threshold)
        {
            // 达到记录profiler条件咯

        }

    }

}
