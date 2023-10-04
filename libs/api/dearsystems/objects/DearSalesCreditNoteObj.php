<?php


class DearSalesCreditNoteObj extends DearBaseObj
{

    public $SaleID; //String
    public $TaskID; //String
    public $CombineAdditionalCharges = false;
    public $CreditNoteInvoiceNumber = '';
    public $Memo = ''; //String
    public $Status; //String
    public $CreditNoteDate; //String   //2012-11-14T13:28:33.363
    public $CreditNoteConversionRate = 0;
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
     * @return string
     */
    public function getCreditNoteInvoiceNumber(): string
    {
        return $this->CreditNoteInvoiceNumber;
    }

    /**
     * @param string $CreditNoteInvoiceNumber
     */
    public function setCreditNoteInvoiceNumber(string $CreditNoteInvoiceNumber)
    {
        $this->CreditNoteInvoiceNumber = $CreditNoteInvoiceNumber;
        return $this;
    }

    /*
     * @return string
     */
    public function getMemo(): string
    {
        return $this->Memo;
    }

    /**
     * @param string $Memo
     */
    public function setMemo(string $Memo)
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
    public function getCreditNoteDate()
    {
        return $this->CreditNoteDate;
    }

    /**
     * @param mixed $CreditNoteDate
     */
    public function setCreditNoteDate($CreditNoteDate)
    {
        $this->CreditNoteDate = $CreditNoteDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditNoteConversionRate(): int
    {
        return $this->CreditNoteConversionRate;
    }

    /**
     * @param int $CreditNoteConversionRate
     */
    public function setCreditNoteConversionRate(int $CreditNoteConversionRate)
    {
        $this->CreditNoteConversionRate = $CreditNoteConversionRate;
        return $this;
    }

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

        return $arr;
    }
}
