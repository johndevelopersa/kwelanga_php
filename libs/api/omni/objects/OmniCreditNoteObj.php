<?php


class OmniCreditNoteObj extends OmniBaseObj
{
    protected $key; //String
    protected $source_type; //String
    protected $source_reference; //String
    protected $customer_account_code; //String
    protected $customer_branch_code; //String
    protected $document_date; //String
    protected $due_date; //String
    protected $customer_name; //String
    protected $warehouse_code; //String
    protected $job_no; //String
    protected $revenue_acc_code; //String
    protected $customer_order_no; //String
    protected $delivery_details; //String
    protected $rep_code; //String
    protected $area_code; //String
    protected $overall_discount_code; //String
    protected $vat_registration_no; //String
    protected $postal_address_1; //String
    protected $postal_address_2; //String
    protected $postal_address_3; //String
    protected $postal_address_4; //String
    protected $postal_address_5; //String
    protected $post_code; //String
    protected $physical_address_1; //String
    protected $physical_address_2; //String
    protected $physical_address_3; //String
    protected $physical_address_4; //String
    protected $physical_address_5; //String
   
    protected $store_name; //String
    protected $captured_by; //String
    protected $analysis_4; //String
    protected $delivery_day; //String
    protected $delivery_route; //String
    protected $extra_info_3; //String
    protected $principle; //String
    protected $customers__name; //String
    protected $extra_info_6; //String
    protected $extra_info_7; //String
    protected $branch_code; //String
    protected $memo; //String

    protected $credit_note_lines = [];

    public function __construct($properties = [])
    {
        $this->initializeSpecialProperties();
        parent::__construct($properties);

        if (is_array($properties) && !empty($properties["credit_note"]) && count($properties)) {
            $this->arrayAssign($properties["invoice"]);
        }
    }

    public function getArray()
    {    	
        $arr = get_object_vars($this);
       // $arr["principal_inv_#"] = $this->getPrincipalInv(); //special field?
        unset($arr[""]);
        return [
            "credit_note" => $arr
        ];
    }

