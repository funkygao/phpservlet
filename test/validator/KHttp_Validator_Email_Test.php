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

class KHttp_Validator_Email_Test extends DHttp_TestBase
{

    /**
     * @var DHttp_Validator
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->validator = new KHttp_Validator_Email();
    }

    public function testGetEmail()
    {
        $fixtures = array(
            'gaopeng@corp.kaixin001.com'  => true,
            'gaopeng@corp.kaixin001.com ' => true, // auto trim

            'ฮารว@163.com'                  => false, // invalid email
            '@a.com'                      => false,
            'http://www.kaixin001.com'    => false, // invalid email
            'wangli'                      => false,
            'sina.com'                    => false,
            0                             => false,
            12.0                          => false,
            ''                            => false,
            -1                            => false,
        );

        foreach ($fixtures as $email => $expected)
        {
            $this->assertEquals($expected, $this->validator->validate($email));
        }


    }

}
