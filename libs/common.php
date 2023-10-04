<?php
	function getPostValue( $name, $default=null ) {

		if( isSet( $_POST[$name] ) ) {
			if (get_magic_quotes_gpc()) {
				return stripSlashes( $_POST[$name] );
			} else {
				return $_POST[$name];
			}
		} else {
			return $default;
		}
	}

	function load_var ($varname) {
		 eval ("global $" . $varname . ";");
		 if (isset ($_REQUEST["$varname"])){
   			eval ("$" . $varname . " = \$_REQUEST['" . $varname . "'];");
  		}
		else {
   			eval ("$" . $varname . " = '';");
  		}
	}



?>