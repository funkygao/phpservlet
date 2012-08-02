<?php
/**
 * Bootstrap.
 *
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 */

define('BASE_PATH', '/Users/apple/github/phpservlet');

final class DHttp_Bootstrap
{

    /**
     * @param string $class_name
     * @return bool
     */
    public static function autoload($class_name)
    {
        if ('K' == $class_name[0])
        {
            // 目录名去掉 "K"，并转换为小写字母
            $dir = strtolower(substr($class_name, 1, strpos($class_name, '_') - 1));

            $file = BASE_PATH . '/' . $dir . '/' . $class_name . '.php';
            require_once($file);
            return true;
        }
        else if ('D' == $class_name[0])
        {

        }

    }

    public static function bootstrap()
    {
        spl_autoload_register(array('DHttp_Bootstrap', 'autoload'));
    }

}

DHttp_Bootstrap::bootstrap();
