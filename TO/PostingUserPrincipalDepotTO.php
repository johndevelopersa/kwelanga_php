<?php

class PostingUserPrincipalDepotTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $userId;
    public $principalId; // set by posting script only
    public $depotId;
}