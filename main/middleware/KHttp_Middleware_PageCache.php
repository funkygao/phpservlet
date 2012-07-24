<?php
/**
 * ר�Ÿ��������Э�̻��Ƶ�ҳ�滺����м��.
 *
 * ��ʵ�����Ǹ�������response header:
 * <ul>
 * <li>Cache-Control</li>
 * <li>ETag</li>
 * <li>Expires</li>
 * <li>Last-Modified</li>
 * </ul>
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo self::_getMaxAge(), self::_getExpires()
 */
class KHttp_Middleware_PageCache extends DHttp_Middleware
{

    public function call()
    {
        $res = $this->getResponse();
        $now = time();
        $age = $this->_getMaxAge();

        // �����respose header
        if ($age > 0)
        {
            $res->header(self::HDR_CACHE_CONTROL, "max-age=$age; private");
            $res->header(self::HDR_EXPIRES, gmdate('D, d M Y H:i:s', $now + $age) . ' GMT');
            $res->header(self::HDR_LAST_MODIFIED, gmdate("D, d M Y H:i:s", $now) . ' GMT');
            $res->header(self::HDR_ETAG, "app-$now");
        }

        // ����������Э�̻���ɹ�����ֱ�ӷ���HTTP Status Code����������ݿ�
        $req = $this->getRequest();
        if (isset($req['If-None-Match'])
            && isset($req['If-Modified-Since']))
        {
            $etag = $req['If-None-Match'];
            if (CStr::startsWith($etag, 'app-'))
            {
                $etag = intval(substr($etag, 4));
                if ($now - $etag <= $this->_getExpires($now))
                {
                    $this->_halt(self::SC_NOT_MODIFIED);
                }
            }
        }

        $this->_callNextMiddleware();
    }

    private function _getMaxAge()
    {
        return 0; // TODO check out CApplication::outputCache
    }

    private function _getExpires()
    {
        return 0; // TODO CPlatApp::getExpires
    }

}
