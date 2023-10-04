<?php
/**
 * VisitFrequency
 *
 * PHP version 7.2
 *
 * @category Class
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Skynamo Public API
 *
 * The specification for Skynamo's public API <br><br>Helpful links<br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671335262749-Creating-a-Public-API-Key\" id=\"hint_box\">Creating a Public API Key</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671463933597-Postman-Examples\" id=\"hint_box\">Postman examples</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671240071453-How-to-upload-customer-images-using-Postman\" id=\"hint_box\">How to upload customer images</a>
 *
 * The version of the OpenAPI document: 1.0.18
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 5.1.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace SkynamoClientAPI\Model;

use \ArrayAccess;
use \SkynamoClientAPI\ObjectSerializer;

/**
 * VisitFrequency Class Doc Comment
 *
 * @category Class
 * @description The frequency that a user should visit a customer. &lt;br&gt;&lt;br&gt;Example: once(1) every 2 weeks
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class VisitFrequency implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'VisitFrequency';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'id' => 'int',
        'customer_id' => 'int',
        'customer_code' => 'string',
        'customer_name' => 'string',
        'user_id' => 'int',
        'user_name' => 'string',
        'cycle' => 'int',
        'frequency' => 'int',
        'period' => 'string',
        'row_version' => 'float',
        'last_modified_time' => '\DateTime'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'id' => null,
        'customer_id' => null,
        'customer_code' => null,
        'customer_name' => null,
        'user_id' => null,
        'user_name' => null,
        'cycle' => null,
        'frequency' => null,
        'period' => null,
        'row_version' => 'long',
        'last_modified_time' => 'date-time'
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
        'id' => 'id',
        'customer_id' => 'customer_id',
        'customer_code' => 'customer_code',
        'customer_name' => 'customer_name',
        'user_id' => 'user_id',
        'user_name' => 'user_name',
        'cycle' => 'cycle',
        'frequency' => 'frequency',
        'period' => 'period',
        'row_version' => 'row_version',
        'last_modified_time' => 'last_modified_time'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'customer_id' => 'setCustomerId',
        'customer_code' => 'setCustomerCode',
        'customer_name' => 'setCustomerName',
        'user_id' => 'setUserId',
        'user_name' => 'setUserName',
        'cycle' => 'setCycle',
        'frequency' => 'setFrequency',
        'period' => 'setPeriod',
        'row_version' => 'setRowVersion',
        'last_modified_time' => 'setLastModifiedTime'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'customer_id' => 'getCustomerId',
        'customer_code' => 'getCustomerCode',
        'customer_name' => 'getCustomerName',
        'user_id' => 'getUserId',
        'user_name' => 'getUserName',
        'cycle' => 'getCycle',
        'frequency' => 'getFrequency',
        'period' => 'getPeriod',
        'row_version' => 'getRowVersion',
        'last_modified_time' => 'getLastModifiedTime'
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
        $this->container['id'] = $data['id'] ?? null;
        $this->container['customer_id'] = $data['customer_id'] ?? null;
        $this->container['customer_code'] = $data['customer_code'] ?? null;
        $this->container['customer_name'] = $data['customer_name'] ?? null;
        $this->container['user_id'] = $data['user_id'] ?? null;
        $this->container['user_name'] = $data['user_name'] ?? null;
        $this->container['cycle'] = $data['cycle'] ?? null;
        $this->container['frequency'] = $data['frequency'] ?? null;
        $this->container['period'] = $data['period'] ?? null;
        $this->container['row_version'] = $data['row_version'] ?? null;
        $this->container['last_modified_time'] = $data['last_modified_time'] ?? null;
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
     * Gets id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->container['id'];
    }

    /**
     * Sets id
     *
     * @param int|null $id The unique identifier of the visit frequency
     *
     * @return self
     */
    public function setId($id)
    {
        $this->container['id'] = $id;

        return $this;
    }

    /**
     * Gets customer_id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->container['customer_id'];
    }

    /**
     * Sets customer_id
     *
     * @param int|null $customer_id The unique identifier of the customer where the visit frequency should be used
     *
     * @return self
     */
    public function setCustomerId($customer_id)
    {
        $this->container['customer_id'] = $customer_id;

        return $this;
    }

    /**
     * Gets customer_code
     *
     * @return string|null
     */
    public function getCustomerCode()
    {
        return $this->container['customer_code'];
    }

    /**
     * Sets customer_code
     *
     * @param string|null $customer_code The unique code of the customer where the visit frequency should be used<br>(customer_code must correspond with customer_id and customer_name)
     *
     * @return self
     */
    public function setCustomerCode($customer_code)
    {
        $this->container['customer_code'] = $customer_code;

        return $this;
    }

    /**
     * Gets customer_name
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->container['customer_name'];
    }

    /**
     * Sets customer_name
     *
     * @param string|null $customer_name The name of the customer where the visit frequency should be used<br>(customer_name must correspond with customer_id and customer_code)
     *
     * @return self
     */
    public function setCustomerName($customer_name)
    {
        $this->container['customer_name'] = $customer_name;

        return $this;
    }

    /**
     * Gets user_id
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->container['user_id'];
    }

    /**
     * Sets user_id
     *
     * @param int|null $user_id The unique identifier of the user that the visit frequency is assigned to
     *
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->container['user_id'] = $user_id;

        return $this;
    }

    /**
     * Gets user_name
     *
     * @return string|null
     */
    public function getUserName()
    {
        return $this->container['user_name'];
    }

    /**
     * Sets user_name
     *
     * @param string|null $user_name The user name of the user that the visit frequency is assigned to<br>(user_name must correspond with user_id)
     *
     * @return self
     */
    public function setUserName($user_name)
    {
        $this->container['user_name'] = $user_name;

        return $this;
    }

    /**
     * Gets cycle
     *
     * @return int|null
     */
    public function getCycle()
    {
        return $this->container['cycle'];
    }

    /**
     * Sets cycle
     *
     * @param int|null $cycle Number of cycles per period (\"2\" in the example at the top)
     *
     * @return self
     */
    public function setCycle($cycle)
    {
        $this->container['cycle'] = $cycle;

        return $this;
    }

    /**
     * Gets frequency
     *
     * @return int|null
     */
    public function getFrequency()
    {
        return $this->container['frequency'];
    }

    /**
     * Sets frequency
     *
     * @param int|null $frequency Number of visits per cycle (\"once(1)\" in example at the top)
     *
     * @return self
     */
    public function setFrequency($frequency)
    {
        $this->container['frequency'] = $frequency;

        return $this;
    }

    /**
     * Gets period
     *
     * @return string|null
     */
    public function getPeriod()
    {
        return $this->container['period'];
    }

    /**
     * Sets period
     *
     * @param string|null $period The duration of a period. (\"weeks\" in example at the top)<br>Contains one of the following values: week, month or year
     *
     * @return self
     */
    public function setPeriod($period)
    {
        $this->container['period'] = $period;

        return $this;
    }

    /**
     * Gets row_version
     *
     * @return float|null
     */
    public function getRowVersion()
    {
        return $this->container['row_version'];
    }

    /**
     * Sets row_version
     *
     * @param float|null $row_version An automatically generated, unique number used to version-stamp table rows in the database
     *
     * @return self
     */
    public function setRowVersion($row_version)
    {
        $this->container['row_version'] = $row_version;

        return $this;
    }

    /**
     * Gets last_modified_time
     *
     * @return \DateTime|null
     */
    public function getLastModifiedTime()
    {
        return $this->container['last_modified_time'];
    }

    /**
     * Sets last_modified_time
     *
     * @param \DateTime|null $last_modified_time The last time the visit frequency was modified
     *
     * @return self
     */
    public function setLastModifiedTime($last_modified_time)
    {
        $this->container['last_modified_time'] = $last_modified_time;

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
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
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
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


