<?php
// http://www.kwelangasolutions.co.za/kwelanga_system/scripts/phpScripts/backup.php

$root = "C:/www/live/";

$dir = "kwelanga_system/" ;
$backupfolder = "temp_backup/";

if (is_dir($root.$backupfolder)){
  echo exec('C:\www\live\kwelanga_system\scripts\phpscripts\backscript\removebackdir.bat');
} else {
	if (!mkdir($root.$backupfolder)){
		echo "Cannot create backkup folder ...\n";
		return;
	}
}
$backuparray = array();

if (is_dir($root.$dir)){
  $files0 = scandir($root.$dir);
}

foreach ($files0 as $x => $x_value) {
	 $currdir = $dir;
	 if (trim($files0[$x]) != "." && trim($files0[$x]) != "..") {
	     $nextdir = trim($files0[$x]);
	     if (is_dir($root. $currdir .$nextdir)){
	         $files1 = scandir($root.$currdir .$nextdir);
	         // First Level
	         echo "<pre>";
	         print_r($files1);
	         echo "</pre>";
	         foreach ($files1 as $x => $x_value) {
	         		 if (trim($files1[$x]) != "." && trim($files1[$x]) != "..") {
	         	       $nextdir1 = trim($files1[$x]);
//	         	       echo ($root. $currdir . $nextdir."/".$nextdir1);
	                 if (is_dir($root. $currdir . $nextdir."/".$nextdir1)){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
	                 	 $files2 = scandir($root.$currdir.$nextdir);
	         echo "<pre>";
	         print_r($files2);
	         echo "</pre>";
           	         foreach ($files2 as $x => $x_value) {
	         		         if (trim($files2[$x]) != "." && trim($files2[$x]) != "..") {
	         	              $nextdir2 = trim($files2[$x]);
	         	              echo "<br>";
	         	              echo "HERE";
	         	              echo ($root. $currdir . $nextdir."/".$nextdir1."/".$nextdir2);
	                        echo "<br>";
	                        if (is_dir($root.$currdir.$nextdir."/".$nextdir1."/".$nextdir2)){
	                           echo $nextdir2; 
      	                     echo "1";
	                          echo '<br>';
	                        } else {
//****************************************************************
	           	              $array_value = trim($files2[$x]);
	           	              $fromdir = ($root.$currdir.$nextdir."/".$nextdir1."/".$nextdir2);
	           	              $todir   = ($root.$backupfolder.$currdir.$nextdir."/".$nextdir1."/".$nextdir2);
                            $backup_files = copy_files( $fromdir, $todir , $array_value);   
                   }             
//****************************************************************
               }
                     }  
 
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
	                 } else {
//****************************************************************
	           	         $array_value = trim($files1[$x]);
	           	         $fromdir = ($root.$currdir.$nextdir);
	           	         $todir   = ($root.$backupfolder.$currdir.$nextdir);
                       $backup_files = copy_files( $fromdir, $todir , $array_value);   
                   }             
//****************************************************************
               }
           }  
	         
	         
	         
	         	         
       } else {
	          $array_value = trim($files0[$x]);
	          $fromdir = ($root.$currdir);
	          $todir   = ($root.$backupfolder.$currdir);
            $backup_files = copy_files( $fromdir, $todir , $array_value);   
       }
   }
}



function copy_files($fromdir, $todir, $array_value ) {
   	   
   	   $livefile = $fromdir."/".$array_value;
   	   $backfile  = $todir."/".$array_value;
       echo "<br>";
   	   echo $todir;
   	   echo "<br>";
   	   if (!is_dir($todir)){
              mkdir($todir);
       }

       if (!copy($livefile, $backfile)) {
          echo "failed to copy". $livefile." ...\n";
       }

}


?>