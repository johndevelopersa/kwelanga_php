<?php

include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once ($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingProductTO.php');
include_once ($ROOT . $PHPFOLDER . 'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

function GetRoot() {
  global $ROOT;
  return $ROOT;
}

function GetPHPFolder() {
  global $PHPFOLDER;
  return $PHPFOLDER;
}

class ProductCategoryController{
  
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
         
    //SHOW PRINCIPAL DROPDOWN
    if (in_array($this->DMLType,array('UPDATE','VIEW'))) $this->GetProductCategoryDD();    

    echo '<div id="', $this->divAjaxMainContentArea, '"></div>';
    
    echo '<script type="text/javascript" defer>
			function refreshProductCat(PROCATID) {
			PROCATID = (PROCATID===false) ? ("") : ("&PROCATID="+PROCATID);
				AjaxRefresh("USERID=', $this->UserID, '&DMLTYPE=', $this->DMLType, '"+PROCATID+"",
    				"', GetRoot(), GetPHPFolder(), 'functional/products/CategoryForm.php",
    			    "', $this->divAjaxMainContentArea, '",
    			    "Please wait whilst page is refreshed...",
    			    "");
			}
			refreshProductCat(false);
		 </script>';

  }

  public function GetProductCategoryDD() {
    
    echo '<BR>';
    echo '<FORM action="" method="post">';
    echo 'Product Category: </TD><TD>';
    
    $vValue = (isset($_POST['prinid'])) ? $_POST['prinid'] : '';
    $postPAGETYPE = (isset($_POST['PAGETYPE'])) ? ($_POST['PAGETYPE']) : (FLAG_STATUS_ACTIVE);
    
    BasicSelectElement::getPrincipalProductCategoryDD('', $vValue, '', '', 'refreshProductCat(this.options[this.selectedIndex].value)', '', '', $this->dbConn, $this->PrincipalID, $postPAGETYPE );
          
     ?>
    <script type="text/JavaScript" defer>
        
    function showStatus(val) {
    	getContent("<?php echo GetRoot(), GetPHPFolder(); ?>functional/products/modifyCategory.php","DMLTYPE=<?php echo $this->DMLType; ?>&PAGETYPE="+val);        
	}
		               
    </script>
    
    <?php   
    
    echo '<br />';
    echo "Show : <input type=radio value='".FLAG_STATUS_ACTIVE."' name='pStatus' onclick='showStatus(this.value);' ".(($postPAGETYPE==FLAG_STATUS_ACTIVE)?(" CHECKED "):(""))." >Active
				  <input type=radio name='pStatus' value='".FLAG_STATUS_DELETED."' onclick='showStatus(this.value);' ".(($postPAGETYPE==FLAG_STATUS_DELETED)?(" CHECKED "):(""))." >Deleted";


    echo '</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</TABLE><br />';
    echo '<INPUT type="hidden" name="Select" value="updateprincipal" />';
    echo '</FORM>';
  
  }

}

$ProductCategory = new ProductCategoryController();
$ProductCategory->dbConn->dbClose();

?>