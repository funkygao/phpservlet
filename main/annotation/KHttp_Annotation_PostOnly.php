<?php
/**
 * ����{@link DHttp_Action}��ҳ���Ƿ������POST��method annotation.
 *
 * @package http
 * @subpackage annotation
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Annotation_PostOnly extends Annotation
{

    /**
     * �������HTTP POST����ô�Զ���ת���ĸ�url.
     *
     * @var string
     */
    public $redirectTo;

}
