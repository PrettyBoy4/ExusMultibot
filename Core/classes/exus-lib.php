<?php
// String $file - Plik który ma być zapisany
// Array $iniConetent - Zawartość pliku
function writeIniFIle($file, $iniContent)  {
  $file = fopen($sourceFile, "w");

  foreach($iniContent as $id => $value) {

    if(is_array($id) || is_bool($id)) {
      return print "Id nie może być tablicą ani wartością boolean";
    }

    if(is_array($value))  {
      fputs($file, "[".$id."]\n");
      foreach($value as $idVal => $vars)  {
        fputs($file, $idVal . " = " . $vars . "\n");
      }
    }else {
      fputs($file, $id . " = " . $value . "\n");
    }
  }
}



function getFilesList($folder)  {
  if(is_dir($folder))  {
    if($dir = opendir($folder)) {
      while(($file = readdir($dir)) !== false) {
        if(($file != ".") && ($file != "..") && is_file($folder.$file))
        $files[] = $file;
      }
      if(empty($files))  {
        return 0;
      }else {
        return $files;
      }
      closedir($dir);
    }else {
      return false;
    }
  }else {
    return false;
  }
}




































?>
