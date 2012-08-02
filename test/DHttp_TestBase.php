<?php
/**
 * 所有单元测试的父类.
 *
 * End programmers just include this file once and extends DHttp_TestBase to
 * write unit test cases.
 *
 * @category test
 * @package
 * @version $Id$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once dirname(__FILE__) . '/../main/bootstrap.php';

abstract class DHttp_TestBase extends PHPUnit_Framework_TestCase
{

    /**
     * @var float
     */
    protected $_beginAt;

    private function getMilliSeconds()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->_beginAt = $this->getMilliSeconds();
    }

    /**
     * 取得当前单元测试用例的用时.
     *
     * @return float
     */
    protected final function getElapsedMs()
    {
        return $this->getMilliSeconds() - $this->_beginAt;
    }

    /**
     * 取得某个文件的最后N行内容.
     *
     * @param string $filename 绝对路径
     * @param int $lines 取最后多少行，默认1
     *
     * @return string
     */
    protected function getLastLine($filename, $lines = 1)
    {
        return `tail -n $lines $filename`;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getFixtureFile($filename)
    {
        return 'fixtures/' . $filename;
    }


}
