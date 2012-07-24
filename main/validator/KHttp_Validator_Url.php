<?php
/**
 *
 *
 * @package http
 * @subpackage validator
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Validator_Url implements DHttp_Validator
{

    public function validate($value)
    {
        if (preg_match("/^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i", $value))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
