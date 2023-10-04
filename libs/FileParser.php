<?php
include_once('ROOT.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

class FileParser {

	/**
	 * convert xml string to php array - useful to get a serializable value, able to handle HTML tags
	 *
	 * @param string $input
	 * @return array
	 * @author Mark
	 */
	public static function xmlToArray($input, $callback = null, $recurse = false) {
		 // NB ! This converts non utf-8 chars to ASCII which means they probably end up as "?" chars or the corresponding.
		 //			 The proper way of doing this would be to include the coding in the header xml version='1.0' encoding='ISO-8859-1'
		 // $data = ((!$recurse) && is_string($input))? simplexml_load_string( mb_convert_encoding ($input, 'ASCII'), 'SimpleXMLElement', LIBXML_NOCDATA): $input;

     $data = ((!$recurse) && is_string($input))? simplexml_load_string( $input, 'SimpleXMLElement', LIBXML_NOCDATA): $input;
		 if ($data instanceof SimpleXMLElement) $data = (array) $data;
		 if (is_array($data)) foreach ($data as &$item) $item = self::xmlToArray($item, $callback, true);
		 if (is_array($data)) foreach ($data as &$item) if ((is_array($item)) && (empty($item))) $item=""; // stop the simplexml_load_string from converting empty tags into an empty array which buggers up processing
		 return (!is_array($data) && is_callable($callback))? call_user_func($callback, $data): $data;
	}

	// use this to get dom errors if using DOMDocument
	public static function getDOMErrors() {
    	$str="";
		$errors = libxml_get_errors();
		foreach ($errors as $error) {
			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$str .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$str .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$str .= "<b>Fatal Error $error->code</b>: ";
					break;
			}
			$str.=trim($error->message)." on line <b>$error->line</b>\n";
		}
		libxml_clear_errors();

		return $str;
	}



        public static function classConstTokenizer($filePath){

          $tokens = token_get_all(file_get_contents($filePath));
          $const = array();

          foreach ($tokens as $idx => &$token) {
            if (is_array($token)) {
              if($token[0]==T_CONST) {
                  $const[$tokens[$idx+2][1]] = str_replace(array("'",'"'), array('',''), $tokens[$idx+6][1]);
              }
            }
          }
          return $const;
        }

}
?>
