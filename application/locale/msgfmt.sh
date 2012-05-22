#!/usr/bin/php
<?php

function scan($dir) {
   $dir = new DirectoryIterator($dir);
   foreach ($dir as $po) {
      if ($po->isDir() && !$po->isDot()) {
         scan($po->getPathname());
      } elseif ($po->isFile() && ('.po' == substr($po->getFilename(), -3))) {
         try {
            $file_po = $po->getPathname();
            $file_mo = substr($po->getPathname(), 0, -3) . '.mo';
            @mkdir(dirname($file_mo), 0777, true);
            @chmod(dirname($file_mo), 0777);
            print "Processing file: $file_po ";
            if (false === system("msgfmt $file_po -o $file_mo")) {
               throw new Exception('Can\'t process file');
            }
            if (!file_exists($file_mo)) {
               touch($file_mo);
            }
            @chmod($file_mo, 0777);
            print '[ OK ]';
         } catch (Exception $e) {
            print '[ ERROR ]';
         }
         print "\n";
      }
   }
}
scan('./');
@system('rm -f ../cache/i18n/*');
@mkdir('../cache/i18n', 0777);
@chmod('../cache/i18n', 0777);

?>