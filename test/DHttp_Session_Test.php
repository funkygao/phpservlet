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

require_once('/kx/tests/KxTestCaseBase.php');

class DHttp_Session_Test extends KxTestCaseBase
{

    /**
     * @var DHttp_Session
     */
    private $session;

    /**
     * Session id.
     *
     * @var string
     */
    private $id;

    protected function setUp()
    {
        $this->id = '10123';

        $this->session = new DHttp_Session($this->id, CMemCacheEx::getInstance());
    }

    public function testGetId()
    {
        $this->assertEquals($this->id, $this->session->getId());
    }

    public function testPutAndGetAndDelete()
    {
        $this->session->putValue('user', 'gaopeng');
        $this->assertEquals('gaopeng', $this->session->getValue('user'));

        // cleanup
        unset($this->session['user']);
        $this->assertNull($this->session->getValue('user'));
    }

    public function testCount()
    {
        $this->assertEquals(0, count($this->session));

        $this->session->putValue('user', 'gaopeng');
        $this->assertEquals(1, count($this->session));

        $this->session->putValue('user1', 'kaixin');
        $this->assertEquals(2, count($this->session));

        // cleanup
        $this->session->delete('user');
        $this->assertEquals(1, count($this->session));
        $this->session->delete('user1');
        $this->assertEquals(0, count($this->session));
    }

    public function testArrayAccess()
    {
        $this->assertEquals(0, count($this->session));

        $this->session['user'] = 'gaopeng';
        $this->assertEquals(1, count($this->session));
        $this->assertEquals('gaopeng', $this->session['user']);

        $this->session['user1'] = 'kaixin';
        $this->assertEquals(2, count($this->session));
        $this->assertEquals('kaixin', $this->session['user1']);

        $this->assertTrue(isset($this->session['user1']));

        $this->assertFalse(isset($this->session['non-exist']));

        // cleanup
        unset($this->session['user']);
        $this->assertEquals(1, count($this->session));
        unset($this->session['user1']);
        $this->assertEquals(0, count(($this->session)));
    }

    public function testIterator()
    {
        $this->session['key1'] = 11;
        $this->session['key2'] = 12;

        $expected = 11;

        foreach ($this->session as $k => $v)
        {
            $this->assertEquals($expected, $v);
            $expected += 1;
        }

        // cleanup
        foreach ($this->session as $k => $v)
        {
            unset($this->session[$k]);
        }

    }

}
