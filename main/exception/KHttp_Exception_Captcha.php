<?php
/**
 *
 *
 * @category
 * @package http
 * @subpackage exception
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Exception_Captcha extends DHttp_Exception
{

    const
        CODE_EMPTY = 1,
        CODE_WRONG = 2;

    /**
     * @return bool
     */
    public function isCaptchaEmpty()
    {
        return self::CODE_EMPTY === $this->getCode();
    }

    /**
     * @return bool
     */
    public function isCaptchaWrong()
    {
        return self::CODE_WRONG === $this->getCode();
    }

    /**
     * 创建验证码错误的异常.
     *
     * 并不抛出，只是new
     *
     * @param string $message
     * @return KHttp_Exception_Captcha
     */
    public static function captchaWrongException($message = '')
    {
        return new self($message, self::CODE_WRONG);
    }

    /**
     * 创建验证码为空的异常.
     *
     * 并不抛出，只是new
     *
     * @param string $message
     * @return KHttp_Exception_Captcha
     */
    public static function captchaEmptyException($message = '')
    {
        return new self($message, self::CODE_EMPTY);
    }

}
