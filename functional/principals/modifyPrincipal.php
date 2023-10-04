<?php

include_once ('ROOT.php');
include_once ($ROOT . 'PHPINI.php');
include_once ($ROOT . $PHPFOLDER . 'functional/main/access_control.php');
include_once ($ROOT . $PHPFOLDER . 'libs/common.php');
include_once ($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/PrincipalDAO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once ($ROOT . $PHPFOLDER . 'TO/PrincipalTO.php');
include_once ($ROOT . $PHPFOLDER . 'elements/basicSelectElement.php');
CommonUtils::getSystemConventions();


class PrincipalController{

  public $UserID;
  public $PrincipalID;
  public $dbConn;
  public $divAjaxMainContentArea = 'ajaxMainContentArea'; // the ajax divs. refreshed independently
  public $DMLType = 'VIEW';

  public function __construct() {

    //debug session
    //var_dump($_SESSION);

    $this->UserID = $_SESSION['user_id'];
    $this->PrincipalID = $_SESSION['principal_id'];
    $this->DMLType = (isset($_POST['DMLTYPE']) && in_array($_POST['DMLTYPE'], array('INSERT', 'UPDATE', 'VIEW'))) ? $_POST['DMLTYPE'] : 'VIEW';

    //Create DB link
    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();

    //Display Content
    $this->DisplayPage();
  }

  public function DisplayPage() {

    global $ROOT, $PHPFOLDER;

    //SHOW PRINCIPAL DROPDOWN
    if (in_array($this->DMLType,array('UPDATE','VIEW'))) $this->GetPrincipalDD();

    echo '<div id="', $this->divAjaxMainContentArea, '"></div>';

    echo '<script type="text/javascript" defer>
			function refreshPrincipal(PRINID) {
			PRINID = (PRINID===false) ? ("") : ("&PRINID="+PRINID);
				AjaxRefresh("USERID=', $this->UserID, '&DMLTYPE=', $this->DMLType, '"+PRINID+"",
    				"', $ROOT, $PHPFOLDER, 'functional/principals/principalForm.php",
    			    "', $this->divAjaxMainContentArea, '",
    			    "Please wait whilst page is refreshed...",
    			    "");
			}
			refreshPrincipal(false);
		 </script>';

  }

  public function GetPrincipalDD() {

    echo '<BR>';
    echo '<FORM action="" method="post">';
    echo '<TABLE>';
    echo '<thead><tr>';
    echo '<th colspan="2">', mb_convert_case($this->DMLType, MB_CASE_TITLE), ' '.SNC::principal.'</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    echo '<tr class="even">';
    echo '<td bgcolor="#87CEFA">Select '.SNC::principal.' to Update</TD><TD>';

    $vValue = (isset($_POST['prinid'])) ? $_POST['prinid'] : 0;
    BasicSelectElement::getUserPrincipalDD('', $vValue, '', '', 'refreshPrincipal(this.options[this.selectedIndex].value)', '', '', $this->dbConn, $_SESSION['user_id']);

    echo '</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</TABLE><br />';
    echo '<INPUT type="hidden" name="Select" value="updateprincipal" />';
    echo '</FORM>';

  }

}

$Principal = new PrincipalController();
$Principal->dbConn->dbClose();

?>