<?php
/**
 * ��֤���{@link DHttp_Action}�ӿ�.
 *
 * �κ���Ҫ��֤���action����ʵ�ֱ��ӿ�
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