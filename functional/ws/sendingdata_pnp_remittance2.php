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
					<sh:Identifier Authority="EAN.UCC">6001651048339</sh:Identifier>
				</sh:Receiver>
				<sh:DocumentIdentification>
					<sh:Standard>EAN.UCC</sh:Standard>
					<sh:TypeVersion>2.3</sh:TypeVersion>
					<sh:InstanceIdentifier>0000000355426876</sh:InstanceIdentifier>
					<sh:Type>Settlement</sh:Type>
					<sh:MultipleType>false</sh:MultipleType>
					<sh:CreationDateAndTime>2014-08-01T13:58:27.272+02:00</sh:CreationDateAndTime>
				</sh:DocumentIdentification>
			</sh:StandardBusinessDocumentHeader>
			<eanucc:message>
				<entityIdentification>
					<uniqueCreatorIdentification>150000090810002015</uniqueCreatorIdentification>
					<contentOwner>
						<gln>6001000000001</gln>
					</contentOwner>
				</entityIdentification>
				<eanucc:transaction>
					<entityIdentification>
						<uniqueCreatorIdentification>150000090810002015</uniqueCreatorIdentification>
						<contentOwner>
							<gln>6001000000001</gln>
						</contentOwner>
					</entityIdentification>
					<command>
						<eanucc:documentCommand>
							<documentCommandHeader type="ADD">
								<entityIdentification>
									<uniqueCreatorIdentification>150000090810002015</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
								</entityIdentification>
							</documentCommandHeader>
							<documentCommandOperand>
								<pay:settlement creationDateTime="2014-08-01T13:58:27.272+02:00" documentStatus="ORIGINAL">
									<contentVersion>
										<versionIdentification />
									</contentVersion>
									<documentStructureVersion>
										<versionIdentification />
									</documentStructureVersion>
									<batchIdentification />
									<paymentEffectiveDate>2014-08-01</paymentEffectiveDate>
									<settlementCurrency>
										<currencyISOCode>ZAR</currencyISOCode>
									</settlementCurrency>
									<totalAmount>3061.73</totalAmount>
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
										<gln>6009625149990</gln>
										<additionalPartyIdentification>
											<additionalPartyIdentificationValue>1000002045</additionalPartyIdentificationValue>
											<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
										</additionalPartyIdentification>
									</remitTo>
									<payee>
										<gln>6009625149990</gln>
										<additionalPartyIdentification>
											<additionalPartyIdentificationValue>1000002045</additionalPartyIdentificationValue>
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
										<uniqueCreatorIdentification>150000090810002015</uniqueCreatorIdentification>
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
										<accountName>ZAMBEZI BEVERAGES (PTY) LTD</accountName>
										<accountNumber>
											<number>1522122508</number>
											<accountNumberType>03_CHECKING_ACCOUNT</accountNumberType>
										</accountNumber>
										<routingNumber>
											<number>152205</number>
											<routingNumberType>BANKSERV</routingNumberType>
										</routingNumber>
										<branch>EPSOM DOWNS</branch>
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
										<amountPaid>469.63</amountPaid>
										<originalAmount>552.52</originalAmount>
										<adjustmentAndDiscount>
											<amount>13.80</amount>
											<adjustmentReason>
												<messageReason>XJ 2.500 % Cash Disc. Merch.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2209862047</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>44.22</amount>
											<adjustmentReason>
												<messageReason>XI 8.000 % Incentive Discount</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2209892219</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>24.87</amount>
											<adjustmentReason>
												<messageReason>XF 4.500 % Advertising Allow.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2209913137</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<invoice creationDateTime="2013-10-08">
											<uniqueCreatorIdentification>INP00920</uniqueCreatorIdentification>
											<contentOwner>
												<gln>6001000000001</gln>
											</contentOwner>
											<invoiceType>INVOICE</invoiceType>
										</invoice>
										<settlementEntity entityType="SITE">
											<partyIdentification>
												<gln>6001007033187</gln>
											</partyIdentification>
										</settlementEntity>
									</settlementLineItem>
									<settlementLineItem number="2">
										<amountPaid>1428.12</amountPaid>
										<originalAmount>1428.12</originalAmount>
										<invoice creationDateTime="2013-10-29">
											<uniqueCreatorIdentification>INP00992</uniqueCreatorIdentification>
											<contentOwner>
												<gln>6001000000001</gln>
											</contentOwner>
											<invoiceType>INVOICE</invoiceType>
										</invoice>
										<settlementEntity entityType="SITE">
											<partyIdentification>
												<gln>6001007033187</gln>
											</partyIdentification>
										</settlementEntity>
									</settlementLineItem>
									<settlementLineItem number="3">
										<amountPaid>317.82</amountPaid>
										<originalAmount>373.92</originalAmount>
										<adjustmentAndDiscount>
											<amount>9.33</amount>
											<adjustmentReason>
												<messageReason>XJ 2.500 % Cash Disc. Merch.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210002754</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>29.94</amount>
											<adjustmentReason>
												<messageReason>XI 8.000 % Incentive Discount</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210017548</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>16.83</amount>
											<adjustmentReason>
												<messageReason>XF 4.500 % Advertising Allow.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210028577</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<invoice creationDateTime="2013-10-11">
											<uniqueCreatorIdentification>55867</uniqueCreatorIdentification>
											<contentOwner>
												<gln>6001000000001</gln>
											</contentOwner>
											<invoiceType>INVOICE</invoiceType>
										</invoice>
										<settlementEntity entityType="SITE">
											<partyIdentification>
												<gln>6001007034016</gln>
											</partyIdentification>
										</settlementEntity>
									</settlementLineItem>
									<settlementLineItem number="4">
										<amountPaid>211.88</amountPaid>
										<originalAmount>249.28</originalAmount>
										<adjustmentAndDiscount>
											<amount>6.22</amount>
											<adjustmentReason>
												<messageReason>XJ 2.500 % Cash Disc. Merch.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210427727</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>19.96</amount>
											<adjustmentReason>
												<messageReason>XI 8.000 % Incentive Discount</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210455535</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>11.22</amount>
											<adjustmentReason>
												<messageReason>XF 4.500 % Advertising Allow.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210474906</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<invoice creationDateTime="2013-10-21">
											<uniqueCreatorIdentification>56187</uniqueCreatorIdentification>
											<contentOwner>
												<gln>6001000000001</gln>
											</contentOwner>
											<invoiceType>INVOICE</invoiceType>
										</invoice>
										<settlementEntity entityType="SITE">
											<partyIdentification>
												<gln>6001007034016</gln>
											</partyIdentification>
										</settlementEntity>
									</settlementLineItem>
									<settlementLineItem number="5">
										<amountPaid>634.28</amountPaid>
										<originalAmount>746.24</originalAmount>
										<adjustmentAndDiscount>
											<amount>18.64</amount>
											<adjustmentReason>
												<messageReason>XJ 2.500 % Cash Disc. Merch.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210628569</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>59.74</amount>
											<adjustmentReason>
												<messageReason>XI 8.000 % Incentive Discount</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210656359</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<adjustmentAndDiscount>
											<amount>33.58</amount>
											<adjustmentReason>
												<messageReason>XF 4.500 % Advertising Allow.</messageReason>
												<sourceCode>PNP</sourceCode>
											</adjustmentReason>
											<alternateAdjustmentReference>
												<alternateAdjustmentReferenceType>DOC_NO</alternateAdjustmentReferenceType>
												<identification>2210675551</identification>
											</alternateAdjustmentReference>
										</adjustmentAndDiscount>
										<invoice creationDateTime="2013-10-24">
											<uniqueCreatorIdentification>56524</uniqueCreatorIdentification>
											<contentOwner>
												<gln>6001000000001</gln>
											</contentOwner>
											<invoiceType>INVOICE</invoiceType>
										</invoice>
										<settlementEntity entityType="SITE">
											<partyIdentification>
												<gln>6001007034016</gln>
											</partyIdentification>
										</settlementEntity>
									</settlementLineItem>
									<extension>
										<settlementLineItem number="1">
											<documentType>RE Invoice - gross</documentType>
											<documentNumber>5109275855</documentNumber>
											<itemText>5109275855</itemText>
										</settlementLineItem>
										<settlementLineItem number="2">
											<documentType>RE Invoice - gross</documentType>
											<documentNumber>5110140288</documentNumber>
											<itemText>5110140288</itemText>
										</settlementLineItem>
										<settlementLineItem number="3">
											<documentType>RE Invoice - gross</documentType>
											<documentNumber>5109379090</documentNumber>
											<itemText>5109379090</itemText>
										</settlementLineItem>
										<settlementLineItem number="4">
											<documentType>RE Invoice - gross</documentType>
											<documentNumber>5109756110</documentNumber>
											<itemText>5109756110</itemText>
										</settlementLineItem>
										<settlementLineItem number="5">
											<documentType>RE Invoice - gross</documentType>
											<documentNumber>5109932436</documentNumber>
											<itemText />
										</settlementLineItem>
										<adjustmentAndDiscountSummary>
											<adjustmentAndDiscount>
												<amount>47.99</amount>
												<adjustmentReason>
													<messageReason>XJ Cash Disc. Merch.</messageReason>
												</adjustmentReason>
											</adjustmentAndDiscount>
											<adjustmentAndDiscount>
												<amount>153.86</amount>
												<adjustmentReason>
													<messageReason>XI Incentive Discount</messageReason>
												</adjustmentReason>
											</adjustmentAndDiscount>
											<adjustmentAndDiscount>
												<amount>86.5</amount>
												<adjustmentReason>
													<messageReason>XF Advertising Allow.</messageReason>
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