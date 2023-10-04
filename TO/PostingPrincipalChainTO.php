<?php

class PostingPrincipalChainTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $chainName;
    public $principalId;
    public $status;
    public $oldCode;
    public $principalChainUId; // if UPDATE, then this is the uid being edited
}