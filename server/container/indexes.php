<?php

class Container_indexes {
  
  static public function getIndexDir() {
    return dirname(__FILE__) . '/../data/indexes';
  }

  /**
   * setIndex('users/name', 'kerphi', 'xxxxx-uid-xxxxx') 
   */
  static public function setIndex($path, $key, $value) {
    $path = self::getIndexDir().'/'.$path;
    $file = $path.'/'.urlencode($key);
    @mkdir($path, 0777, true);
    file_put_contents($file, $value);
  }
  
  /**
   * getIndex('users/name', 'kerphi')
   */
  static public function getIndex($path, $key) {
    $path = self::getIndexDir().'/'.$path;
    $file = $path.'/'.urlencode($key);
    if (!file_exists($file)) {
      return null;
    } else {
      return file_get_contents($file);
    }
  }
  
  /**
   * rmIndex('users/name', 'kerphi') 
   */
  static public function rmIndex($path, $key) {
    $path = self::getIndexDir().'/'.$path;
    $file = $path.'/'.urlencode($key);
    @unlink($file);
  }
}


