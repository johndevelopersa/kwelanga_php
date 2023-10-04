<?php


class DearSalesOrderAdditionalChargeItemObj extends DearBaseObj
{
    public $Description; //String
    public $Price; //int
    public $Quantity; //int
    public $Discount; //int
    public $Tax; //int
    public $Total; //int
    public $TaxRule; //String
    public $Comment;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param mixed $Description
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->Price;
    }

    /**
     * @param mixed $Price
     */
    public function setPrice($Price)
    {
        $this->Price = $Price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->Quantity;
    }

    /**
     * @param mixed $Quantity
     */
    public function setQuantity($Quantity)
    {
        $this->Quantity = $Quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->Discount;
    }

    /**
     * @param mixed $Discount
     */
    public function setDiscount($Discount)
    {
        $this->Discount = $Discount;
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
    }

    /**
     * @return mixed
     */
    public function getTaxRule()
    {
        return $this->TaxRule;
    }

    /**
     * @param mixed $TaxRule
     */
    public function setTaxRule($TaxRule)
    {
        $this->TaxRule = $TaxRule;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @param mixed $Comment
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
        return $this;
    } //String

}