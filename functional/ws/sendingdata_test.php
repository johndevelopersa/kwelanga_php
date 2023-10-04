<?php

$sendXML = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
		<sh:StandardBusinessDocument xmlns:sh="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader" xmlns:order="urn:ean.ucc:order:2" xmlns:eanucc="urn:ean.ucc:2" xmlns:ns0="http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<sh:StandardBusinessDocumentHeader>
				<sh:HeaderVersion>2.4.0</sh:HeaderVersion>
				<sh:Sender>
					<sh:Identifier Authority="EAN.UCC">6001000000001</sh:Identifier>
				</sh:Sender>
				<sh:Receiver>
					<sh:Identifier Authority="EAN.UCC">6001007802929</sh:Identifier>
				</sh:Receiver>
				<sh:DocumentIdentification>
					<sh:Standard>EAN.UCC</sh:Standard>
					<sh:TypeVersion>2.4</sh:TypeVersion>
					<sh:InstanceIdentifier>355353563</sh:InstanceIdentifier>
					<sh:Type>STANDARD MANUAL</sh:Type>
					<sh:MultipleType>false</sh:MultipleType>
					<sh:CreationDateAndTime>2014-05-30T12:23:45</sh:CreationDateAndTime>
				</sh:DocumentIdentification>
			</sh:StandardBusinessDocumentHeader>
			<eanucc:message>
				<entityIdentification>
					<uniqueCreatorIdentification>0469871945</uniqueCreatorIdentification>
					<contentOwner>
						<gln>6001000000001</gln>
					</contentOwner>
				</entityIdentification>
				<eanucc:transaction>
					<entityIdentification>
						<uniqueCreatorIdentification>0469871945</uniqueCreatorIdentification>
						<contentOwner>
							<gln>6001000000001</gln>
						</contentOwner>
					</entityIdentification>
					<command>
						<eanucc:documentCommand>
							<documentCommandHeader type="ADD">
								<entityIdentification>
									<uniqueCreatorIdentification>0469871945</uniqueCreatorIdentification>
									<contentOwner>
										<gln>6001000000001</gln>
									</contentOwner>
								</entityIdentification>
							</documentCommandHeader>
							<documentCommandOperand>
								<order:order creationDateTime="2014-05-30T00:00:00" documentStatus="ORIGINAL">
									<orderIdentification>
										<uniqueCreatorIdentification>0469871945</uniqueCreatorIdentification>
										<contentOwner>
											<gln>6001000000001</gln>
											<additionalPartyIdentification>
												<additionalPartyIdentificationValue />
												<additionalPartyIdentificationType>STANDARD MANUAL</additionalPartyIdentificationType>
											</additionalPartyIdentification>
										</contentOwner>
									</orderIdentification>
									<orderPartyInformation>
										<seller>
											<gln>6009646610004</gln>
											<additionalPartyIdentification>
												<additionalPartyIdentificationValue>1000001762</additionalPartyIdentificationValue>
												<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
											</additionalPartyIdentification>
											<additionalPartyIdentification>
												<additionalPartyIdentificationValue>Rialto Food Services (Pty) Ltd</additionalPartyIdentificationValue>
												<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
											</additionalPartyIdentification>
											<additionalPartyIdentification>
												<additionalPartyIdentificationValue>4240249849</additionalPartyIdentificationValue>
												<additionalPartyIdentificationType>SELLER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
											</additionalPartyIdentification>
										</seller>
										<buyer>
											<gln>6001007031006</gln>
											<additionalPartyIdentification>
												<additionalPartyIdentificationValue>Western Cape Corporate</additionalPartyIdentificationValue>
												<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
											</additionalPartyIdentification>
										</buyer>
									</orderPartyInformation>
									<orderLogisticalInformation>
										<shipToLogistics>
											<shipTo>
												<gln>6001007031046</gln>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>WC04</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>Kenilworth</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>BUYER_ASSIGNED_IDENTIFIER_FOR_A_PARTY</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>WC04</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_ID</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>KENILWORTH</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_NAME</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>KENILWORTH CENTRE DONCASTER STREET</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_STREET</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>KENILWORTH</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_CITY</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>7700</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_POSTALCODE</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>021 683 8055/6</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_TELEPHONE</additionalPartyIdentificationType>
												</additionalPartyIdentification>
												<additionalPartyIdentification>
													<additionalPartyIdentificationValue>021 638 1277</additionalPartyIdentificationValue>
													<additionalPartyIdentificationType>DELIVERY_FAX</additionalPartyIdentificationType>
												</additionalPartyIdentification>
											</shipTo>
										</shipToLogistics>
										<orderLogisticalDateGroup>
											<requestedDeliveryDateAtUltimateConsignee>
												<date>2014-06-03</date>
											</requestedDeliveryDateAtUltimateConsignee>
										</orderLogisticalDateGroup>
									</orderLogisticalInformation>
									<orderLineItem number="10">
										<requestedQuantity>
											<value>8.000</value>
										</requestedQuantity>
										<netPrice>
											<amount>
												<currencyCode>
													<currencyISOCode>ZAR</currencyISOCode>
												</currencyCode>
												<monetaryAmount>268.63</monetaryAmount>
											</amount>
										</netPrice>
										<netAmount>
											<amount>
												<currencyCode>
													<currencyISOCode>ZAR</currencyISOCode>
												</currencyCode>
												<monetaryAmount>2449.92</monetaryAmount>
											</amount>
										</netAmount>
										<tradeItemIdentification>
											<gtin>6009646611490</gtin>
											<additionalTradeItemIdentification>
												<additionalTradeItemIdentificationValue>JC073</additionalTradeItemIdentificationValue>
												<additionalTradeItemIdentificationType>SUPPLIER_ASSIGNED</additionalTradeItemIdentificationType>
											</additionalTradeItemIdentification>
											<additionalTradeItemIdentification>
												<additionalTradeItemIdentificationValue>SINCERE LITE COCONUT MILK 400ML</additionalTradeItemIdentificationValue>
												<additionalTradeItemIdentificationType>BUYER_ASSIGNED</additionalTradeItemIdentificationType>
											</additionalTradeItemIdentification>
											<additionalTradeItemIdentification>
												<additionalTradeItemIdentificationValue>253130</additionalTradeItemIdentificationValue>
												<additionalTradeItemIdentificationType>BUYER_ASSIGNED</additionalTradeItemIdentificationType>
											</additionalTradeItemIdentification>
											<additionalTradeItemIdentification>
												<additionalTradeItemIdentificationValue>24</additionalTradeItemIdentificationValue>
												<additionalTradeItemIdentificationType>SUPPLIER_ASSIGNED</additionalTradeItemIdentificationType>
											</additionalTradeItemIdentification>
										</tradeItemIdentification>
									</orderLineItem>
									<orderHeaderIndicator>
										<isApplicationReceiptAcknowledgementRequired>true</isApplicationReceiptAcknowledgementRequired>
										<isOrderFreeOfExciseTaxDuty>false</isOrderFreeOfExciseTaxDuty>
									</orderHeaderIndicator>
								</order:order>
							</documentCommandOperand>
						</eanucc:documentCommand>
					</command>
				</eanucc:transaction>
			</eanucc:message>
		</sh:StandardBusinessDocument>
	</soap:Body>
</soap:Envelope>';


?>