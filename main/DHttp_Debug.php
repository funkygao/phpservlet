<?php
/**
 * 专门用于在002/009上进行调试的工具.
 *
 * 根据当前运行的环境以及请求参数，决定是否打开调试模式
 *
 * 在kaixin001上不起作用
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
final class DHttp_Debug
{

    // disable new DHttp_Debug();
    private function __construct()
    {

    }

    /**
     * 在002环境下根据某个url请求参数判断是否打开调试模式.
     *
     * 即使发布到001也没有关系，因为只对002/009环境起作用
     * 不过，还是不建议发到001上
     *
     * <code>
     * if (DHttp_Debug::isDebugTurnedOn('gaopeng')
     * {
     *     print_r($theValue);
     * }
     * </code>
     * 这样，在002上该url请求后面加 &gaopeng=1，就会print_r了
     *
     * @param string $requestParam Url的某个参数
     *
     * @return bool
     */
    public static function isDebugTurnedOn($requestParam)
    {
        $request = DHttp_ContextUtil::getKxRequest();
        return ($request->isStagingServer() || $request->isTestingServer())
            && $request->getBool($requestParam, false);
    }

}
