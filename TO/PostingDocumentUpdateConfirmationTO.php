<?php

class PostingDocumentUpdateConfirmationTO
{
    // document master
    public $dmUId;
    public $principalUId;
    public $depotUId;
    public $mergedDate;
    public $mergedTime;
    public $confirmationFile;

    //doc header
    public $dueDeliveryDate = '0000-00-00'; //predicted del date.

    // store
    public $deliveryDayUId;
}

