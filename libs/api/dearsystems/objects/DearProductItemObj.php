<?php


class DearProductItemObj extends DearBaseObj
{
    public $ID; //String
    public $SKU; //Date
    public $Name; //Date
    public $Category; //String
    public $Brand; //array( undefined )
    public $Type; //String
    public $CostingMethod; //String
    public $DropShipMode; //String
    public $DefaultLocation; //String
    public $Length; //int
    public $Width; //int
    public $Height; //int
    public $Weight; //int
    public $UOM; //String
    public $WeightUnits; //String
    public $DimensionsUnits; //String
    public $Barcode; //array( undefined )
    public $MinimumBeforeReorder; //int
    public $ReorderQuantity; //int
    public $PriceTier1; //int
    public $PriceTier2; //int
    public $PriceTier3; //int
    public $PriceTier4; //int
    public $PriceTier5; //int
    public $PriceTier6; //int
    public $PriceTier7; //int
    public $PriceTier8; //int
    public $PriceTier9; //int
    public $PriceTier10; //int
    public $PriceTiers; //Array
    public $AverageCost; //int
    public $ShortDescription; //String
    public $InternalNote; //String
    public $Description; //String
    public $AdditionalAttribute1; //array( undefined )
    public $AdditionalAttribute2; //array( undefined )
    public $AdditionalAttribute3; //array( undefined )
    public $AdditionalAttribute4; //array( undefined )
    public $AdditionalAttribute5; //array( undefined )
    public $AdditionalAttribute6; //array( undefined )
    public $AdditionalAttribute7; //array( undefined )
    public $AdditionalAttribute8; //array( undefined )
    public $AdditionalAttribute9; //array( undefined )
    public $AdditionalAttribute10; //array( undefined )
    public $AttributeSet; //array( undefined )
    public $DiscountRule; //array( undefined )
    public $Tags; //array( undefined )
    public $Status; //String
    public $StockLocator; //array( undefined )
    public $COGSAccount; //array( undefined )
    public $RevenueAccount; //array( undefined )
    public $ExpenseAccount; //array( undefined )
    public $InventoryAccount; //array( undefined )
    public $PurchaseTaxRule; //array( undefined )
    public $SaleTaxRule; //array( undefined )
    public $LastModifiedOn; //Date
    public $Sellable; //boolean
    public $PickZones; //array( undefined )
    public $BillOfMaterial; //boolean
    public $AutoAssembly; //boolean
    public $AutoDisassembly; //boolean
    public $QuantityToProduce; //int
    public $AlwaysShowQuantity; //int
    public $AssemblyInstructionURL; //String
    public $AssemblyCostEstimationMethod; //String
    public $Suppliers;  //array( undefined )
    public $ReorderLevels;  //array( undefined )
    public $BillOfMaterialsProducts;  //array( undefined )
    public $BillOfMaterialsServices;  //array( undefined )
    public $Movements;  //array( undefined )
    public $Attachments;  //array( undefined )
    public $BOMType; //String
    public $WarrantyName; //array( undefined )
    public $CustomPrices;

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @param mixed $ID
     */
    public function setID($ID)
    {
        $this->ID = $ID;
    }

    /**
     * @return mixed
     */
    public function getSKU()
    {
        return $this->SKU;
    }

