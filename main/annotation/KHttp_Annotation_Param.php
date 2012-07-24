<?php
/**
 * 控制{@link DHttp_Action}的页面参数的property annotation.
 *
 * 包括GET和POST的参数
 *
 * @package http
 * @subpackage annotation
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @see DHttp_Controller
 */
class KHttp_Annotation_Param extends Annotation
{

    /**
     * 该参数的数据类型.
     *
     * 最终会对应到 DHttp_Request::get${type}()方法名称上
     *
     * @var string e,g int|string
     */
    public $type;

    /**
     * 如果该参数没有传入，那么默认值是什么.
     *
     * @var mixed
     */
    public $default = null;

    /**
     * @var bool
     */
    public $decode = false;

    /**
     *
     * e,g 'email,required'
     *
     * @var string Class name of validators, seperated by ',' symbol
     *
     * @see DHttp_Controller::_validateParam()
     */
    public $validators;

}
