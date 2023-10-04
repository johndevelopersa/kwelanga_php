<?php

$sendXML = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          	<soap:Body>
          		<sh:StandardBusinessDocument xmlns:sh="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader" xmlns:eanucc="urn:ean.ucc:2" xmlns:pay="urn:ean.ucc:pay:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns0="http://www.unece.org/cefact/namespaces/StandardBusinessDocum">
             <sh:StandardBusinessDocumentHeader>
                <sh:HeaderVersion>1.0</sh:HeaderVersion>
                <sh:Sender>
                   <sh:Identifier Authority="EAN.UCC">6001000000001</sh:Identifier>
                </sh:Sender>
                <sh:Receiver>
                   <sh:Identifier Authority="EAN.UCC">6001007802929</sh:Identifier>
                </sh:Receiver>
                <sh:DocumentIdentification>
                   <sh:Standard>EAN.UCC</sh:Standard>
                   <sh:TypeVersion>2.3</sh:TypeVersion>
                   <sh:InstanceIdentifier>0000000055069223</sh:InstanceIdentifier>
                   <sh:Type>Settlement</sh:Type>
                   <sh:MultipleType>false</sh:MultipleType>
                   <sh:CreationDateAndTime>2010-02-18T12:05:18.182+02:00</sh:CreationDateAndTime>
                </sh:DocumentIdentification>
             </sh:StandardBusinessDocumentHeader>
             <eanucc:message>
                <entityIdentification>
                   <uniqueCreatorIdentification>200004835110002010</uniqueCreatorIdentification>
                   <contentOwner>
                      <gln>6001007000004</gln>
                   </contentOwner>
                </entityIdentification>
                <eanucc:transaction>
                   <entityIdentification>
                      <uniqueCreatorIdentification>200004835110002010</uniqueCreatorIdentification>
                      <contentOwner>
                         <gln>6001007000004</gln>
                      </contentOwner>
                   </entityIdentification>
                   <command>
                      <eanucc:documentCommand>
                         <documentCommandHeader type="ADD">
                            <entityIdentification>
                               <uniqueCreatorIdentification>200004835110002010</uniqueCreatorIdentification>
                               <contentOwner>
                                  <gln>6001007000004</gln>
                               </contentOwner>
                            </entityIdentification>
                         </documentCommandHeader>
                         <documentCommandOperand>
                            <pay:settlement creationDateTime="2010-02-18T12:05:18.182+02:00" documentStatus="ORIGINAL">
                               <contentVersion>
                                  <versionIdentification/>
                               </contentVersion>
                               <documentStructureVersion>
                                  <versionIdentification/>
                               </documentStructureVersion>
                               <batchIdentification/>
                               <paymentEffectiveDate>20100215</paymentEffectiveDate>
                               <settlementCurrency>
                                  <currencyISOCode>ZAR</currencyISOCode>
                               </settlementCurrency>
                               <totalAmount>88949.07</totalAmount>
                               <transactionHandlingType>REMITTANCE_ONLY</transactionHandlingType>
                               <paymentMethod>
                                  <automatedClearingHousePaymentFormat/>
                                  <paymentMethodType>CHEQUE</paymentMethodType>
                               </paymentMethod>
                               <payer>
                                  <gln>6001007000004</gln>
                               </payer>
                               <payersFinancialInstitution>
                                  <gln/>
                               </payersFinancialInstitution>
                               <remitTo>
                                  <gln>6002531000959</gln>
                               </remitTo>
                               <payee>
                                  <gln>6002531000959</gln>
                                  <additionalPartyIdentification>
                                     <additionalPartyIdentificationValue>0010005232</additionalPartyIdentificationValue>
                                     <additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
                                  </additionalPartyIdentification>
                               </payee>
                               <remitToFinancialInstitution>
                                  <gln/>
                               </remitToFinancialInstitution>
                               <payeesFinancialInstitution>
                                  <gln/>
                               </payeesFinancialInstitution>
                               <settlementIdentification>
                                  <uniqueCreatorIdentification>200004835110002010</uniqueCreatorIdentification>
                                  <contentOwner>
                                     <gln>6001007000004</gln>
                                  </contentOwner>
                               </settlementIdentification>
                               <payerFinancialAccountInformation>
                                  <accountName/>
                                  <accountNumber>
                                     <number/>
                                     <accountNumberType/>
                                  </accountNumber>
                                  <routingNumber>
                                     <number/>
                                     <routingNumberType/>
                                  </routingNumber>
                                  <financialInsitutionNameAndAddress>
                                     <city/>
                                     <cityCode/>
                                     <countryCode>
                                        <countryISOCode/>
                                     </countryCode>
                                     <languageOfTheParty>
                                        <languageISOCode/>
                                     </languageOfTheParty>
                                     <name/>
                                     <currency>
                                        <currencyISOCode/>
                                     </currency>
                                  </financialInsitutionNameAndAddress>
                                  <branch/>
                               </payerFinancialAccountInformation>
                               <payeeFinancialAccountInformation>
                                  <accountName/>
                                  <accountNumber>
                                     <number/>
                                     <accountNumberType/>
                                  </accountNumber>
                                  <routingNumber>
                                     <number/>
                                     <routingNumberType/>
                                  </routingNumber>
                                  <branch/>
                               </payeeFinancialAccountInformation>
                               <remitToFinancialAccountInformation>
                                  <accountName/>
                                  <accountNumber>
                                     <number/>
                                     <accountNumberType/>
                                  </accountNumber>
                                  <routingNumber>
                                     <number/>
                                     <routingNumberType/>
                                  </routingNumber>
                                  <branch/>
                               </remitToFinancialAccountInformation>
                               <settlementLineItem number="1">
                                  <amountPaid>29649.69</amountPaid>
                                  <originalAmount>34678.00</originalAmount>
                                  <adjustmentAndDiscount>
                                     <amount>346.78</amount>
                                     <adjustmentReason>
                                        <messageReason>XJ 1.000 % Cash Disc. Merch.</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849131</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <adjustmentAndDiscount>
                                     <amount>4681.53</amount>
                                     <adjustmentReason>
                                        <messageReason>XI 13.500 % Incentive Discount</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849134</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <invoice creationDateTime="2010-02-15">
                                     <uniqueCreatorIdentification>INV410928519-1</uniqueCreatorIdentification>
                                     <contentOwner>
                                        <gln>6001007000004</gln>
                                     </contentOwner>
                                     <invoiceType>INVOICE</invoiceType>
                                  </invoice>
                                  <settlementEntity entityType="SITE">
                                     <partyIdentification>
                                        <gln>6001007031299</gln>
                                     </partyIdentification>
                                  </settlementEntity>
                                  <documentType/>
                               </settlementLineItem>
                               <settlementLineItem number="2">
                                  <amountPaid>29649.69</amountPaid>
                                  <originalAmount>35704.00</originalAmount>
                                  <adjustmentAndDiscount>
                                     <amount>346.78</amount>
                                     <adjustmentReason>
                                        <messageReason>XJ 1.000 % Cash Disc. Merch.</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849132</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <adjustmentAndDiscount>
                                     <amount>4681.53</amount>
                                     <adjustmentReason>
                                        <messageReason>XI 13.500 % Incentive Discount</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849135</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <adjustmentAndDiscount>
                                     <amount>1026.00</amount>
                                     <adjustmentReason>
                                        <messageReason>RN Credit memo (invoice reduction)</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>1700056777</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <invoice creationDateTime="2010-02-15">
                                     <uniqueCreatorIdentification>INV410928519-2</uniqueCreatorIdentification>
                                     <contentOwner>
                                        <gln>6001007000004</gln>
                                     </contentOwner>
                                     <invoiceType>INVOICE</invoiceType>
                                  </invoice>
                                  <settlementEntity entityType="SITE">
                                     <partyIdentification>
                                        <gln>6001007031299</gln>
                                     </partyIdentification>
                                  </settlementEntity>
                                  <documentType/>
                               </settlementLineItem>
                               <settlementLineItem number="3">
                                  <amountPaid>29649.69</amountPaid>
                                  <originalAmount>42088.00</originalAmount>
                                  <adjustmentAndDiscount>
                                     <amount>346.78</amount>
                                     <adjustmentReason>
                                        <messageReason>XJ 1.000 % Cash Disc. Merch.</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849133</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <adjustmentAndDiscount>
                                     <amount>4681.53</amount>
                                     <adjustmentReason>
                                        <messageReason>XI 13.500 % Incentive Discount</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>2200849136</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <adjustmentAndDiscount>
                                     <amount>7410.00</amount>
                                     <adjustmentReason>
                                        <messageReason>RN Credit memo (invoice reduction)</messageReason>
                                        <sourceCode>PNP</sourceCode>
                                     </adjustmentReason>
                                     <alternateAdjustmentReference>
                                        <alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
                                        <identification>1700056778</identification>
                                     </alternateAdjustmentReference>
                                  </adjustmentAndDiscount>
                                  <invoice creationDateTime="2010-02-15">
                                     <uniqueCreatorIdentification>INV410928519-3</uniqueCreatorIdentification>
                                     <contentOwner>
                                        <gln>6001007000004</gln>
                                     </contentOwner>
                                     <invoiceType>INVOICE</invoiceType>
                                  </invoice>
                                  <settlementEntity entityType="SITE">
                                     <partyIdentification>
                                        <gln>6001007031299</gln>
                                     </partyIdentification>
                                  </settlementEntity>
                                  <documentType/>
                               </settlementLineItem>
                               <extension>
                                  <settlementLineItem number="1">
                                     <documentType>RE Invoice - gross</documentType>
                                     <documentNumber>5100754282</documentNumber>
                                  </settlementLineItem>
                                  <settlementLineItem number="2">
                                     <documentType>RE Invoice - gross</documentType>
                                     <documentNumber>5100754283</documentNumber>
                                  </settlementLineItem>
                                  <settlementLineItem number="3">
                                     <documentType>RE Invoice - gross</documentType>
                                     <documentNumber>5100754284</documentNumber>
                                  </settlementLineItem>
                                  <adjustmentAndDiscountSummary>
                                     <adjustmentAndDiscount>
                                        <amount>1040.34</amount>
                                        <adjustmentReason>
                                           <messageReason>XJ Cash Disc. Merch.</messageReason>
                                        </adjustmentReason>
                                     </adjustmentAndDiscount>
                                     <adjustmentAndDiscount>
                                        <amount>14044.59</amount>
                                        <adjustmentReason>
                                           <messageReason>XI Incentive Discount</messageReason>
                                        </adjustmentReason>
                                     </adjustmentAndDiscount>
                                     <adjustmentAndDiscount>
                                        <amount>8436</amount>
                                        <adjustmentReason>
                                           <messageReason>RN Invoice - net</messageReason>
                                        </adjustmentReason>
                                     </adjustmentAndDiscount>
                                  </adjustmentAndDiscountSummary>
                               </extension>
                            </pay:settlement>
                         </documentCommandOperand>
                      </eanucc:documentCommand>
                   </command>
                </eanucc:transaction>
             </eanucc:message>
          </sh:StandardBusinessDocument>
	</soap:Body>
</soap:Envelope>';


?>