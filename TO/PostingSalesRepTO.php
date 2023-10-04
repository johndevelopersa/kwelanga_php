<?php

class PostingSalesRepTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $UId;
    public $principalUId;
    public $repCode = '';
    public $firstName = '';
    public $surname = '';
    public $identityNumber = '';
    public $emailAddr = '';
    public $mobileNumber = '';
    public $alternateContactNumber = '';
    public $shiptoAddress1 = '';
    public $shiptoAddress2 = '';
    public $shiptoAddress3 = '';
    public $salesTarget = '';
    public $status = FLAG_STATUS_ACTIVE;
}