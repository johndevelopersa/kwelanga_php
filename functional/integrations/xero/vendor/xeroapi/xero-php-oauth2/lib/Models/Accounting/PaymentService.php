<?php
/**
 * PaymentService
 *
 * PHP version 5
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Accounting API
 *
 * No description provided (generated by Openapi Generator https://github.com/openapitools/openapi-generator)
 *
 * OpenAPI spec version: 2.0.8
 * Contact: api@xero.com
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 4.0.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace XeroAPI\XeroPHP\Models\Accounting;

use \ArrayAccess;
use \XeroAPI\XeroPHP\AccountingObjectSerializer;
use \XeroAPI\XeroPHP\StringUtil;
/**
 * PaymentService Class Doc Comment
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class PaymentService implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'PaymentService';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'payment_service_id' => 'string',
        'payment_service_name' => 'string',
        'payment_service_url' => 'string',
        'pay_now_text' => 'string',
        'payment_service_type' => 'string',
        'validation_errors' => '\XeroAPI\XeroPHP\Models\Accounting\ValidationError[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'payment_service_id' => 'uuid',
        'payment_service_name' => null,
        'payment_service_url' => null,
        'pay_now_text' => null,
        'payment_service_type' => null,
        'validation_errors' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'payment_service_id' => 'PaymentServiceID',
        'payment_service_name' => 'PaymentServiceName',
        'payment_service_url' => 'PaymentServiceUrl',
        'pay_now_text' => 'PayNowText',
        'payment_service_type' => 'PaymentServiceType',
        'validation_errors' => 'ValidationErrors'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'payment_service_id' => 'setPaymentServiceId',
        'payment_service_name' => 'setPaymentServiceName',
        'payment_service_url' => 'setPaymentServiceUrl',
        'pay_now_text' => 'setPayNowText',
        'payment_service_type' => 'setPaymentServiceType',
        'validation_errors' => 'setValidationErrors'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'payment_service_id' => 'getPaymentServiceId',
        'payment_service_name' => 'getPaymentServiceName',
        'payment_service_url' => 'getPaymentServiceUrl',
        'pay_now_text' => 'getPayNowText',
        'payment_service_type' => 'getPaymentServiceType',
        'validation_errors' => 'getValidationErrors'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['payment_service_id'] = isset($data['payment_service_id']) ? $data['payment_service_id'] : null;
        $this->container['payment_service_name'] = isset($data['payment_service_name']) ? $data['payment_service_name'] : null;
        $this->container['payment_service_url'] = isset($data['payment_service_url']) ? $data['payment_service_url'] : null;
        $this->container['pay_now_text'] = isset($data['pay_now_text']) ? $data['pay_now_text'] : null;
        $this->container['payment_service_type'] = isset($data['payment_service_type']) ? $data['payment_service_type'] : null;
        $this->container['validation_errors'] = isset($data['validation_errors']) ? $data['validation_errors'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets payment_service_id
     *
     * @return string|null
     */
    public function getPaymentServiceId()
    {
        return $this->container['payment_service_id'];
    }

    /**
     * Sets payment_service_id
     *
     * @param string|null $payment_service_id Xero identifier
     *
     * @return $this
     */
    public function setPaymentServiceId($payment_service_id)
    {

        $this->container['payment_service_id'] = $payment_service_id;

        return $this;
    }



    /**
     * Gets payment_service_name
     *
     * @return string|null
     */
    public function getPaymentServiceName()
    {
        return $this->container['payment_service_name'];
    }

    /**
     * Sets payment_service_name
     *
     * @param string|null $payment_service_name Name of payment service
     *
     * @return $this
     */
    public function setPaymentServiceName($payment_service_name)
    {

        $this->container['payment_service_name'] = $payment_service_name;

        return $this;
    }



    /**
     * Gets payment_service_url
     *
     * @return string|null
     */
    public function getPaymentServiceUrl()
    {
        return $this->container['payment_service_url'];
    }

    /**
     * Sets payment_service_url
     *
     * @param string|null $payment_service_url The custom payment URL
     *
     * @return $this
     */
    public function setPaymentServiceUrl($payment_service_url)
    {

        $this->container['payment_service_url'] = $payment_service_url;

        return $this;
    }



    /**
     * Gets pay_now_text
     *
     * @return string|null
     */
    public function getPayNowText()
    {
        return $this->container['pay_now_text'];
    }

    /**
     * Sets pay_now_text
     *
     * @param string|null $pay_now_text The text displayed on the Pay Now button in Xero Online Invoicing. If this is not set it will default to Pay by credit card
     *
     * @return $this
     */
    public function setPayNowText($pay_now_text)
    {

        $this->container['pay_now_text'] = $pay_now_text;

        return $this;
    }



    /**
     * Gets payment_service_type
     *
     * @return string|null
     */
    public function getPaymentServiceType()
    {
        return $this->container['payment_service_type'];
    }

    /**
     * Sets payment_service_type
     *
     * @param string|null $payment_service_type This will always be CUSTOM for payment services created via the API.
     *
     * @return $this
     */
    public function setPaymentServiceType($payment_service_type)
    {

        $this->container['payment_service_type'] = $payment_service_type;

        return $this;
    }



    /**
     * Gets validation_errors
     *
     * @return \XeroAPI\XeroPHP\Models\Accounting\ValidationError[]|null
     */
    public function getValidationErrors()
    {
        return $this->container['validation_errors'];
    }

    /**
     * Sets validation_errors
     *
     * @param \XeroAPI\XeroPHP\Models\Accounting\ValidationError[]|null $validation_errors Displays array of validation error messages from the API
     *
     * @return $this
     */
    public function setValidationErrors($validation_errors)
    {

        $this->container['validation_errors'] = $validation_errors;

        return $this;
    }


    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            AccountingObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }
}


