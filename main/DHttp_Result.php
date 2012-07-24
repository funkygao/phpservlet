<?php
/**
 * {@link DHttp_Action}的返回结果.
 *
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo move to DHttp_Constant
 */
interface DHttp_Result
{

    // 全局result name
    const
        RESULT_GLOBAL_DATAERROR = 'dataError',
        RESULT_GLOBAL_INVALID_PARAM = 'invalidParameter',
        RESULT_GLOBAL_LOGIN = 'login';

    // 给action使用的常用result name
    const
        RESULT_SUCCESS = 'success',
        RESULT_NONE = 'none',
        RESULT_INPUT = 'input',
        RESULT_FAIL = 'fail';

    const
        TYPE_SMARTY = 'smarty',
        TYPE_JSON = 'json',
        TYPE_XML = 'xml',
        TYPE_REDIRECT = 'redirect';

}
