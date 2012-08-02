<?php
/**
 *
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class KHttp_Config_Default_Test extends KxTestCaseBase
{

    /**
     * @var KHttp_Config_Default
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->config = new KHttp_Config_Default();
    }

    public function testGetMiddlewareNames()
    {
        $this->assertGreaterThanOrEqual(2, count($this->config->getMiddlewareNames()));
    }

    public function testProfilerShoubeBeLast()
    {
        $wares = $this->config->getMiddlewareNames();
        $last = $wares[count($wares) - 1];
        $this->assertEquals('profiler', $last);
    }

}
