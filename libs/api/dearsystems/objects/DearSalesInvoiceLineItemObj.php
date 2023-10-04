<?php


class DearSalesInvoiceLineItemObj extends DearBaseObj
{
    public $ProductID; //String
    public $SKU; //String
    public $Name; //String
    public $Quantity; //int
    public $Price; //int
    public $Discount = 0; //int
    public $Tax = 0; //int
    public $AverageCost = 0; //int
    public $TaxRule; //String
    public $Comment = ''; //String
    public $DropShip = false; //boolean
    public $BackorderQuantity = 0; //int
    public $Account = '';
    public $Total = 0;

    /**
     * @return string
     */
    public function getAccount(): string
    {
        return $this->Account;
    }

    /**
     * @param string $Account
     */
    public function setAccount(string $Account)
    {
        $this->Account = $Account;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSKU()
    {
        return $this->SKU;
    }

    /**
     * @param mixed $SKU
     */
    public function setSKU($SKU)
    {
        $this->SKU = $SKU;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
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
    public function getAverageCost()
    {
        return $this->AverageCost;
    }

    /**
     * @param mixed $AverageCost
     */
    public function setAverageCost($AverageCost)
    {
        $this->AverageCost = $AverageCost;
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
    }

    /**
     * @return mixed
     */
    public function getDropShip()
    {
        return $this->DropShip;
    }

    /**
     * @param mixed $DropShip
     */
    public function setDropShip($DropShip)
    {
        $this->DropShip = $DropShip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackorderQuantity()
    {
        return $this->BackorderQuantity;
    }

    /**
     * @param mixed $BackorderQuantity
     */
    public function setBackorderQuantity($BackorderQuantity)
    {
        $this->BackorderQuantity = $BackorderQuantity;
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

    public function getArray()
    {
        $arr = get_object_vars($this);

        //ignore fields
        unset($arr['response']);
        unset($arr['data']);


        return $arr;
    }

}