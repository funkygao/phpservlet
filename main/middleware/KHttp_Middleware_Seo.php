<?php
/**
 * ר��Ϊ���������Ż��������м��.
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

        // ��ȷ֪�������Լ��ڷ����Լ�������Ŀ���û�û�н�ֹSEO
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
