<?php
/**
 * 专门负责浏览器协商机制的页面缓存的中间件.
 *
 * 其实，就是负责如下response header:
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

        // 先输出respose header
        if ($age > 0)
        {
            $res->header(self::HDR_CACHE_CONTROL, "max-age=$age; private");
            $res->header(self::HDR_EXPIRES, gmdate('D, d M Y H:i:s', $now + $age) . ' GMT');
            $res->header(self::HDR_LAST_MODIFIED, gmdate("D, d M Y H:i:s", $now) . ' GMT');
            $res->header(self::HDR_ETAG, "app-$now");
        }

        // 如果与浏览器协商缓存成功，则直接返回HTTP Status Code，不输出内容咯
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
