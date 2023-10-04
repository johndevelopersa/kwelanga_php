<?php


class DearSaleObj extends DearBaseObj
{
    public $ID; //String
    public $CustomerID; //String
    public $Customer; //String
    public $Contact; //String
    public $Phone; //Date
    public $Email; //String
    public $DefaultAccount; //String
    public $SkipQuote; //Bool

    public $BillingAddress; //BillingAddress
    public $ShippingAddress; //ShippingAddress
    public $ShippingNotes; //String
    public $TaxRule; //String
    public $TaxInclusive; //String
    public $Terms; //String
    public $PriceTier; //Date
    public $Location; //String
    public $Note; //String
    public $CustomerReference; //String
    public $AutoPickPackShipMode; //String
    public $SalesRepresentative; //String
    public $Carrier; //String
    public $CurrencyRate; //String
    public $AdditionalAttributes; //AdditionalAttributes
    public $ShipBy; //Date
    public $SaleOrderDate;

    public $Order;

    /**
     * @return mixed
     */
    public function getOrder() : DearSalesOrderObj
    {
        return new DearSalesOrderObj($this->Order);
    }

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return mixed
     */
    public function getCustomerID()
    {
        return $this->CustomerID;
    }

    /**
     * @param mixed $CustomerID
     */
    public function setCustomerID($CustomerID)
    {
        $this->CustomerID = $CustomerID;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * @param mixed $Customer
     */
    public function setCustomer($Customer)
    {
        $this->Customer = $Customer;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->Contact;
    }

    /**
     * @param mixed $Contact
     */
    public function setContact($Contact)
    {
        $this->Contact = $Contact;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->Phone;
    }

    /**
     * @param mixed $Phone
     */
    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @param mixed $Email
     */
    public function setEmail($Email)
    {
        $this->Email = $Email;
    }

    /**
     * @return mixed
     */
    public function getDefaultAccount()
    {
        return $this->DefaultAccount;
    }

    /**
     * @param mixed $DefaultAccount
     */
    public function setDefaultAccount($DefaultAccount)
    {
        $this->DefaultAccount = $DefaultAccount;
    }

    /**
     * @return mixed
     */
    public function getSkipQuote()
    {
        return $this->SkipQuote;
    }

    /**
     * @param mixed $SkipQuote
     */
    public function setSkipQuote($SkipQuote)
    {
        $this->SkipQuote = $SkipQuote;
    }

    /**
     * @return mixed
     */
    public function getBillingAddress(): DearSaleBillingAddressObj
    {
        return new DearSaleBillingAddressObj($this->BillingAddress);
    }

    /**
     * @param mixed $BillingAddress
     */
    public function setBillingAddress(DearSaleBillingAddressObj $BillingAddress)
    {
        $this->BillingAddress = $BillingAddress->getArray();
    }

    /**
     * @return mixed
     */
    public function getShippingAddress(): DearSaleShippingAddressObj
    {
        return new DearSaleShippingAddressObj($this->ShippingAddress);
    }

    /**
     * @param mixed $ShippingAddress
     */
    public function setShippingAddress(DearSaleShippingAddressObj $ShippingAddress)
    {
        $this->ShippingAddress = $ShippingAddress->getArray();
    }

    /**
     * @return mixed
     */
    public function getShippingNotes()
    {
        return $this->ShippingNotes;
    }

    /**
     * @param mixed $ShippingNotes
     */
    public function setShippingNotes($ShippingNotes)
    {
        $this->ShippingNotes = $ShippingNotes;
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
    }

    /**
     * @return mixed
     */
    public function getTaxInclusive()
    {
        return $this->TaxInclusive;
    }

    /**
     * @param mixed $TaxInclusive
     */
    public function setTaxInclusive($TaxInclusive)
    {
        $this->TaxInclusive = $TaxInclusive;
    }

    /**
     * @return mixed
     */
    public function getTerms()
    {
        return $this->Terms;
    }

    /**
     * @param mixed $Terms
     */
    public function setTerms($Terms)
    {
        $this->Terms = $Terms;
    }

    /**
     * @return mixed
     */
    public function getPriceTier()
    {
        return $this->PriceTier;
    }

    /**
     * @param mixed $PriceTier
     */
    public function setPriceTier($PriceTier)
    {
        $this->PriceTier = $PriceTier;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->Location;
    }

    /**
     * @param mixed $Location
     */
    public function setLocation($Location)
    {
        $this->Location = $Location;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->Note;
    }

    /**
     * @param mixed $Note
     */
    public function setNote($Note)
    {
        $this->Note = $Note;
    }

    /**
     * @return mixed
     */
    public function getCustomerReference()
    {
        return $this->CustomerReference;
    }

    /**
     * @param mixed $CustomerReference
     */
    public function setCustomerReference($CustomerReference)
    {
        $this->CustomerReference = $CustomerReference;
    }

    /**
     * @return mixed
     */
    public function getAutoPickPackShipMode()
    {
        return $this->AutoPickPackShipMode;
    }

    /**
     * @param mixed $AutoPickPackShipMode
     */
    public function setAutoPickPackShipMode($AutoPickPackShipMode)
    {
        $this->AutoPickPackShipMode = $AutoPickPackShipMode;
    }

    /**
     * @return mixed
     */
    public function getSalesRepresentative()
    {
        return $this->SalesRepresentative;
    }

    /**
     * @param mixed $SalesRepresentative
     */
    public function setSalesRepresentative($SalesRepresentative)
    {
        $this->SalesRepresentative = $SalesRepresentative;
    }

    /**
     * @return mixed
     */
    public function getCarrier()
    {
        return $this->Carrier;
    }

    /**
     * @param mixed $Carrier
     */
    public function setCarrier($Carrier)
    {
        $this->Carrier = $Carrier;
    }

    /**
     * @return mixed
     */
    public function getCurrencyRate()
    {
        return $this->CurrencyRate;
    }

    /**
     * @param mixed $CurrencyRate
     */
    public function setCurrencyRate($CurrencyRate)
    {
        $this->CurrencyRate = $CurrencyRate;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttributes()
    {
        return $this->AdditionalAttributes;
    }

    /**
     * @param mixed $AdditionalAttributes
     */
    public function setAdditionalAttributes($AdditionalAttributes)
    {
        $this->AdditionalAttributes = $AdditionalAttributes;
    }

    /**
     * @return mixed
     */
    public function getShipBy()
    {
        return $this->ShipBy;
    }

    /**
     * @param mixed $ShipBy
     */
    public function setShipBy($ShipBy)
    {
        $this->ShipBy = $ShipBy;
    }

    /**
     * @return mixed
     */
    public function getSaleOrderDate()
    {
        return $this->SaleOrderDate;
    }

    /**
     * @param mixed $SaleOrderDate
     */
    public function setSaleOrderDate($SaleOrderDate)
    {
        $this->SaleOrderDate = $SaleOrderDate;
    } //Date



}