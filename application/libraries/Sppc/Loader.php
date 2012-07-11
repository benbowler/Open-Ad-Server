<?php
/**
 * Class loader. We extend Zend_Loader with tryLoadClass
 * to rid off notices that file doesn't exist. We check
 * file and if it doesn't exist we throw an exception.
 * It's usefull for dynamic loading of classes that extends
 * functionality.
 *
 * @version $Id$
 */
require_once 'Zend/Loader.php';

class Sppc_Loader extends Zend_Loader {
    /**
     * Try to load class
     *
     * @param string $class
     * @param string|array $dirs
     * @throws Sppc_Loader_Exception
     */
    public static function tryLoadClass($class, $dirs = null) {
        $filePath = self::_classToFile( $class );
        $fileExist = false;
        foreach ( explode( PATH_SEPARATOR, get_include_path() ) as $dir ) {
            $dir = rtrim( $dir, '\\/' );
            if (file_exists( $dir . DIRECTORY_SEPARATOR . $filePath )) {
                $fileExist = true;
                break;
            }
        }
        if ($fileExist === false) {
            require_once 'Sppc/Loader/Exception.php';
            throw new Sppc_Loader_Exception( 'class ' . $class . ' is not found in ' . get_include_path() );
        }
        parent::loadClass( $class, $dirs );
    }
    /**
     * Build file path to class
     *
     * @param string $class
     * @return string
     */
    protected static function _classToFile($class) {
        // autodiscover the path from the class name
        $file = str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';
        return $file;
    }
}