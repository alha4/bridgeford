<?php
namespace Cian;

class Logger {

 private static $DEFAULT_LOG_FILE = '/log.txt';

 private static $MAX_LOG_SIZE = 1;

 public static function log($mess) { 

   $path = constant("LOG_PATH")  ? $_SERVER['DOCUMENT_ROOT'].constant("LOG_PATH") : $_SERVER['DOCUMENT_ROOT'].self::$DEFAULT_LOG_FILE;

   $file_size = (int)(filesize($path) / 1024 / 1024);

   $max_log_size = constant("MAX_LOG_SIZE") ? : self::$MAX_LOG_SIZE;
   
   if($file_size > $max_log_size) {
    
     file_put_contents($path, '');

   }

   file_put_contents($path, print_r($mess,1).'|'.date("d.m.Y H:i:s")."\r\n",FILE_APPEND);
 
 }

}