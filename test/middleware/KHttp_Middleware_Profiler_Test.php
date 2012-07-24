<?php
/**
 * 利于本测试用例，可以实现简单的benchmark.
 *
 * 例如，跑100次，然后就可以得出profiler值的分布，从而了解本框架的性能如何
 * 目前在142上跑500的结果，基本上是可以在0.8ms内处理完一个dummy request/response
 * 按照这个值，如果不考虑web server的性能，它本身可以实现 rps = 1250
 *
 * 部分原始结果：
 * <pre>
 * 0.000877857208252
 * 0.000844955444336
 * 0.000856876373291
 * 0.000838994979858
 * 0.000869989395142
 * 0.00084400177002
 * 0.000833034515381
 * 0.000859022140503
 * 0.000858068466187
 * 0.000838994979858
 * 0.000904083251953
 * 0.000900030136108
 * 0.000829935073853
 * 0.000848054885864
 * 0.000854969024658
 * 0.000912189483643
 * 0.000832080841064
 * 0.000819206237793
 * 0.000866174697876
 * 0.000845909118652
 * 0.000890016555786
 * 0.000906944274902
 * 0.000839948654175
 * 0.000813007354736
 * 0.000826120376587
 * 0.000800132751465
 * 0.000849962234497
 * 0.000805854797363
 * 0.00084114074707
 * 0.000850915908813
 * </pre>
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('/kx/tests/KxTestCaseBase.php');

class MyKHttp_Middleware_Profiler extends KHttp_Middleware_Profiler
{

    const PROFILER_RESULT = 'profiler_result';

    public function endProfiler()
    {
        $now = CTime::getMilliSeconds();
        $then = $this->getRequest()->getAttribute('profiler_begin');

        // 显示出来，以便收集性能数据
        echo "\n", $now - $then . "\n";

        // 放到request里，以便检验本方法的结果是否正常
        $this->getRequest()->setAttribute(self::PROFILER_RESULT, $now - $then);
    }

    public function getBegin()
    {
        return $this->getRequest()->getAttribute('profiler_begin');
    }

}

class KHttp_Middleware_Profiler_Test extends KxTestCaseBase
{

    /**
     * @var DHttp_App
     */
    private $app;

    /**
     * @var DHttp_Config
     */
    private $config;

    /**
     * @var MyKHttp_Middleware_Profiler
     */
    private $profiler;


    protected function setUp()
    {
        parent::setUp();

        $this->profiler = new MyKHttp_Middleware_Profiler();

        $this->config = new KHttp_Config_Default('', 'KSamples_Http_Action', 'index');
        $this->config->setMode('debug');
        $this->config->mapResult(DHttp_Result::RESULT_SUCCESS, DHttp_Result::TYPE_SMARTY, 'samples/http/app_test.html')
            ->mapResult(DHttp_Result::RESULT_FAIL, DHttp_Result::TYPE_SMARTY, 'samples/http/fail.html');
    }

    public function testProfilerNormal()
    {
        $now = CTime::getMilliSeconds();

        $this->app = new DHttp_App($this->config, DHttp_Env::mock());
        $this->app->register($this->profiler);
        $this->app->run();

        $this->assertGreaterThanOrEqual($now, $this->profiler->getBegin());

        // 启动时间不会超过1秒吧?
        $this->assertLessThan(1 + $now, $this->profiler->getBegin());

        $result = $this->app->request()->getAttribute(MyKHttp_Middleware_Profiler::PROFILER_RESULT);
        $this->assertNotNull($result);

        // 请求完成时间不会小于 0.001ms 吧?
        $this->assertGreaterThanOrEqual(0.000001, $result);

        // 请求完成时间不会超过1秒吧?
        $this->assertLessThan(1, $result);
    }

}
