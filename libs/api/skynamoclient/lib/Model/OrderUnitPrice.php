<?php
/**
 * OrderUnitPrice
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
 * OrderUnitPrice Class Doc Comment
 *
 * @category Class
 * @description This is a price of an order unit in Skynamo, used for fetching information about prices on products
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class OrderUnitPrice implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'OrderUnitPrice';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'price' => 'float',
        'price_list_id' => 'int',
        'price_list_name' => 'string',
        'product_id' => 'int',
        'product_code' => 'string',
        'product_name' => 'string',
        'order_unit_id' => 'int',
        'order_unit_name' => 'string',
        'last_modified_time' => '\DateTime',
        'tax_rate_id' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'price' => 'float',
        'price_list_id' => null,
        'price_list_name' => null,
        'product_id' => null,
        'product_code' => 'string',
        'product_name' => 'string',
        'order_unit_id' => null,
        'order_unit_name' => 'string',
        'last_modified_time' => 'date-time',
        'tax_rate_id' => null
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
        'price' => 'price',
        'price_list_id' => 'price_list_id',
        'price_list_name' => 'price_list_name',
        'product_id' => 'product_id',
        'product_code' => 'product_code',
        'product_name' => 'product_name',
        'order_unit_id' => 'order_unit_id',
        'order_unit_name' => 'order_unit_name',
        'last_modified_time' => 'last_modified_time',
        'tax_rate_id' => 'tax_rate_id'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'price' => 'setPrice',
        'price_list_id' => 'setPriceListId',
        'price_list_name' => 'setPriceListName',
        'product_id' => 'setProductId',
        'product_code' => 'setProductCode',
        'product_name' => 'setProductName',
        'order_unit_id' => 'setOrderUnitId',
        'order_unit_name' => 'setOrderUnitName',
        'last_modified_time' => 'setLastModifiedTime',
        'tax_rate_id' => 'setTaxRateId'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'price' => 'getPrice',
        'price_list_id' => 'getPriceListId',
        'price_list_name' => 'getPriceListName',
        'product_id' => 'getProductId',
        'product_code' => 'getProductCode',
        'product_name' => 'getProductName',
        'order_unit_id' => 'getOrderUnitId',
        'order_unit_name' => 'getOrderUnitName',
        'last_modified_time' => 'getLastModifiedTime',
        'tax_rate_id' => 'getTaxRateId'
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
        $this->container['price'] = $data['price'] ?? null;
        $this->container['price_list_id'] = $data['price_list_id'] ?? null;
        $this->container['price_list_name'] = $data['price_list_name'] ?? null;
        $this->container['product_id'] = $data['product_id'] ?? null;
        $this->container['product_code'] = $data['product_code'] ?? null;
        $this->container['product_name'] = $data['product_name'] ?? null;
        $this->container['order_unit_id'] = $data['order_unit_id'] ?? null;
        $this->container['order_unit_name'] = $data['order_unit_name'] ?? null;
        $this->container['last_modified_time'] = $data['last_modified_time'] ?? null;
        $this->container['tax_rate_id'] = $data['tax_rate_id'] ?? null;
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
     * Gets price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->container['price'];
    }

    /**
     * Sets price
     *
     * @param float|null $price The price of the order unit
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->container['price'] = $price;

        return $this;
    }

    /**
     * Gets price_list_id
     *
     * @return int|null
     */
    public function getPriceListId()
    {
        return $this->container['price_list_id'];
    }

    /**
     * Sets price_list_id
     *
     * @param int|null $price_list_id The unique identifier of the price list associated with this order unit price
     *
     * @return self
     */
    public function setPriceListId($price_list_id)
    {
        $this->container['price_list_id'] = $price_list_id;

        return $this;
    }

    /**
     * Gets price_list_name
     *
     * @return string|null
     */
    public function getPriceListName()
    {
        return $this->container['price_list_name'];
    }

    /**
     * Sets price_list_name
     *
     * @param string|null $price_list_name The name of the price list associated with this order unit price
     *
     * @return self
     */
    public function setPriceListName($price_list_name)
    {
        $this->container['price_list_name'] = $price_list_name;

        return $this;
    }

    /**
     * Gets product_id
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->container['product_id'];
    }

    /**
     * Sets product_id
     *
     * @param int|null $product_id The unique identifier of the product associated with this order unit price
     *
     * @return self
     */
    public function setProductId($product_id)
    {
        $this->container['product_id'] = $product_id;

        return $this;
    }

    /**
     * Gets product_code
     *
     * @return string|null
     */
    public function getProductCode()
    {
        return $this->container['product_code'];
    }

    /**
     * Sets product_code
     *
     * @param string|null $product_code The code of the product associated with the stock level
     *
     * @return self
     */
    public function setProductCode($product_code)
    {
        $this->container['product_code'] = $product_code;

        return $this;
    }

    /**
     * Gets product_name
     *
     * @return string|null
     */
    public function getProductName()
    {
        return $this->container['product_name'];
    }

    /**
     * Sets product_name
     *
     * @param string|null $product_name The name of the product associated with this order unit price
     *
     * @return self
     */
    public function setProductName($product_name)
    {
        $this->container['product_name'] = $product_name;

        return $this;
    }

    /**
     * Gets order_unit_id
     *
     * @return int|null
     */
    public function getOrderUnitId()
    {
        return $this->container['order_unit_id'];
    }

    /**
     * Sets order_unit_id
     *
     * @param int|null $order_unit_id The unique identifier of the order unit associated with this order unit price
     *
     * @return self
     */
    public function setOrderUnitId($order_unit_id)
    {
        $this->container['order_unit_id'] = $order_unit_id;

        return $this;
    }

    /**
     * Gets order_unit_name
     *
     * @return string|null
     */
    public function getOrderUnitName()
    {
        return $this->container['order_unit_name'];
    }

    /**
     * Sets order_unit_name
     *
     * @param string|null $order_unit_name The name of the order unit associated with this order unit price
     *
     * @return self
     */
    public function setOrderUnitName($order_unit_name)
    {
        $this->container['order_unit_name'] = $order_unit_name;

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
     * @param \DateTime|null $last_modified_time The last time the order unit associated with this order unit price was modified
     *
     * @return self
     */
    public function setLastModifiedTime($last_modified_time)
    {
        $this->container['last_modified_time'] = $last_modified_time;

        return $this;
    }

    /**
     * Gets tax_rate_id
     *
     * @return int|null
     */
    public function getTaxRateId()
    {
        return $this->container['tax_rate_id'];
    }

    /**
     * Sets tax_rate_id
     *
     * @param int|null $tax_rate_id The unique identifier of the tax rate associated with this order unit price
     *
     * @return self
     */
    public function setTaxRateId($tax_rate_id)
    {
        $this->container['tax_rate_id'] = $tax_rate_id;

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


