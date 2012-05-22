<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
     * Copy file in one directories to another
     *
     * @param string $source
     * @param string $path
     */
   function _copydir($source, $path) {
        if (is_dir( $path ) === false) {
           // echo 'Create: ', $path, PHP_EOL;
            _mkdir( $path );
        }
        $d = new DirectoryIterator($source);
        foreach ($d as $node) {
            /*@var $node DirectoryIterator*/
            if($node->isDot()) {
                continue;
            }
            if($node->isDir()===true) {
                if($node->getFilename()=='.svn') {
                    continue;
                }
                _copydir($node->getPathname(), $path . DIRECTORY_SEPARATOR . $node->getFilename());
                continue;
            }
            if($node->isFile()===true) {
                //echo 'Copy: ', $node->getPathname(), PHP_EOL;
                _copy($node->getPathname(),$path);
                continue;
            }
        }
    }

   function _copy($source, $path) {
        $filename = basename($source);
        copy($source, $path . DIRECTORY_SEPARATOR . $filename);
        chmod($path . DIRECTORY_SEPARATOR . $filename,0777);
    }

   function _mkdir($path) {
        mkdir($path,0777,true);
        chmod($path,0777);
    }

   function _rmdir($path) {
        if (is_dir( $path ) === false) {
           return;
        }
        $d = new DirectoryIterator($path);
        foreach ($d as $node) {
            /*@var $node DirectoryIterator*/
         if($node->isDot()) {
             continue;
         }
         if($node->isDir()) {
             _rmdir($node->getPathname());

             continue;
         }

         @unlink($node->getPathname());
        }
        @rmdir($path);
    }
    
   function _cleandir($path) {
        $d = new DirectoryIterator($path);
        foreach ($d as $node) {
            /*@var $node DirectoryIterator*/
         if($node->isDot()) {
             continue;
         }
         if($node->isDir()) {
             _rmdir($node->getPathname());

             continue;
         }

         @unlink($node->getPathname());
        }
    }    
    
    function _listdir($source) {
        $list = array();
        if (is_dir( $source ) !== false) {
        
        $d = new DirectoryIterator($source);
        foreach ($d as $node) {
            /*@var $node DirectoryIterator*/
            if($node->isDot()) {
                continue;
            }
            if($node->isDir()===true) {
                $list[] = $node->getFilename();
            }        
          }
        }
        
        return $list;
     }    
     
   function deleteDirectory($dir) {
      if (!file_exists($dir))
         return true;
      if (!is_dir($dir) || is_link($dir))
         return unlink($dir);
      foreach (scandir($dir) as $item) {
         if ($item == '.' || $item == '..')
            continue;
         if (!deleteDirectory($dir . "/" . $item)) {
            chmod($dir . "/" . $item, 0777);
            if (!deleteDirectory($dir . "/" . $item))
               return false;
         }
         ;
      }
      return rmdir($dir);
   } 
     

    