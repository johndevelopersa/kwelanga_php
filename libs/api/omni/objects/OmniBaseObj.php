<?php


class OmniBaseObj implements ArrayAccess
{

    /**
     * @var OmniHTTPResponseObj
     */
    private $response = null;
    private $data = [];

    public function __construct($properties = [])
    {
        $this->data = $properties;
        $this->initializeSpecialProperties();
        if (is_array($properties) && count($properties)) {
            $this->arrayAssign($properties);
        }
    }

    public function arrayAssign($properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /*
     * @return OmniBaseObj
     */
    public function setResponse(OmniHTTPResponseObj $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse(): OmniHTTPResponseObj
    {
        return $this->response;
    }

    protected function initializeSpecialProperties()
    {

    }

    public function getArray()
    {
        return get_object_vars($this);
    }

    public function getJSON()
    {
        return json_encode($this->getArray(), JSON_PRETTY_PRINT);
    }

    public function getProperties(): Array
    {
        return get_object_vars($this);
    }

    public function getDataArray(): Array
    {
        return $this->data;
    }

    public function hasDataArray(): bool
    {
        return is_array($this->data) && count($this->data);
    }

    //getter - setters for array
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
