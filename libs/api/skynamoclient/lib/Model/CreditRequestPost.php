<?php
/**
 * CreditRequestPost
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
 * CreditRequestPost Class Doc Comment
 *
 * @category Class
 * @description This is a credit request in Skynamo, used for adding a credit request (All values not specified will assume their default values)
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class CreditRequestPost implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'CreditRequestPost';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'date' => '\DateTime',
        'customer_id' => 'int',
        'user_id' => 'int',
        'discount' => 'float',
        'prices_include_vat' => 'bool',
        'warehouse_id' => 'int',
        'transaction_id' => 'int',
        'items' => '\SkynamoClientAPI\Model\CreditRequestItemPost[]',
        'forms' => '\SkynamoClientAPI\Model\CreditRequestForms[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'date' => 'date-time',
        'customer_id' => null,
        'user_id' => null,
        'discount' => 'float',
        'prices_include_vat' => null,
        'warehouse_id' => null,
        'transaction_id' => null,
        'items' => null,
        'forms' => null
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
        'date' => 'date',
        'customer_id' => 'customer_id',
        'user_id' => 'user_id',
        'discount' => 'discount',
        'prices_include_vat' => 'prices_include_vat',
        'warehouse_id' => 'warehouse_id',
        'transaction_id' => 'transaction_id',
        'items' => 'items',
        'forms' => 'forms'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'date' => 'setDate',
        'customer_id' => 'setCustomerId',
        'user_id' => 'setUserId',
        'discount' => 'setDiscount',
        'prices_include_vat' => 'setPricesIncludeVat',
        'warehouse_id' => 'setWarehouseId',
        'transaction_id' => 'setTransactionId',
        'items' => 'setItems',
        'forms' => 'setForms'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'date' => 'getDate',
        'customer_id' => 'getCustomerId',
        'user_id' => 'getUserId',
        'discount' => 'getDiscount',
        'prices_include_vat' => 'getPricesIncludeVat',
        'warehouse_id' => 'getWarehouseId',
        'transaction_id' => 'getTransactionId',
        'items' => 'getItems',
        'forms' => 'getForms'
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
        $this->container['date'] = $data['date'] ?? null;
        $this->container['customer_id'] = $data['customer_id'] ?? null;
        $this->container['user_id'] = $data['user_id'] ?? null;
        $this->container['discount'] = $data['discount'] ?? null;
        $this->container['prices_include_vat'] = $data['prices_include_vat'] ?? null;
        $this->container['warehouse_id'] = $data['warehouse_id'] ?? null;
        $this->container['transaction_id'] = $data['transaction_id'] ?? null;
        $this->container['items'] = $data['items'] ?? null;
        $this->container['forms'] = $data['forms'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['date'] === null) {
            $invalidProperties[] = "'date' can't be null";
        }
        if ($this->container['customer_id'] === null) {
            $invalidProperties[] = "'customer_id' can't be null";
        }
        if ($this->container['user_id'] === null) {
            $invalidProperties[] = "'user_id' can't be null";
        }
        if (!is_null($this->container['discount']) && ($this->container['discount'] > 1E+2)) {
            $invalidProperties[] = "invalid value for 'discount', must be smaller than or equal to 1E+2.";
        }

        if (!is_null($this->container['discount']) && ($this->container['discount'] < 0)) {
            $invalidProperties[] = "invalid value for 'discount', must be bigger than or equal to 0.";
        }

        if ($this->container['items'] === null) {
            $invalidProperties[] = "'items' can't be null";
        }
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
     * Gets date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->container['date'];
    }

    /**
     * Sets date
     *
     * @param \DateTime $date The date when the credit request was issued
     *
     * @return self
     */
    public function setDate($date)
    {
        $this->container['date'] = $date;

        return $this;
    }

    /**
     * Gets customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->container['customer_id'];
    }

    /**
     * Sets customer_id
     *
     * @param int $customer_id The unique id of the customer that placed the credit request
     *
     * @return self
     */
    public function setCustomerId($customer_id)
    {
        $this->container['customer_id'] = $customer_id;

        return $this;
    }

    /**
     * Gets user_id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->container['user_id'];
    }

    /**
     * Sets user_id
     *
     * @param int $user_id The unique id of the user that placed the credit request
     *
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->container['user_id'] = $user_id;

        return $this;
    }

    /**
     * Gets discount
     *
     * @return float|null
     */
    public function getDiscount()
    {
        return $this->container['discount'];
    }

    /**
     * Sets discount
     *
     * @param float|null $discount The discount percentage on the credit request
     *
     * @return self
     */
    public function setDiscount($discount)
    {

        if (!is_null($discount) && ($discount > 1E+2)) {
            throw new \InvalidArgumentException('invalid value for $discount when calling CreditRequestPost., must be smaller than or equal to 1E+2.');
        }
        if (!is_null($discount) && ($discount < 0)) {
            throw new \InvalidArgumentException('invalid value for $discount when calling CreditRequestPost., must be bigger than or equal to 0.');
        }

        $this->container['discount'] = $discount;

        return $this;
    }

    /**
     * Gets prices_include_vat
     *
     * @return bool|null
     */
    public function getPricesIncludeVat()
    {
        return $this->container['prices_include_vat'];
    }

    /**
     * Sets prices_include_vat
     *
     * @param bool|null $prices_include_vat Indicates whether the price is vat inclusive or not
     *
     * @return self
     */
    public function setPricesIncludeVat($prices_include_vat)
    {
        $this->container['prices_include_vat'] = $prices_include_vat;

        return $this;
    }

    /**
     * Gets warehouse_id
     *
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->container['warehouse_id'];
    }

    /**
     * Sets warehouse_id
     *
     * @param int|null $warehouse_id The unique identifier of the warehouse associated with the stock level
     *
     * @return self
     */
    public function setWarehouseId($warehouse_id)
    {
        $this->container['warehouse_id'] = $warehouse_id;

        return $this;
    }

    /**
     * Gets transaction_id
     *
     * @return int|null
     */
    public function getTransactionId()
    {
        return $this->container['transaction_id'];
    }

    /**
     * Sets transaction_id
     *
     * @param int|null $transaction_id The transaction id associated with files in order to link files
     *
     * @return self
     */
    public function setTransactionId($transaction_id)
    {
        $this->container['transaction_id'] = $transaction_id;

        return $this;
    }

    /**
     * Gets items
     *
     * @return \SkynamoClientAPI\Model\CreditRequestItemPost[]
     */
    public function getItems()
    {
        return $this->container['items'];
    }

    /**
     * Sets items
     *
     * @param \SkynamoClientAPI\Model\CreditRequestItemPost[] $items A list of items included in the credit request
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->container['items'] = $items;

        return $this;
    }

    /**
     * Gets forms
     *
     * @return \SkynamoClientAPI\Model\CreditRequestForms[]|null
     */
    public function getForms()
    {
        return $this->container['forms'];
    }

    /**
     * Sets forms
     *
     * @param \SkynamoClientAPI\Model\CreditRequestForms[]|null $forms Certain custom fields may be required depending on the custom fields that have been set up
     *
     * @return self
     */
    public function setForms($forms)
    {
        $this->container['forms'] = $forms;

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


