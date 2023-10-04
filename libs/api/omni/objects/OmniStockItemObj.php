<?php


class OmniStockItemObj extends OmniBaseObj
{
    protected $warehouse_description = "";
    protected $stock_code = "";
    protected $stock_description = "";
    protected $level = 0;
    protected $on_order = 0;
    protected $reserved = 0;
    protected $available = 0;
    protected $short = 0;
    protected $cost_price = 0;

    public function __construct($properties = [])
    {
        $this->arrayAssign($properties);
    }

    /**
     * @return string
     */
    public function getStockCode(): string
    {
        return $this->stock_code;
    }

    /**
     * @return string
     */
    public function getStockDescription(): string
    {
        return trim($this->stock_description);
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getOnOrder(): int
    {
        return $this->on_order;
    }

    /**
     * @return int
     */
    public function getReserved(): int
    {
        return $this->reserved;
    }

    /**
     * @return int
     */
    public function getAvailable(): int
    {
        return $this->available;
    }

    /**
     * @return int
     */
    public function getShort(): int
    {
        return $this->short;
    }

    /**
     * @return int
     */
    public function getCostPrice(): float
    {
        return $this->cost_price;
    }


}
