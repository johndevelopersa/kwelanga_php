<?php
$orderHeaderXMl = '<?xml version="1.0" encoding="UTF-8"?>
<ORDERS05>
  <IDOC BEGIN="1">
    <EDI_DC40 SEGMENT="1">
        <TABNAM>EDI_DC40</TABNAM>
       <MANDT></MANDT>
       <DOCNUM>&&file_seq_num&&</DOCNUM>
       <DOCREL>700</DOCREL>
       <STATUS></STATUS>
       <DIRECT>2</DIRECT>
       <OUTMOD>4</OUTMOD>
       <TEST></TEST>
       <IDOCTYP>ORDERS05</IDOCTYP>
       <CIMTYP></CIMTYP>
       <MESTYP>ORDERS</MESTYP>
       <MESCOD></MESCOD>
       <MESFCT>SP</MESFCT>
       <SNDPOR></SNDPOR>
       <SNDPRT>KU</SNDPRT>
       <SNDPFC>SP</SNDPFC>
       <SNDPRN>Honeyfields</SNDPRN>
       <RCVPOR></RCVPOR>
       <RCVPRT>KU</RCVPRT>
       <RCVPFC>SP</RCVPFC>
       <RCVPRN></RCVPRN>
       <ARCKEY></ARCKEY>
       <SERIAL></SERIAL>
    </EDI_DC40>
    <E1EDK14 SEGMENT="1">
       <QUALF>012</QUALF>
       <ORGID>ZOUT</ORGID>
    </E1EDK14>
    <E1EDK14 SEGMENT="1">
        <QUALF>006</QUALF>
       <ORGID></ORGID>
    </E1EDK14>
    <E1EDK14 SEGMENT="1">
        <QUALF>007</QUALF>
        <ORGID></ORGID>
    </E1EDK14>
    <E1EDK14 SEGMENT="1">
       <QUALF>008</QUALF>
       <ORGID></ORGID>
    </E1EDK14>
    <E1EDKA1>
      <PARVW>YC</PARVW>
      <PARTN>&&cust_number&&</PARTN>
    </E1EDKA1>
    <E1EDKA1>
      <PARVW>AG</PARVW>
      <PARTN></PARTN>
      <IHREZ>&&doc_number&&</IHREZ>
    </E1EDKA1>    
    <E1EDKA1>
      <PARVW>WE</PARVW>
      <PARTN></PARTN>
    </E1EDKA1>
    <E1EDK02>
      <QUALF>001</QUALF>
      <BELNR>&&po_number&&</BELNR>
      <POSNR></POSNR>
      <DATUM></DATUM>
      <UZEIT></UZEIT>
    </E1EDK02>';
$orderDetailXMl = '    <E1EDP01 SEGMENT="1">
       <POSEX>1</POSEX>
       <E1EDP05 SEGMENT="1"> 
          <KOTXT>Product Price</KOTXT>
          <KRATE>&&extended_price&&</KRATE>
       </E1EDP05>
       <E1EDP19 SEGMENT="1">
           <QUALF>001</QUALF>
          <IDTNR>&&product_code&&</IDTNR>
       </E1EDP19>
    </E1EDP01>';     

$orderEndXMl = '  </IDOC>
</ORDERS05>';
?>