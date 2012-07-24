<?php
/**
 * This middleware provides protection from CSRF attacks.
 *
 * Cross-site request forgery
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * 使用方法
 * <pre>
 * <input type="hidden" name="<?=$csrf_key?>" value="<?=$csrf_token?>" />
 * </pre>
 *
 */
class KHttp_Middleware_CsrfGuard extends DHttp_Middleware
{
    /**
     * Request key.
     *
     * @var string
     */
    protected $_key;

    /**
     * Constructor.
     *
     * @param string $key Request key
     *
     * @throws OutOfBoundsException
     */
    public function __construct($key = self::CSRF_TOKEN)
    {
        if (!is_string($key) || empty($key) || preg_match('/[^a-zA-Z0-9\-\_]/', $key))
        {
            throw new OutOfBoundsException('Invalid key' . $key);
        }

        $this->_key = $key;
    }

    public function call()
    {
        $this->_app->hook(DHttp_App::HOOK_BEFORE, array($this, 'checkToken'));

        $this->_callNextMiddleware();
    }

    public function checkToken()
    {
        // 创建token，并进行合法性检查
        $req = $this->_app->request();

        $token = sha1($req->getRemoteAddr() . '|' . $req->getUserAgent());
        $userToken = $this->_app->request()->getStr($this->_key);
        if ($token !== $userToken)
        {
            $this->_app->halt(self::SC_BAD_REQUEST, 'Missing token');
        }

        // Assign to template
        $this->_app->request()->setAttribute(self::CSRF_KEY, $this->_key);
        $this->_app->request()->setAttribute(self::CSRF_TOKEN, $token);
    }

}
