<?php
/**
 * ����ע���ʺŵ��м��.
 *
 * ע�������Ǹ�wizard flow������û���������flow���ʺţ���Ϊ�����ʺš�
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_SemiAccount extends DHttp_Middleware
{

    const REDIRECT_TARGET = '/reg/wizard.php';

    public function call()
    {
        $req = $this->getRequest();
        $uid = $req->getLoggedInUid();
        if ($uid && KBiz_User_Config::getInstance($uid)->isSemiAccount())
        {
            // ���ǰ�ע���ʺ�
            $skippedPaths = array(
                "/register/", "/reg/", "/sso/", "/login/", "/s/", "/t/",
                "/rest/fanbox.php", "/friend/addverify.php",
                "/act/", "/!repaste/", "/!rating/", "/!vote/",
                "/!film/", "/page/", "/interface", "/!ptest",
                "/!fish/", "/app/", "/nverify/", "/!spiderman/", "/!city/", "/!farm/", "/!house/",
                "/!bird/", "/!cafe/", "/!app_ddzpoker/", "/!app_landlord/", "/!app_village/",
                "/!app_winninggoal/", "/!app_pvzonline/", "/!sims/", "/pay/fast/",
            );

            $script = $req->getScriptName();
            $redirectToReg = true;
            foreach ($skippedPaths as $path)
            {
                if (strpos($script, $path) !== false)
                {
                    $redirectToReg = false;
                    break;
                }
            }

            if ($redirectToReg)
            {
                $cookie = new DHttp_Cookie(self::COOKIE_REG_GOTO, $req->getRequestUri());
                $cookie->expireAfter(KBase_Const_Time::SECONDS_IN_DAY);
                $this->getResponse()->setCookie($cookie);

                $this->_redirect(self::REDIRECT_TARGET);
            }

        }

        $this->_callNextMiddleware();
    }

}
