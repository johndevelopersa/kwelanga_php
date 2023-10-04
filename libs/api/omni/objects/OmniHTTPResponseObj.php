<?php


class OmniHTTPResponseObj
{

    //meta
    protected $success = false;

    //request
    protected $url;
    protected $method;
    protected $requestData = null;

    //response
    protected $debugArr;
    protected $responseStatusCode = 0;
    protected $responseHeaders = [];

    protected $xhrResponse = "";
    protected $body = ""; //undecoded response
    protected $decodedBody = ""; //based on content type of response.


    public function __construct($url, $method, $requestData, $response, $curlDebug)
    {
        $this->url = $url;
        $this->method = $method;
        $this->requestData = $requestData;

        $this->xhrResponse = $response;
        $this->debugArr = is_array($curlDebug) ? $curlDebug : [];
        $this->responseStatusCode = ($this->debugArr['http_code'] ?? 0);

        $headerSize = $this->debugArr['header_size'] ?? 0;
        $this->responseHeaders = explode("\n", trim(substr($response, 0, $headerSize)));
        $this->body = trim(substr($response, $headerSize));

        if (stripos($this->getResponseHeader("content-type"), "application/json") !== false) {
            $this->decodedBody = json_decode($this->body, true);
        } else {
            $this->decodedBody = $this->body;
        }

        //success primarily dependant on the status Code and not returning data, each response will need to set that
        $this->setSuccess($this->responseStatusCode == 200);
    }


    public function getResponseHeader($findHeader): string
    {
        foreach ($this->responseHeaders as $header) {
            $headerParts = explode(':', $header, 2);
            if (count($headerParts) == 2) {
                $headerKey = trim($headerParts[0]);
                $headerValue = trim($headerParts[1]);
                if (stripos($findHeader, $headerKey) === 0) {
                    return $headerValue;
                }
            }
        }
        return "";
    }

    public function setSuccess(bool $success)
    {
        return $this->success = $success;
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function getRequestURL(){
        return $this->url;
    }

    public function getErrorMessage(): string
    {
        return $this->body;
    }

    public function getXHRResponse(): string
    {
        return $this->xhrResponse;
    }

    public function getBody()
    {
        return $this->decodedBody;
    }

    public function setBody($body)
    {
        return $this->decodedBody = $body;
    }

}
