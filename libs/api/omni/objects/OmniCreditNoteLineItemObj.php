<?php


class OmniCreditNoteLineItemObj extends OmniBaseObj
{
    protected $id; //int
    protected $reference; //String
    protected $line_no; //int
    protected $sequence_no; //int
    protected $line_type; //String
    protected $stock_code; //String
    protected $description; //String
    protected $warehouse; //String
    protected $ordered; //int
    protected $measure; //String
    protected $delivered; //int
    protected $invoiced; //int
    protected $credited; //int
    protected $to_invoice; //int
    protected $to_deliver; //int
    protected $to_credit; //int
    protected $back_orders; //String
    protected $cost_price; //int
    protected $cost_price_per; //int
    protected $unit_cost_price; //int
    protected $selling_price; //int
    protected $selling_price_per; //int
    protected $discount; //int
    protected $vat_code; //String
    protected $vat_rate; //int
    protected $revenue_acc_code; //String
    protected $sub_job_category_code; //String
    protected $deal_id; //int
    protected $deal_group_no; //int
    protected $no_discount; //String
    protected $discount_type; //String

    protected $store_name; //String
    protected $captured_by; //String
    protected $analysis_4; //String
    protected $due_date; //String
    protected $reference_no; //String
    protected $source_reference_no; //String
    protected $source_type; //String
    protected $job_no; //String
    protected $measure_description; //String
    protected $ext_price; //int
    protected $ext_discount_value; //int
    protected $ext_price_excl; //int
    protected $ext_price_incl; //int
    protected $vat_value; //int
    protected $local_vat_value; //int
    protected $tax_type; //String
    protected $unit_price; //int
    protected $ext_cost_price; //int
    protected $gross_profit; //int
    protected $gp_percent; //int
    protected $markup_percent; //int
    protected $quantity; //int
    protected $unit_weight; //int
    protected $unit_volume; //int
    protected $ext_weight; //int
    protected $ext_volume; //int
    protected $stock_level; //int
    protected $min_level; //int
    protected $qty_short; //int
    protected $on_order; //int
    protected $stock_type; //String
    protected $pack; //int
    protected $bin_location_code; //String
    protected $bin_location_description; //String
    protected $product_group_code; //String
    protected $product_group_description; //String
    protected $stock_category_code; //String
    protected $stock_category_description; //String
    protected $stock_sub_category_code; //String
    protected $stock_sub_category_description; //String
    protected $revenue_acc_description; //String
    protected $sub_job_category_description; //String
    protected $stock_description; //String
    protected $warehouse_description; //String
    protected $bar_code; //String
    protected $extra_info_1; //String
    protected $extra_info_2; //String
    protected $extra_info_3; //String
    protected $extra_info_4; //String
    protected $extra_info_5; //String
    protected $extra_info_6; //String
    protected $extra_info_7; //String
    protected $extra_info_8; //String
    protected $use_serial_no; //boolean
    protected $serial_no; //String
    protected $use_good_stock; //boolean
    protected $good_stock; //String
    protected $use_redundant; //boolean
    protected $redundant; //String
    protected $use_tracking_no_4; //boolean
    protected $tracking_no_4; //String
    protected $warranty_period; //int
    protected $deal_narrative; //String
    protected $promotion_name; //object
	protected $tracking_nos = [];
	
    protected function initializeSpecialProperties()
    {
        //$this->{"principal_inv_#"} = null; //String
    }

	public function addTrackingNumbers($serial_no, $batch_no, $tracking_no_3, $tracking_no_4, $qty){
		$this->tracking_nos[] = [
			"serial_no" => $serial_no,
			"batch_no" => $batch_no,
			"tracking_no_3" => $tracking_no_3,
			"tracking_no_4" => $tracking_no_4,
			"qty" => $qty		
		];		
	}
	
	public function getTrackingNumbers(){
		return $this->tracking_nos;		
	}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return OmniSalesOrderLineItemObj
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrincipalInv()
    {
        return $this->{"principal_inv_#"};
    }