    protected function initializeSpecialProperties(){
       // $this->{"principal_inv_#"} = null; //String
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
     * @return OmniSalesOrderObj
     */
    public function setPrincipalInv($principal_inv)
    {
        //$this->{"principal_inv_#"} = $principal_inv;
        return $this;
    }

    /**
     * @return []OmniSalesOrderLineItemObj
     */
    public function getOrderLines()
    {
        $lines = [];
        foreach($this->credit_note_lines as $line){
            $lines[] = new OmniCreditNoteLineItemObj($line);
        }
        return $lines;
    }

    /**
     * @param OmniSalesOrderLineItemObj $line
     * @return OmniSalesOrderObj
     */
    public function addOrderLine(OmniCreditNoteLineItemObj $line)
    {
        $this->credit_note_lines[] = $line->getArray();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
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
     * @return OmniSalesOrderObj
     */
    public function setSourceType($source_type)
    {
        $this->source_type = $source_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceReference()
    {
        return $this->source_reference;
    }

    /**
     * @param mixed $source_reference
     * @return OmniSalesOrderObj
     */
    public function setSourceReference($source_reference)
    {
        $this->source_reference = $source_reference;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAccountCode()
    {
        return $this->customer_account_code;
    }

    /**
     * @param mixed $customer_account_code
     * @return OmniSalesOrderObj
     */
    public function setCustomerAccountCode($customer_account_code)
    {
        $this->customer_account_code = $customer_account_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerBranchCode()
    {
        return $this->customer_branch_code;
    }

    /**
     * @param mixed $customer_branch_code
     * @return OmniSalesOrderObj
     */
    public function setCustomerBranchCode($customer_branch_code)
    {
        $this->customer_branch_code = $customer_branch_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocumentDate()
    {
        return $this->document_date;
    }

    /**
     * @param mixed $document_date
     * @return OmniSalesOrderObj
     */
    public function setDocumentDate($document_date)
    {
        $this->document_date = $document_date;
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
     * @return OmniSalesOrderObj
     */
    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * @param mixed $customer_name
     * @return OmniSalesOrderObj
     */
    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWarehouseCode()
    {
        return $this->warehouse_code;
    }

    /**
     * @param mixed $warehouse_code
     * @return OmniSalesOrderObj
     */
    public function setWarehouseCode($warehouse_code)
    {
        $this->warehouse_code = $warehouse_code;
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
     * @return OmniSalesOrderObj
     */
    public function setJobNo($job_no)
    {
        $this->job_no = $job_no;
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
     * @return OmniSalesOrderObj
     */
    public function setRevenueAccCode($revenue_acc_code)
    {
        $this->revenue_acc_code = $revenue_acc_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerOrderNo()
    {
        return $this->customer_order_no;
    }

    /**
     * @param mixed $customer_order_no
     * @return OmniSalesOrderObj
     */
    public function setCustomerOrderNo($customer_order_no)
    {
        $this->customer_order_no = $customer_order_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryDetails()
    {
        return $this->delivery_details;
    }

    /**
     * @param mixed $delivery_details
     * @return OmniSalesOrderObj
     */
    public function setDeliveryDetails($delivery_details)
    {
        $this->delivery_details = $delivery_details;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRepCode()
    {
        return $this->rep_code;
    }

    /**
     * @param mixed $rep_code
     * @return OmniSalesOrderObj
     */
    public function setRepCode($rep_code)
    {
        $this->rep_code = $rep_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAreaCode()
    {
        return $this->area_code;
    }

    /**
     * @param mixed $area_code
     * @return OmniSalesOrderObj
     */
    public function setAreaCode($area_code)
    {
        $this->area_code = $area_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOverallDiscountCode()
    {
        return $this->overall_discount_code;
    }

    /**
     * @param mixed $overall_discount_code
     * @return OmniSalesOrderObj
     */
    public function setOverallDiscountCode($overall_discount_code)
    {
        $this->overall_discount_code = $overall_discount_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRegistrationNo()
    {
        return $this->vat_registration_no;
    }

    /**
     * @param mixed $vat_registration_no
     * @return OmniSalesOrderObj
     */
    public function setVatRegistrationNo($vat_registration_no)
    {
        $this->vat_registration_no = $vat_registration_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress1()
    {
        return $this->postal_address_1;
    }

    /**
     * @param mixed $postal_address_1
     * @return OmniSalesOrderObj
     */
    public function setPostalAddress1($postal_address_1)
    {
        $this->postal_address_1 = $postal_address_1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress2()
    {
        return $this->postal_address_2;
    }

    /**
     * @param mixed $postal_address_2
     * @return OmniSalesOrderObj
     */
    public function setPostalAddress2($postal_address_2)
    {
        $this->postal_address_2 = $postal_address_2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress3()
    {
        return $this->postal_address_3;
    }

    /**
     * @param mixed $postal_address_3
     * @return OmniSalesOrderObj
     */
    public function setPostalAddress3($postal_address_3)
    {
        $this->postal_address_3 = $postal_address_3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress4()
    {
        return $this->postal_address_4;
    }

    /**
     * @param mixed $postal_address_4
     * @return OmniSalesOrderObj
     */
    public function setPostalAddress4($postal_address_4)
    {
        $this->postal_address_4 = $postal_address_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress5()
    {
        return $this->postal_address_5;
    }

    /**
     * @param mixed $postal_address_5
     * @return OmniSalesOrderObj
     */
    public function setPostalAddress5($postal_address_5)
    {
        $this->postal_address_5 = $postal_address_5;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostCode()
    {
        return $this->post_code;
    }

    /**
     * @param mixed $post_code
     * @return OmniSalesOrderObj
     */
    public function setPostCode($post_code)
    {
        $this->post_code = $post_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAddress1()
    {
        return $this->physical_address_1;
    }

    /**
     * @param mixed $physical_address_1
     * @return OmniSalesOrderObj
     */
    public function setPhysicalAddress1($physical_address_1)
    {
        $this->physical_address_1 = $physical_address_1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAddress2()
    {
        return $this->physical_address_2;
    }

    /**
     * @param mixed $physical_address_2
     * @return OmniSalesOrderObj
     */
    public function setPhysicalAddress2($physical_address_2)
    {
        $this->physical_address_2 = $physical_address_2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAddress3()
    {
        return $this->physical_address_3;
    }

    /**
     * @param mixed $physical_address_3
     * @return OmniSalesOrderObj
     */
    public function setPhysicalAddress3($physical_address_3)
    {
        $this->physical_address_3 = $physical_address_3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAddress4()
    {
        return $this->physical_address_4;
    }

    /**
     * @param mixed $physical_address_4
     * @return OmniSalesOrderObj
     */
    public function setPhysicalAddress4($physical_address_4)
    {
        $this->physical_address_4 = $physical_address_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAddress5()
    {
        return $this->physical_address_5;
    }

    /**
     * @param mixed $physical_address_5
     * @return OmniSalesOrderObj
     */
    public function setPhysicalAddress5($physical_address_5)
    {
        $this->physical_address_5 = $physical_address_5;
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
     * @return OmniSalesOrderObj
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
     * @return OmniSalesOrderObj
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
     * @return OmniSalesOrderObj
     */
    public function setAnalysis4($analysis_4)
    {
        $this->analysis_4 = $analysis_4;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryDay()
    {
        return $this->delivery_day;
    }

    /**
     * @param mixed $delivery_day
     * @return OmniSalesOrderObj
     */
    public function setDeliveryDay($delivery_day)
    {
        $this->delivery_day = $delivery_day;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryRoute()
    {
        return $this->delivery_route;
    }

    /**
     * @param mixed $delivery_route
     * @return OmniSalesOrderObj
     */
    public function setDeliveryRoute($delivery_route)
    {
        $this->delivery_route = $delivery_route;
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
     * @return OmniSalesOrderObj
     */
    public function setExtraInfo3($extra_info_3)
    {
        $this->extra_info_3 = $extra_info_3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrinciple()
    {
        return $this->principle;
    }

    /**
     * @param mixed $principle
     * @return OmniSalesOrderObj
     */
    public function setPrinciple($principle)
    {
        $this->principle = $principle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomersName()
    {
        return $this->customers__name;
    }

    /**
     * @param mixed $customers__name
     * @return OmniSalesOrderObj
     */
    public function setCustomersName($customers__name)
    {
        $this->customers__name = $customers__name;
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
     * @return OmniSalesOrderObj
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
     * @return OmniSalesOrderObj
     */
    public function setExtraInfo7($extra_info_7)
    {
        $this->extra_info_7 = $extra_info_7;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBranchCode()
    {
        return $this->branch_code;
    }

    /**
     * @param mixed $branch_code
     * @return OmniSalesOrderObj
     */
    public function setBranchCode($branch_code)
    {
        $this->branch_code = $branch_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     * @return OmniSalesOrderObj
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
        return $this;
    }

}
