<?php

class ErrorTO
{
    public $type;
    public $description = '';
    public $identifier;
    public $identifier2;
    public $object;
    public $sql;

    /**
     * @param string $desc
     * @param $identifier
     * @param $identifier2
     * @return ErrorTO
     */
    public static function NewError(string $desc, $identifier = null, $identifier2 = null): ErrorTO
    {
        return (new ErrorTO())
            ->setType(FLAG_ERRORTO_ERROR)
            ->setDescription($desc)
            ->setIdentifier($identifier)
            ->setIdentifier2($identifier2);
    }

    /**
     * @param string $desc
     * @param $identifier
     * @param $identifier2
     * @return ErrorTO
     */
    public static function NewSuccess(string $desc, $identifier = null, $identifier2 = null): ErrorTO
    {
        return (new ErrorTO())
            ->setType(FLAG_ERRORTO_SUCCESS)
            ->setDescription($desc)
            ->setIdentifier($identifier)
            ->setIdentifier2($identifier2);
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isType(FLAG_ERRORTO_ERROR);
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isType(FLAG_ERRORTO_SUCCESS);
    }

    /**
     * @param $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type == $type;
    }

    //getters and setters

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ErrorTO
     */
    public function setType(string $type): ErrorTO
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ErrorTO
     */
    public function setDescription(string $description): ErrorTO
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @return ErrorTO
     */
    public function setIdentifier($identifier): ErrorTO
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier2()
    {
        return $this->identifier2;
    }

    /**
     * @param mixed $identifier2
     * @return ErrorTO
     */
    public function setIdentifier2($identifier2): ErrorTO
    {
        $this->identifier2 = $identifier2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     * @return ErrorTO
     */
    public function setObject($object): ErrorTO
    {
        $this->object = $object;
        return $this;
    }

}