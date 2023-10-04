<?php
// http://www.kwelangasolutions.co.za/kwelanga_system/scripts/phpScripts/backup.php

$root = "C:/www/live/";

$dir = "kwelanga_system" ;
$backupfolder = "temp_backup/";

if (is_dir($root.$backupfolder)){
  echo exec('C:\www\live\kwelanga_system\scripts\phpscripts\backscript\removebackdir.bat C:\www\live\temp_backup');
} 
if (!mkdir($root.$backupfolder)){
		echo "Cannot create backkup folder ...\n";
	return;
}

$backuparray = array();

if (is_dir($root.$dir)){
  $files0 = scandir($root.$dir);
}
// Create an array of the directory structure

$dir_struct =array($root."/".$dir);
// first Level
foreach ($files0 as $x => $x_value) {
	 $currdir = $dir;
	 if (trim($files0[$x]) != "." && trim($files0[$x]) != "..") {
	     $dfname = trim($files0[$x]);
	     if (is_dir($root."/".$currdir."/".$dfname)){
	     	  $newfolder = $root. $currdir."/".$dfname;	     	
	     	  array_push($dir_struct,$newfolder);
	     	  $nextfolder = 2;
	     }   	
   }
}
// Second Level
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files1 = scandir($dir_struct[$x]);
      foreach ($files1 as $x1 => $x_value1) {
    	  if (trim($files1[$x1]) != "." && trim($files1[$x1]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files1[$x1] )){
   	          if (!in_array($dir_struct[$x] ."/". $files1[$x1],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files1[$x1]);
	     	      }
	         }   	
        }
     }
   }
}
// Third Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files2 = scandir($dir_struct[$x]);
      foreach ($files2 as $x2 => $x_value2) {
    	  if (trim($files2[$x2]) != "." && trim($files2[$x2]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files2[$x2] )){
   	          if (!in_array($dir_struct[$x] ."/". $files2[$x2],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files2[$x2]);
	     	      }
	         }   	
        }
     }
   }
}     	
// Forth Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files3 = scandir($dir_struct[$x]);
      foreach ($files3 as $x3 => $x_value3) {
    	  if (trim($files3[$x3]) != "." && trim($files3[$x3]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files3[$x3] )){
   	          if (!in_array($dir_struct[$x] ."/". $files3[$x3],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files3[$x3]);
	     	      }
	         }   	
        }
     }
   }
}     	
// 5th Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files4 = scandir($dir_struct[$x]);
      foreach ($files4 as $x4 => $x_value4) {
    	  if (trim($files4[$x4]) != "." && trim($files4[$x4]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files4[$x4] )){
   	          if (!in_array($dir_struct[$x] ."/". $files4[$x4],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files4[$x4]);
	     	      }
	         }   	
        }
     }
   }
}
     	
// 6th Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files5 = scandir($dir_struct[$x]);
      foreach ($files5 as $x5 => $x_value5) {
    	  if (trim($files5[$x5]) != "." && trim($files5[$x5]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files5[$x5] )){
   	          if (!in_array($dir_struct[$x] ."/". $files5[$x5],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files5[$x5]);
	     	      }
	         }   	
        }
     }
   }
}     

// 7th Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files6 = scandir($dir_struct[$x]);
      foreach ($files6 as $x6 => $x_value6) {
    	  if (trim($files6[$x6]) != "." && trim($files6[$x6]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files6[$x6] )){
   	          if (!in_array($dir_struct[$x] ."/". $files6[$x6],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files6[$x6]);
	     	      }
	         }   	
        }
     }
   }
}     

// 8th Level         
foreach ($dir_struct as $x => $x_value) {
	  if (is_dir($dir_struct[$x])) {
      $files7 = scandir($dir_struct[$x]);
      foreach ($files7 as $x7 => $x_value7) {
    	  if (trim($files7[$x7]) != "." && trim($files7[$x7]) != "..") {
    	 	   if (is_dir($dir_struct[$x] ."/". $files7[$x7] )){
   	          if (!in_array($dir_struct[$x] ."/". $files7[$x7],$dir_struct)) {
	     	            array_push($dir_struct,$dir_struct[$x] ."/". $files7[$x7]);
	     	      }
	         }   	
        }
     }
   }
}     

foreach ($dir_struct as $x => $x_value) {
	      $cfiles = scandir($dir_struct[$x]);
        foreach ($cfiles as $f => $f_value) {
           if (!is_dir($dir_struct[$x] ."/". $cfiles[$f] )){
  	           $array_value = trim($cfiles[$f]);
   	           $fromdir = ($dir_struct[$x]);
	             $todir   = str_replace("C:/www/live/", "C:/www/live/temp_backup",$dir_struct[$x]);
               $backup_files = copy_files( $fromdir, $todir , $array_value);  
           }
       }

}     

function copy_files($fromdir, $todir, $array_value ) {
   	   
   	   $livefile = $fromdir."/".$array_value;
   	   $backfile  = $todir."/".$array_value;

   	   if (!is_dir($todir)){
              mkdir($todir);
       }
       
       echo "Copying .. " . $livefile . " to " . $backfile;
       echo "<br>";

       if (!copy($livefile, $backfile)) {
          echo "failed to copy". $livefile." ...\n";
       }

}
?>