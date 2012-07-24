<?php
/**
 * ����{@link DHttp_Action}��ҳ�������property annotation.
 *
 * ����GET��POST�Ĳ���
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
     * �ò�������������.
     *
     * ���ջ��Ӧ�� DHttp_Request::get${type}()����������
     *
     * @var string e,g int|string
     */
    public $type;

    /**
     * ����ò���û�д��룬��ôĬ��ֵ��ʲô.
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
