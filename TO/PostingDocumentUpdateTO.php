<?php

class PostingDocumentUpdateTO
{
    public $udUId;  //uid
    public $documentMasterUid;  // document master that is updated
    public $sourceDocumentMasterUid;  // document master of the document that caused this event
    public $documentUpdatedFlag;
    public $updateTypeUid;
    public $additionalType;
    public $processedDatetime;
    public $processedStatus;
    public $processedMsg;
    public $principalUId = 0;
    public $principalLookup;
    public $depotLookup;
    public $depotUId = 0;
    public $skipDepotUpdate = "N"; // update the psm depot uid or not
    public $documentNumber;
    public $sourceDocumentNumber;
    public $createdDatetime;
    public $mergeDate = '0000-00-00';
    public $mergeTime = '00:00:00';
    public $incomingFilename;
    public $documentStatusLookup;
    public $documentStatusUId = 0;
    public $fileLogUId;
    public $deliveryDayUId;
    public $documentTypeUId;
    public $documentSourceOptional = "N";
    public $reference = "";
    public $claimNumber = "";
    public $additionalDetails = "";
    public $grvNumber = "";
    public $dueDeliveryDate = '0000-00-00'; //predicted del date.
    public $pages;
    //invoice fields
    public $invoiceDate = '0000-00-00';
    public $deliveryDate = '0000-00-00';
    public $invoiceNumber;
    public $podReasonLookup;
    public $podReasonUId = 0;
    public $detailArr = [];
    public $batchArr = [];

    /**
     * @param mixed $udUId
     * @return PostingDocumentUpdateTO
     */
    public function setUdUId($udUId)
    {
        $this->udUId = $udUId;
        return $this;
    }

    /**
     * @param mixed $documentMasterUid
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentMasterUid($documentMasterUid)
    {
        $this->documentMasterUid = $documentMasterUid;
        return $this;
    }

    /**
     * @param mixed $sourceDocumentMasterUid
     * @return PostingDocumentUpdateTO
     */
    public function setSourceDocumentMasterUid($sourceDocumentMasterUid)
    {
        $this->sourceDocumentMasterUid = $sourceDocumentMasterUid;
        return $this;
    }

    /**
     * @param mixed $documentUpdatedFlag
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentUpdatedFlag($documentUpdatedFlag)
    {
        $this->documentUpdatedFlag = $documentUpdatedFlag;
        return $this;
    }

    /**
     * @param mixed $updateTypeUid
     * @return PostingDocumentUpdateTO
     */
    public function setUpdateTypeUid($updateTypeUid)
    {
        $this->updateTypeUid = $updateTypeUid;
        return $this;
    }

    /**
     * @param mixed $additionalType
     * @return PostingDocumentUpdateTO
     */
    public function setAdditionalType($additionalType)
    {
        $this->additionalType = $additionalType;
        return $this;
    }

    /**
     * @param mixed $processedDatetime
     * @return PostingDocumentUpdateTO
     */
    public function setProcessedDatetime($processedDatetime)
    {
        $this->processedDatetime = $processedDatetime;
        return $this;
    }

    /**
     * @param mixed $processedStatus
     * @return PostingDocumentUpdateTO
     */
    public function setProcessedStatus($processedStatus)
    {
        $this->processedStatus = $processedStatus;
        return $this;
    }

    /**
     * @param mixed $processedMsg
     * @return PostingDocumentUpdateTO
     */
    public function setProcessedMsg($processedMsg)
    {
        $this->processedMsg = $processedMsg;
        return $this;
    }

    /**
     * @param int $principalUId
     * @return PostingDocumentUpdateTO
     */
    public function setPrincipalUId(int $principalUId): PostingDocumentUpdateTO
    {
        $this->principalUId = $principalUId;
        return $this;
    }

    /**
     * @param mixed $principalLookup
     * @return PostingDocumentUpdateTO
     */
    public function setPrincipalLookup($principalLookup)
    {
        $this->principalLookup = $principalLookup;
        return $this;
    }

    /**
     * @param mixed $depotLookup
     * @return PostingDocumentUpdateTO
     */
    public function setDepotLookup($depotLookup)
    {
        $this->depotLookup = $depotLookup;
        return $this;
    }

    /**
     * @param mixed $depotUId
     * @return PostingDocumentUpdateTO
     */
    public function setDepotUId($depotUId): PostingDocumentUpdateTO
    {
        $this->depotUId = $depotUId;
        return $this;
    }

    /**
     * @param string $skipDepotUpdate
     * @return PostingDocumentUpdateTO
     */
    public function setSkipDepotUpdate(string $skipDepotUpdate): PostingDocumentUpdateTO
    {
        $this->skipDepotUpdate = $skipDepotUpdate;
        return $this;
    }

    /**
     * @param mixed $documentNumber
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    /**
     * @param mixed $sourceDocumentNumber
     * @return PostingDocumentUpdateTO
     */
    public function setSourceDocumentNumber($sourceDocumentNumber)
    {
        $this->sourceDocumentNumber = $sourceDocumentNumber;
        return $this;
    }

