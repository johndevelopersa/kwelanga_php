<?php
/**
 * BookDepreciationDetail
 *
 * PHP version 5
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Xero Assets API
 *
 * This is the Xero Assets API
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

namespace XeroAPI\XeroPHP\Models\Asset;

use \ArrayAccess;
use \XeroAPI\XeroPHP\AssetObjectSerializer;
use \XeroAPI\XeroPHP\StringUtil;
/**
 * BookDepreciationDetail Class Doc Comment
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class BookDepreciationDetail implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'BookDepreciationDetail';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'current_capital_gain' => 'float',
        'current_gain_loss' => 'float',
        'depreciation_start_date' => '\DateTime',
        'cost_limit' => 'float',
        'residual_value' => 'float',
        'prior_accum_depreciation_amount' => 'float',
        'current_accum_depreciation_amount' => 'float'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'current_capital_gain' => 'float',
        'current_gain_loss' => 'float',
        'depreciation_start_date' => 'date',
        'cost_limit' => 'float',
        'residual_value' => 'float',
        'prior_accum_depreciation_amount' => 'float',
        'current_accum_depreciation_amount' => 'float'
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
        'current_capital_gain' => 'currentCapitalGain',
        'current_gain_loss' => 'currentGainLoss',
        'depreciation_start_date' => 'depreciationStartDate',
        'cost_limit' => 'costLimit',
        'residual_value' => 'residualValue',
        'prior_accum_depreciation_amount' => 'priorAccumDepreciationAmount',
        'current_accum_depreciation_amount' => 'currentAccumDepreciationAmount'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'current_capital_gain' => 'setCurrentCapitalGain',
        'current_gain_loss' => 'setCurrentGainLoss',
        'depreciation_start_date' => 'setDepreciationStartDate',
        'cost_limit' => 'setCostLimit',
        'residual_value' => 'setResidualValue',
        'prior_accum_depreciation_amount' => 'setPriorAccumDepreciationAmount',
        'current_accum_depreciation_amount' => 'setCurrentAccumDepreciationAmount'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'current_capital_gain' => 'getCurrentCapitalGain',
        'current_gain_loss' => 'getCurrentGainLoss',
        'depreciation_start_date' => 'getDepreciationStartDate',
        'cost_limit' => 'getCostLimit',
        'residual_value' => 'getResidualValue',
        'prior_accum_depreciation_amount' => 'getPriorAccumDepreciationAmount',
        'current_accum_depreciation_amount' => 'getCurrentAccumDepreciationAmount'
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
        $this->container['current_capital_gain'] = isset($data['current_capital_gain']) ? $data['current_capital_gain'] : null;
        $this->container['current_gain_loss'] = isset($data['current_gain_loss']) ? $data['current_gain_loss'] : null;
        $this->container['depreciation_start_date'] = isset($data['depreciation_start_date']) ? $data['depreciation_start_date'] : null;
        $this->container['cost_limit'] = isset($data['cost_limit']) ? $data['cost_limit'] : null;
        $this->container['residual_value'] = isset($data['residual_value']) ? $data['residual_value'] : null;
        $this->container['prior_accum_depreciation_amount'] = isset($data['prior_accum_depreciation_amount']) ? $data['prior_accum_depreciation_amount'] : null;
        $this->container['current_accum_depreciation_amount'] = isset($data['current_accum_depreciation_amount']) ? $data['current_accum_depreciation_amount'] : null;
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
     * Gets current_capital_gain
     *
     * @return float|null
     */
    public function getCurrentCapitalGain()
    {
        return $this->container['current_capital_gain'];
    }

    /**
     * Sets current_capital_gain
     *
     * @param float|null $current_capital_gain When an asset is disposed, this will be the sell price minus the purchase price if a profit was made.
     *
     * @return $this
     */
    public function setCurrentCapitalGain($current_capital_gain)
    {

        $this->container['current_capital_gain'] = $current_capital_gain;

        return $this;
    }



    /**
     * Gets current_gain_loss
     *
     * @return float|null
     */
    public function getCurrentGainLoss()
    {
        return $this->container['current_gain_loss'];
    }

    /**
     * Sets current_gain_loss
     *
     * @param float|null $current_gain_loss When an asset is disposed, this will be the lowest one of sell price or purchase price, minus the current book value.
     *
     * @return $this
     */
    public function setCurrentGainLoss($current_gain_loss)
    {

        $this->container['current_gain_loss'] = $current_gain_loss;

        return $this;
    }



    /**
     * Gets depreciation_start_date
     *
     * @return \DateTime|null
     */
    public function getDepreciationStartDate()
    {
        return $this->container['depreciation_start_date'];
    }

    /**
     * Sets depreciation_start_date
     *
     * @param \DateTime|null $depreciation_start_date YYYY-MM-DD
     *
     * @return $this
     */
    public function setDepreciationStartDate($depreciation_start_date)
    {

        $this->container['depreciation_start_date'] = $depreciation_start_date;

        return $this;
    }



    /**
     * Gets cost_limit
     *
     * @return float|null
     */
    public function getCostLimit()
    {
        return $this->container['cost_limit'];
    }

    /**
     * Sets cost_limit
     *
     * @param float|null $cost_limit The value of the asset you want to depreciate, if this is less than the cost of the asset.
     *
     * @return $this
     */
    public function setCostLimit($cost_limit)
    {

        $this->container['cost_limit'] = $cost_limit;

        return $this;
    }



    /**
     * Gets residual_value
     *
     * @return float|null
     */
    public function getResidualValue()
    {
        return $this->container['residual_value'];
    }

    /**
     * Sets residual_value
     *
     * @param float|null $residual_value The value of the asset remaining when you've fully depreciated it.
     *
     * @return $this
     */
    public function setResidualValue($residual_value)
    {

        $this->container['residual_value'] = $residual_value;

        return $this;
    }



    /**
     * Gets prior_accum_depreciation_amount
     *
     * @return float|null
     */
    public function getPriorAccumDepreciationAmount()
    {
        return $this->container['prior_accum_depreciation_amount'];
    }

    /**
     * Sets prior_accum_depreciation_amount
     *
     * @param float|null $prior_accum_depreciation_amount All depreciation prior to the current financial year.
     *
     * @return $this
     */
    public function setPriorAccumDepreciationAmount($prior_accum_depreciation_amount)
    {

        $this->container['prior_accum_depreciation_amount'] = $prior_accum_depreciation_amount;

        return $this;
    }



    /**
     * Gets current_accum_depreciation_amount
     *
     * @return float|null
     */
    public function getCurrentAccumDepreciationAmount()
    {
        return $this->container['current_accum_depreciation_amount'];
    }

    /**
     * Sets current_accum_depreciation_amount
     *
     * @param float|null $current_accum_depreciation_amount All depreciation occurring in the current financial year.
     *
     * @return $this
     */
    public function setCurrentAccumDepreciationAmount($current_accum_depreciation_amount)
    {

        $this->container['current_accum_depreciation_amount'] = $current_accum_depreciation_amount;

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
            AssetObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }
}

