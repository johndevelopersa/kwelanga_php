<?php

/* -----------------------------
 *   Document Update Processor
 * -----------------------------
 *
 * Global update process and posting
 *
 * created date : 2012.07.30
 * owner : onyx
 *
 * ----------------------------- */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDocumentUpdateDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostStockDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateDetailTO.php');


class ProcessorTOU {


  private $dbConn;
  private $docUpdateDAO;
  private $postStockDAO;


  function __construct($dbConn) {
    $this->dbConn = $dbConn;
    $this->docUpdateDAO = new PostDocumentUpdateDAO($dbConn);
    $this->postStockDAO = new PostStockDAO($dbConn);
  }


  public function postTOU($arrPostingTOUTO, $onlineFileProcessItem) {
  	
    $eTO = new ErrorTO;
    if(count($arrPostingTOUTO)==0 || !is_array($arrPostingTOUTO)){

      // allow empty as Vital confirmation files may not have any rows inside
      /*
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "Empty PostingTOU Array in ProcessorTOU!";
      return $eTO;
      */

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      return $eTO;

    } else {

      foreach ($arrPostingTOUTO as &$TO){

        $TO->fileLogUId = $onlineFileProcessItem['fileLogUId'];

        // ummmm .... really really bad, but unfortunately Vital send thru stock lines in the same file as Confirmations !!!
        if ($TO instanceof PostingStockTO) {
          $rTO = $this->postStockDAO->postStock($TO);
        } else {
          $rTO = $this->docUpdateDAO->postDocumentUpdate($TO);  //post to document update table.
        }

        if($rTO->type != FLAG_ERRORTO_SUCCESS){
          BroadcastingUtils::sendAlertEmail("Error in ProcessorTOU", $rTO->description, "Y", false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Failed to INSERT Document Update : " . $rTO->description;
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }
      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      return $eTO;

    }
  }
}



