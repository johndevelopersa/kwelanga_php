<?php
/*
 * NOTE: THis was created to make E_NOTICE and E_WARNINGS to immediately HALT program execution.
 * 		 Failure to do so means that if you assign a boolean value to a variable and the function fails, it can either still return true/false
 * 		 or a non boolean value. It may still return true/false if the last call in your function returning value is a "return true" because other
 *       lines higher up in the function failed. eg $bool=isValid($x); if ($bool)...
 *
 */

if(!isset($_GET['disable_sessions'])) {
	if (!isset($_SESSION)) session_start();
}


//if($_SESSION["user_id"]!="1033") ExceptionThrower::Start(); // forces E_NOTICE and E_WARNINGS to halt program execution
// ExceptionThrower::Start();
class ExceptionThrower
{

	static $IGNORE_DEPRECATED = true;

	/**
	 * Start redirecting PHP errors
	 * @param int $level PHP Error level to catch (Default = E_ALL & ~E_DEPRECATED)
	 */
	static function Start($level = null)
	{

		if ($level == null)
		{
			if (defined("E_DEPRECATED"))
			{
				$level = E_ALL & ~E_DEPRECATED ;
			}
			else
			{
				// php 5.2 and earlier don't support E_DEPRECATED
				$level = E_ALL;
				self::$IGNORE_DEPRECATED = true;
			}
		}
		set_error_handler(array("ExceptionThrower", "HandleError"), $level);
		//register_shutdown_function('self::shutDownFunction');
	}

	/**
	 * Stop redirecting PHP errors
	 */
	static function Stop()
	{
		restore_error_handler();
	}

	/**
	 * Fired by the PHP error handler function.  Calling this function will
	 * always throw an exception unless error_reporting == 0.  If the
	 * PHP command is called with @ preceeding it, then it will be ignored
	 * here as well.
	 *
	 * @param string $code
	 * @param string $string
	 * @param string $file
	 * @param string $line
	 * @param string $context
	 */
	static function HandleError($code, $string, $file, $line, $context)
	{
		// ignore supressed errors
		if (error_reporting() == 0) return;
		if (self::$IGNORE_DEPRECATED && strpos($string,"deprecated") === true) return true;

		throw new Exception($string,$code);
	}

	static function shutDownFunction() {
	    $error = error_get_last();
	    if ($error['type'] == 1) {
	        echo "RT System Shutdown Message : <br>
				  This request could not be completed.<br><br>
				  It is possible your query resulted in too many rows. In such a case, perhaps try refining your parameters to return fewer rows such using a narrower date range.";
	    }
	}
}

?>
