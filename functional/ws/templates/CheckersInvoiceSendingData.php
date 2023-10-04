<?php

$sendXML = '<invoiceMessage xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:gs1:ecom:invoice:xsd:3">
<StandardBusinessDocumentHeader xmlns="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader">
<HeaderVersion>3.2.0</HeaderVersion>
<Sender>
<Identifier Authority="SenderEAN">&&var_principalGLN&&</Identifier>
</Sender>
<Receiver>
<Identifier Authority="ReceiverEAN">&&var_storeGLN&&</Identifier>
</Receiver>
<DocumentIdentification>
<Standard>GS1</Standard>
<TypeVersion>3.2</TypeVersion>
<InstanceIdentifier>&&var_uniqueCreatorId&&</InstanceIdentifier>
<Type>Invoice</Type>
<MultipleType>true</MultipleType>
<CreationDateAndTime>&&var_uploadDateTime&&</CreationDateAndTime>
</DocumentIdentification>
<Manifest>
<NumberOfItems>&&var_invoiceCount&&</NumberOfItems>
</Manifest>
</StandardBusinessDocumentHeader>
<invoice xmlns="">
<creationDateTime>&&var_invoiceDate&&</creationDateTime>
<documentStatusCode>ORIGINAL</documentStatusCode>
<documentActionCode>ADD</documentActionCode>
<documentStructureVersion>3.2.0</documentStructureVersion>
<revisionNumber>1.0</revisionNumber>
<documentEffectiveDate>
<date>&&var_invoiceDate&&</date>
</documentEffectiveDate>
<avpList>
<eComStringAttributeValuePairList attributeName="InvoiceRefNo">&&var_invoiceNumber&&</eComStringAttributeValuePairList>
<eComStringAttributeValuePairList attributeName="InstanceIdentifier">INVOICE NUMBER</eComStringAttributeValuePairList>
</avpList>
<InvoiceIdentification>
<entityIdentification>&&var_invoiceNumber&&</entityIdentification>
<contentOwner>
<gln>&&var_principalGLN&&</gln>
</contentOwner>
</InvoiceIdentification>
<invoiceType>INVOICE</invoiceType>
<invoiceCurrencyCode>ZAR</invoiceCurrencyCode>
<countryOfSupplyOfGoods>ZA</countryOfSupplyOfGoods>
<buyer>
<gln>&&var_storeGLN&&</gln>
</buyer>
<seller>
<gln>&&var_principalGLN&&</gln>
<organisationDetails>
<legalRegistration>
<legalRegistrationNumber>&&var_sellerVATNumber&&</legalRegistrationNumber>
</legalRegistration>
</organisationDetails>
</seller>
<shipTo>
<gln>&&var_storeGLN&&</gln>
</shipTo>
<invoiceTotals>
<totalInvoiceAmount currencyCode="ZAR">&&var_invoiceTotalExclusive&&</totalInvoiceAmount>
<totalInvoiceAmountPayable currencyCode="ZAR">&&var_invoiceTotalInclusive&&</totalInvoiceAmountPayable>
<totalVATAmount currencyCode="ZAR">&&var_invoiceTotalVatAmount&&</totalVATAmount>
</invoiceTotals>
<purchaseOrder>
<entityIdentification>&&var_purchaseOrderReference&&</entityIdentification>
</purchaseOrder>
<invoice>
<entityIdentification>&&var_invoiceNumber&&</entityIdentification>
<contentOwner>
<gln>&&var_principalGLN&&</gln>
</contentOwner>
</invoice>
&&var_invoiceDetailLines&&
</invoice>
</invoiceMessage>';

?>