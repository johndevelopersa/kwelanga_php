<?php

class PostingDocumentUpdateDetailTO
{
    public $ddUId;
    public $documentUpdateUId;
    public $productUId = 0;
    public $productCode;
    public $lineNo;
    public $pageNo;
    public $orderedQty = 0;
    public $documentQty = 0;
    public $deliveredQty = 0;
    public $podReasonLookup;
    public $podReasonUid = 0;

    // pricing
    public $listPrice = 0;
    public $discountValue = 0;
    public $nettPrice = 0;
    public $extendedPrice = 0;
    public $vatAmount = 0;
    public $vatRate = 0;
    public $total = 0;

    /**
     * @param mixed $ddUId
     * @return PostingDocumentUpdateDetailTO
     */
    public function setDdUId($ddUId)
    {
        $this->ddUId = $ddUId;
        return $this;
    }

    /**
     * @param mixed $documentUpdateUId
     * @return PostingDocumentUpdateDetailTO
     */
    public function setDocumentUpdateUId($documentUpdateUId)
    {
        $this->documentUpdateUId = $documentUpdateUId;
        return $this;
    }

    /**
     * @param int $productUId
     * @return PostingDocumentUpdateDetailTO
     */
    public function setProductUId(int $productUId): PostingDocumentUpdateDetailTO
    {
        $this->productUId = $productUId;
        return $this;
    }

    /**
     * @param mixed $productCode
     * @return PostingDocumentUpdateDetailTO
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
        return $this;
    }

    /**
     * @param mixed $lineNo
     * @return PostingDocumentUpdateDetailTO
     */
    public function setLineNo($lineNo)
    {
        $this->lineNo = $lineNo;
        return $this;
    }

    /**
     * @param mixed $pageNo
     * @return PostingDocumentUpdateDetailTO
     */
    public function setPageNo($pageNo)
    {
        $this->pageNo = $pageNo;
        return $this;
    }

    /**
     * @param int $orderedQty
     * @return PostingDocumentUpdateDetailTO
     */
    public function setOrderedQty(int $orderedQty): PostingDocumentUpdateDetailTO
    {
        $this->orderedQty = $orderedQty;
        return $this;
    }

    /**
     * @param int $documentQty
     * @return PostingDocumentUpdateDetailTO
     */
    public function setDocumentQty(int $documentQty): PostingDocumentUpdateDetailTO
    {
        $this->documentQty = $documentQty;
        return $this;
    }

    /**
     * @param int $deliveredQty
     * @return PostingDocumentUpdateDetailTO
     */
    public function setDeliveredQty(int $deliveredQty): PostingDocumentUpdateDetailTO
    {
        $this->deliveredQty = $deliveredQty;
        return $this;
    }

    /**
     * @param mixed $podReasonLookup
     * @return PostingDocumentUpdateDetailTO
     */
    public function setPodReasonLookup($podReasonLookup)
    {
        $this->podReasonLookup = $podReasonLookup;
        return $this;
    }

    /**
     * @param int $podReasonUid
     * @return PostingDocumentUpdateDetailTO
     */
    public function setPodReasonUid(int $podReasonUid): PostingDocumentUpdateDetailTO
    {
        $this->podReasonUid = $podReasonUid;
        return $this;
    }

    /**
     * @param int $listPrice
     * @return PostingDocumentUpdateDetailTO
     */
    public function setListPrice(int $listPrice): PostingDocumentUpdateDetailTO
    {
        $this->listPrice = $listPrice;
        return $this;
    }

    /**
     * @param int $discountValue
     * @return PostingDocumentUpdateDetailTO
     */
    public function setDiscountValue(int $discountValue): PostingDocumentUpdateDetailTO
    {
        $this->discountValue = $discountValue;
        return $this;
    }

    /**
     * @param int $nettPrice
     * @return PostingDocumentUpdateDetailTO
     */
    public function setNettPrice(int $nettPrice): PostingDocumentUpdateDetailTO
    {
        $this->nettPrice = $nettPrice;
        return $this;
    }

    /**
     * @param int $extendedPrice
     * @return PostingDocumentUpdateDetailTO
     */
    public function setExtendedPrice(int $extendedPrice): PostingDocumentUpdateDetailTO
    {
        $this->extendedPrice = $extendedPrice;
        return $this;
    }

    /**
     * @param int $vatAmount
     * @return PostingDocumentUpdateDetailTO
     */
    public function setVatAmount(int $vatAmount): PostingDocumentUpdateDetailTO
    {
        $this->vatAmount = $vatAmount;
        return $this;
    }

    /**
     * @param int $vatRate
     * @return PostingDocumentUpdateDetailTO
     */
    public function setVatRate(int $vatRate): PostingDocumentUpdateDetailTO
    {
        $this->vatRate = $vatRate;
        return $this;
    }

    /**
     * @param int $total
     * @return PostingDocumentUpdateDetailTO
     */
    public function setTotal(int $total): PostingDocumentUpdateDetailTO
    {
        $this->total = $total;
        return $this;
    }
}
