<?php
/**
 * ����ܵ��쳣�Ļ���.
 *
 * �����쳣���̳б���
 *
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class DHttp_Exception extends Exception
{

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getClassName() . " [{$this->code}]: {$this->message}";
    }

    /**
     * ȡ�õ�ǰ�쳣������.
     *
     * @return string
     */
    public final function getClassName()
    {
        return get_class($this);
    }

}