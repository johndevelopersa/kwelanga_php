<?php

class SmartEventTO
{
    public $uid;
    public $type;
    public $typeUid;
    public $dataUid;
    public $generalReference1;
    public $generalReference2;
    public $status = "Q"; // only postSmartEventBulk uses this
    public $statusMsg;  // only postSmartEventBulk uses this
    public $metaArr = [];

    public function __construct($type = null)
    {
        if ($type) {
            $this->setType($type);
        }
    }

    public static function fromJSON(string $jsonStr): ?SmartEventTO
    {
        $arr = json_decode($jsonStr, true);
        if (!$arr || !is_array($arr)) {
            return null;
        }
        $seTO = new SmartEventTO();
        foreach ($arr as $key => $value) {
            if (property_exists($seTO, $key)) {
                $seTO->{$key} = $value;
            }
        }
        return $seTO;
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }

    public function toJSON($prettyPrint = false): string
    {
        if ($prettyPrint) {
            return json_encode($this, JSON_PRETTY_PRINT);
        }
        return json_encode($this);
    }

    /**
     * @return array
     */
    public function getMetaArr(): array
    {
        return $this->metaArr;
    }

    /**
     * @param array $metaArr
     * @return SmartEventTO
     */
    public function setMetaArr(array $metaArr): SmartEventTO
    {
        $this->metaArr = $metaArr;
        return $this;
    }

    /**
     * @return int
     */
    public function getUid(): ?int
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SmartEventTO
     */
    public function setType(string $type): SmartEventTO
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeUid(): ?int
    {
        return $this->typeUid;
    }

    /**
     * @param int $typeUid
     * @return SmartEventTO
     */
    public function setTypeUid(int $typeUid): SmartEventTO
    {
        $this->typeUid = $typeUid;
        return $this;
    }

    /**
     * @return int
     */
    public function getDataUid(): int
    {
        return (int)$this->dataUid;
    }

    /**
     * @param int $dataUid
     * @return SmartEventTO
     */
    public function setDataUid(int $dataUid): SmartEventTO
    {
        $this->dataUid = $dataUid;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneralReference1(): string
    {
        return $this->generalReference1;
    }

    /**
     * @param string $generalReference1
     * @return SmartEventTO
     */
    public function setGeneralReference1(string $generalReference1): SmartEventTO
    {
        if (strlen($generalReference1) > 80) {
            $generalReference1 = substr($generalReference1, 0, 77) . "...";
        }
        $this->generalReference1 = $generalReference1;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneralReference2(): string
    {
        return $this->generalReference2;
    }

    /**
     * @param string $generalReference2
     * @return SmartEventTO
     */
    public function setGeneralReference2(string $generalReference2): SmartEventTO
    {
        if (strlen($generalReference2) > 100) {
            $generalReference2 = substr($generalReference2, 0, 97) . "...";
        }
        $this->generalReference2 = $generalReference2;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return SmartEventTO
     */
    public function setStatus(string $status): SmartEventTO
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusMsg(): string
    {
        return $this->statusMsg;
    }

    /**
     * @param string $statusMsg
     * @return SmartEventTO
     */
    public function setStatusMsg(string $statusMsg): SmartEventTO
    {
        $this->statusMsg = $statusMsg;
        return $this;
    }

}