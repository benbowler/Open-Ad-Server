<?php

// setting variables
$cache = true;
$cachedir = realpath(dirname(__FILE__)) . '/system/cache/compress';
if (!file_exists($cachedir)) {
   mkdir($cachedir);
   chmod($cachedir, 0777);
}

// Determine the directory and file extension
$fn = isset($_GET['f']) ? $_GET['f'] : '';
$t = explode('.', $fn);
$ext = $t[count($t) - 1];
switch($ext) {
   case 'css':
      $type = 'css';
      break;
   case 'js':
      $type = 'javascript';
      break;
}

// Determine last modification date of the files
$lastmodified = filemtime($fn);

// Send Etag hash
$hash = $lastmodified . '-' . md5($fn);
header ("Etag: \"" . $hash . "\"");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') {
   // Return visit and no modifications, so do not send anything
   header ("HTTP/1.0 304 Not Modified");
   header ('Content-Length: 0');
} else {
   // First time visit or files were modified
   if ($cache) {
      // Determine supported compression method
      $enc = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
      $gzip = strstr($enc, 'gzip');
      $deflate = strstr($enc, 'deflate');

      // Determine used compression method
      $encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

      // Check for buggy versions of Internet Explorer
      if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
         preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
         $version = floatval($matches[1]);

         if ($version < 6) {
            $encoding = 'none';
         }

         if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) {
            $encoding = 'none';
         }
      }

      // Try the cache first to see if the compressed file is already generated
      $cachefile = 'cache-' . $hash . '.' . str_replace('/', '-', $fn) . ($encoding != 'none' ? '.' . $encoding : '');

      if (file_exists($cachedir . '/' . $cachefile)) {
         if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

            if ($encoding != 'none') {
               header ("Content-Encoding: " . $encoding);
            }

            header ("Content-Type: text/" . $type);
            header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));

            fpassthru($fp);
            fclose($fp);
            exit;
         }
      }
   }

   // Get contents of the files
   $content = file_get_contents($fn);
   // Send Content-Type
   header ("Content-Type: text/" . $type);

   if (isset($encoding) && $encoding != 'none') {
      //Send compressed contents
      $content = gzencode($content, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
      header ("Content-Encoding: " . $encoding);
      header ('Content-Length: ' . strlen($content));
      echo $content;
   } else {
      // Send regular contents
      header ('Content-Length: ' . strlen($content));
      echo $content;
   }

   // Store cache
   if ($cache) {
      if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
         fwrite($fp, $content);
         fclose($fp);
      }
   }
}
