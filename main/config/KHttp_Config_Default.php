<?php
/**
 * Ĭ�ϵ�������Ϣ.
 *
 * @category
 * @package http
 * @subpackage config
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Config_Default extends DHttp_Config
{

    public function getMiddlewareNames()
    {
        // Խ����Խ������
        return array(
            'debug',
            'profiler',
        );
    }

    protected function _init()
    {

    }

}
