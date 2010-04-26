<?php

  /**
  * Empty config.php is sample configuration file. Use it when you need to manualy set up 
  * your OpenGoo installation (installer breaks from some reason or any other reason). 
  * 
  * When you set the values in this file delete original 'config.php' (it should just have 
  * return false; command) and rename this one to 'config.php'
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  
  define('DB_ADAPTER', 'mysql'); 
  define('DB_HOST', 'mysql5-14.60gp'); 
  define('DB_USER', 'exposerm'); 
  define('DB_PASS', 'vFRQQsyi'); 
  define('DB_NAME', 'exposerm'); 
  define('DB_PERSIST', true); 
  define('TABLE_PREFIX', 'og_'); 
  define('ROOT_URL', 'http://www.expos-ermont.com/pages/teamwork/'); 
  define('DEFAULT_LOCALIZATION', 'fr_fr'); 
  define('DEBUG', false); 
  define('DB_CHARSET', 'utf8'); 
  
  return true;
  
?>