<?php
/**
 * ͳһ��֤���У��.
 *
 * ��������actionӦ��ֻ��Ҫ���ģ�
 * <ol>
 * <li>���߱��м����֤������͡���ǰ������ֵ</li>
 * <li>�����֤ʧ�ܣ������ʾ���</li>
 * </ol>
 *
 * <code>
 * class MyAction extends DHttp_Action implements KHttp_Action_CaptchaAware, DHttp_Constant
 * {
 *     public function outputCaptchaError(KHttp_Exception_Captcha $ex)
 *     {
 *     }
 *
 *     public function on_index(DHttp_Request $req, DHttp_Response $res)
 *     {
 *         $req->setAttribute(self::CAPTCHA_PARAM_KEYPE, 'a');
 *         $req->setAttribute(self::CAPTCHA_PARAM_CODE, 'b');
 *         $req->setAttribute(self::CAPTCHA_PARAM_RCODE, 'c');
 *     }
 *
 * }
 * </code>
 *
 * @category
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_CaptchaGuard extends DHttp_Middleware implements DHttp_Constant
{

    public function call()
    {
        // ����actionִ�����
        $this->_callNextMiddleware();

        $req = $this->getRequest();
        $keytype = $req->getAttribute(self::CAPTCHA_PARAM_KEYPE);
        $code = $req->getAttribute(self::CAPTCHA_PARAM_CODE); // �û������ֵ
        $rcode = $req->getAttribute(self::CAPTCHA_PARAM_RCODE); // ��֤����cache�е�keyֵ

        if (!$rcode)
        {
            // ����Ҫ��֤����֤������memcache�����֤��ֵ�޷�ȡ��������!
            return;
        }

        if (!$code)
        {
            throw KHttp_Exception_Captcha::captchaEmptyException();
        }

        // ��ʼ��֤
        if (!DCaptcha_Api::checkMemcacheCode($rcode, $code, $keytype))
        {
            throw KHttp_Exception_Captcha::captchaWrongException();
        }

    }

}
