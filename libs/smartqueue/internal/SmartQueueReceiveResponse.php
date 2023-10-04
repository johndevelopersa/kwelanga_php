<?php

class SmartQueueReceiveResponse {

    /** @var ErrorTO $errorTo */
    private $errorTo = null;

    /** @var SmartQueueMessage[] $messages */
    private $messages = [];

    /** @var array */
    private $metadata = [];

    public function __construct($queueURL = null, $result = null)
    {
        if($result) {
            if($result->hasKey("Messages")){
                $this->metadata = $result->get("@metadata");
                $messages = $result->get("Messages");
                foreach ($messages as $msg){
                    $this->messages[] = New SmartQueueMessage($queueURL, $msg);
                }
            }
        }
    }

    /**
     * @return ErrorTO
     */
    public function getErrorTo(): ErrorTO
    {
        return $this->errorTo;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->errorTo != null && $this->errorTo->isError();
    }

    /**
     * @return bool
     */
    public function hasMessages(): bool
    {
        return is_array($this->messages) && count($this->messages) > 0;
    }

    /**
     * @return SmartQueueMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param ErrorTO $errorTO
     * @return SmartQueueReceiveResponse
     */
    public function setError(ErrorTO $errorTO): SmartQueueReceiveResponse
    {
        $this->errorTo = $errorTO;
        return $this;
    }
}