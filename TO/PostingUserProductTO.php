<?php

class PostingUserProductTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $userId;
    public $principalProductUId;

    // the principal you are adding the store to.
    // The store is not looked up and that principal is not the one used.
    public $principalId;
}