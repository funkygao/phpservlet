<?php
/**
 * 统一验证码的校验.
 *
 * 有了它，action应用只需要关心：
 * <ol>
 * <li>告诉本中间件验证码的类型、当前的输入值</li>
 * <li>如果验证失败，如何显示结果</li>
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
        // 先让action执行完毕
        $this->_callNextMiddleware();

        $req = $this->getRequest();
        $keytype = $req->getAttribute(self::CAPTCHA_PARAM_KEYPE);
        $code = $req->getAttribute(self::CAPTCHA_PARAM_CODE); // 用户输入的值
        $rcode = $req->getAttribute(self::CAPTCHA_PARAM_RCODE); // 验证码在cache中的key值

        if (!$rcode)
        {
            // 不需要验证码验证，或者memcache里的验证码值无法取到，放行!
            return;
        }

        if (!$code)
        {
            throw KHttp_Exception_Captcha::captchaEmptyException();
        }

        // 开始验证
        if (!DCaptcha_Api::checkMemcacheCode($rcode, $code, $keytype))
        {
            throw KHttp_Exception_Captcha::captchaWrongException();
        }

    }

}
