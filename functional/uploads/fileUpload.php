<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    
    //Create new database object
    $dbConn  = new dbConnect(); 
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;

    $uploadDirectory = $ROOT."ftp/storagedocuments/";

    $errors = []; // Store errors here

    $fileExtensionsAllowed = ['xlsx','pdf','csv','txt']; // These will be the only file extensions allowed 

    $fileName = $_FILES['myfile']['name'];
    $fileSize = $_FILES['myfile']['size'];
    $fileTmpName  = $_FILES['myfile']['tmp_name'];
    $fileType = $_FILES['myfile']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName); 
    
    $uFileErrors = 'N';
    
    if($postUtasks <> 'Y') {
        $errors[] = "User not authorised to upload files";
        $uFileErrors = 'Y';    	
    }

    if (! in_array($fileExtension,$fileExtensionsAllowed) && $uFileErrors == 'N') {
        $errors[] = "This file extension is not allowed. Please upload a xlsx or pdf file";
    }

    if ($fileSize > 4000000 && $uFileErrors == 'N') {
        $errors[] = "File exceeds maximum size (4MB)";
    }

    if ($fileSize < 5 && $uFileErrors == 'N') {
        $errors[] = "File below Minimum size (5B)";
    }

    if ($uFileErrors == 'N') {

          $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

          if ($didUpload) {
            	// Add file details to Control Table - 
              $errors[] = "The file " . basename($fileName) . " has been uploaded";
              
              $TaskManDAO = new TaskManDAO($dbConn);
              $errorTO    = $TaskManDAO->insertIntoUploadIndex($postPrinID, $fileName, $postRepType, $postTuser,$transUid);
              $uplresult  = $errorTO;
              
              
          } else {
              $errors[] = "An error occurred. Please contact the administrator.";
        }
    }

?>