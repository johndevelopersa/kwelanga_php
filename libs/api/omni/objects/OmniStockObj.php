<?php


class OmniStockObj extends OmniBaseObj
{

    /* @var OmniStockItemObj[] */
    protected $autostocklevels = [];

    public function __construct($properties = [])
    {
        if (is_array($properties) && !empty($properties["autostocklevels"]) && count($properties['autostocklevels'])) {
            foreach($properties["autostocklevels"] as $row) {
                $this->autostocklevels[] = new OmniStockItemObj($row);
            }
        }
    }

    /**
     * @return OmniStockItemObj[]
     */
    public function getAutostocklevels(): array
    {
        return $this->autostocklevels;
    }



}
