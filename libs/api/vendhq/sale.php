<?php

class VendHQSale
{
    private $register_id;
    private $customer_id;
    private $user_id;
    private $sale_date;
    private $note;
    private $status;
    private $short_code;
    private $invoice_number;
    private $invoice_sequence;
    private $products = [];
    private $register_sale_payments;

    public function addProduct(VendHQProduct $products)
    {
        $this->products[] = $products;
        return $this;
    }

    public function addPayments($register_sale_payments)
    {
        $this->register_sale_payments = $register_sale_payments;
        return $this;
    }

    /**
     * @return string
     */
    public function asJSON(): string
    {
        return json_encode([
            'register_id' => $this->getRegisterId(),
            'customer_id' => $this->getCustomerId(),
            'user_id' => $this->getUserId(),
            'sale_date' => $this->getSaleDate(),
            'note' => $this->getNote(),
            'status' => $this->getStatus(),
            'short_code' => $this->getShortCode(),
            'invoice_number' => $this->getInvoiceNumber(),
            'invoice_sequence' => $this->getInvoiceSequence(),
            'register_sale_payments' => $this->getPayments(),
            'register_sale_products' => $this->getProductsArray(),
        ], JSON_PRETTY_PRINT);
    }

    /**
     * @return VendHQProduct[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return VendHQProduct[]
     */
    public function getProductsArray(): array
    {
        $products = [];
        foreach ($this->getProducts() as $product) {
            $products[] = $product->toArray();
        }
        return $products;
    }

    public function getRegisterId()
    {
        return $this->register_id;
    }

    //https://docs.vendhq.com/docs/sales_statuses

    public function setRegisterId($register_id)
    {
        $this->register_id = $register_id;
        return $this;
    }

    public function getCustomerId()
    {
        return $this->customer_id;
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getSaleDate()
    {
        return $this->sale_date;
    }

    public function setSaleDate($sale_date)
    {
        $this->sale_date = $sale_date;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getShortCode()
    {
        return $this->short_code;
    }

    public function setShortCode($short_code)
    {
        $this->short_code = $short_code;
        return $this;
    }

    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;
        return $this;
    }

    public function getInvoiceSequence()
    {
        return $this->invoice_sequence;
    }

    public function setInvoiceSequence($invoice_sequence)
    {
        $this->invoice_sequence = $invoice_sequence;
        return $this;
    }

    public function getPayments()
    {
        return $this->register_sale_payments;
    }
}