<?php

class SequenceTO
{
    public $sequenceKey;
    public $sequenceStart;
    public $sequenceLen;
    public $nextSequence;
    public $documentTypeUId;
    public $principalUId; // optional, depends
    public $depotUId; // optional, depends
    public $dataSource; // optional, depends
}