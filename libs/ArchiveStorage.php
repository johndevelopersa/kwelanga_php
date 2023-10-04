<?php
class ArchiveStorage {
	
	function compressArray($array) { 
		return(gzcompress(var_export($array,true),9)); 
  	}
  	
  	function decompressArray($content) { 
  		eval('$array='.(empty($content)?"array()":gzuncompress($content)).';'); return($array); 
  	}
  	
  	function compressObject($obj) { 
		return(gzcompress(serialize($obj),9)); 
  	}
  	
  	function decompressObject($content) { 
  		$obj = unserialize(gzuncompress($content));
  		return $obj; 
  	}
}
?>