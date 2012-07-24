<?php
/**
 * ר��������002/009�Ͻ��е��ԵĹ���.
 *
 * ���ݵ�ǰ���еĻ����Լ���������������Ƿ�򿪵���ģʽ
 *
 * ��kaixin001�ϲ�������
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
     * ��002�����¸���ĳ��url��������ж��Ƿ�򿪵���ģʽ.
     *
     * ��ʹ������001Ҳû�й�ϵ����Ϊֻ��002/009����������
     * ���������ǲ����鷢��001��
     *
     * <code>
     * if (DHttp_Debug::isDebugTurnedOn('gaopeng')
     * {
     *     print_r($theValue);
     * }
     * </code>
     * ��������002�ϸ�url�������� &gaopeng=1���ͻ�print_r��
     *
     * @param string $requestParam Url��ĳ������
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
