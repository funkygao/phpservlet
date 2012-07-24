<?php
/**
 * 用于模版变量替换的自动化.
 *
 * Object Graph Navigation Language[xworks]
 *
 * @package http
 * @subpackage annotation
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @see KHttp_Result_Smarty
 */
class KHttp_Annotation_Ognl extends Annotation
{

    /**
     * 传递到模版里的变量名称.
     *
     * 默认是根据方法名称自动计算的
     *
     * @var string
     */
    public $name = null;

}