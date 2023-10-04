<?php


class DearSalesInvoiceObj extends DearBaseObj {

    public $SaleID; //String
    public $TaskID; //String
    public $CombineAdditionalCharges = false;
    public $Memo = ''; //String
    public $Status; //String
    public $InvoiceDate; //String   //2012-11-14T13:28:33.363
    public $InvoiceDueDate; //String //2012-11-14T13:28:33.363
    public $CurrencyConversionRate = 0;
    public $BillingAddressLine1 = '';
    public $BillingAddressLine2 = '';
    public $LinkedFulfillmentNumber = 0;
    public $Lines;  //array
    public $AdditionalCharges;

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
    public function getTaskID()
    {
        return $this->TaskID;
    }

    /**
     * @param mixed $TaskID
     */
    public function setTaskID($TaskID)
    {
        $this->TaskID = $TaskID;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCombineAdditionalCharges(): bool
    {
        return $this->CombineAdditionalCharges;
    }

    /**
     * @param bool $CombineAdditionalCharges
     */
    public function setCombineAdditionalCharges(bool $CombineAdditionalCharges)
    {
        $this->CombineAdditionalCharges = $CombineAdditionalCharges;
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
     * @return mixed
     */
    public function getInvoiceDate()
    {
        return $this->InvoiceDate;
    }

    /**
     * @param mixed $InvoiceDate
     */
    public function setInvoiceDate($InvoiceDate)
    {
        $this->InvoiceDate = $InvoiceDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceDueDate()
    {
        return $this->InvoiceDueDate;
    }

    /**
     * @param mixed $InvoiceDueDate
     */
    public function setInvoiceDueDate($InvoiceDueDate)
    {
        $this->InvoiceDueDate = $InvoiceDueDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrencyConversionRate(): int
    {
        return $this->CurrencyConversionRate;
    }

    /**
     * @param int $CurrencyConversionRate
     */
    public function setCurrencyConversionRate(int $CurrencyConversionRate)
    {
        $this->CurrencyConversionRate = $CurrencyConversionRate;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingAddressLine1(): string
    {
        return $this->BillingAddressLine1;
    }

    /**
     * @param string $BillingAddressLine1
     */
    public function setBillingAddressLine1(string $BillingAddressLine1)
    {
        $this->BillingAddressLine1 = $BillingAddressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingAddressLine2(): string
    {
        return $this->BillingAddressLine2;
    }

    /**
     * @param string $BillingAddressLine2
     */
    public function setBillingAddressLine2(string $BillingAddressLine2)
    {
        $this->BillingAddressLine2 = $BillingAddressLine2;
        return $this;
    }

    /**
     * @return int
     */
    public function getLinkedFulfillmentNumber(): int
    {
        return $this->LinkedFulfillmentNumber;
    }

    /**
     * @param int $LinkedFulfillmentNumber
     */
    public function setLinkedFulfillmentNumber(int $LinkedFulfillmentNumber)
    {
        $this->LinkedFulfillmentNumber = $LinkedFulfillmentNumber;
        return $this;
    }  //array


    /**
     * @return DearSalesInvoiceLineItemObj[]
     */
    public function getLines()
    {
        $lines = [];
        foreach($this->Lines as $line){
            $lines[] = new DearSalesInvoiceLineItemObj($line);
        }
        return $lines;
    }

    /**
     * @param mixed $Lines
     */
    public function addLine(DearSalesInvoiceLineItemObj $line)
    {
        $this->Lines[] = $line->getArray();
        return $this;
    }

    /**
     * @return DearSalesInvoiceAdditionalChargeItemObj[]
     */
    public function getAdditionalCharges()
    {
        $additionalCharges = [];
        foreach($this->AdditionalCharges as $line){
            $additionalCharges[] = new DearSalesInvoiceAdditionalChargeItemObj($line);
        }
        return $additionalCharges;
    }

    /**
     * @param mixed $AdditionalCharges
     */
    public function addAdditionalCharge(DearSalesInvoiceAdditionalChargeItemObj $AdditionalCharge)
    {
        $this->AdditionalCharges[] = $AdditionalCharge->getArray();
        return $this;
    }

    public function getArray()
    {
        $arr = get_object_vars($this);

        //ignore fields
        unset($arr['response']);
        unset($arr['data']);

        if(!$this->LinkedFulfillmentNumber > 0){
            unset($arr['LinkedFulfillmentNumber']);
        }

        return $arr;
    }
}
