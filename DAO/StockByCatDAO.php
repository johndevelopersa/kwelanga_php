<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class StockByCatDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  
//*************************************************************START OF PROGRAM***************************************************************************************
public function Autosave($pruid, $cat, $count, $userUId){	
	
       $sql = "SELECT *
               FROM captured_stock cs
               WHERE cs.product_cat = " . mysqli_real_escape_string($this->dbConn->connection, $cat) ."
               AND   cs.product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $pruid) ."
               AND   cs.status = 'Y';";
       
       $cntCheck = $this->dbConn->dbGetAll($sql);
      
       If(count($cntCheck) != 0 ) {
             $sql = "UPDATE captured_stock cs SET cs.count = " . mysqli_real_escape_string($this->dbConn->connection, $count) ."
                     WHERE cs.product_cat = " . mysqli_real_escape_string($this->dbConn->connection, $cat) ."
                     AND   cs.product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $pruid) .";"; 
       
             $this->errorTO = $this->dbConn->processPosting($sql,"");

             if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description="Failed To Save Counts Contact (CS003) Kwelanga Support".$this->errorTO->description;
                    echo "<br>";
                    echo $sql;
                    echo "<br>";
                    return $this->errorTO;
             }        	
       	
       } else {
             $sql = "INSERT INTO captured_stock(captured_stock.product_uid,
                                                captured_stock.product_cat,
                                                captured_stock.count,
                                                captured_stock.user_code)
                     VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $pruid) .","
                               . mysqli_real_escape_string($this->dbConn->connection, $cat) .","
                               . mysqli_real_escape_string($this->dbConn->connection, $count) .","
                               . mysqli_real_escape_string($this->dbConn->connection, $userUId) .");";    
       
             $this->errorTO = $this->dbConn->processPosting($sql,"");

             if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description="Failed To Save Counts Contact (CS002) Kwelanga Support".$this->errorTO->description;
                    echo "<br>";
                    echo $sql;
                    echo "<br>";
                    return $this->errorTO;
             } 
       }      

       $this->dbConn->dbQuery("commit");
       return $this->errorTO; 			

}   
//***************************************************************Clear Auto Saved Data *********************************************************************************************  
public function AutoSaveClear($userUId,$cat){	
 
      $sql = "UPDATE captured_stock c SET c.`status` = 'N'
              WHERE user_code = ". mysqli_real_escape_string($this->dbConn->connection, $userUId) ."
              AND   product_cat = ". mysqli_real_escape_string($this->dbConn->connection, $cat) ."
              AND   c.status = 'Y';";    
       
      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed To Update Depots Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
      }
      $this->dbConn->dbQuery("commit");
      return $this->errorTO; 			
}
//*******************Get List Of Cat For Principal*********************************************************************************************  

  public function GetCat($principalId){
   
            $sql = "SELECT *
                    FROM principal_product_category ppc 
                    WHERE ppc.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
                    AND ppc.`status` = 'A'";

            $Categories = $this->dbConn->dbGetAll($sql);         
            return $Categories;	  	
  }
//*******************************************************************Get Product List*****************************************************************************************  
public function GetProducts($cat, $prinUid, $wareHouseCde) {
   
            $sql = "SELECT pp.uid,pp.product_code,pp.product_description
                    FROM principal_product pp
                    LEFT JOIN stock s ON s.principal_product_uid = pp.uid 
                                      AND s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $prinUid) ."
                                      AND s.depot_id     = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."
                    WHERE pp.major_category = ". mysqli_real_escape_string($this->dbConn->connection, $cat) ."
                    AND pp.`status` = 'A'
                    AND s.principal_product_uid IS NOT NULL 
                    ORDER BY pp.major_category, pp.product_code ";
            
            $Products = $this->dbConn->dbGetAll($sql);         
            return $Products;	  	
  }

