<?php
/**
 * ���е�Ԫ���Եĸ���.
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
     * ȡ�õ�ǰ��Ԫ������������ʱ.
     *
     * @return float
     */
    protected final function getElapsedMs()
    {
        return $this->getMilliSeconds() - $this->_beginAt;
    }

    /**
     * ȡ��ĳ���ļ������N������.
     *
     * @param string $filename ����·��
     * @param int $lines ȡ�������У�Ĭ��1
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
