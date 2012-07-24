<?php
/**
 * 专门为搜索引擎优化而做的中间件.
 *
 * SEO = Search Engine Optimization
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Middleware_Seo extends DHttp_Middleware
{

    public function call()
    {
        // don't forget about this
        $this->_prepareUserContext();

        // 明确知道不是自己在访问自己，而且目标用户没有禁止SEO
        if ($this->uid
            && $this->_uid != $this->uid
            && DSeo_Api::isForbidSEOUid($this->uid))
        {
            $this->_redirect(
                DSeo_Api::staticUrlTOProgram($this->getRequest()->getRequestUri())
            );
        }

        // setup seo title/meta
    }

}