    /**
     * @param mixed $createdDatetime
     * @return PostingDocumentUpdateTO
     */
    public function setCreatedDatetime($createdDatetime)
    {
        $this->createdDatetime = $createdDatetime;
        return $this;
    }

    /**
     * @param string $mergeDate
     * @return PostingDocumentUpdateTO
     */
    public function setMergeDate(string $mergeDate): PostingDocumentUpdateTO
    {
        $this->mergeDate = $mergeDate;
        return $this;
    }

    /**
     * @param string $mergeTime
     * @return PostingDocumentUpdateTO
     */
    public function setMergeTime(string $mergeTime): PostingDocumentUpdateTO
    {
        $this->mergeTime = $mergeTime;
        return $this;
    }

    /**
     * @param mixed $incomingFilename
     * @return PostingDocumentUpdateTO
     */
    public function setIncomingFilename($incomingFilename)
    {
        $this->incomingFilename = $incomingFilename;
        return $this;
    }

    /**
     * @param mixed $documentStatusLookup
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentStatusLookup($documentStatusLookup)
    {
        $this->documentStatusLookup = $documentStatusLookup;
        return $this;
    }

    /**
     * @param int $documentStatusUId
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentStatusUId(int $documentStatusUId): PostingDocumentUpdateTO
    {
        $this->documentStatusUId = $documentStatusUId;
        return $this;
    }

    /**
     * @param mixed $fileLogUId
     * @return PostingDocumentUpdateTO
     */
    public function setFileLogUId($fileLogUId)
    {
        $this->fileLogUId = $fileLogUId;
        return $this;
    }

    /**
     * @param mixed $deliveryDayUId
     * @return PostingDocumentUpdateTO
     */
    public function setDeliveryDayUId($deliveryDayUId)
    {
        $this->deliveryDayUId = $deliveryDayUId;
        return $this;
    }

    /**
     * @param mixed $documentTypeUId
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentTypeUId($documentTypeUId)
    {
        $this->documentTypeUId = $documentTypeUId;
        return $this;
    }

    /**
     * @param string $documentSourceOptional
     * @return PostingDocumentUpdateTO
     */
    public function setDocumentSourceOptional(string $documentSourceOptional): PostingDocumentUpdateTO
    {
        $this->documentSourceOptional = $documentSourceOptional;
        return $this;
    }

    /**
     * @param mixed $reference
     * @return PostingDocumentUpdateTO
     */
    public function setReference($reference): PostingDocumentUpdateTO
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @param string $claimNumber
     * @return PostingDocumentUpdateTO
     */
    public function setClaimNumber(string $claimNumber): PostingDocumentUpdateTO
    {
        $this->claimNumber = $claimNumber;
        return $this;
    }

    /**
     * @param string $additionalDetails
     * @return PostingDocumentUpdateTO
     */
    public function setAdditionalDetails(string $additionalDetails): PostingDocumentUpdateTO
    {
        $this->additionalDetails = $additionalDetails;
        return $this;
    }

    /**
     * @param string $grvNumber
     * @return PostingDocumentUpdateTO
     */
    public function setGrvNumber(string $grvNumber): PostingDocumentUpdateTO
    {
        $this->grvNumber = $grvNumber;
        return $this;
    }

    /**
     * @param string $dueDeliveryDate
     * @return PostingDocumentUpdateTO
     */
    public function setDueDeliveryDate(string $dueDeliveryDate): PostingDocumentUpdateTO
    {
        $this->dueDeliveryDate = $dueDeliveryDate;
        return $this;
    }

    /**
     * @param mixed $pages
     * @return PostingDocumentUpdateTO
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @param string $invoiceDate
     * @return PostingDocumentUpdateTO
     */
    public function setInvoiceDate(string $invoiceDate): PostingDocumentUpdateTO
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    /**
     * @param string $deliveryDate
     * @return PostingDocumentUpdateTO
     */
    public function setDeliveryDate(string $deliveryDate): PostingDocumentUpdateTO
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }

    /**
     * @param mixed $invoiceNumber
     * @return PostingDocumentUpdateTO
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    /**
     * @param mixed $podReasonLookup
     * @return PostingDocumentUpdateTO
     */
    public function setPodReasonLookup($podReasonLookup)
    {
        $this->podReasonLookup = $podReasonLookup;
        return $this;
    }

    /**
     * @param int $podReasonUId
     * @return PostingDocumentUpdateTO
     */
    public function setPodReasonUId(int $podReasonUId): PostingDocumentUpdateTO
    {
        $this->podReasonUId = $podReasonUId;
        return $this;
    }

    /**
     * @param mixed $detailArr
     * @return PostingDocumentUpdateTO
     */
    public function addDetailArr($detailArr): PostingDocumentUpdateTO
    {
        $this->detailArr[] = $detailArr;
        return $this;
    }

}
