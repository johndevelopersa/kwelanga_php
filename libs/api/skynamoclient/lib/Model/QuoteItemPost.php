<?php
/**
 * QuoteItemPost
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
 * QuoteItemPost Class Doc Comment
 *
 * @category Class
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class QuoteItemPost implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'QuoteItemPost';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'product_id' => 'int',
        'product_code' => 'string',
        'unit_name' => 'string',
        'multiplier' => 'float',
        'quantity' => 'float',
        'price' => 'float',
        'list_price' => 'float',
        'unit_price' => 'float',
        'tax_rate_id' => 'int',
        'tax_rate_value' => 'float'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'product_id' => null,
        'product_code' => null,
        'unit_name' => null,
        'multiplier' => 'float',
        'quantity' => 'float',
        'price' => 'float',
        'list_price' => 'float',
        'unit_price' => 'float',
        'tax_rate_id' => null,
        'tax_rate_value' => 'float'
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
        'product_id' => 'product_id',
        'product_code' => 'product_code',
        'unit_name' => 'unit_name',
        'multiplier' => 'multiplier',
        'quantity' => 'quantity',
        'price' => 'price',
        'list_price' => 'list_price',
        'unit_price' => 'unit_price',
        'tax_rate_id' => 'tax_rate_id',
        'tax_rate_value' => 'tax_rate_value'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'product_id' => 'setProductId',
        'product_code' => 'setProductCode',
        'unit_name' => 'setUnitName',
        'multiplier' => 'setMultiplier',
        'quantity' => 'setQuantity',
        'price' => 'setPrice',
        'list_price' => 'setListPrice',
        'unit_price' => 'setUnitPrice',
        'tax_rate_id' => 'setTaxRateId',
        'tax_rate_value' => 'setTaxRateValue'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'product_id' => 'getProductId',
        'product_code' => 'getProductCode',
        'unit_name' => 'getUnitName',
        'multiplier' => 'getMultiplier',
        'quantity' => 'getQuantity',
        'price' => 'getPrice',
        'list_price' => 'getListPrice',
        'unit_price' => 'getUnitPrice',
        'tax_rate_id' => 'getTaxRateId',
        'tax_rate_value' => 'getTaxRateValue'
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
        $this->container['product_id'] = $data['product_id'] ?? null;
        $this->container['product_code'] = $data['product_code'] ?? null;
        $this->container['unit_name'] = $data['unit_name'] ?? null;
        $this->container['multiplier'] = $data['multiplier'] ?? null;
        $this->container['quantity'] = $data['quantity'] ?? null;
        $this->container['price'] = $data['price'] ?? null;
        $this->container['list_price'] = $data['list_price'] ?? null;
        $this->container['unit_price'] = $data['unit_price'] ?? null;
        $this->container['tax_rate_id'] = $data['tax_rate_id'] ?? null;
        $this->container['tax_rate_value'] = $data['tax_rate_value'] ?? null;
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
     * @param int|null $product_id The unique id of the product that has been included in the quote
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
     * @param string|null $product_code The unique code of the product that has been included in the quote
     *
     * @return self
     */
    public function setProductCode($product_code)
    {
        $this->container['product_code'] = $product_code;

        return $this;
    }

    /**
     * Gets unit_name
     *
     * @return string|null
     */
    public function getUnitName()
    {
        return $this->container['unit_name'];
    }

    /**
     * Sets unit_name
     *
     * @param string|null $unit_name The item unit name
     *
     * @return self
     */
    public function setUnitName($unit_name)
    {
        $this->container['unit_name'] = $unit_name;

        return $this;
    }

    /**
     * Gets multiplier
     *
     * @return float|null
     */
    public function getMultiplier()
    {
        return $this->container['multiplier'];
    }

    /**
     * Sets multiplier
     *
     * @param float|null $multiplier The item multiplier
     *
     * @return self
     */
    public function setMultiplier($multiplier)
    {
        $this->container['multiplier'] = $multiplier;

        return $this;
    }

    /**
     * Gets quantity
     *
     * @return float|null
     */
    public function getQuantity()
    {
        return $this->container['quantity'];
    }

    /**
     * Sets quantity
     *
     * @param float|null $quantity The quantity of the product that has been included in the quote
     *
     * @return self
     */
    public function setQuantity($quantity)
    {
        $this->container['quantity'] = $quantity;

        return $this;
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
     * @param float|null $price The total price of the product included in the quote
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->container['price'] = $price;

        return $this;
    }

    /**
     * Gets list_price
     *
     * @return float|null
     */
    public function getListPrice()
    {
        return $this->container['list_price'];
    }

    /**
     * Sets list_price
     *
     * @param float|null $list_price The price list price of the product included in the quote
     *
     * @return self
     */
    public function setListPrice($list_price)
    {
        $this->container['list_price'] = $list_price;

        return $this;
    }

    /**
     * Gets unit_price
     *
     * @return float|null
     */
    public function getUnitPrice()
    {
        return $this->container['unit_price'];
    }

    /**
     * Sets unit_price
     *
     * @param float|null $unit_price The unit price of the product included in the quote
     *
     * @return self
     */
    public function setUnitPrice($unit_price)
    {
        $this->container['unit_price'] = $unit_price;

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
     * @param int|null $tax_rate_id The unique identifier of the tax rate associated with the item
     *
     * @return self
     */
    public function setTaxRateId($tax_rate_id)
    {
        $this->container['tax_rate_id'] = $tax_rate_id;

        return $this;
    }

    /**
     * Gets tax_rate_value
     *
     * @return float|null
     */
    public function getTaxRateValue()
    {
        return $this->container['tax_rate_value'];
    }

    /**
     * Sets tax_rate_value
     *
     * @param float|null $tax_rate_value The value of the tax rate associated with the item
     *
     * @return self
     */
    public function setTaxRateValue($tax_rate_value)
    {
        $this->container['tax_rate_value'] = $tax_rate_value;

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


