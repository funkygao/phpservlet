<?php
/**
 * 控制{@link DHttp_Action}的页面是否仅允许POST的method annotation.
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
     * 如果不是HTTP POST，那么自动跳转到哪个url.
     *
     * @var string
     */
    public $redirectTo;

}
