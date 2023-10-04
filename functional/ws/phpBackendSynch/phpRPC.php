<?php

function getParams($request){
	if((!isset($request['methodCall']['params'])) || (!is_array($request['methodCall']['params']))){
		#If there are no parameters, return an empty array
		return array();
	}else{
		/*
		print("<pre>");
		print_r($request);
		print("</pre>");
		*/
		return $request['methodCall']['params']['param'];
	}
}

function getMethodName($methodCall){
	#returns the method name
	return $methodCall['methodCall']['methodName'];
}


?>
