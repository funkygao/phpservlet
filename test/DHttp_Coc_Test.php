<?php
/**
 *
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class DHttp_Coc_Test extends KxTestCaseBase
{

    /**
     * @var DHttp_Config
     */
    private $config;

    /**
     * @var DHttp_Coc
     */
    private $coc;

    protected function setUp()
    {
        parent::setUp();

        $this->config = new KHttp_Config_Default();
        $this->coc = DHttp_Coc::getInstance($this->config);
    }

    public function testSingleton()
    {
        $config = new KHttp_Config_Default();

        $this->assertInstanceOf('DHttp_Coc', DHttp_Coc::getInstance($config));

        $this->assertSame(DHttp_Coc::getInstance($config),
            DHttp_Coc::getInstance($config));

    }

    public function testResolveResultName()
    {
        $fixtures = array(
            'samples/diary/fdiary.html@s' => array(
                'samples/diary/fdiary.html',
                'smarty'
            ),
            '1@j' => array(
                '1',
                'json'
            )

        );
        foreach ($fixtures as $resultName => $expected)
        {
            list($value, $type) = $expected;
            list($v, $t) = $this->coc->resolveResultName($resultName);

            $this->assertEquals($v, $value);
            $this->assertEquals($t, $type);
        }
    }

    public function testResolveActionClassAndMethod()
    {
        $coc = DHttp_Coc::getInstance($this->config);

        $req = new DHttp_Request(DHttp_Env::mock(array('SCRIPT_NAME' => '/samples/diary/fdiarylist.php')));
        list($cls, $method) = $coc->resolveActionClassAndMethod($req);

        $this->assertEquals('KSamples_Diary_Action', $cls);
        $this->assertEquals('fdiarylist', $method);
    }

}
