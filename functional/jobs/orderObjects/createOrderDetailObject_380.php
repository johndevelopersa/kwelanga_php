<?php

/*-------------------------------------------------------------
      CREATE ORDER LINES WITHOUT PRICES
  -----------------------------------------------------------*/

$orderLineItem = (new OmniSalesOrderLineItemObj)

   //->setBackOrders()
   //->setBarCode()
   //->setBinLocationCode()
   //->setBinLocationDescription()
   //->setCapturedBy()
   //->setCostPrice()
   //->setCostPricePer()
   //->setCredited()
   //->setDealGroupNo()
   //->setDealId()
   //->setDealNarrative()
   //->setDelivered()
   //->setDescription()
   //->setDiscount()
   //->setDiscountType()
   //->setDueDate()
   //->setExtCostPrice()            
   //->setExtDiscountValue()
   //->setExtPrice()
   //->setExtPriceExcl()
   //->setExtPriceIncl()            	
   //->setExtraInfo1()
   //->setExtraInfo2()
   //->setExtraInfo3()
   //->setExtraInfo4()
   //->setExtraInfo5()
   //->setExtraInfo6()
   //->setExtraInfo7()
   //->setExtraInfo8()
   //->setExtVolume()
   //->setExtWeight()
   //->setGoodStock()
   //->setGpPercent()
   //->setGrossProfit()
   //->setId()
   //->setInvoiced(0)  //OMNI to invoice document
   //->setJobNo()
   ->setLineNo($serow['line_no'])
   //->setLineType()
   //->setLocalVatValue()
   //->setMarkupPercent()
   //->setMeasure()
   //->setMeasureDescription()
   //->setMinLevel()
   //->setNoDiscount()
   //->setOnOrder()
   ->setOrdered($documentQty)
   //->setPack()
   //->setPrincipalInv()
   //->setProductGroupCode()
   //->setProductGroupDescription()
   //->setPromotionName()
   //->setQtyShort()
   ->setQuantity($documentQty)
   //->setRedundant()
   //->setReference()
   //->setReferenceNo()
   ->setRevenueAccCode('100113')
   //->setRevenueAccDescription()
   //->setSellingPrice(number_format($d['net_price'], 2, '.', ''))
   //->setSellingPricePer(1)
   //->setSequenceNo()
   //->setSerialNo()
   //->setSourceReferenceNo()
   //->setSourceType()
   //->setStockCategoryCode()
   //->setStockCategoryDescription()
   ->setStockCode($productCode)
   //setStockDescription($productDescription)
   //->setStockLevel()
   //->setStockType()
   //->setTaxType('Exclusive')
   //->setToCredit()
   //->setToDeliver()
   //->setToInvoice()
   //->setTrackingNo4()
   //->setUnitCostPrice()
   //->setUnitPrice()
   //->setUnitVolume()
   //->setUnitWeight()
   //->setUseGoodStock()
   //->setUseRedundant()
   //->setUseSerialNo()
   //->setUseTrackingNo4()
   //->setVatCode($vatCode)
   //->setVatRate(number_format($d['vat_rate'], 2, '.', ''))
   //->setVatValue(number_format($d['vat_amount'], 2, '.', ''))
   //->setWarehouse()
   //->setWarehouseDescription()
   //->setWarrantyPeriod()
    ;
   
   
   ?>