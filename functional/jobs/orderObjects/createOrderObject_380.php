<?php

echo "HHssssssssssssssssssssssssssssssssssssssssssssssssssssssssHHH";
echo "<br>";
    /*-------------------------------------------------------------
     *      CREATE ORDER OBJECT
     *-----------------------------------------------------------*/
    $omniOrder = (new OmniSalesOrderObj)
        ->setAnalysis4($storeSpecialValue)	//###
        //->setAreaCode()
        ->setBranchCode($branchCode)
        //->setCapturedBy()
        ->setCustomerAccountCode($storeSpecialValue)
        ->setCustomerOrderNo($customerOrderNumber)
        ->setDeliveryDay($deliveryDay)
        ->setDeliveryDetails($deliveryDetails)  //  *************
        //->setDeliveryRoute()
        ->setDocumentDate($invoiceDate)
        ->setDueDate($dueDeliveryDate)
        ->setExtraInfo3($incomingFile)
        //->setExtraInfo6()
        //->setExtraInfo7()
        //->setJobNo()
        //->setMemo()
        //->setOverallDiscountCode()
        //->setPhysicalAddress1($deliverName)
        //->setPhysicalAddress2($deliverAdd1)
        //->setPhysicalAddress3($deliverAdd2)
        //->setPhysicalAddress4($deliverAdd3)
        //->setPhysicalAddress5()
        //->setPostalAddress1($billName)
        //->setPostalAddress2($billAdd1)
        //->setPostalAddress3($billAdd2)
        //->setPostalAddress4($billAdd3)
        //->setPostalAddress5()
        //->setPostCode()
        ->setPrincipalinv($clientDocNumber)  //  ************
        ->setPrinciple($gdsPrin)
        ->setRepCode("")
        ->setRevenueAccCode($RevenueAccount)
        //->setSourceReference($sourceDocumentNumber)
        //->setSourceType($dataSource)
        ->setStoreName($deliverName)
        ->setVatRegistrationNo($buyeraccountreference)
        ->setWarehouseCode($depotSpecialValue);
        //->setAnalysis4();
    /*-------------------------------------------------------------
     *      ORDER
     *------------------------------------------------------------
     */     