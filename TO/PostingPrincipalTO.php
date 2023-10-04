<?php

class PostingPrincipalTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $puid;
    public $principal_code;
    public $name;
    public $physical_add1;
    public $physical_add2;
    public $physical_add3;
    public $physical_add4;
    public $postal_add1;
    public $postal_add2;
    public $postal_add3;
    public $postal_add4;
    public $vat_num;
    public $rt_acc_num;
    public $office_tel;
    public $email_add;
    public $contactperson;
    public $suspended;
    public $bankingDetails;
    public $altPrincipalCode;
    public $principalUpliftCode;
    public $principalType;
    public $principalGLN;
    public $status;
    public $exportNumber;
    public $taskmanAccount;
    public $lastUpdated;    // synching
}