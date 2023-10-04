<?php

class PostingSpecialFieldTO
{
    public $DMLType;
    public $principal;
    public $type;
    public $deliverName;
    public $storeString;
    public $fielduid;
    public $value;
    public $editable = '';
    public $entityUId; // used during DMLTYPE=update
    // controls processing
    public $allowUpdate = "N"; // EDI Only. Some adaptors update the m/f from EDI files.
    public $skipValidation = "N";
    public $depotUId = false; // must be false if you dont want validator to validate against depot
}