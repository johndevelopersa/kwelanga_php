<?php
/**
 * TimeEntryCreateOrUpdate
 *
 * PHP version 5
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Xero Projects API
 *
 * This is the Xero Projects API
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

namespace XeroAPI\XeroPHP\Models\Project;

use \ArrayAccess;
use \XeroAPI\XeroPHP\ProjectObjectSerializer;
use \XeroAPI\XeroPHP\StringUtil;
/**
 * TimeEntryCreateOrUpdate Class Doc Comment
 *
 * @category Class
 * @package  XeroAPI\XeroPHP
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class TimeEntryCreateOrUpdate implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TimeEntryCreateOrUpdate';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'user_id' => 'string',
        'task_id' => 'string',
        'date_utc' => '\DateTime',
        'duration' => 'int',
        'description' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'user_id' => 'uuid',
        'task_id' => 'uuid',
        'date_utc' => 'date-time',
        'duration' => null,
        'description' => null
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
        'user_id' => 'userId',
        'task_id' => 'taskId',
        'date_utc' => 'dateUtc',
        'duration' => 'duration',
        'description' => 'description'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'user_id' => 'setUserId',
        'task_id' => 'setTaskId',
        'date_utc' => 'setDateUtc',
        'duration' => 'setDuration',
        'description' => 'setDescription'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'user_id' => 'getUserId',
        'task_id' => 'getTaskId',
        'date_utc' => 'getDateUtc',
        'duration' => 'getDuration',
        'description' => 'getDescription'
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
        $this->container['user_id'] = isset($data['user_id']) ? $data['user_id'] : null;
        $this->container['task_id'] = isset($data['task_id']) ? $data['task_id'] : null;
        $this->container['date_utc'] = isset($data['date_utc']) ? $data['date_utc'] : null;
        $this->container['duration'] = isset($data['duration']) ? $data['duration'] : null;
        $this->container['description'] = isset($data['description']) ? $data['description'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['user_id'] === null) {
            $invalidProperties[] = "'user_id' can't be null";
        }
        if ($this->container['task_id'] === null) {
            $invalidProperties[] = "'task_id' can't be null";
        }
        if ($this->container['date_utc'] === null) {
            $invalidProperties[] = "'date_utc' can't be null";
        }
        if ($this->container['duration'] === null) {
            $invalidProperties[] = "'duration' can't be null";
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
     * Gets user_id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->container['user_id'];
    }

    /**
     * Sets user_id
     *
     * @param string $user_id The xero user identifier of the person logging the time.
     *
     * @return $this
     */
    public function setUserId($user_id)
    {

        $this->container['user_id'] = $user_id;

        return $this;
    }



    /**
     * Gets task_id
     *
     * @return string
     */
    public function getTaskId()
    {
        return $this->container['task_id'];
    }

    /**
     * Sets task_id
     *
     * @param string $task_id Identifier of the task that time entry is logged against.
     *
     * @return $this
     */
    public function setTaskId($task_id)
    {

        $this->container['task_id'] = $task_id;

        return $this;
    }



    /**
     * Gets date_utc
     *
     * @return \DateTime
     */
    public function getDateUtc()
    {
        return $this->container['date_utc'];
    }

    /**
     * Sets date_utc
     *
     * @param \DateTime $date_utc Date time entry is logged on. UTC Date Time in ISO-8601 format.
     *
     * @return $this
     */
    public function setDateUtc($date_utc)
    {

        $this->container['date_utc'] = $date_utc;

        return $this;
    }



    /**
     * Gets duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->container['duration'];
    }

    /**
     * Sets duration
     *
     * @param int $duration Number of minutes to be logged. Duration is between 1 and 59940 inclusively.
     *
     * @return $this
     */
    public function setDuration($duration)
    {

        $this->container['duration'] = $duration;

        return $this;
    }



    /**
     * Gets description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->container['description'];
    }

    /**
     * Sets description
     *
     * @param string|null $description An optional description of the time entry, will be set to null if not provided during update.
     *
     * @return $this
     */
    public function setDescription($description)
    {

        $this->container['description'] = $description;

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
            ProjectObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }
}


