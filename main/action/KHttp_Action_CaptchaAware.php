<?php
/**
 * 验证码的{@link DHttp_Action}接口.
 *
 * 任何需要验证码的action必须实现本接口
 *
 * @category
 * @package http
 * @subpackage action
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
interface KHttp_Action_CaptchaAware
{

    /**
     *
     * @param KHttp_Exception_Captcha $ex
     *
     * return void
     */
    public function outputCaptchaError(KHttp_Exception_Captcha $ex);

}