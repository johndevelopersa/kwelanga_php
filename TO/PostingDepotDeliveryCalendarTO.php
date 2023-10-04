<?php

class PostingDepotDeliveryCalendarTO
{
    public $DMLType; // INSERT, DELETE, UPDATE

    public $UId;
    public $depotUId;
    public $timestamp;
    public $type;
    public $status;
    public $comment;
    public $createdByUserUId;
    #created_datetime
    public $calendarDate;
}