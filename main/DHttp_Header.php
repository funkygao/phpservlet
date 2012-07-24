<?php
/**
 * HTTP Response Headers��������.
 *
 * Ϊ�˷���ʹ�ã�ʵ����{@link ArrayAccess} {@link Iterator}��{@link Countable}
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class DHttp_Header implements ArrayAccess, Iterator, Countable
{

    /**
     * HTTP response headers.
     *
     * @var array
     */
    protected $_headers;

    public function __construct($headers = array())
    {
        foreach ($headers as $name => $value)
        {
            $this[$name] = $value;
        }

        if (is_null($this->_headers))
        {
            $this->_headers = array();
        }
    }

    /**
     * Reset header.
     *
     */
    public function reset()
    {
        $this->_headers = array();
    }

    /**
     * Merge Headers.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     */
    public function merge($name, $value = null, $replace = true)
    {
        $name = $this->_normalize($name);

        if (!$replace && isset($this->_headers[$name]))
        {
            $this->_headers[$name] = implode("\n", array($this->_headers[$name], $value));
        }
        else
        {
            $this->_headers[$name] = $value;
        }
    }

    /**
     * @return array Array of string
     */
    public function toArray()
    {
        $ret = array();
        foreach($this->_headers as $name => $value)
        {
            $headValues = explode("\n", $value);
            foreach ($headValues as $val)
            {
                $ret[] = "$name: $val";
            }
        }

        return $ret;
    }

    /**
     * ��HEADER����ת���ɱ�׼��.
     *
     * 'contEnt-tYpe' -> 'Content-Type'
     *
     * �����ڼ���headerʱ���Ͳ��õ��Ĵ�Сд�ˣ�
     * <p>
     * ���Ǹ���Content-Type�����أ�����Content-type?
     * �����ԣ� �����ִ�Сд
     * </p>
     *
     * @param string $name
     *
     * @return string
     */
    protected function _normalize($name)
    {
        $norm = explode('-', strtolower(trim($name)));
        for ($i = 0, $count = count($norm); $i < $count; $i++)
        {
            $norm[$i] = ucfirst($norm[$i]);
        }

        return implode('-', $norm);
    }

    public function offsetExists($offset)
    {
        return isset($this->_headers[$this->_normalize($offset)]);
    }

    public function offsetGet($offset)
    {
        $name = $this->_normalize($offset);
        if (isset($this->_headers[$name]))
        {
            return $this->_headers[$name];
        }
        else
        {
            return null;
        }
    }

    public function offsetSet($offset, $value)
    {
        $name = $this->_normalize($offset);
        $this->_headers[$name] = $value;
    }

    public function offsetUnset($offset)
    {
        $name = $this->_normalize($offset);
        unset($this->_headers[$name]);
    }

    public function count()
    {
        return count($this->_headers);
    }

    public function rewind()
    {
        reset($this->_headers);
    }

    public function current()
    {
        return current($this->_headers);
    }

    public function key()
    {
        $key = key($this->_headers);
        return $this->_normalize($key);
    }

    public function next()
    {
        return next($this->_headers);
    }

    public function valid()
    {
        return current($this->_headers) !== false;
    }

}
