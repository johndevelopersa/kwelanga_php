<?php


class DearSalesOrderObj extends DearBaseObj {

    public $SaleID; //String
    public $SaleOrderNumber; //Date
    public $Memo; //String
    public $Status; //String
    public $Lines;  //array
    public $AdditionalCharges;  //array
    public $TotalBeforeTax; //int
    public $Tax; //int
    public $Total;

    /**
     * @return mixed
     */
    public function getSaleID()
    {
        return $this->SaleID;
    }

    /**
     * @param mixed $SaleID
     */
    public function setSaleID($SaleID)
    {
        $this->SaleID = $SaleID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSaleOrderNumber()
    {
        return $this->SaleOrderNumber;
    }

    /**
     * @param mixed $SaleOrderNumber
     */
    public function setSaleOrderNumber($SaleOrderNumber)
    {
        $this->SaleOrderNumber = $SaleOrderNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->Memo;
    }

    /**
     * @param mixed $Memo
     */
    public function setMemo($Memo)
    {
        $this->Memo = $Memo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
        return $this;
    }

    /**
     * @return DearSalesOrderLineItemObj[]
     */
    public function getLines()
    {
        $lines = [];
        foreach($this->Lines as $line){
            $lines[] = new DearSalesOrderLineItemObj($line);
        }
        return $lines;
    }

    /**
     * @param mixed $Lines
     */
    public function addLine(DearSalesOrderLineItemObj $line)
    {
        $this->Lines[] = $line->getArray();
        return $this;
    }

    /**
     * @return DearSalesOrderAdditionalChargeItemObj[]
     */
    public function getAdditionalCharges()
    {
        $additionalCharges = [];
        foreach($this->AdditionalCharges as $line){
            $additionalCharges[] = new DearSalesOrderAdditionalChargeItemObj($line);
        }
        return $additionalCharges;
    }

    /**
     * @param mixed $AdditionalCharges
     */
    public function addAdditionalCharge(DearSalesOrderAdditionalChargeItemObj $AdditionalCharge)
    {
        $this->AdditionalCharges[] = $AdditionalCharge->getArray();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalBeforeTax()
    {
        return $this->TotalBeforeTax;
    }

    /**
     * @param mixed $TotalBeforeTax
     */
    public function setTotalBeforeTax($TotalBeforeTax)
    {
        $this->TotalBeforeTax = $TotalBeforeTax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->Tax;
    }

    /**
     * @param mixed $Tax
     */
    public function setTax($Tax)
    {
        $this->Tax = $Tax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->Total;
    }

    /**
     * @param mixed $Total
     */
    public function setTotal($Total)
    {
        $this->Total = $Total;
        return $this;
    } //int


}
