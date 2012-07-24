<?php
/**
 * 本框架的异常的基类.
 *
 * 各种异常都继承本类
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
     * 取得当前异常的类名.
     *
     * @return string
     */
    public final function getClassName()
    {
        return get_class($this);
    }

}