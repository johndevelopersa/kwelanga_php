<?php

$sendXMLDtl = '<invoiceLineItem>
<lineItemNumber>&&var_lineNumber&&</lineItemNumber>
<invoicedQuantity>&&var_quantity&&</invoicedQuantity>
<amountExclusiveAllowancesCharges currencyCode="ZAR">&&var_extendedPriceExclusive&&</amountExclusiveAllowancesCharges>
<amountInclusiveAllowancesCharges currencyCode="ZAR">&&var_extendedPriceInclusive&&</amountInclusiveAllowancesCharges>
<transferOfOwnershipDate>&&var_transferOfOwnershipDate&&</transferOfOwnershipDate>
<note languageCode="EN">&&var_productDescription&&</note>
<transactionalTradeItem>
<gtin>&&var_productGTIN&&</gtin>
<transactionalItemData>
<transactionalItemWeight>
<measurementValue measurementUnitCode="&&uomc&&">&&var_productWeight&&</measurementValue>
</transactionalItemWeight>
</transactionalItemData>
<size>
<sizeCode>&&var_productPackSize&&</sizeCode>
</size>
</transactionalTradeItem>
<invoiceLineTaxInformation>
<dutyFeeTaxAmount currencyCode="ZAR">&&var_vatAmount&&</dutyFeeTaxAmount>
<dutyFeeTaxCategoryCode>&&var_vatCategory&&</dutyFeeTaxCategoryCode>
<dutyFeeTaxPercentage>&&var_vatRate&&</dutyFeeTaxPercentage>
<dutyFeeTaxTypeCode>VAT</dutyFeeTaxTypeCode>
</invoiceLineTaxInformation>
<avpList>
<eComStringAttributeValuePairList attributeName="InvoiceDetailRefNo">&&var_invoiceDetailRef&&</eComStringAttributeValuePairList>
<eComStringAttributeValuePairList attributeName="InvoiceRefNo">&&var_invoiceNumber&&</eComStringAttributeValuePairList>
</avpList>
</invoiceLineItem>';


?>