    /**
     * @param mixed $principal_inv
     * @return OmniSalesOrderLineItemObj
     */
    public function setPrincipalInv($principal_inv)
    {
        $this->{"principal_inv_#"} = $principal_inv;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     * @return OmniSalesOrderLineItemObj
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLineNo()
    {
        return $this->line_no;
    }

    /**
     * @param mixed $line_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setLineNo($line_no)
    {
        $this->line_no = $line_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSequenceNo()
    {
        return $this->sequence_no;
    }

    /**
     * @param mixed $sequence_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setSequenceNo($sequence_no)
    {
        $this->sequence_no = $sequence_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLineType()
    {
        return $this->line_type;
    }

    /**
     * @param mixed $line_type
     * @return OmniSalesOrderLineItemObj
     */
    public function setLineType($line_type)
    {
        $this->line_type = $line_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockCode()
    {
        return $this->stock_code;
    }

    /**
     * @param mixed $stock_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockCode($stock_code)
    {
        $this->stock_code = $stock_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return OmniSalesOrderLineItemObj
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param mixed $warehouse
     * @return OmniSalesOrderLineItemObj
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrdered()
    {
        return $this->ordered;
    }

    /**
     * @param mixed $ordered
     * @return OmniSalesOrderLineItemObj
     */
    public function setOrdered($ordered)
    {
        $this->ordered = $ordered;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * @param mixed $measure
     * @return OmniSalesOrderLineItemObj
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * @param mixed $delivered
     * @return OmniSalesOrderLineItemObj
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiced()
    {
        return $this->invoiced;
    }

    /**
     * @param mixed $invoiced
     * @return OmniSalesOrderLineItemObj
     */
    public function setInvoiced($invoiced)
    {
        $this->invoiced = $invoiced;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredited()
    {
        return $this->credited;
    }

    /**
     * @param mixed $credited
     * @return OmniSalesOrderLineItemObj
     */
    public function setCredited($credited)
    {
        $this->credited = $credited;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToInvoice()
    {
        return $this->to_invoice;
    }

    /**
     * @param mixed $to_invoice
     * @return OmniSalesOrderLineItemObj
     */
    public function setToInvoice($to_invoice)
    {
        $this->to_invoice = $to_invoice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToDeliver()
    {
        return $this->to_deliver;
    }

    /**
     * @param mixed $to_deliver
     * @return OmniSalesOrderLineItemObj
     */
    public function setToDeliver($to_deliver)
    {
        $this->to_deliver = $to_deliver;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToCredit()
    {
        return $this->to_credit;
    }

    /**
     * @param mixed $to_credit
     * @return OmniSalesOrderLineItemObj
     */
    public function setToCredit($to_credit)
    {
        $this->to_credit = $to_credit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackOrders()
    {
        return $this->back_orders;
    }

    /**
     * @param mixed $back_orders
     * @return OmniSalesOrderLineItemObj
     */
    public function setBackOrders($back_orders)
    {
        $this->back_orders = $back_orders;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostPrice()
    {
        return $this->cost_price;
    }

    /**
     * @param mixed $cost_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setCostPrice($cost_price)
    {
        $this->cost_price = $cost_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostPricePer()
    {
        return $this->cost_price_per;
    }

    /**
     * @param mixed $cost_price_per
     * @return OmniSalesOrderLineItemObj
     */
    public function setCostPricePer($cost_price_per)
    {
        $this->cost_price_per = $cost_price_per;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitCostPrice()
    {
        return $this->unit_cost_price;
    }

    /**
     * @param mixed $unit_cost_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setUnitCostPrice($unit_cost_price)
    {
        $this->unit_cost_price = $unit_cost_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellingPrice()
    {
        return $this->selling_price;
    }

    /**
     * @param mixed $selling_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setSellingPrice($selling_price)
    {
        $this->selling_price = (float)$selling_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellingPricePer()
    {
        return $this->selling_price_per;
    }

    /**
     * @param mixed $selling_price_per
     * @return OmniSalesOrderLineItemObj
     */
    public function setSellingPricePer($selling_price_per)
    {
        $this->selling_price_per = $selling_price_per;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     * @return OmniSalesOrderLineItemObj
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatCode()
    {
        return $this->vat_code;
    }

    /**
     * @param mixed $vat_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setVatCode($vat_code)
    {
        $this->vat_code = $vat_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRate()
    {
        return $this->vat_rate;
    }

    /**
     * @param mixed $vat_rate
     * @return OmniSalesOrderLineItemObj
     */
    public function setVatRate($vat_rate)
    {
        $this->vat_rate = (float)$vat_rate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRevenueAccCode()
    {
        return $this->revenue_acc_code;
    }

    /**
     * @param mixed $revenue_acc_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setRevenueAccCode($revenue_acc_code)
    {
        $this->revenue_acc_code = $revenue_acc_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubJobCategoryCode()
    {
        return $this->sub_job_category_code;
    }

    /**
     * @param mixed $sub_job_category_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setSubJobCategoryCode($sub_job_category_code)
    {
        $this->sub_job_category_code = $sub_job_category_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDealId()
    {
        return $this->deal_id;
    }

    /**
     * @param mixed $deal_id
     * @return OmniSalesOrderLineItemObj
     */
    public function setDealId($deal_id)
    {
        $this->deal_id = $deal_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDealGroupNo()
    {
        return $this->deal_group_no;
    }

    /**
     * @param mixed $deal_group_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setDealGroupNo($deal_group_no)
    {
        $this->deal_group_no = $deal_group_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNoDiscount()
    {
        return $this->no_discount;
    }

    /**
     * @param mixed $no_discount
     * @return OmniSalesOrderLineItemObj
     */
    public function setNoDiscount($no_discount)
    {
        $this->no_discount = $no_discount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountType()
    {
        return $this->discount_type;
    }

    /**
     * @param mixed $discount_type
     * @return OmniSalesOrderLineItemObj
     */
    public function setDiscountType($discount_type)
    {
        $this->discount_type = $discount_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreName()
    {
        return $this->store_name;
    }

    /**
     * @param mixed $store_name
     * @return OmniSalesOrderLineItemObj
     */
    public function setStoreName($store_name)
    {
        $this->store_name = $store_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapturedBy()
    {
        return $this->captured_by;
    }

    /**
     * @param mixed $captured_by
     * @return OmniSalesOrderLineItemObj
     */
    public function setCapturedBy($captured_by)
    {
        $this->captured_by = $captured_by;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnalysis4()
    {
        return $this->analysis_4;
    }

    /**
     * @param mixed $analysis_4
     * @return OmniSalesOrderLineItemObj
     */
    public function setAnalysis4($analysis_4)
    {
        $this->analysis_4 = $analysis_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * @param mixed $due_date
     * @return OmniSalesOrderLineItemObj
     */
    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferenceNo()
    {
        return $this->reference_no;
    }

    /**
     * @param mixed $reference_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setReferenceNo($reference_no)
    {
        $this->reference_no = $reference_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceReferenceNo()
    {
        return $this->source_reference_no;
    }

    /**
     * @param mixed $source_reference_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setSourceReferenceNo($source_reference_no)
    {
        $this->source_reference_no = $source_reference_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceType()
    {
        return $this->source_type;
    }

    /**
     * @param mixed $source_type
     * @return OmniSalesOrderLineItemObj
     */
    public function setSourceType($source_type)
    {
        $this->source_type = $source_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobNo()
    {
        return $this->job_no;
    }

    /**
     * @param mixed $job_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setJobNo($job_no)
    {
        $this->job_no = $job_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasureDescription()
    {
        return $this->measure_description;
    }

    /**
     * @param mixed $measure_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setMeasureDescription($measure_description)
    {
        $this->measure_description = $measure_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtPrice()
    {
        return $this->ext_price;
    }

    /**
     * @param mixed $ext_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtPrice($ext_price)
    {
        $this->ext_price = (float)$ext_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtDiscountValue()
    {
        return $this->ext_discount_value;
    }

    /**
     * @param mixed $ext_discount_value
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtDiscountValue($ext_discount_value)
    {
        $this->ext_discount_value = (float)$ext_discount_value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtPriceExcl()
    {
        return $this->ext_price_excl;
    }

    /**
     * @param mixed $ext_price_excl
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtPriceExcl($ext_price_excl)
    {
        $this->ext_price_excl = (float)$ext_price_excl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtPriceIncl()
    {
        return $this->ext_price_incl;
    }

    /**
     * @param mixed $ext_price_incl
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtPriceIncl($ext_price_incl)
    {
        $this->ext_price_incl = (float)$ext_price_incl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatValue()
    {
        return $this->vat_value;
    }

    /**
     * @param mixed $vat_value
     * @return OmniSalesOrderLineItemObj
     */
    public function setVatValue($vat_value)
    {
        $this->vat_value = (float)$vat_value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalVatValue()
    {
        return $this->local_vat_value;
    }

    /**
     * @param mixed $local_vat_value
     * @return OmniSalesOrderLineItemObj
     */
    public function setLocalVatValue($local_vat_value)
    {
        $this->local_vat_value = $local_vat_value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxType()
    {
        return $this->tax_type;
    }

    /**
     * @param mixed $tax_type
     * @return OmniSalesOrderLineItemObj
     */
    public function setTaxType($tax_type)
    {
        $this->tax_type = $tax_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @param mixed $unit_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtCostPrice()
    {
        return $this->ext_cost_price;
    }

    /**
     * @param mixed $ext_cost_price
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtCostPrice($ext_cost_price)
    {
        $this->ext_cost_price = $ext_cost_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGrossProfit()
    {
        return $this->gross_profit;
    }

    /**
     * @param mixed $gross_profit
     * @return OmniSalesOrderLineItemObj
     */
    public function setGrossProfit($gross_profit)
    {
        $this->gross_profit = $gross_profit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGpPercent()
    {
        return $this->gp_percent;
    }

    /**
     * @param mixed $gp_percent
     * @return OmniSalesOrderLineItemObj
     */
    public function setGpPercent($gp_percent)
    {
        $this->gp_percent = $gp_percent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMarkupPercent()
    {
        return $this->markup_percent;
    }

    /**
     * @param mixed $markup_percent
     * @return OmniSalesOrderLineItemObj
     */
    public function setMarkupPercent($markup_percent)
    {
        $this->markup_percent = $markup_percent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return OmniSalesOrderLineItemObj
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitWeight()
    {
        return $this->unit_weight;
    }

    /**
     * @param mixed $unit_weight
     * @return OmniSalesOrderLineItemObj
     */
    public function setUnitWeight($unit_weight)
    {
        $this->unit_weight = $unit_weight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitVolume()
    {
        return $this->unit_volume;
    }

    /**
     * @param mixed $unit_volume
     * @return OmniSalesOrderLineItemObj
     */
    public function setUnitVolume($unit_volume)
    {
        $this->unit_volume = $unit_volume;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtWeight()
    {
        return $this->ext_weight;
    }

    /**
     * @param mixed $ext_weight
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtWeight($ext_weight)
    {
        $this->ext_weight = $ext_weight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtVolume()
    {
        return $this->ext_volume;
    }

    /**
     * @param mixed $ext_volume
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtVolume($ext_volume)
    {
        $this->ext_volume = $ext_volume;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockLevel()
    {
        return $this->stock_level;
    }

    /**
     * @param mixed $stock_level
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockLevel($stock_level)
    {
        $this->stock_level = $stock_level;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinLevel()
    {
        return $this->min_level;
    }

    /**
     * @param mixed $min_level
     * @return OmniSalesOrderLineItemObj
     */
    public function setMinLevel($min_level)
    {
        $this->min_level = $min_level;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtyShort()
    {
        return $this->qty_short;
    }

    /**
     * @param mixed $qty_short
     * @return OmniSalesOrderLineItemObj
     */
    public function setQtyShort($qty_short)
    {
        $this->qty_short = $qty_short;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnOrder()
    {
        return $this->on_order;
    }

    /**
     * @param mixed $on_order
     * @return OmniSalesOrderLineItemObj
     */
    public function setOnOrder($on_order)
    {
        $this->on_order = $on_order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockType()
    {
        return $this->stock_type;
    }

    /**
     * @param mixed $stock_type
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockType($stock_type)
    {
        $this->stock_type = $stock_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * @param mixed $pack
     * @return OmniSalesOrderLineItemObj
     */
    public function setPack($pack)
    {
        $this->pack = $pack;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBinLocationCode()
    {
        return $this->bin_location_code;
    }

    /**
     * @param mixed $bin_location_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setBinLocationCode($bin_location_code)
    {
        $this->bin_location_code = $bin_location_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBinLocationDescription()
    {
        return $this->bin_location_description;
    }

    /**
     * @param mixed $bin_location_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setBinLocationDescription($bin_location_description)
    {
        $this->bin_location_description = $bin_location_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductGroupCode()
    {
        return $this->product_group_code;
    }

    /**
     * @param mixed $product_group_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setProductGroupCode($product_group_code)
    {
        $this->product_group_code = $product_group_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductGroupDescription()
    {
        return $this->product_group_description;
    }

    /**
     * @param mixed $product_group_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setProductGroupDescription($product_group_description)
    {
        $this->product_group_description = $product_group_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockCategoryCode()
    {
        return $this->stock_category_code;
    }

    /**
     * @param mixed $stock_category_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockCategoryCode($stock_category_code)
    {
        $this->stock_category_code = $stock_category_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockCategoryDescription()
    {
        return $this->stock_category_description;
    }

    /**
     * @param mixed $stock_category_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockCategoryDescription($stock_category_description)
    {
        $this->stock_category_description = $stock_category_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockSubCategoryCode()
    {
        return $this->stock_sub_category_code;
    }

    /**
     * @param mixed $stock_sub_category_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockSubCategoryCode($stock_sub_category_code)
    {
        $this->stock_sub_category_code = $stock_sub_category_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockSubCategoryDescription()
    {
        return $this->stock_sub_category_description;
    }

    /**
     * @param mixed $stock_sub_category_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockSubCategoryDescription($stock_sub_category_description)
    {
        $this->stock_sub_category_description = $stock_sub_category_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRevenueAccDescription()
    {
        return $this->revenue_acc_description;
    }

    /**
     * @param mixed $revenue_acc_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setRevenueAccDescription($revenue_acc_description)
    {
        $this->revenue_acc_description = $revenue_acc_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubJobCategoryDescription()
    {
        return $this->sub_job_category_description;
    }

    /**
     * @param mixed $sub_job_category_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setSubJobCategoryDescription($sub_job_category_description)
    {
        $this->sub_job_category_description = $sub_job_category_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockDescription()
    {
        return $this->stock_description;
    }

    /**
     * @param mixed $stock_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setStockDescription($stock_description)
    {
        $this->stock_description = $stock_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWarehouseDescription()
    {
        return $this->warehouse_description;
    }

    /**
     * @param mixed $warehouse_description
     * @return OmniSalesOrderLineItemObj
     */
    public function setWarehouseDescription($warehouse_description)
    {
        $this->warehouse_description = $warehouse_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBarCode()
    {
        return $this->bar_code;
    }

    /**
     * @param mixed $bar_code
     * @return OmniSalesOrderLineItemObj
     */
    public function setBarCode($bar_code)
    {
        $this->bar_code = $bar_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo1()
    {
        return $this->extra_info_1;
    }

    /**
     * @param mixed $extra_info_1
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo1($extra_info_1)
    {
        $this->extra_info_1 = $extra_info_1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo2()
    {
        return $this->extra_info_2;
    }

    /**
     * @param mixed $extra_info_2
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo2($extra_info_2)
    {
        $this->extra_info_2 = $extra_info_2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo3()
    {
        return $this->extra_info_3;
    }

    /**
     * @param mixed $extra_info_3
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo3($extra_info_3)
    {
        $this->extra_info_3 = $extra_info_3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo4()
    {
        return $this->extra_info_4;
    }

    /**
     * @param mixed $extra_info_4
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo4($extra_info_4)
    {
        $this->extra_info_4 = $extra_info_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo5()
    {
        return $this->extra_info_5;
    }

    /**
     * @param mixed $extra_info_5
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo5($extra_info_5)
    {
        $this->extra_info_5 = $extra_info_5;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo6()
    {
        return $this->extra_info_6;
    }

    /**
     * @param mixed $extra_info_6
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo6($extra_info_6)
    {
        $this->extra_info_6 = $extra_info_6;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo7()
    {
        return $this->extra_info_7;
    }

    /**
     * @param mixed $extra_info_7
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo7($extra_info_7)
    {
        $this->extra_info_7 = $extra_info_7;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo8()
    {
        return $this->extra_info_8;
    }

    /**
     * @param mixed $extra_info_8
     * @return OmniSalesOrderLineItemObj
     */
    public function setExtraInfo8($extra_info_8)
    {
        $this->extra_info_8 = $extra_info_8;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseSerialNo()
    {
        return $this->use_serial_no;
    }

    /**
     * @param mixed $use_serial_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setUseSerialNo($use_serial_no)
    {
        $this->use_serial_no = $use_serial_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSerialNo()
    {
        return $this->serial_no;
    }

    /**
     * @param mixed $serial_no
     * @return OmniSalesOrderLineItemObj
     */
    public function setSerialNo($serial_no)
    {
        $this->serial_no = $serial_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseGoodStock()
    {
        return $this->use_good_stock;
    }

    /**
     * @param mixed $use_good_stock
     * @return OmniSalesOrderLineItemObj
     */
    public function setUseGoodStock($use_good_stock)
    {
        $this->use_good_stock = $use_good_stock;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoodStock()
    {
        return $this->good_stock;
    }

    /**
     * @param mixed $good_stock
     * @return OmniSalesOrderLineItemObj
     */
    public function setGoodStock($good_stock)
    {
        $this->good_stock = $good_stock;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseRedundant()
    {
        return $this->use_redundant;
    }

    /**
     * @param mixed $use_redundant
     * @return OmniSalesOrderLineItemObj
     */
    public function setUseRedundant($use_redundant)
    {
        $this->use_redundant = $use_redundant;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRedundant()
    {
        return $this->redundant;
    }

    /**
     * @param mixed $redundant
     * @return OmniSalesOrderLineItemObj
     */
    public function setRedundant($redundant)
    {
        $this->redundant = $redundant;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseTrackingNo4()
    {
        return $this->use_tracking_no_4;
    }

    /**
     * @param mixed $use_tracking_no_4
     * @return OmniSalesOrderLineItemObj
     */
    public function setUseTrackingNo4($use_tracking_no_4)
    {
        $this->use_tracking_no_4 = $use_tracking_no_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrackingNo4()
    {
        return $this->tracking_no_4;
    }

    /**
     * @param mixed $tracking_no_4
     * @return OmniSalesOrderLineItemObj
     */
    public function setTrackingNo4($tracking_no_4)
    {
        $this->tracking_no_4 = $tracking_no_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWarrantyPeriod()
    {
        return $this->warranty_period;
    }

    /**
     * @param mixed $warranty_period
     * @return OmniSalesOrderLineItemObj
     */
    public function setWarrantyPeriod($warranty_period)
    {
        $this->warranty_period = $warranty_period;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDealNarrative()
    {
        return $this->deal_narrative;
    }

    /**
     * @param mixed $deal_narrative
     * @return OmniSalesOrderLineItemObj
     */
    public function setDealNarrative($deal_narrative)
    {
        $this->deal_narrative = $deal_narrative;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPromotionName()
    {
        return $this->promotion_name;
    }

    /**
     * @param mixed $promotion_name
     * @return OmniSalesOrderLineItemObj
     */
    public function setPromotionName($promotion_name)
    {
        $this->promotion_name = $promotion_name;
        return $this;
    }

    public function getArray()
    {
        $arr = get_object_vars($this);
        //$arr["principal_inv_#"] = $this->getPrincipalInv(); //special field?
        return $arr;
    }
}