    /**
     * @param mixed $SKU
     */
    public function setSKU($SKU)
    {
        $this->SKU = $SKU;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * @param mixed $Category
     */
    public function setCategory($Category)
    {
        $this->Category = $Category;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->Brand;
    }

    /**
     * @param mixed $Brand
     */
    public function setBrand($Brand)
    {
        $this->Brand = $Brand;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param mixed $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return mixed
     */
    public function getCostingMethod()
    {
        return $this->CostingMethod;
    }

    /**
     * @param mixed $CostingMethod
     */
    public function setCostingMethod($CostingMethod)
    {
        $this->CostingMethod = $CostingMethod;
    }

    /**
     * @return mixed
     */
    public function getDropShipMode()
    {
        return $this->DropShipMode;
    }

    /**
     * @param mixed $DropShipMode
     */
    public function setDropShipMode($DropShipMode)
    {
        $this->DropShipMode = $DropShipMode;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocation()
    {
        return $this->DefaultLocation;
    }

    /**
     * @param mixed $DefaultLocation
     */
    public function setDefaultLocation($DefaultLocation)
    {
        $this->DefaultLocation = $DefaultLocation;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->Length;
    }

    /**
     * @param mixed $Length
     */
    public function setLength($Length)
    {
        $this->Length = $Length;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->Width;
    }

    /**
     * @param mixed $Width
     */
    public function setWidth($Width)
    {
        $this->Width = $Width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->Height;
    }

    /**
     * @param mixed $Height
     */
    public function setHeight($Height)
    {
        $this->Height = $Height;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->Weight;
    }

    /**
     * @param mixed $Weight
     */
    public function setWeight($Weight)
    {
        $this->Weight = $Weight;
    }

    /**
     * @return mixed
     */
    public function getUOM()
    {
        return $this->UOM;
    }

    /**
     * @param mixed $UOM
     */
    public function setUOM($UOM)
    {
        $this->UOM = $UOM;
    }

    /**
     * @return mixed
     */
    public function getWeightUnits()
    {
        return $this->WeightUnits;
    }

    /**
     * @param mixed $WeightUnits
     */
    public function setWeightUnits($WeightUnits)
    {
        $this->WeightUnits = $WeightUnits;
    }

    /**
     * @return mixed
     */
    public function getDimensionsUnits()
    {
        return $this->DimensionsUnits;
    }

    /**
     * @param mixed $DimensionsUnits
     */
    public function setDimensionsUnits($DimensionsUnits)
    {
        $this->DimensionsUnits = $DimensionsUnits;
    }

    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->Barcode;
    }

    /**
     * @param mixed $Barcode
     */
    public function setBarcode($Barcode)
    {
        $this->Barcode = $Barcode;
    }

    /**
     * @return mixed
     */
    public function getMinimumBeforeReorder()
    {
        return $this->MinimumBeforeReorder;
    }

    /**
     * @param mixed $MinimumBeforeReorder
     */
    public function setMinimumBeforeReorder($MinimumBeforeReorder)
    {
        $this->MinimumBeforeReorder = $MinimumBeforeReorder;
    }

    /**
     * @return mixed
     */
    public function getReorderQuantity()
    {
        return $this->ReorderQuantity;
    }

    /**
     * @param mixed $ReorderQuantity
     */
    public function setReorderQuantity($ReorderQuantity)
    {
        $this->ReorderQuantity = $ReorderQuantity;
    }

    /**
     * @return mixed
     */
    public function getPriceTier1()
    {
        return $this->PriceTier1;
    }

    /**
     * @param mixed $PriceTier1
     */
    public function setPriceTier1($PriceTier1)
    {
        $this->PriceTier1 = $PriceTier1;
    }

    /**
     * @return mixed
     */
    public function getPriceTier2()
    {
        return $this->PriceTier2;
    }

    /**
     * @param mixed $PriceTier2
     */
    public function setPriceTier2($PriceTier2)
    {
        $this->PriceTier2 = $PriceTier2;
    }

    /**
     * @return mixed
     */
    public function getPriceTier3()
    {
        return $this->PriceTier3;
    }

    /**
     * @param mixed $PriceTier3
     */
    public function setPriceTier3($PriceTier3)
    {
        $this->PriceTier3 = $PriceTier3;
    }

    /**
     * @return mixed
     */
    public function getPriceTier4()
    {
        return $this->PriceTier4;
    }

    /**
     * @param mixed $PriceTier4
     */
    public function setPriceTier4($PriceTier4)
    {
        $this->PriceTier4 = $PriceTier4;
    }

    /**
     * @return mixed
     */
    public function getPriceTier5()
    {
        return $this->PriceTier5;
    }

    /**
     * @param mixed $PriceTier5
     */
    public function setPriceTier5($PriceTier5)
    {
        $this->PriceTier5 = $PriceTier5;
    }

    /**
     * @return mixed
     */
    public function getPriceTier6()
    {
        return $this->PriceTier6;
    }

    /**
     * @param mixed $PriceTier6
     */
    public function setPriceTier6($PriceTier6)
    {
        $this->PriceTier6 = $PriceTier6;
    }

    /**
     * @return mixed
     */
    public function getPriceTier7()
    {
        return $this->PriceTier7;
    }

    /**
     * @param mixed $PriceTier7
     */
    public function setPriceTier7($PriceTier7)
    {
        $this->PriceTier7 = $PriceTier7;
    }

    /**
     * @return mixed
     */
    public function getPriceTier8()
    {
        return $this->PriceTier8;
    }

    /**
     * @param mixed $PriceTier8
     */
    public function setPriceTier8($PriceTier8)
    {
        $this->PriceTier8 = $PriceTier8;
    }

    /**
     * @return mixed
     */
    public function getPriceTier9()
    {
        return $this->PriceTier9;
    }

    /**
     * @param mixed $PriceTier9
     */
    public function setPriceTier9($PriceTier9)
    {
        $this->PriceTier9 = $PriceTier9;
    }

    /**
     * @return mixed
     */
    public function getPriceTier10()
    {
        return $this->PriceTier10;
    }

    /**
     * @param mixed $PriceTier10
     */
    public function setPriceTier10($PriceTier10)
    {
        $this->PriceTier10 = $PriceTier10;
    }

    /**
     * @return mixed
     */
    public function getPriceTiers()
    {
        return $this->PriceTiers;
    }

    /**
     * @param mixed $PriceTiers
     */
    public function setPriceTiers($PriceTiers)
    {
        $this->PriceTiers = $PriceTiers;
    }

    /**
     * @return mixed
     */
    public function getAverageCost()
    {
        return $this->AverageCost;
    }

    /**
     * @param mixed $AverageCost
     */
    public function setAverageCost($AverageCost)
    {
        $this->AverageCost = $AverageCost;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->ShortDescription;
    }

    /**
     * @param mixed $ShortDescription
     */
    public function setShortDescription($ShortDescription)
    {
        $this->ShortDescription = $ShortDescription;
    }

    /**
     * @return mixed
     */
    public function getInternalNote()
    {
        return $this->InternalNote;
    }

    /**
     * @param mixed $InternalNote
     */
    public function setInternalNote($InternalNote)
    {
        $this->InternalNote = $InternalNote;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param mixed $Description
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute1()
    {
        return $this->AdditionalAttribute1;
    }

    /**
     * @param mixed $AdditionalAttribute1
     */
    public function setAdditionalAttribute1($AdditionalAttribute1)
    {
        $this->AdditionalAttribute1 = $AdditionalAttribute1;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute2()
    {
        return $this->AdditionalAttribute2;
    }

    /**
     * @param mixed $AdditionalAttribute2
     */
    public function setAdditionalAttribute2($AdditionalAttribute2)
    {
        $this->AdditionalAttribute2 = $AdditionalAttribute2;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute3()
    {
        return $this->AdditionalAttribute3;
    }

    /**
     * @param mixed $AdditionalAttribute3
     */
    public function setAdditionalAttribute3($AdditionalAttribute3)
    {
        $this->AdditionalAttribute3 = $AdditionalAttribute3;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute4()
    {
        return $this->AdditionalAttribute4;
    }

    /**
     * @param mixed $AdditionalAttribute4
     */
    public function setAdditionalAttribute4($AdditionalAttribute4)
    {
        $this->AdditionalAttribute4 = $AdditionalAttribute4;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute5()
    {
        return $this->AdditionalAttribute5;
    }

    /**
     * @param mixed $AdditionalAttribute5
     */
    public function setAdditionalAttribute5($AdditionalAttribute5)
    {
        $this->AdditionalAttribute5 = $AdditionalAttribute5;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute6()
    {
        return $this->AdditionalAttribute6;
    }

    /**
     * @param mixed $AdditionalAttribute6
     */
    public function setAdditionalAttribute6($AdditionalAttribute6)
    {
        $this->AdditionalAttribute6 = $AdditionalAttribute6;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute7()
    {
        return $this->AdditionalAttribute7;
    }

    /**
     * @param mixed $AdditionalAttribute7
     */
    public function setAdditionalAttribute7($AdditionalAttribute7)
    {
        $this->AdditionalAttribute7 = $AdditionalAttribute7;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute8()
    {
        return $this->AdditionalAttribute8;
    }

    /**
     * @param mixed $AdditionalAttribute8
     */
    public function setAdditionalAttribute8($AdditionalAttribute8)
    {
        $this->AdditionalAttribute8 = $AdditionalAttribute8;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute9()
    {
        return $this->AdditionalAttribute9;
    }

    /**
     * @param mixed $AdditionalAttribute9
     */
    public function setAdditionalAttribute9($AdditionalAttribute9)
    {
        $this->AdditionalAttribute9 = $AdditionalAttribute9;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttribute10()
    {
        return $this->AdditionalAttribute10;
    }

    /**
     * @param mixed $AdditionalAttribute10
     */
    public function setAdditionalAttribute10($AdditionalAttribute10)
    {
        $this->AdditionalAttribute10 = $AdditionalAttribute10;
    }

    /**
     * @return mixed
     */
    public function getAttributeSet()
    {
        return $this->AttributeSet;
    }

    /**
     * @param mixed $AttributeSet
     */
    public function setAttributeSet($AttributeSet)
    {
        $this->AttributeSet = $AttributeSet;
    }

    /**
     * @return mixed
     */
    public function getDiscountRule()
    {
        return $this->DiscountRule;
    }

    /**
     * @param mixed $DiscountRule
     */
    public function setDiscountRule($DiscountRule)
    {
        $this->DiscountRule = $DiscountRule;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->Tags;
    }

    /**
     * @param mixed $Tags
     */
    public function setTags($Tags)
    {
        $this->Tags = $Tags;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
    }

    /**
     * @return mixed
     */
    public function getStockLocator()
    {
        return $this->StockLocator;
    }

    /**
     * @param mixed $StockLocator
     */
    public function setStockLocator($StockLocator)
    {
        $this->StockLocator = $StockLocator;
    }

    /**
     * @return mixed
     */
    public function getCOGSAccount()
    {
        return $this->COGSAccount;
    }

    /**
     * @param mixed $COGSAccount
     */
    public function setCOGSAccount($COGSAccount)
    {
        $this->COGSAccount = $COGSAccount;
    }

    /**
     * @return mixed
     */
    public function getRevenueAccount()
    {
        return $this->RevenueAccount;
    }

    /**
     * @param mixed $RevenueAccount
     */
    public function setRevenueAccount($RevenueAccount)
    {
        $this->RevenueAccount = $RevenueAccount;
    }

    /**
     * @return mixed
     */
    public function getExpenseAccount()
    {
        return $this->ExpenseAccount;
    }

    /**
     * @param mixed $ExpenseAccount
     */
    public function setExpenseAccount($ExpenseAccount)
    {
        $this->ExpenseAccount = $ExpenseAccount;
    }

    /**
     * @return mixed
     */
    public function getInventoryAccount()
    {
        return $this->InventoryAccount;
    }

    /**
     * @param mixed $InventoryAccount
     */
    public function setInventoryAccount($InventoryAccount)
    {
        $this->InventoryAccount = $InventoryAccount;
    }

    /**
     * @return mixed
     */
    public function getPurchaseTaxRule()
    {
        return $this->PurchaseTaxRule;
    }

    /**
     * @param mixed $PurchaseTaxRule
     */
    public function setPurchaseTaxRule($PurchaseTaxRule)
    {
        $this->PurchaseTaxRule = $PurchaseTaxRule;
    }

    /**
     * @return mixed
     */
    public function getSaleTaxRule()
    {
        return $this->SaleTaxRule;
    }

    /**
     * @param mixed $SaleTaxRule
     */
    public function setSaleTaxRule($SaleTaxRule)
    {
        $this->SaleTaxRule = $SaleTaxRule;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedOn()
    {
        return $this->LastModifiedOn;
    }

    /**
     * @param mixed $LastModifiedOn
     */
    public function setLastModifiedOn($LastModifiedOn)
    {
        $this->LastModifiedOn = $LastModifiedOn;
    }

    /**
     * @return mixed
     */
    public function getSellable()
    {
        return $this->Sellable;
    }

    /**
     * @param mixed $Sellable
     */
    public function setSellable($Sellable)
    {
        $this->Sellable = $Sellable;
    }

    /**
     * @return mixed
     */
    public function getPickZones()
    {
        return $this->PickZones;
    }

    /**
     * @param mixed $PickZones
     */
    public function setPickZones($PickZones)
    {
        $this->PickZones = $PickZones;
    }

    /**
     * @return mixed
     */
    public function getBillOfMaterial()
    {
        return $this->BillOfMaterial;
    }

    /**
     * @param mixed $BillOfMaterial
     */
    public function setBillOfMaterial($BillOfMaterial)
    {
        $this->BillOfMaterial = $BillOfMaterial;
    }

    /**
     * @return mixed
     */
    public function getAutoAssembly()
    {
        return $this->AutoAssembly;
    }

    /**
     * @param mixed $AutoAssembly
     */
    public function setAutoAssembly($AutoAssembly)
    {
        $this->AutoAssembly = $AutoAssembly;
    }

    /**
     * @return mixed
     */
    public function getAutoDisassembly()
    {
        return $this->AutoDisassembly;
    }

    /**
     * @param mixed $AutoDisassembly
     */
    public function setAutoDisassembly($AutoDisassembly)
    {
        $this->AutoDisassembly = $AutoDisassembly;
    }

    /**
     * @return mixed
     */
    public function getQuantityToProduce()
    {
        return $this->QuantityToProduce;
    }

    /**
     * @param mixed $QuantityToProduce
     */
    public function setQuantityToProduce($QuantityToProduce)
    {
        $this->QuantityToProduce = $QuantityToProduce;
    }

    /**
     * @return mixed
     */
    public function getAlwaysShowQuantity()
    {
        return $this->AlwaysShowQuantity;
    }

    /**
     * @param mixed $AlwaysShowQuantity
     */
    public function setAlwaysShowQuantity($AlwaysShowQuantity)
    {
        $this->AlwaysShowQuantity = $AlwaysShowQuantity;
    }

    /**
     * @return mixed
     */
    public function getAssemblyInstructionURL()
    {
        return $this->AssemblyInstructionURL;
    }

    /**
     * @param mixed $AssemblyInstructionURL
     */
    public function setAssemblyInstructionURL($AssemblyInstructionURL)
    {
        $this->AssemblyInstructionURL = $AssemblyInstructionURL;
    }

    /**
     * @return mixed
     */
    public function getAssemblyCostEstimationMethod()
    {
        return $this->AssemblyCostEstimationMethod;
    }

    /**
     * @param mixed $AssemblyCostEstimationMethod
     */
    public function setAssemblyCostEstimationMethod($AssemblyCostEstimationMethod)
    {
        $this->AssemblyCostEstimationMethod = $AssemblyCostEstimationMethod;
    }

    /**
     * @return mixed
     */
    public function getSuppliers()
    {
        return $this->Suppliers;
    }

    /**
     * @param mixed $Suppliers
     */
    public function setSuppliers($Suppliers)
    {
        $this->Suppliers = $Suppliers;
    }

    /**
     * @return mixed
     */
    public function getReorderLevels()
    {
        return $this->ReorderLevels;
    }

    /**
     * @param mixed $ReorderLevels
     */
    public function setReorderLevels($ReorderLevels)
    {
        $this->ReorderLevels = $ReorderLevels;
    }

    /**
     * @return mixed
     */
    public function getBillOfMaterialsProducts()
    {
        return $this->BillOfMaterialsProducts;
    }

    /**
     * @param mixed $BillOfMaterialsProducts
     */
    public function setBillOfMaterialsProducts($BillOfMaterialsProducts)
    {
        $this->BillOfMaterialsProducts = $BillOfMaterialsProducts;
    }

    /**
     * @return mixed
     */
    public function getBillOfMaterialsServices()
    {
        return $this->BillOfMaterialsServices;
    }

    /**
     * @param mixed $BillOfMaterialsServices
     */
    public function setBillOfMaterialsServices($BillOfMaterialsServices)
    {
        $this->BillOfMaterialsServices = $BillOfMaterialsServices;
    }

    /**
     * @return mixed
     */
    public function getMovements()
    {
        return $this->Movements;
    }

    /**
     * @param mixed $Movements
     */
    public function setMovements($Movements)
    {
        $this->Movements = $Movements;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->Attachments;
    }

    /**
     * @param mixed $Attachments
     */
    public function setAttachments($Attachments)
    {
        $this->Attachments = $Attachments;
    }

    /**
     * @return mixed
     */
    public function getBOMType()
    {
        return $this->BOMType;
    }

    /**
     * @param mixed $BOMType
     */
    public function setBOMType($BOMType)
    {
        $this->BOMType = $BOMType;
    }

    /**
     * @return mixed
     */
    public function getWarrantyName()
    {
        return $this->WarrantyName;
    }

    /**
     * @param mixed $WarrantyName
     */
    public function setWarrantyName($WarrantyName)
    {
        $this->WarrantyName = $WarrantyName;
    }

    /**
     * @return mixed
     */
    public function getCustomPrices()
    {
        return $this->CustomPrices;
    }

    /**
     * @param mixed $CustomPrices
     */
    public function setCustomPrices($CustomPrices)
    {
        $this->CustomPrices = $CustomPrices;
    }  //array( undefined )


}