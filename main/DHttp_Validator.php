<?php
/**
 * HTTP参数验证器接口.
 *
 * 用于对GET、POST的数据进行合法性检查
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