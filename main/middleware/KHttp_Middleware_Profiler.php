<?php
/**
 * ִ��ʱ��ͳ�Ƶ�profiler�м��.
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
     * �м��������ִ���߼�.
     *
     * ��app���ȡrequest/response�������Ը�����ֵ
     *
     * ���������Ƿ������һ���м����������������������ʾֱ�������
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
            // �ﵽ��¼profiler������

        }

    }

}
