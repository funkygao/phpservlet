<?php
/**
 * HTTP������֤���ӿ�.
 *
 * ���ڶ�GET��POST�����ݽ��кϷ��Լ��
 *
 * @package http
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
interface DHttp_Validator
{

    /**
     *
     * @param string $value
     *
     * @return bool
     */
    public function validate($value);


}