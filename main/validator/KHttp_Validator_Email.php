<?php
/**
 * A valid email address?
 *
 * @package http
 * @subpackage validator
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Validator_Email implements DHttp_Validator
{

    public function validate($value)
    {
        if (empty($value))
        {
            return false;
        }

        $email = filter_var(trim($value), FILTER_VALIDATE_EMAIL);
        return !$email ? false : true;
    }

}