//************************************************************************************************************************************************************
    public function getStockVariances($prinUid, $wareHouseCde, $ppUid, $lineCount, $selectVarType) {
    	
    	      if($selectVarType == 3) {
    	      	     $selVar = "AND ". $lineCount . " - s.closing > 0" ;
    	      } elseif($selectVarType == 2) {
    	      	     $selVar = "AND ". $lineCount . " - s.closing < 0" ;
    	      } else {
    	             $selVar = "";	
    	      }	     
    	
    	      $lnCount = mysqli_real_escape_string($this->dbConn->connection, $lineCount);
   
            $sql = "SELECT pp.uid AS 'ppUid',
                           pp.principal_uid,
                           s.depot_id,
                           pp.product_code,
                           pp.product_description,
                           pp.major_category,
                           s.closing,
                           ". mysqli_real_escape_string($this->dbConn->connection, $lineCount) ." AS 'count',
                           if(". $lineCount . " = s.closing,0,if(". $lineCount . " - s.closing > 0,1,2)) AS 'adjTyp'
                    FROM principal_product pp
                    LEFT JOIN stock s ON s.principal_product_uid = pp.uid 
                                      AND s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $prinUid) ."
                                      AND s.depot_id     = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."
                    WHERE pp.uid IN (". mysqli_real_escape_string($this->dbConn->connection, $ppUid) .")
                    " . $selVar . "
                    ORDER BY pp.major_category, pp.product_code ";
 
            $stkTots = $this->dbConn->dbGetAll($sql);         
            return $stkTots;	  	
    }
    
//************************************************************************************************************************************************************
public function GetAutoSavedData($userUId, $cat){
   
            $sql = "SELECT *
                    FROM captured_stock cs
                    WHERE cs.user_code = ". mysqli_real_escape_string($this->dbConn->connection, $userUId) ."
                    AND cs.product_cat = ". mysqli_real_escape_string($this->dbConn->connection, $cat) ."
                    AND   cs.status = 'Y'
                    ORDER BY cs.date_time desc";
            
            $Products = $this->dbConn->dbGetAll($sql);         
            return $Products;	  	
  }
//************************************************************************************************************************************************************
public function getProductCountSheet($cat, $prin, $dep) {

          $sql = "SELECT ppc.description AS 'Category',
                         pp.product_code AS 'ProductCode',
                         pp.product_description AS 'Product',
                         '' AS 'Count'
                  FROM .principal_product_category ppc
                  LEFT JOIN .principal_product pp ON pp.major_category = ppc.uid
                  LEFT JOIN stock s ON s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $prin) . " 
                                    AND s.depot_id = ". mysqli_real_escape_string($this->dbConn->connection, $dep) . "
                                    AND pp.uid = s.principal_product_uid
                  LEFT JOIN depot d ON d.uid = ". mysqli_real_escape_string($this->dbConn->connection, $dep) . "                
                  WHERE ppc.uid = ". mysqli_real_escape_string($this->dbConn->connection, $cat) . "
                  AND if(d.show_full_prod_list = 'N', s.principal_product_uid IS NOT NULL,1)   
                  ORDER BY pp.major_category, pp.product_code ";
                  
                  // echo $sql;

            $Products = $this->dbConn->dbGetAll($sql);         
            return $Products;	  	
}
//************************************************************************************************************************************************************
public function stockRolloverByProduct($count, $prn, $dep, $lineId) {
	
     $sql = "UPDATE stock s SET s.last_opening = s.opening,
                    s.last_count_date = s.stock_count_date,
                    s.opening = ". mysqli_real_escape_string($this->dbConn->connection, $count) . ",
                    s.stock_count = ". mysqli_real_escape_string($this->dbConn->connection, $count) . ",
                    s.arrivals = 0,
                    s.adjustment = 0,
                    s.returns_cancel = 0,
                    s.delivered = 0,
                    s.stock_count_date = NOW(),
                    s.data_generated_date = NOW()
             WHERE s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $prn) . "
             AND   s.depot_id = ". mysqli_real_escape_string($this->dbConn->connection, $dep) . "
             AND   s.principal_product_uid = ". mysqli_real_escape_string($this->dbConn->connection, $lineId) . ";"	;

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Roll Over line (RO001) " . "Contact Kwelanga Support";
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
      }
      $this->dbConn->dbQuery("commit");
      return $this->errorTO; 				
} 

//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
//***********************************************************END OF PROGRAM*************************************************************************************************
}
  