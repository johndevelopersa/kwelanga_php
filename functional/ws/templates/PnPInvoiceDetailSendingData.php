<?php

$sendXMLDtl = '<invoiceLineItem number="&&var_lineNumber&&">
                  <tradeItemIdentification>
                      <gtin>&&var_productGTIN&&</gtin>
                      <additionalTradeItemIdentification>
                          <additionalTradeItemIdentificationValue>&&var_productCode&&</additionalTradeItemIdentificationValue>
                          <additionalTradeItemIdentificationType>SUPPLIER_ASSIGNED</additionalTradeItemIdentificationType>
                      </additionalTradeItemIdentification>
                  </tradeItemIdentification>
                  <invoicedQuantity>
                      <value>&&var_quantity&&</value>
                      <unitOfMeasure>
                          <measurementUnitCodeValue>CS</measurementUnitCodeValue>
                      </unitOfMeasure>
                  </invoicedQuantity>
                  <transferOfOwnershipDate>&&var_transferOfOwnershipDate&&</transferOfOwnershipDate>
                  <amountInclusiveAllowancesCharges>&&var_extendedPriceExclusive&&</amountInclusiveAllowancesCharges>
                  <itemDescription>
                      <text>&&var_productDescription&&</text> 
                  </itemDescription>
                  <itemPriceInclusiveAllowancesCharges>&&var_sellingPriceExclusive&&</itemPriceInclusiveAllowancesCharges>
                  <invoiceLineItemAfterTaxes>
                      <amountInclusiveAllowancesCharges>&&var_extendedPriceInclusive&&</amountInclusiveAllowancesCharges>
                  </invoiceLineItemAfterTaxes>
                  <invoiceLineTaxInformation>
                      <dutyTaxFeeType>VALUE_ADDED_TAX</dutyTaxFeeType>
                      <taxableAmount>&&var_sellingPriceExclusive&&</taxableAmount>
                      <taxAmount>&&var_vatAmount&&</taxAmount>
                      <extension>
                          <vat:vATTaxInformationExtension xmlns:vat="urn:ean.ucc:pay:vat:2" xsi:schemaLocation="urn:ean.ucc:pay:vat:2 ../Schemas/Invoice_VATExtensionProxy.xsd">
                              <rate>&&var_vatRate&&</rate>
                              <vATCategory>&&var_vatCategory&&</vATCategory>
                          </vat:vATTaxInformationExtension>
                      </extension>
                  </invoiceLineTaxInformation>
                  <extension>
                      <invoicedWeight>
                          <value>&&var_productWeight&&</value>
                          <unitOfMeasure>
                              <measurementUnitCodeValue>&&var_productWeightUnit&&</measurementUnitCodeValue>
                          </unitOfMeasure>
                      </invoicedWeight>
                  </extension>
              </invoiceLineItem>';


?>