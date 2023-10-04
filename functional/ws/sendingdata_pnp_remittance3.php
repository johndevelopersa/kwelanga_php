<?php

$sendXML = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
<sh:StandardBusinessDocument xmlns:sh="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader" xmlns:eanucc="urn:ean.ucc:2" xmlns:pay="urn:ean.ucc:pay:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns0="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader">
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
			<sh:InstanceIdentifier>0000000427867789</sh:InstanceIdentifier>
			<sh:Type>Settlement</sh:Type>
			<sh:MultipleType>false</sh:MultipleType>
			<sh:CreationDateAndTime>2014-10-16T09:27:46.462+02:00</sh:CreationDateAndTime>
		</sh:DocumentIdentification>
	</sh:StandardBusinessDocumentHeader>
	<eanucc:message>
		<entityIdentification>
			<uniqueCreatorIdentification>150007424110002015</uniqueCreatorIdentification>
			<contentOwner>
				<gln>6001000000001</gln>
			</contentOwner>
		</entityIdentification>
		<eanucc:transaction>
			<entityIdentification>
				<uniqueCreatorIdentification>150007424110002015</uniqueCreatorIdentification>
				<contentOwner>
					<gln>6001000000001</gln>
				</contentOwner>
			</entityIdentification>
			<command>
				<eanucc:documentCommand>
					<documentCommandHeader type="ADD">
						<entityIdentification>
							<uniqueCreatorIdentification>150007424110002015</uniqueCreatorIdentification>
							<contentOwner>
								<gln>6001000000001</gln>
							</contentOwner>
						</entityIdentification>
					</documentCommandHeader>
					<documentCommandOperand>
						<pay:settlement creationDateTime="2014-10-16T09:27:46.462+02:00" documentStatus="ORIGINAL">
							<contentVersion>
								<versionIdentification />
							</contentVersion>
							<documentStructureVersion>
								<versionIdentification />
							</documentStructureVersion>
							<batchIdentification />
							<paymentEffectiveDate>2014-11-03</paymentEffectiveDate>
							<settlementCurrency>
								<currencyISOCode>ZAR</currencyISOCode>
							</settlementCurrency>
							<totalAmount>90421.84</totalAmount>
							<transactionHandlingType>REMITTANCE_ONLY</transactionHandlingType>
							<paymentMethod>
								<automatedClearingHousePaymentFormat />
								<paymentMethodType>ELECTRONIC_CREDIT_ACH</paymentMethodType>
							</paymentMethod>
							<payer>
								<gln>6001000000001</gln>
							</payer>
							<payersFinancialInstitution>
								<gln />
							</payersFinancialInstitution>
							<remitTo>
								<gln>6009662560000</gln>
								<additionalPartyIdentification>
									<additionalPartyIdentificationValue>1000000123</additionalPartyIdentificationValue>
									<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
								</additionalPartyIdentification>
							</remitTo>
							<payee>
								<gln>6009662560000</gln>
								<additionalPartyIdentification>
									<additionalPartyIdentificationValue>1000000123</additionalPartyIdentificationValue>
									<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
								</additionalPartyIdentification>
							</payee>
							<remitToFinancialInstitution>
								<gln />
							</remitToFinancialInstitution>
							<payeesFinancialInstitution>
								<gln />
							</payeesFinancialInstitution>
							<settlementIdentification>
								<uniqueCreatorIdentification>150007424110002015</uniqueCreatorIdentification>
								<contentOwner>
									<gln>6001000000001</gln>
								</contentOwner>
							</settlementIdentification>
							<payerFinancialAccountInformation>
								<accountName />
								<accountNumber>
									<number />
									<accountNumberType />
								</accountNumber>
								<routingNumber>
									<number />
									<routingNumberType />
								</routingNumber>
								<financialInsitutionNameAndAddress>
									<city />
									<cityCode />
									<countryCode>
										<countryISOCode />
									</countryCode>
									<languageOfTheParty>
										<languageISOCode />
									</languageOfTheParty>
									<name />
									<currency>
										<currencyISOCode />
									</currency>
								</financialInsitutionNameAndAddress>
								<branch />
							</payerFinancialAccountInformation>
							<payeeFinancialAccountInformation>
								<accountName>LIJANI FOODS (PTY ) LTD</accountName>
								<accountNumber>
									<number>21499764</number>
									<accountNumberType>03_CHECKING_ACCOUNT</accountNumberType>
								</accountNumber>
								<routingNumber>
									<number>015841</number>
									<routingNumberType>BANKSERV</routingNumberType>
								</routingNumber>
								<branch>KEY WEST</branch>
							</payeeFinancialAccountInformation>
							<remitToFinancialAccountInformation>
								<accountName />
								<accountNumber>
									<number />
									<accountNumberType />
								</accountNumber>
								<routingNumber>
									<number />
									<routingNumberType />
								</routingNumber>
								<branch />
							</remitToFinancialAccountInformation>
							<settlementLineItem number="1">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844989</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870257</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889366</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902085</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913462</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112539</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033019</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="2">
								<amountPaid>718.70</amountPaid>
								<originalAmount>830.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768031</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792374</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823264</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834339</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112544</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033125</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="3">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096170</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123042</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142155</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154790</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166548</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112618</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033125</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="4">
								<amountPaid>226.19</amountPaid>
								<originalAmount>261.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450330</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>14.39</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475497</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.53</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494466</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.53</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507431</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.61</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518431</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>5011820623</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033125</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="5">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210195652</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210208809</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210219503</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210226721</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210233224</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112460</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033033</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="6">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001163</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014702</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025791</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033104</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039947</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112572</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033033</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="7">
								<amountPaid>281.45</amountPaid>
								<originalAmount>325.34</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.49</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529309</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>17.90</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554608</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.12</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573691</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.12</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587296</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.26</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598057</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-01">
									<uniqueCreatorIdentification>5011884047</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033033</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="8">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985857</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007910</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023979</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034929</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044487</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112459</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033040</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="9">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210053646</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210075797</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210092538</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210104151</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210114046</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112438</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033064</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="10">
								<amountPaid>335.37</amountPaid>
								<originalAmount>387.71</originalAmount>
								<adjustmentAndDiscount>
									<amount>7.77</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211917382</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>21.33</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211942940</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211962126</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211975584</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.88</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211986782</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-08">
									<uniqueCreatorIdentification>5012180018</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033132</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="11">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450323</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475490</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494459</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507424</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518424</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112650</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033880</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="12">
								<amountPaid>202.34</amountPaid>
								<originalAmount>233.93</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.68</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211607524</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.87</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211631489</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.85</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211649190</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.85</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211661506</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.34</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211671614</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-02">
									<uniqueCreatorIdentification>5011929942</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033071</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="13">
								<amountPaid>186.85</amountPaid>
								<originalAmount>216.00</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211997088</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212025120</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212044932</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212058693</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212070734</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-09">
									<uniqueCreatorIdentification>5012233132</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033071</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="14">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916841</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939229</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955748</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966800</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976975</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112418</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007015008</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="15">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691138</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716068</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733948</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746097</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757044</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112525</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007015008</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="16">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096174</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123046</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142159</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154794</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166552</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112548</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007033224</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="17">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526686</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552203</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570925</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583381</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594519</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112513</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034047</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="18">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526687</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552204</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570926</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583382</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594520</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112511</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034351</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="19">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916845</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939233</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955752</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966804</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976979</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112429</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034061</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="20">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691136</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716066</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733946</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746095</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757042</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112533</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034061</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="21">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210053648</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210075799</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210092540</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210104153</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210114048</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112452</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034078</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="22">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210445744</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210471327</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210490950</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210504108</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210515883</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112500</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034078</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="23">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122678</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145637</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163263</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176223</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186628</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112462</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034122</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="24">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923714</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949066</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967615</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980302</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991512</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112584</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034139</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="25">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276543</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300601</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319605</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332313</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342954</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112642</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034139</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="26">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985853</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007906</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023975</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034925</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044483</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112434</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034146</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="27">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844988</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870256</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889365</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902084</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913461</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112537</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034146</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="28">
								<amountPaid>797.76</amountPaid>
								<originalAmount>922.23</originalAmount>
								<adjustmentAndDiscount>
									<amount>18.42</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210053649</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>50.73</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210075800</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210092541</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210104154</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.24</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210114049</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112450</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034153</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="29">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844991</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870259</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889368</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902087</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913464</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112557</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034153</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="30">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450328</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475495</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494464</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507429</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518429</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112648</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034160</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="31">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526688</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552205</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570927</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583383</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594521</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112510</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034184</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="32">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276544</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300602</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319606</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332314</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342955</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112639</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034184</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="33">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526690</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552207</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570929</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583385</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594523</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112512</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034207</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="34">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096177</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123049</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142162</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154797</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166555</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112606</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034924</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="35">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351755</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365508</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376771</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384504</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391136</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112655</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034245</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="36">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923711</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949063</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967612</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980299</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991509</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112579</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034368</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="37">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844992</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870260</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889369</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902088</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913465</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112570</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034283</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="38">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526689</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552206</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570928</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583384</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594522</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112508</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034382</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="39">
								<amountPaid>218.07</amountPaid>
								<originalAmount>252.10</originalAmount>
								<adjustmentAndDiscount>
									<amount>5.05</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276547</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.86</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300605</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.30</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319609</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.30</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332317</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.52</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342958</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>5011704264</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034399</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="40">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923708</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949060</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967609</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980296</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991506</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112562</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007037031</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="41">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351759</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365512</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376775</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384508</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391140</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112482</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007037062</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="42">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096176</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123048</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142161</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154796</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166554</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112603</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007037116</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="43">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289010</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312780</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331063</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343369</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354346</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112480</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034535</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="44">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768034</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792377</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811068</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823267</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834342</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112549</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034559</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="45">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211176345</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211191277</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211202595</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211210089</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211216952</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112615</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034597</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="46">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210604111</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210617577</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210628545</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210635437</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210642216</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112521</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034603</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="47">
								<amountPaid>718.70</amountPaid>
								<originalAmount>830.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985859</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007912</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023981</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034931</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044489</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112421</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034610</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="48">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529310</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554609</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573692</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587297</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598058</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112658</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034610</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="49">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916842</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939230</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955749</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966801</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976976</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112433</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034627</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="50">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210195650</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210208807</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210219501</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210226719</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210233222</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112472</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034634</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="51">
								<amountPaid>1307.90</amountPaid>
								<originalAmount>1512.05</originalAmount>
								<adjustmentAndDiscount>
									<amount>30.24</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001167</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>83.17</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014706</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>37.81</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025795</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>37.81</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033108</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.12</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039951</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112591</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034641</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="52">
								<amountPaid>171.32</amountPaid>
								<originalAmount>198.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>3.97</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276546</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.90</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300604</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.96</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319608</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.96</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332316</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>1.98</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342957</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>5011700191</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007055882</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="53">
								<amountPaid>833.46</amountPaid>
								<originalAmount>963.50</originalAmount>
								<adjustmentAndDiscount>
									<amount>19.26</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691131</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>52.99</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716061</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733941</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746090</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.63</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757037</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112520</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034672</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="54">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289012</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312782</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343371</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354348</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112493</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034993</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="55">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691135</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733945</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746094</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757041</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112545</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034993</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="56">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844987</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870255</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889364</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902083</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913460</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112543</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034948</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="57">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276539</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300597</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319601</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332309</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342950</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112612</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034948</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="58">
								<amountPaid>2888.63</amountPaid>
								<originalAmount>3339.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>66.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210365162</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>183.65</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210390802</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>83.49</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210410093</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>83.49</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210423067</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.36</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210434604</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112455</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007034955</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="59">
								<amountPaid>718.70</amountPaid>
								<originalAmount>1354.25</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210245716</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210259058</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210269921</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210277149</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210282468</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>523.42</amount>
									<adjustmentReason>
										<messageReason>RN Credit Memo (Invoice Reduction)</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>1700426044</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112416</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007003005</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="60">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210445740</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210471323</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210490946</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210504104</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210515879</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112474</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007003005</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="61">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122681</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145640</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163266</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176226</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186631</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112461</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020064</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="62">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276542</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300600</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319604</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332312</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342953</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112641</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020064</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="63">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001166</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014705</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025794</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033107</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039950</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112596</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007039073</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="64">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351758</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365511</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376774</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384507</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391139</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112649</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007039073</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="65">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289007</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312777</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331060</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343366</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354343</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112487</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007008000</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="66">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691134</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716064</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733944</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746093</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757040</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112530</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007008000</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="67">
								<amountPaid>797.76</amountPaid>
								<originalAmount>922.23</originalAmount>
								<adjustmentAndDiscount>
									<amount>18.42</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450329</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>50.73</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475496</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494465</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507430</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.24</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518430</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112653</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007008000</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="68">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289013</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312783</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331066</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343372</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354349</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112485</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007009007</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="69">
								<amountPaid>718.70</amountPaid>
								<originalAmount>830.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450324</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475491</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494460</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507425</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518425</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112651</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007009007</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="70">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210053647</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210075798</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210092539</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210104152</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210114047</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112451</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007010003</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="71">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844994</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870262</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889371</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902090</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913467</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112558</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007010003</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="72">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001168</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014707</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025796</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033109</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039952</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112593</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007014001</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="73">
								<amountPaid>1099.38</amountPaid>
								<originalAmount>1270.91</originalAmount>
								<adjustmentAndDiscount>
									<amount>25.40</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916843</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>69.90</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939231</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955750</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966802</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.71</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976977</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112427</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020170</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="74">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289006</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312776</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331059</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343365</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354342</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112490</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020170</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="75">
								<amountPaid>646.60</amountPaid>
								<originalAmount>747.49</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.94</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096173</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.11</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123045</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142158</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154793</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.48</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166551</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112608</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020170</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="76">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844995</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870263</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889372</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902091</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913468</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112554</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020187</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="77">
								<amountPaid>567.54</amountPaid>
								<originalAmount>656.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211176344</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.08</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211191276</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211202594</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211210088</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.55</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211216951</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112617</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020194</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="78">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122682</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145641</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163267</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176227</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186632</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112453</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036362</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="79">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351756</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365509</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376772</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384505</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391137</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112630</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036362</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="80">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122683</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145642</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163268</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176228</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186633</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112423</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036058</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="81">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691133</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716063</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733943</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746092</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757039</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112522</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036089</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="82">
								<amountPaid>1178.44</amountPaid>
								<originalAmount>1362.31</originalAmount>
								<adjustmentAndDiscount>
									<amount>27.22</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096175</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>74.93</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123047</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>34.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142160</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>34.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154795</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.64</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166553</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112588</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036089</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="83">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691132</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716062</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733942</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746091</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757038</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112527</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036102</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="84">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001165</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014704</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025793</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033106</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039949</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112571</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036140</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="85">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210239313</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210240697</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210242190</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210243280</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210243894</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-06">
									<uniqueCreatorIdentification>112470</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036164</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="86">
								<amountPaid>1178.44</amountPaid>
								<originalAmount>1362.31</originalAmount>
								<adjustmentAndDiscount>
									<amount>27.22</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923709</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>74.93</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949061</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>34.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967610</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>34.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980297</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.64</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991507</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112561</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036188</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="87">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985855</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007908</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023977</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034927</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044485</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112448</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036225</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="88">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985856</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007909</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023978</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034928</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044486</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112449</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036225</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="89">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210445742</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210471325</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210490948</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210504106</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210515881</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112502</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036225</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="90">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529305</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554604</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573687</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587292</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598053</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112628</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036225</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="91">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529304</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554603</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573686</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587291</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598052</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112688</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036225</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="92">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210365160</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210390800</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210410091</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210423065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210434602</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112481</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036232</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="93">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985858</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007911</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023980</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034930</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044488</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112436</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036249</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="94">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211224064</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211240263</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211252799</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211262160</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211269473</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-24">
									<uniqueCreatorIdentification>112620</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036249</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="95">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211397001</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211398655</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211400616</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211401985</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211402902</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-27">
									<uniqueCreatorIdentification>112640</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007004002</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="96">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210604112</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210617578</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210628546</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210635438</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210642217</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112507</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020132</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="97">
								<amountPaid>646.60</amountPaid>
								<originalAmount>747.49</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.94</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351757</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.11</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365510</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376773</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384506</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.48</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391138</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112635</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007020132</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="98">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529308</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554607</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573690</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587295</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598056</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112675</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035143</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="99">
								<amountPaid>176.55</amountPaid>
								<originalAmount>204.12</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.08</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211404792</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211419885</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.11</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211430293</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.11</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211438094</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.04</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211443510</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>5011773560</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007036744</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="100">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923712</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949064</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967613</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980300</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991510</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112575</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038014</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="101">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210604110</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210617576</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210628544</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210635436</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210642215</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112524</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038021</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="102">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122680</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145639</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163265</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176225</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186630</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112463</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038038</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="103">
								<amountPaid>75.71</amountPaid>
								<originalAmount>87.52</originalAmount>
								<adjustmentAndDiscount>
									<amount>1.75</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212254711</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.81</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212281198</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.19</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212300022</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.19</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212312508</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>0.87</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2212323725</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-14">
									<uniqueCreatorIdentification>5012469357</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038045</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="104">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210445741</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210471324</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210490947</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210504105</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210515880</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112505</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038083</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="105">
								<amountPaid>725.66</amountPaid>
								<originalAmount>838.89</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.76</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768028</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>46.14</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792371</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.96</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811062</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.96</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823261</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.41</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834336</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112529</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038090</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="106">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529307</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554606</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573689</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587294</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598055</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112674</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038106</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="107">
								<amountPaid>1099.38</amountPaid>
								<originalAmount>1270.91</originalAmount>
								<adjustmentAndDiscount>
									<amount>25.40</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276540</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>69.90</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300598</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319602</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332310</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.71</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342951</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112553</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038717</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="108">
								<amountPaid>1437.40</amountPaid>
								<originalAmount>1661.66</originalAmount>
								<adjustmentAndDiscount>
									<amount>33.20</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923707</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>91.40</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949059</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.52</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967608</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.52</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980295</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.62</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991505</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112580</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038229</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="109">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276541</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300599</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319603</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332311</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342952</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112645</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038243</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="110">
								<amountPaid>833.46</amountPaid>
								<originalAmount>963.50</originalAmount>
								<adjustmentAndDiscount>
									<amount>19.26</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923710</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>52.99</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949062</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967611</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980298</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.63</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991508</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112582</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038281</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="111">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211529306</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211554605</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211573688</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211587293</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211598054</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112654</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038298</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="112">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210691137</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210716067</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210733947</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210746096</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210757043</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112528</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038304</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="113">
								<amountPaid>725.66</amountPaid>
								<originalAmount>838.89</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.76</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985860</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>46.14</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007913</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.96</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023982</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.96</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034932</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.41</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044490</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112430</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038373</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="114">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210445743</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210471326</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210490949</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210504107</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210515882</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112497</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038410</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="115">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211224065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211240264</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211252800</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211262161</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211269474</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112488</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038434</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="116">
								<amountPaid>264.75</amountPaid>
								<originalAmount>306.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211680762</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.84</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211704381</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.65</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211724063</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.65</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211736782</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.06</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211747127</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-10-03">
									<uniqueCreatorIdentification>5012018638</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007038458</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="117">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001161</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014700</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025789</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033102</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039945</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112586</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031015</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="118">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211276545</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211300603</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211319607</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211332315</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211342956</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112614</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031046</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="119">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210526685</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210552202</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210570924</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210583380</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210594518</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112514</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031053</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="120">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985852</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007905</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023974</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034924</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044482</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112445</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031084</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="121">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096171</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123043</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142156</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154791</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166549</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112602</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031091</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="122">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450325</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475492</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494461</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507426</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518426</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112661</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031091</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="123">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923715</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949067</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967616</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980303</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991513</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112573</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031107</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="124">
								<amountPaid>912.52</amountPaid>
								<originalAmount>1054.90</originalAmount>
								<adjustmentAndDiscount>
									<amount>21.08</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210365161</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>58.02</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210390801</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210410092</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210423066</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.56</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210434603</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112498</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031121</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="125">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768029</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792372</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811063</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823262</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834337</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112552</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031121</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="126">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289009</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312779</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331062</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343368</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354345</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112486</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031169</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="127">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096169</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123041</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142154</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154789</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166547</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112607</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031169</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="128">
								<amountPaid>912.52</amountPaid>
								<originalAmount>1054.90</originalAmount>
								<adjustmentAndDiscount>
									<amount>21.08</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210923713</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>58.02</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210949065</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210967614</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210980301</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.56</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210991511</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112574</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031176</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="129">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844990</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870258</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889367</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902086</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913463</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112590</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031183</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="130">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289008</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312778</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331061</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343367</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354344</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112489</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031213</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="131">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450326</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475493</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494462</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507427</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518427</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112644</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031213</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="132">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211450327</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211475494</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211494463</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211507428</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211518428</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112664</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031213</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="133">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122679</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145638</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163264</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176224</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186629</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112466</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031220</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="134">
								<amountPaid>797.76</amountPaid>
								<originalAmount>922.23</originalAmount>
								<adjustmentAndDiscount>
									<amount>18.42</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001164</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>50.73</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014703</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025792</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033105</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.24</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039948</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112577</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031275</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="135">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210195651</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210208808</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210219502</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210226720</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210233223</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112471</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031299</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="136">
								<amountPaid>567.54</amountPaid>
								<originalAmount>656.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916844</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.08</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939232</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955751</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966803</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.55</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976978</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112432</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031749</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="137">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209985854</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210007907</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210023976</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210034926</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210044484</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112447</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031350</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="138">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768032</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792375</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811066</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823265</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834340</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112550</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031350</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="139">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211176346</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211191278</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211202596</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211210090</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211216953</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112623</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031350</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="140">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210122677</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210145636</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210163262</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210176222</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210186627</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112467</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031367</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="141">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210844993</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210870261</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210889370</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210902089</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210913466</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112569</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031374</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="142">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210604109</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210617575</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210628543</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210635435</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210642214</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112519</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031381</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="143">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001162</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014701</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025790</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033103</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039946</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112587</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031381</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="144">
								<amountPaid>1171.48</amountPaid>
								<originalAmount>1354.25</originalAmount>
								<adjustmentAndDiscount>
									<amount>27.06</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768030</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>74.49</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792373</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.84</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811064</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.84</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823263</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.54</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834338</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112551</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031411</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="145">
								<amountPaid>567.54</amountPaid>
								<originalAmount>656.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211176347</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.08</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211191279</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211202597</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211210091</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.55</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211216954</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112622</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031428</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="146">
								<amountPaid>1099.38</amountPaid>
								<originalAmount>1270.91</originalAmount>
								<adjustmentAndDiscount>
									<amount>25.40</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211176348</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>69.90</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211191280</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211202598</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>31.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211210092</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.71</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211216955</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112624</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031435</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="147">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210195649</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210208806</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210219500</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210226718</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210233221</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112477</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007002008</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="148">
								<amountPaid>912.52</amountPaid>
								<originalAmount>1054.90</originalAmount>
								<adjustmentAndDiscount>
									<amount>21.08</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211001160</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>58.02</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211014699</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211025788</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211033101</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.56</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211039944</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112595</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007002008</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="149">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211351754</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211365507</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211376770</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211384503</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211391135</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112663</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007002008</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="150">
								<amountPaid>646.60</amountPaid>
								<originalAmount>747.49</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.94</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211096168</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.11</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211123040</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211142153</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211154788</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.48</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2211166546</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112598</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007012007</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="151">
								<amountPaid>905.56</amountPaid>
								<originalAmount>1046.84</originalAmount>
								<adjustmentAndDiscount>
									<amount>20.92</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210768033</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>57.58</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210792376</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210811067</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210823266</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210834341</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112540</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007031541</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="152">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209916840</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209939228</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209955747</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209966799</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2209976974</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112413</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032029</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="153">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210289011</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210312781</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210331064</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210343370</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2210354347</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112476</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032029</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="154">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009005</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027122</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040408</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047360</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055497</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112428</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007028121</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="155">
								<amountPaid>467.09</amountPaid>
								<originalAmount>540.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.81</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207778774</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.71</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207796516</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.50</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207809645</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.50</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207816739</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207824424</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>5011570657</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>CREDIT_NOTE </invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007028121</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="156">
								<amountPaid>718.70</amountPaid>
								<originalAmount>830.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207063588</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207080494</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207093298</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207100074</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207107942</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112431</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007068011</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="157">
								<amountPaid>567.54</amountPaid>
								<originalAmount>656.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207949297</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.08</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207957495</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207964001</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207967492</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.55</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207971868</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112662</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026011</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="158">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009004</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027121</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040407</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047359</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055496</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112439</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026035</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="159">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207830845</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207839936</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207846826</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207850512</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207855113</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112611</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026035</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="160">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207063587</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207080493</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207093297</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207100073</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207107941</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112442</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026042</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="161">
								<amountPaid>459.74</amountPaid>
								<originalAmount>531.48</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.62</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207227732</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>29.23</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207244778</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207257853</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207264914</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.33</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207272690</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112491</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026059</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="162">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009001</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027118</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040404</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047356</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055493</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112409</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026073</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="163">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207830848</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207839939</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207846829</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207850515</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207855116</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112610</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026073</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="164">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009003</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027120</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040406</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047358</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055495</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112441</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026110</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="165">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207557416</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207574755</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207588135</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207595029</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207603142</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112541</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026110</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="166">
								<amountPaid>833.46</amountPaid>
								<originalAmount>963.50</originalAmount>
								<adjustmentAndDiscount>
									<amount>19.26</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208068710</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>52.99</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208087357</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208101735</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208109437</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.63</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208118239</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112665</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026110</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="167">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207778775</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207796517</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207809646</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207816740</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207824425</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112604</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026141</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="168">
								<amountPaid>452.78</amountPaid>
								<originalAmount>523.42</originalAmount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207714482</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.79</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207722455</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207728710</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>13.08</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207732080</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.23</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207736213</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112592</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026158</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="169">
								<amountPaid>905.53</amountPaid>
								<originalAmount>1046.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>20.92</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207949298</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>57.58</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207957496</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207964002</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207967493</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.48</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207971869</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-26">
									<uniqueCreatorIdentification>112657</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026158</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="170">
								<amountPaid>1437.40</amountPaid>
								<originalAmount>1661.66</originalAmount>
								<adjustmentAndDiscount>
									<amount>33.20</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207557415</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>91.40</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207574754</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.52</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207588134</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.52</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207595028</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.62</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207603141</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112546</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026165</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="171">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610214</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628320</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641559</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648458</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656467</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112535</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026196</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="172">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208068707</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208087354</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208101732</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208109434</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208118236</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112652</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026257</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="173">
								<amountPaid>574.48</amountPaid>
								<originalAmount>664.14</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009007</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.53</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027124</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.61</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040410</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.61</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047362</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.63</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055499</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112437</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026271</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="174">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207280420</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207298749</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207312691</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207320227</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207328753</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112492</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007064020</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="175">
								<amountPaid>905.56</amountPaid>
								<originalAmount>1046.84</originalAmount>
								<adjustmentAndDiscount>
									<amount>20.92</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610219</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>57.58</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628325</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641564</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.16</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648463</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.46</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656472</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112567</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007027025</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="176">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207859957</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207872435</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207881945</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207887478</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207893551</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-24">
									<uniqueCreatorIdentification>112494</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007027049</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="177">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207063586</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207080492</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207093296</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207100072</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207107940</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112400</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007027087</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="178">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207557417</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207574756</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207588136</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207595030</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207603143</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-16">
									<uniqueCreatorIdentification>112499</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007027094</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="179">
								<amountPaid>991.58</amountPaid>
								<originalAmount>1146.30</originalAmount>
								<adjustmentAndDiscount>
									<amount>22.90</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207280419</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>63.05</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207298748</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.64</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207312690</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.64</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207320226</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.49</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207328752</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112464</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026530</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="180">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207714481</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207722454</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207728709</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207732079</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207736212</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112506</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026530</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="181">
								<amountPaid>1444.36</amountPaid>
								<originalAmount>1669.72</originalAmount>
								<adjustmentAndDiscount>
									<amount>33.36</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207390993</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>91.84</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207409060</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.72</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207423240</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>41.72</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207430153</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.72</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207438475</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112475</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026561</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="182">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2206960151</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2206976757</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2206988918</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2206995426</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207002454</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-01">
									<uniqueCreatorIdentification>112420</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026585</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="183">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207227731</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207244777</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207257852</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207264913</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207272689</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-08">
									<uniqueCreatorIdentification>112479</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026585</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="184">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207507004</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207524015</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207536553</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207542954</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207550301</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112526</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026585</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="185">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207778776</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207796518</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207809647</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207816741</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207824426</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-22">
									<uniqueCreatorIdentification>112597</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026585</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="186">
								<amountPaid>912.52</amountPaid>
								<originalAmount>1054.90</originalAmount>
								<adjustmentAndDiscount>
									<amount>21.08</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207114531</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>58.02</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207131179</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207143475</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>26.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207150738</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.56</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207157847</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112456</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026707</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="187">
								<amountPaid>718.70</amountPaid>
								<originalAmount>830.83</originalAmount>
								<adjustmentAndDiscount>
									<amount>16.60</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610218</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>45.70</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628324</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641563</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>20.76</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648462</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>8.31</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656471</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112560</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026707</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="188">
								<amountPaid>1063.68</amountPaid>
								<originalAmount>1229.64</originalAmount>
								<adjustmentAndDiscount>
									<amount>24.56</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009006</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>67.64</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027123</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040409</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047361</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055498</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112443</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026721</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="189">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207445393</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207453636</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207459919</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207463218</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207467531</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112468</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026783</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="190">
								<amountPaid>1250.53</amountPaid>
								<originalAmount>1661.65</originalAmount>
								<adjustmentAndDiscount>
									<amount>28.88</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207227733</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>79.52</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207244779</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.12</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207257854</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.12</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207264915</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>14.47</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207272691</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>216.01</amount>
									<adjustmentReason>
										<messageReason>RN Invoice reduction</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>5113499405</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112424</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007026837</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="191">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207114532</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207131180</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207143476</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207150739</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207157848</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112465</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035037</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="192">
								<amountPaid>373.69</amountPaid>
								<originalAmount>432.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207663212</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207679943</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207693717</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207700300</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207708132</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112581</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035037</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="193">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207471444</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207473034</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207474369</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207475140</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207475839</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-13">
									<uniqueCreatorIdentification>112501</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035044</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="194">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207714483</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207722456</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207728711</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207732081</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207736214</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-19">
									<uniqueCreatorIdentification>112563</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035044</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="195">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207163623</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207172328</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207178794</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207182326</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207186728</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112440</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007035334</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="196">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208014982</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208032309</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208045816</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208052885</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208060725</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112634</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025038</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="197">
								<amountPaid>797.76</amountPaid>
								<originalAmount>922.23</originalAmount>
								<adjustmentAndDiscount>
									<amount>18.42</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207163622</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>50.73</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207172327</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207178793</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.04</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207182325</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>9.24</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207186727</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-05">
									<uniqueCreatorIdentification>112426</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025045</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="198">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207507003</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207524014</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207536552</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207542953</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207550300</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-15">
									<uniqueCreatorIdentification>112532</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025045</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="199">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207009002</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207027119</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207040405</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207047357</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207055494</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-02">
									<uniqueCreatorIdentification>112435</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025076</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="200">
								<amountPaid>567.54</amountPaid>
								<originalAmount>656.09</originalAmount>
								<adjustmentAndDiscount>
									<amount>13.12</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208068708</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>36.08</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208087355</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208101733</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208109435</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.55</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208118237</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112673</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025076</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="201">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207337188</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207354769</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207368264</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207375439</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207383697</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-10">
									<uniqueCreatorIdentification>112496</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025083</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="202">
								<amountPaid>193.82</amountPaid>
								<originalAmount>224.07</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.48</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610215</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628321</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641560</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.60</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648459</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.25</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656468</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112556</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025083</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="203">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207390990</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207409057</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207423237</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207430150</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207438472</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112483</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025090</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="204">
								<amountPaid>639.64</amountPaid>
								<originalAmount>739.43</originalAmount>
								<adjustmentAndDiscount>
									<amount>14.78</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207280418</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>40.67</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207298747</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207312689</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>18.48</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207320225</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.38</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207328751</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-09">
									<uniqueCreatorIdentification>112444</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025137</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="205">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207390991</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207409058</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207423238</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207430151</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207438473</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112484</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025168</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="206">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208014981</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208032308</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208045815</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208052884</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208060724</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-29">
									<uniqueCreatorIdentification>112605</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025168</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="207">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207063585</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207080491</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207093295</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207100071</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207107939</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112401</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025243</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="208">
								<amountPaid>373.69</amountPaid>
								<originalAmount>432.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610213</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628319</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641558</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648457</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656466</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112509</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025243</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="209">
								<amountPaid>984.62</amountPaid>
								<originalAmount>1138.24</originalAmount>
								<adjustmentAndDiscount>
									<amount>22.74</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207390989</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>62.61</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207409056</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.44</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207423236</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>28.44</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207430149</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.39</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207438471</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112454</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025250</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="210">
								<amountPaid>373.72</amountPaid>
								<originalAmount>432.02</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.64</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207830847</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>23.76</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207839938</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207846828</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>10.80</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207850514</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.30</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207855115</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112534</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025304</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="211">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207063584</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207080490</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207093294</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207100070</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207107938</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-03">
									<uniqueCreatorIdentification>112396</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025021</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="212">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207830846</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207839937</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207846827</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207850513</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207855114</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-23">
									<uniqueCreatorIdentification>112621</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025434</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="213">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208068709</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208087356</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208101734</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208109436</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2208118238</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-30">
									<uniqueCreatorIdentification>112680</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007025434</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="214">
								<amountPaid>531.84</amountPaid>
								<originalAmount>614.82</originalAmount>
								<adjustmentAndDiscount>
									<amount>12.28</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207114533</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>33.82</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207131181</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207143477</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>15.36</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207150740</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>6.16</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207157849</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-04">
									<uniqueCreatorIdentification>112457</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032043</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="215">
								<amountPaid>1063.68</amountPaid>
								<originalAmount>1229.64</originalAmount>
								<adjustmentAndDiscount>
									<amount>24.56</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207445394</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>67.64</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207453637</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207459920</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207463219</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207467532</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-12">
									<uniqueCreatorIdentification>112515</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032067</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="216">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207390992</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207409059</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207423239</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207430152</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207438474</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-11">
									<uniqueCreatorIdentification>112517</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032098</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="217">
								<amountPaid>265.92</amountPaid>
								<originalAmount>307.41</originalAmount>
								<adjustmentAndDiscount>
									<amount>6.14</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207663213</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>16.91</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207679944</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207693718</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>7.68</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207700301</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>3.08</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207708133</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-18">
									<uniqueCreatorIdentification>112585</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032098</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="218">
								<amountPaid>1063.68</amountPaid>
								<originalAmount>1229.64</originalAmount>
								<adjustmentAndDiscount>
									<amount>24.56</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610216</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>67.64</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628322</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641561</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>30.72</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648460</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>12.32</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656469</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112564</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032135</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="219">
								<amountPaid>380.68</amountPaid>
								<originalAmount>440.08</originalAmount>
								<adjustmentAndDiscount>
									<amount>8.80</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207898434</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>24.20</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207914916</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207928617</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.00</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207935377</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>4.40</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207942976</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-25">
									<uniqueCreatorIdentification>112636</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032173</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<settlementLineItem number="220">
								<amountPaid>186.86</amountPaid>
								<originalAmount>216.01</originalAmount>
								<adjustmentAndDiscount>
									<amount>4.32</amount>
									<adjustmentReason>
										<messageReason>XJ 2.000 % Cash Disc. Merch.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207610217</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>11.88</amount>
									<adjustmentReason>
										<messageReason>XI 5.500 % Incentive Discount</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207628323</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XH 2.500 % Marketing Fund</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207641562</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>5.40</amount>
									<adjustmentReason>
										<messageReason>XF 2.500 % Advertising Allow.</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207648461</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<adjustmentAndDiscount>
									<amount>2.15</amount>
									<adjustmentReason>
										<messageReason>X1 1.000 % Smart Shopper</messageReason>
										<sourceCode>PNP</sourceCode>
									</adjustmentReason>
									<alternateAdjustmentReference>
										<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
										<identification>2207656470</identification>
									</alternateAdjustmentReference>
								</adjustmentAndDiscount>
								<invoice creationDateTime="2014-09-17">
									<uniqueCreatorIdentification>112555</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
									<invoiceType>INVOICE</invoiceType>
								</invoice>
								<settlementEntity entityType="SITE">
									<partyIdentification>
										<gln>6001007032203</gln>
									</partyIdentification>
								</settlementEntity>
							</settlementLineItem>
							<extension>
								<settlementLineItem number="1">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114023528</documentNumber>
									<itemText>5114023528</itemText>
								</settlementLineItem>
								<settlementLineItem number="2">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113970505</documentNumber>
									<itemText>5113970505</itemText>
								</settlementLineItem>
								<settlementLineItem number="3">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114210152</documentNumber>
									<itemText>5114210152</itemText>
								</settlementLineItem>
								<settlementLineItem number="4">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114501138</documentNumber>
									<itemText>5114501138</itemText>
								</settlementLineItem>
								<settlementLineItem number="5">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113560104</documentNumber>
									<itemText>5113560104</itemText>
								</settlementLineItem>
								<settlementLineItem number="6">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114133134</documentNumber>
									<itemText>5114133134</itemText>
								</settlementLineItem>
								<settlementLineItem number="7">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114550810</documentNumber>
									<itemText>5114550810</itemText>
								</settlementLineItem>
								<settlementLineItem number="8">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113403648</documentNumber>
									<itemText>5113403648</itemText>
								</settlementLineItem>
								<settlementLineItem number="9">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113447871</documentNumber>
									<itemText>5113447871</itemText>
								</settlementLineItem>
								<settlementLineItem number="10">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114835097</documentNumber>
									<itemText>5114835097</itemText>
								</settlementLineItem>
								<settlementLineItem number="11">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114470955</documentNumber>
									<itemText>5114470955</itemText>
								</settlementLineItem>
								<settlementLineItem number="12">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114601078</documentNumber>
									<itemText>5114601078</itemText>
								</settlementLineItem>
								<settlementLineItem number="13">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114886319</documentNumber>
									<itemText>5114886319</itemText>
								</settlementLineItem>
								<settlementLineItem number="14">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113352323</documentNumber>
									<itemText>5113352323</itemText>
								</settlementLineItem>
								<settlementLineItem number="15">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113921985</documentNumber>
									<itemText>5113921985</itemText>
								</settlementLineItem>
								<settlementLineItem number="16">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205077</documentNumber>
									<itemText>5114205077</itemText>
								</settlementLineItem>
								<settlementLineItem number="17">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113793723</documentNumber>
									<itemText>5113793723</itemText>
								</settlementLineItem>
								<settlementLineItem number="18">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113793738</documentNumber>
									<itemText>5113793738</itemText>
								</settlementLineItem>
								<settlementLineItem number="19">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113353812</documentNumber>
									<itemText>5113353812</itemText>
								</settlementLineItem>
								<settlementLineItem number="20">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113921488</documentNumber>
									<itemText>5113921488</itemText>
								</settlementLineItem>
								<settlementLineItem number="21">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113456428</documentNumber>
									<itemText>5113456428</itemText>
								</settlementLineItem>
								<settlementLineItem number="22">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113746624</documentNumber>
									<itemText>5113746624</itemText>
								</settlementLineItem>
								<settlementLineItem number="23">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113508828</documentNumber>
									<itemText>5113508828</itemText>
								</settlementLineItem>
								<settlementLineItem number="24">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114078999</documentNumber>
									<itemText>5114078999</itemText>
								</settlementLineItem>
								<settlementLineItem number="25">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114340751</documentNumber>
									<itemText>5114340751</itemText>
								</settlementLineItem>
								<settlementLineItem number="26">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405398</documentNumber>
									<itemText>5113405398</itemText>
								</settlementLineItem>
								<settlementLineItem number="27">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114019038</documentNumber>
									<itemText>5114019038</itemText>
								</settlementLineItem>
								<settlementLineItem number="28">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113459311</documentNumber>
									<itemText>5113459311</itemText>
								</settlementLineItem>
								<settlementLineItem number="29">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114029228</documentNumber>
									<itemText>5114029228</itemText>
								</settlementLineItem>
								<settlementLineItem number="30">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114472195</documentNumber>
									<itemText>5114472195</itemText>
								</settlementLineItem>
								<settlementLineItem number="31">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113793704</documentNumber>
									<itemText>5113793704</itemText>
								</settlementLineItem>
								<settlementLineItem number="32">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114340736</documentNumber>
									<itemText>5114340736</itemText>
								</settlementLineItem>
								<settlementLineItem number="33">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113798799</documentNumber>
									<itemText>5113798799</itemText>
								</settlementLineItem>
								<settlementLineItem number="34">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205922</documentNumber>
									<itemText>5114205922</itemText>
								</settlementLineItem>
								<settlementLineItem number="35">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114398587</documentNumber>
									<itemText>5114398587</itemText>
								</settlementLineItem>
								<settlementLineItem number="36">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114083046</documentNumber>
									<itemText>5114083046</itemText>
								</settlementLineItem>
								<settlementLineItem number="37">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114029267</documentNumber>
									<itemText>5114029267</itemText>
								</settlementLineItem>
								<settlementLineItem number="38">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113798790</documentNumber>
									<itemText>5113798790</itemText>
								</settlementLineItem>
								<settlementLineItem number="39">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114372034</documentNumber>
									<itemText>5114372034</itemText>
								</settlementLineItem>
								<settlementLineItem number="40">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114070380</documentNumber>
									<itemText>5114070380</itemText>
								</settlementLineItem>
								<settlementLineItem number="41">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114364416</documentNumber>
									<itemText>5114364416</itemText>
								</settlementLineItem>
								<settlementLineItem number="42">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205874</documentNumber>
									<itemText>5114205874</itemText>
								</settlementLineItem>
								<settlementLineItem number="43">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113638849</documentNumber>
									<itemText>5113638849</itemText>
								</settlementLineItem>
								<settlementLineItem number="44">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113974390</documentNumber>
									<itemText>5113974390</itemText>
								</settlementLineItem>
								<settlementLineItem number="45">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114270342</documentNumber>
									<itemText>5114270342</itemText>
								</settlementLineItem>
								<settlementLineItem number="46">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113842789</documentNumber>
									<itemText>5113842789</itemText>
								</settlementLineItem>
								<settlementLineItem number="47">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113399111</documentNumber>
									<itemText>5113399111</itemText>
								</settlementLineItem>
								<settlementLineItem number="48">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114515137</documentNumber>
									<itemText>5114515137</itemText>
								</settlementLineItem>
								<settlementLineItem number="49">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113352641</documentNumber>
									<itemText>5113352641</itemText>
								</settlementLineItem>
								<settlementLineItem number="50">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113571680</documentNumber>
									<itemText>5113571680</itemText>
								</settlementLineItem>
								<settlementLineItem number="51">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114128424</documentNumber>
									<itemText>5114128424</itemText>
								</settlementLineItem>
								<settlementLineItem number="52">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114372033</documentNumber>
									<itemText>5114372033</itemText>
								</settlementLineItem>
								<settlementLineItem number="53">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113917357</documentNumber>
									<itemText>5113917357</itemText>
								</settlementLineItem>
								<settlementLineItem number="54">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113636631</documentNumber>
									<itemText>5113636631</itemText>
								</settlementLineItem>
								<settlementLineItem number="55">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113921519</documentNumber>
									<itemText>5113921519</itemText>
								</settlementLineItem>
								<settlementLineItem number="56">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114019834</documentNumber>
									<itemText>5114019834</itemText>
								</settlementLineItem>
								<settlementLineItem number="57">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114335267</documentNumber>
									<itemText>5114335267</itemText>
								</settlementLineItem>
								<settlementLineItem number="58">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113690917</documentNumber>
									<itemText>5113690917</itemText>
								</settlementLineItem>
								<settlementLineItem number="59">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113499404</documentNumber>
									<itemText>5113499404</itemText>
								</settlementLineItem>
								<settlementLineItem number="60">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113733028</documentNumber>
									<itemText>5113733028</itemText>
								</settlementLineItem>
								<settlementLineItem number="61">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113511827</documentNumber>
									<itemText>5113511827</itemText>
								</settlementLineItem>
								<settlementLineItem number="62">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114340734</documentNumber>
									<itemText>5114340734</itemText>
								</settlementLineItem>
								<settlementLineItem number="63">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114128430</documentNumber>
									<itemText>5114128430</itemText>
								</settlementLineItem>
								<settlementLineItem number="64">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114393593</documentNumber>
									<itemText>5114393593</itemText>
								</settlementLineItem>
								<settlementLineItem number="65">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113635254</documentNumber>
									<itemText>5113635254</itemText>
								</settlementLineItem>
								<settlementLineItem number="66">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113918723</documentNumber>
									<itemText>5113918723</itemText>
								</settlementLineItem>
								<settlementLineItem number="67">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114472172</documentNumber>
									<itemText>5114472172</itemText>
								</settlementLineItem>
								<settlementLineItem number="68">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113636520</documentNumber>
									<itemText>5113636520</itemText>
								</settlementLineItem>
								<settlementLineItem number="69">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114470953</documentNumber>
									<itemText>5114470953</itemText>
								</settlementLineItem>
								<settlementLineItem number="70">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113450808</documentNumber>
									<itemText>5113450808</itemText>
								</settlementLineItem>
								<settlementLineItem number="71">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114025347</documentNumber>
									<itemText>5114025347</itemText>
								</settlementLineItem>
								<settlementLineItem number="72">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114128412</documentNumber>
									<itemText>5114128412</itemText>
								</settlementLineItem>
								<settlementLineItem number="73">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113352644</documentNumber>
									<itemText>5113352644</itemText>
								</settlementLineItem>
								<settlementLineItem number="74">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113633764</documentNumber>
									<itemText>5113633764</itemText>
								</settlementLineItem>
								<settlementLineItem number="75">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205105</documentNumber>
									<itemText>5114205105</itemText>
								</settlementLineItem>
								<settlementLineItem number="76">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114025323</documentNumber>
									<itemText>5114025323</itemText>
								</settlementLineItem>
								<settlementLineItem number="77">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114270311</documentNumber>
									<itemText>5114270311</itemText>
								</settlementLineItem>
								<settlementLineItem number="78">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113502306</documentNumber>
									<itemText>5113502306</itemText>
								</settlementLineItem>
								<settlementLineItem number="79">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114388694</documentNumber>
									<itemText>5114388694</itemText>
								</settlementLineItem>
								<settlementLineItem number="80">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113502293</documentNumber>
									<itemText>5113502293</itemText>
								</settlementLineItem>
								<settlementLineItem number="81">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113918722</documentNumber>
									<itemText>5113918722</itemText>
								</settlementLineItem>
								<settlementLineItem number="82">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205089</documentNumber>
									<itemText>5114205089</itemText>
								</settlementLineItem>
								<settlementLineItem number="83">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113919987</documentNumber>
									<itemText>5113919987</itemText>
								</settlementLineItem>
								<settlementLineItem number="84">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114125356</documentNumber>
									<itemText>5114125356</itemText>
								</settlementLineItem>
								<settlementLineItem number="85">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113587612</documentNumber>
									<itemText>5113587612</itemText>
								</settlementLineItem>
								<settlementLineItem number="86">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114071814</documentNumber>
									<itemText>5114071814</itemText>
								</settlementLineItem>
								<settlementLineItem number="87">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405436</documentNumber>
									<itemText>5113405436</itemText>
								</settlementLineItem>
								<settlementLineItem number="88">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405434</documentNumber>
									<itemText>5113405434</itemText>
								</settlementLineItem>
								<settlementLineItem number="89">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113736823</documentNumber>
									<itemText>5113736823</itemText>
								</settlementLineItem>
								<settlementLineItem number="90">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114521291</documentNumber>
									<itemText>5114521291</itemText>
								</settlementLineItem>
								<settlementLineItem number="91">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114521308</documentNumber>
									<itemText>5114521308</itemText>
								</settlementLineItem>
								<settlementLineItem number="92">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113684217</documentNumber>
									<itemText>5113684217</itemText>
								</settlementLineItem>
								<settlementLineItem number="93">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113407325</documentNumber>
									<itemText>5113407325</itemText>
								</settlementLineItem>
								<settlementLineItem number="94">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114292877</documentNumber>
									<itemText>5114292877</itemText>
								</settlementLineItem>
								<settlementLineItem number="95">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114421758</documentNumber>
									<itemText>5114421758</itemText>
								</settlementLineItem>
								<settlementLineItem number="96">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113842768</documentNumber>
									<itemText>5113842768</itemText>
								</settlementLineItem>
								<settlementLineItem number="97">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114393581</documentNumber>
									<itemText>5114393581</itemText>
								</settlementLineItem>
								<settlementLineItem number="98">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114523192</documentNumber>
									<itemText>5114523192</itemText>
								</settlementLineItem>
								<settlementLineItem number="99">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114451722</documentNumber>
									<itemText>5114451722</itemText>
								</settlementLineItem>
								<settlementLineItem number="100">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114082987</documentNumber>
									<itemText>5114082987</itemText>
								</settlementLineItem>
								<settlementLineItem number="101">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113848170</documentNumber>
									<itemText>5113848170</itemText>
								</settlementLineItem>
								<settlementLineItem number="102">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113507007</documentNumber>
									<itemText>5113507007</itemText>
								</settlementLineItem>
								<settlementLineItem number="103">
									<documentType>RZ Returns</documentType>
									<documentNumber>5115071480</documentNumber>
									<itemText>5115071480</itemText>
								</settlementLineItem>
								<settlementLineItem number="104">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113736829</documentNumber>
									<itemText>5113736829</itemText>
								</settlementLineItem>
								<settlementLineItem number="105">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113967403</documentNumber>
									<itemText>5113967403</itemText>
								</settlementLineItem>
								<settlementLineItem number="106">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114525011</documentNumber>
									<itemText>5114525011</itemText>
								</settlementLineItem>
								<settlementLineItem number="107">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114333233</documentNumber>
									<itemText>5114333233</itemText>
								</settlementLineItem>
								<settlementLineItem number="108">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114073198</documentNumber>
									<itemText>5114073198</itemText>
								</settlementLineItem>
								<settlementLineItem number="109">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114340770</documentNumber>
									<itemText>5114340770</itemText>
								</settlementLineItem>
								<settlementLineItem number="110">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114082980</documentNumber>
									<itemText>5114082980</itemText>
								</settlementLineItem>
								<settlementLineItem number="111">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114518109</documentNumber>
									<itemText>5114518109</itemText>
								</settlementLineItem>
								<settlementLineItem number="112">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113921480</documentNumber>
									<itemText>5113921480</itemText>
								</settlementLineItem>
								<settlementLineItem number="113">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113400707</documentNumber>
									<itemText>5113400707</itemText>
								</settlementLineItem>
								<settlementLineItem number="114">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113736807</documentNumber>
									<itemText>5113736807</itemText>
								</settlementLineItem>
								<settlementLineItem number="115">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114303865</documentNumber>
									<itemText>5114303865</itemText>
								</settlementLineItem>
								<settlementLineItem number="116">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114650797</documentNumber>
									<itemText>5114650797</itemText>
								</settlementLineItem>
								<settlementLineItem number="117">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114131111</documentNumber>
									<itemText>5114131111</itemText>
								</settlementLineItem>
								<settlementLineItem number="118">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114351171</documentNumber>
									<itemText>5114351171</itemText>
								</settlementLineItem>
								<settlementLineItem number="119">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113790938</documentNumber>
									<itemText>5113790938</itemText>
								</settlementLineItem>
								<settlementLineItem number="120">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405408</documentNumber>
									<itemText>5113405408</itemText>
								</settlementLineItem>
								<settlementLineItem number="121">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114210120</documentNumber>
									<itemText>5114210120</itemText>
								</settlementLineItem>
								<settlementLineItem number="122">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114470986</documentNumber>
									<itemText>5114470986</itemText>
								</settlementLineItem>
								<settlementLineItem number="123">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114078958</documentNumber>
									<itemText>5114078958</itemText>
								</settlementLineItem>
								<settlementLineItem number="124">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113685327</documentNumber>
									<itemText>5113685327</itemText>
								</settlementLineItem>
								<settlementLineItem number="125">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113969914</documentNumber>
									<itemText>5113969914</itemText>
								</settlementLineItem>
								<settlementLineItem number="126">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113638862</documentNumber>
									<itemText>5113638862</itemText>
								</settlementLineItem>
								<settlementLineItem number="127">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114210134</documentNumber>
									<itemText>5114210134</itemText>
								</settlementLineItem>
								<settlementLineItem number="128">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114075327</documentNumber>
									<itemText>5114075327</itemText>
								</settlementLineItem>
								<settlementLineItem number="129">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114037475</documentNumber>
									<itemText>5114037475</itemText>
								</settlementLineItem>
								<settlementLineItem number="130">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113635244</documentNumber>
									<itemText>5113635244</itemText>
								</settlementLineItem>
								<settlementLineItem number="131">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114470916</documentNumber>
									<itemText>5114470916</itemText>
								</settlementLineItem>
								<settlementLineItem number="132">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114471006</documentNumber>
									<itemText>5114471006</itemText>
								</settlementLineItem>
								<settlementLineItem number="133">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113508844</documentNumber>
									<itemText>5113508844</itemText>
								</settlementLineItem>
								<settlementLineItem number="134">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114126435</documentNumber>
									<itemText>5114126435</itemText>
								</settlementLineItem>
								<settlementLineItem number="135">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113556661</documentNumber>
									<itemText>5113556661</itemText>
								</settlementLineItem>
								<settlementLineItem number="136">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113352685</documentNumber>
									<itemText>5113352685</itemText>
								</settlementLineItem>
								<settlementLineItem number="137">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405442</documentNumber>
									<itemText>5113405442</itemText>
								</settlementLineItem>
								<settlementLineItem number="138">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113974712</documentNumber>
									<itemText>5113974712</itemText>
								</settlementLineItem>
								<settlementLineItem number="139">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114266493</documentNumber>
									<itemText>5114266493</itemText>
								</settlementLineItem>
								<settlementLineItem number="140">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113508870</documentNumber>
									<itemText>5113508870</itemText>
								</settlementLineItem>
								<settlementLineItem number="141">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114025369</documentNumber>
									<itemText>5114025369</itemText>
								</settlementLineItem>
								<settlementLineItem number="142">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113848162</documentNumber>
									<itemText>5113848162</itemText>
								</settlementLineItem>
								<settlementLineItem number="143">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114133138</documentNumber>
									<itemText>5114133138</itemText>
								</settlementLineItem>
								<settlementLineItem number="144">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113969913</documentNumber>
									<itemText>5113969913</itemText>
								</settlementLineItem>
								<settlementLineItem number="145">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114266679</documentNumber>
									<itemText>5114266679</itemText>
								</settlementLineItem>
								<settlementLineItem number="146">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114266758</documentNumber>
									<itemText>5114266758</itemText>
								</settlementLineItem>
								<settlementLineItem number="147">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113562240</documentNumber>
									<itemText>5113562240</itemText>
								</settlementLineItem>
								<settlementLineItem number="148">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114136110</documentNumber>
									<itemText>5114136110</itemText>
								</settlementLineItem>
								<settlementLineItem number="149">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114395535</documentNumber>
									<itemText>5114395535</itemText>
								</settlementLineItem>
								<settlementLineItem number="150">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114210138</documentNumber>
									<itemText>5114210138</itemText>
								</settlementLineItem>
								<settlementLineItem number="151">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113974569</documentNumber>
									<itemText>5113974569</itemText>
								</settlementLineItem>
								<settlementLineItem number="152">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113347829</documentNumber>
									<itemText>5113347829</itemText>
								</settlementLineItem>
								<settlementLineItem number="153">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113638839</documentNumber>
									<itemText>5113638839</itemText>
								</settlementLineItem>
								<settlementLineItem number="154">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113403646</documentNumber>
									<itemText>5113403646</itemText>
								</settlementLineItem>
								<settlementLineItem number="155">
									<documentType>RZ Returns</documentType>
									<documentNumber>5114242748</documentNumber>
									<itemText>5114242748</itemText>
								</settlementLineItem>
								<settlementLineItem number="156">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113447480</documentNumber>
									<itemText>5113447480</itemText>
								</settlementLineItem>
								<settlementLineItem number="157">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114398592</documentNumber>
									<itemText>5114398592</itemText>
								</settlementLineItem>
								<settlementLineItem number="158">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113407316</documentNumber>
									<itemText>5113407316</itemText>
								</settlementLineItem>
								<settlementLineItem number="159">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114270289</documentNumber>
									<itemText>5114270289</itemText>
								</settlementLineItem>
								<settlementLineItem number="160">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113450590</documentNumber>
									<itemText>5113450590</itemText>
								</settlementLineItem>
								<settlementLineItem number="161">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113636587</documentNumber>
									<itemText>5113636587</itemText>
								</settlementLineItem>
								<settlementLineItem number="162">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113400151</documentNumber>
									<itemText>5113400151</itemText>
								</settlementLineItem>
								<settlementLineItem number="163">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114260704</documentNumber>
									<itemText>5114260704</itemText>
								</settlementLineItem>
								<settlementLineItem number="164">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113407334</documentNumber>
									<itemText>5113407334</itemText>
								</settlementLineItem>
								<settlementLineItem number="165">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113974577</documentNumber>
									<itemText>5113974577</itemText>
								</settlementLineItem>
								<settlementLineItem number="166">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114523181</documentNumber>
									<itemText>5114523181</itemText>
								</settlementLineItem>
								<settlementLineItem number="167">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114205885</documentNumber>
									<itemText>5114205885</itemText>
								</settlementLineItem>
								<settlementLineItem number="168">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114131103</documentNumber>
									<itemText>5114131103</itemText>
								</settlementLineItem>
								<settlementLineItem number="169">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114395532</documentNumber>
									<itemText>5114395532</itemText>
								</settlementLineItem>
								<settlementLineItem number="170">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113974706</documentNumber>
									<itemText>5113974706</itemText>
								</settlementLineItem>
								<settlementLineItem number="171">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114019023</documentNumber>
									<itemText>5114019023</itemText>
								</settlementLineItem>
								<settlementLineItem number="172">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114514879</documentNumber>
									<itemText>5114514879</itemText>
								</settlementLineItem>
								<settlementLineItem number="173">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405397</documentNumber>
									<itemText>5113405397</itemText>
								</settlementLineItem>
								<settlementLineItem number="174">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113694015</documentNumber>
									<itemText>5113694015</itemText>
								</settlementLineItem>
								<settlementLineItem number="175">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114029271</documentNumber>
									<itemText>5114029271</itemText>
								</settlementLineItem>
								<settlementLineItem number="176">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114285025</documentNumber>
									<itemText>5114285025</itemText>
								</settlementLineItem>
								<settlementLineItem number="177">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113446824</documentNumber>
									<itemText>5113446824</itemText>
								</settlementLineItem>
								<settlementLineItem number="178">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113965687</documentNumber>
									<itemText>5113965687</itemText>
								</settlementLineItem>
								<settlementLineItem number="179">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113681547</documentNumber>
									<itemText>5113681547</itemText>
								</settlementLineItem>
								<settlementLineItem number="180">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114114617</documentNumber>
									<itemText>5114114617</itemText>
								</settlementLineItem>
								<settlementLineItem number="181">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113788667</documentNumber>
									<itemText>5113788667</itemText>
								</settlementLineItem>
								<settlementLineItem number="182">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113352319</documentNumber>
									<itemText>5113352319</itemText>
								</settlementLineItem>
								<settlementLineItem number="183">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113636506</documentNumber>
									<itemText>5113636506</itemText>
								</settlementLineItem>
								<settlementLineItem number="184">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113921450</documentNumber>
									<itemText>5113921450</itemText>
								</settlementLineItem>
								<settlementLineItem number="185">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114207857</documentNumber>
									<itemText>5114207857</itemText>
								</settlementLineItem>
								<settlementLineItem number="186">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113503856</documentNumber>
									<itemText>5113503856</itemText>
								</settlementLineItem>
								<settlementLineItem number="187">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114029236</documentNumber>
									<itemText>5114029236</itemText>
								</settlementLineItem>
								<settlementLineItem number="188">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113405417</documentNumber>
									<itemText>5113405417</itemText>
								</settlementLineItem>
								<settlementLineItem number="189">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113833325</documentNumber>
									<itemText>5113833325</itemText>
								</settlementLineItem>
								<settlementLineItem number="190">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113499405</documentNumber>
									<itemText>5113499405</itemText>
								</settlementLineItem>
								<settlementLineItem number="191">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113502336</documentNumber>
									<itemText>5113502336</itemText>
								</settlementLineItem>
								<settlementLineItem number="192">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114071855</documentNumber>
									<itemText>5114071855</itemText>
								</settlementLineItem>
								<settlementLineItem number="193">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113872481</documentNumber>
									<itemText>5113872481</itemText>
								</settlementLineItem>
								<settlementLineItem number="194">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114125370</documentNumber>
									<itemText>5114125370</itemText>
								</settlementLineItem>
								<settlementLineItem number="195">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113553487</documentNumber>
									<itemText>5113553487</itemText>
								</settlementLineItem>
								<settlementLineItem number="196">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114465717</documentNumber>
									<itemText>5114465717</itemText>
								</settlementLineItem>
								<settlementLineItem number="197">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113561496</documentNumber>
									<itemText>5113561496</itemText>
								</settlementLineItem>
								<settlementLineItem number="198">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113918692</documentNumber>
									<itemText>5113918692</itemText>
								</settlementLineItem>
								<settlementLineItem number="199">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113407328</documentNumber>
									<itemText>5113407328</itemText>
								</settlementLineItem>
								<settlementLineItem number="200">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114525003</documentNumber>
									<itemText>5114525003</itemText>
								</settlementLineItem>
								<settlementLineItem number="201">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113740849</documentNumber>
									<itemText>5113740849</itemText>
								</settlementLineItem>
								<settlementLineItem number="202">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114025338</documentNumber>
									<itemText>5114025338</itemText>
								</settlementLineItem>
								<settlementLineItem number="203">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113784749</documentNumber>
									<itemText>5113784749</itemText>
								</settlementLineItem>
								<settlementLineItem number="204">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113681306</documentNumber>
									<itemText>5113681306</itemText>
								</settlementLineItem>
								<settlementLineItem number="205">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113784800</documentNumber>
									<itemText>5113784800</itemText>
								</settlementLineItem>
								<settlementLineItem number="206">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114464808</documentNumber>
									<itemText>5114464808</itemText>
								</settlementLineItem>
								<settlementLineItem number="207">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113446823</documentNumber>
									<itemText>5113446823</itemText>
								</settlementLineItem>
								<settlementLineItem number="208">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114016097</documentNumber>
									<itemText>5114016097</itemText>
								</settlementLineItem>
								<settlementLineItem number="209">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113772173</documentNumber>
									<itemText>5113772173</itemText>
								</settlementLineItem>
								<settlementLineItem number="210">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114257053</documentNumber>
									<itemText>5114257053</itemText>
								</settlementLineItem>
								<settlementLineItem number="211">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113446700</documentNumber>
									<itemText>5113446700</itemText>
								</settlementLineItem>
								<settlementLineItem number="212">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114270334</documentNumber>
									<itemText>5114270334</itemText>
								</settlementLineItem>
								<settlementLineItem number="213">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114525014</documentNumber>
									<itemText>5114525014</itemText>
								</settlementLineItem>
								<settlementLineItem number="214">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113508816</documentNumber>
									<itemText>5113508816</itemText>
								</settlementLineItem>
								<settlementLineItem number="215">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113839943</documentNumber>
									<itemText>5113839943</itemText>
								</settlementLineItem>
								<settlementLineItem number="216">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5113788678</documentNumber>
									<itemText>5113788678</itemText>
								</settlementLineItem>
								<settlementLineItem number="217">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114071862</documentNumber>
									<itemText>5114071862</itemText>
								</settlementLineItem>
								<settlementLineItem number="218">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114025435</documentNumber>
									<itemText>5114025435</itemText>
								</settlementLineItem>
								<settlementLineItem number="219">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114338438</documentNumber>
									<itemText>5114338438</itemText>
								</settlementLineItem>
								<settlementLineItem number="220">
									<documentType>RE Invoice - gross</documentType>
									<documentNumber>5114023554</documentNumber>
									<itemText />
								</settlementLineItem>
								<adjustmentAndDiscountSummary>
									<adjustmentAndDiscount>
										<amount>2089.03</amount>
										<adjustmentReason>
											<messageReason>XJ Cash Disc. Merch.</messageReason>
										</adjustmentReason>
									</adjustmentAndDiscount>
									<adjustmentAndDiscount>
										<amount>5749.2</amount>
										<adjustmentReason>
											<messageReason>XI Incentive Discount</messageReason>
										</adjustmentReason>
									</adjustmentAndDiscount>
									<adjustmentAndDiscount>
										<amount>2612.22</amount>
										<adjustmentReason>
											<messageReason>XH Marketing Fund</messageReason>
										</adjustmentReason>
									</adjustmentAndDiscount>
									<adjustmentAndDiscount>
										<amount>2612.22</amount>
										<adjustmentReason>
											<messageReason>XF Advertising Allow.</messageReason>
										</adjustmentReason>
									</adjustmentAndDiscount>
									<adjustmentAndDiscount>
										<amount>1045.31</amount>
										<adjustmentReason>
											<messageReason>X1 Smart Shopper</messageReason>
										</adjustmentReason>
									</adjustmentAndDiscount>
									<adjustmentAndDiscount>
										<amount>739.43</amount>
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