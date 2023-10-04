<?php

class DearSaleBillingAddressObj extends DearBaseObj
{
    public $Line1; //String
    public $Line2; //String
    public $City; //String
    public $State; //String
    public $Postcode; //String
    public $Country;

    /**
     * @return mixed
     */
    public function getLine1()
    {
        return $this->Line1;
    }

    /**
     * @param mixed $Line1
     */
    public function setLine1($Line1)
    {
        $this->Line1 = $Line1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLine2()
    {
        return $this->Line2;
    }

    /**
     * @param mixed $Line2
     */
    public function setLine2($Line2)
    {
        $this->Line2 = $Line2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->City;
    }

    /**
     * @param mixed $City
     */
    public function setCity($City)
    {
        $this->City = $City;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->State;
    }

    /**
     * @param mixed $State
     */
    public function setState($State)
    {
        $this->State = $State;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->Postcode;
    }

    /**
     * @param mixed $Postcode
     */
    public function setPostcode($Postcode)
    {
        $this->Postcode = $Postcode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->Country;
    }

    /**
     * @param mixed $Country
     */
    public function setCountry($Country)
    {
        $this->Country = $Country;
        return $this;
    } //String


}