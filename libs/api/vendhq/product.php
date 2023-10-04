<?php

class VendHQProduct
{
    private $product_id;
    private $register_id;
    private $sequence;
    private $quantity;
    private $price;
    private $cost;
    private $price_set;
    private $discount;
    private $loyalty_value;
    private $tax;
    private $tax_id;
    private $status;

    public function setProductId(string $product_id)
    {
        $this->product_id = $product_id;
        return $this;
    }

    public function setRegisterId(string $register_id)
    {
        $this->register_id = $register_id;
        return $this;
    }

    public function setSequence(string $sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function setPrice(float $price)
    {
        $this->price = $price;
        return $this;
    }

    public function setCost(float $cost)
    {
        $this->cost = $cost;
        return $this;
    }

    public function setPriceSet(int $price_set)
    {
        $this->price_set = $price_set;
        return $this;
    }

    public function setDiscount(float $discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function setLoyaltyValue(float $loyalty_value)
    {
        $this->loyalty_value = $loyalty_value;
        return $this;
    }

    public function setTax(float $tax)
    {
        $this->tax = $tax;
        return $this;
    }

    public function setTaxId(string $taxId)
    {
        $this->tax_id = $taxId;
        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
