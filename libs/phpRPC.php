<?php

class PhpRPC {

	public static function getParams($request){
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
	
	public static function getMethodName($methodCall){
		#returns the method name
		return $methodCall['methodCall']['methodName'];
	}
	
	public static function encodeData($methodName,$params){
		$data["methodCall"]["methodName"] = $methodName;
		$param_count = count($params);
		if(!$param_count){
			$data["methodCall"]["params"] = NULL;
		}else{
			for($n = 0; $n<$param_count; $n++){
				$data["methodCall"]["params"]["param"][$n]["value"] = $params[$n];
			}
		}
		
		return $data;
	}

}
?>
