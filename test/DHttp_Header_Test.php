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

class DHttp_Header_Test extends KxTestCaseBase
{

    public function testConstrucWithoutArgs()
    {
        $h = new DHttp_Header();
        $this->assertEquals(0, count($h));
    }

    public function testConstrucWithArgs()
    {
        $h = new DHttp_Header(array('Content-Type' => 'text/html'));
        $this->assertEquals(1, count($h));
    }

    public function testSetAndGetHeader()
    {
        $h = new DHttp_Header();
        $h['Content-Type'] = 'text/html';
        $this->assertEquals('text/html', $h['Content-Type']);
        $this->assertEquals('text/html', $h['Content-type']);
        $this->assertEquals('text/html', $h['cOntEnt-tYpe']);
        $this->assertEquals('text/html', $h['content-type']);
    }

    public function testGetNonExistentHeader()
    {
        $h = new DHttp_Header();

        $this->assertNull($h['foo']);
        $this->assertTrue(empty($h['non-exist-header']));
    }

    public function testHeaderIsSet()
    {
        $h = new DHttp_Header();

        $h['Content-Type'] = 'text/html';
        $this->assertTrue(isset($h['Content-Type']));
        $this->assertTrue(isset($h['Content-type']));
        $this->assertTrue(isset($h['content-type']));

        $this->assertFalse(isset($h['foo']));
    }

    public function testUnsetHeader()
    {
        $h = new DHttp_Header();
        $h['Content-Type'] = 'text/html';
        $this->assertEquals(1, count($h));

        unset($h['Content-Type']);
        $this->assertEquals(0, count($h));

        $h['conTent-TyPe'] = 'text/json';
        $this->assertEquals(1, count($h));
        unset($h['content-type']);
        $this->assertEquals(0, count($h));
    }

    public function testIteration()
    {
        $h = new DHttp_Header();
        $h['One'] = 'Foo';
        $h['Two'] = 'Bar';
        $output = '';
        foreach ($h as $key => $value)
        {
            $output .= $key . $value;
        }
        $this->assertEquals('OneFooTwoBar', $output);
    }

    public function testOutputsOriginalNotCanonicalName()
    {
        $h = new DHttp_Header();
        $h['X-Powered-By'] = 'Kaixin001';
        $h['Content-Type'] = 'text/csv';

        $keys = array();
        foreach ($h as $name => $value)
        {
            $keys[] = $name;
        }
        $this->assertContains('X-Powered-By', $keys);
        $this->assertContains('Content-Type', $keys);
    }

    public function testToArray()
    {
        $h = new DHttp_Header();
        $this->assertEmpty($h->toArray());

        $h['x-pOwered-by'] = 'Kaixin001';
        $h['ContEnt-TYpe'] = 'text/csv';
        $this->assertEquals(
            array(
                'X-Powered-By: Kaixin001',
                'Content-Type: text/csv',
            ),
            $h->toArray()
        );

    }

    public function testMergeHeaders()
    {
        $h = new DHttp_Header();
        $h['Content-Type'] = 'text/html';
        $this->assertEquals(1, count($h));
        $this->assertEquals('text/html', $h['content-type']);

        $h->merge('content-type', 'text/plain'); // replace = true
        $this->assertEquals(1, count($h));
        $this->assertEquals('text/plain', $h['content-type']);

        $h->merge('set-cookie', '_user=gaopeng');
        $this->assertEquals(2, count($h));
        $h->merge('set-Cookie', '_preemail=kx@corp.kaixin001.com', $replace = false);
        $this->assertEquals(2, count($h));
        $this->assertEquals(
            array(
                'Content-Type: text/plain',
                'Set-Cookie: _user=gaopeng',
                'Set-Cookie: _preemail=kx@corp.kaixin001.com',
            ),
            $h->toArray()
        );
    }

}
