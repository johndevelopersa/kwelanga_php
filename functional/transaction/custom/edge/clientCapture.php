<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."TO/PostingOrderTO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");
include_once($ROOT.$PHPFOLDER."elements/Messages.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
CommonUtils::getSystemConventions();


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$userId = $_SESSION['user_id'];
$systemId = $_SESSION['system_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$productMinorFilter = (isset($_GET['prod_minor_category'])) ? ($_GET['prod_minor_category']) : array();
$postDEPOT = '';


?>
<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/default.css' rel='stylesheet' type='text/css'>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<div align="center">


  <div style="width:600px;padding:10px 0px">

    <div align="left">
      <h2 style="color:#047;margin-bottom:0px;">Client Order</h2>
      Place an order for the selected items.
    </div>

    <form name="orderFrm" id="orderFrm">
    <table border="0" style="margin:30px 0px 20px 0px;" width="600">
      <tbody>
        <tr>
          <td width="180">From Depot : </td>
          <td width="440">
            <?php
              BasicSelectElement::getUserDepotsForPrincipalDD("DEPOT",$postDEPOT,"N","N","depotChange()",null,null,$dbConn,$userId,$principalId);
            ?>
          </td>
        </tr>
        <tr class="odd">
          <td valign="top">Delivery Point : </td><td><textarea cols="30" rows="3" name="DELPOINT"></textarea></td>
        </tr>
        <tr class="even">
          <td>Postal Code : </td><td><input type="text" size="5" MAXLENGTH="5" name="DELPCODE"></td>
        </tr>
        <tr class="odd">
          <td>Delivery Instruction : </td><td><input type="text" size="50" MAXLENGTH="100" name="DELINSTR"></td>
        </tr>
        <tr class="odd">
          <td>Delivery Contact : </td><td><input type="text" size="50" MAXLENGTH="50" name="DELCONTACT"></td>
        </tr>
        <tr class="even">
          <td>Delivery Contact No. : </td><td><input type="text" size="20" MAXLENGTH="20" name="DELCONTACTNO"></td>
        </tr>

        <tr class="odd">
          <td>Service Type : </td><td>
              <select name="SERVICE">
                <option value="ECONOMY">Economy
                <option value="COLLECTION">Collection
                <option value="OVERNIGHT">Overnight
                <option value="SAME DAY">Same Day
                <option value="INSTALLATION">Installation
              </select></td>
        </tr>
      </tbody>
    </table>


    <div align="left" style="margin:5px 0px;">
      <input type="button" class="submit" onClick="openProductFinder()" value="Add Product(s)" style="margin-left:0px" />
    </div>

    <table width="600">
      <thead><tr>
        <th width="400" colspan="2">Product</th>
        <th width="50">Order Qty</th>
        <th width="150">Available Stock </th>
      </tr></thead>
      <tbody id="productRows">

      </tbody>
    </table>

    <BR>

    <input class="submit" type="button" onclick="submitContentForm();" value="Submit">
    <input class="submit" type="button" onclick="resetForm();" value="Cancel">

    </form>

    <div id="productFinder" style="display:none;position:absolute;top:30%;bottom:70%;left:50%;right:50%;z-index:100;" >
      <div class="rdCrn8" style="display:block;position:absolute;width:920px;margin-left:-460px;margin-top:-120px;background:#efefef;border:4px solid #666;padding:15px 0px;" align="center">

        <div style="width:900px;display:block;text-align:left;">

            <div style="background:#ccc;padding: 5px 5px;" class="rdCrn3" >
              <div style="padding-left:10px;margin-bottom:5px;"><span >Search: </span> <input type="text" class="proSearch" value=""/></div>
              <span id="prodFilter">
                <?php
                basicSelectElement::getProductMinorCategoryFilter('prod_minor_category',$productMinorFilter,"N","N",$onChange="changeFilter()",$onClick=null,$onMouseOver=null,$dbConn,$principalId, $systemId, 'H');
                $productDAO = new ProductDAO($dbConn);
                $mfPP = $productDAO->getUserPrincipalProductsArray($principalId,$userId);
                ?>
              </span>
            </div>
            <br>
            <div style="display:block;height:200px;overflow:auto;background:#fff;border:1px solid #ccc;" id="productList"></div>
            <BR>
            </div>
        <input type="button" class="submit" value="Add Selected Product(s)" onClick="submitAddProducts()" >
        <input type="button" class="submit" value="Cancel" onClick="closeProductFinder();" >
      </div>
    </div>
    <div id="productFinderBack" style="display:none;position:absolute;top:0px;bottom:0px;left:0px;right:0px;z-index:99;background:aliceBlue;opacity:0.4" ></div>

</div>
</div>

<script type="text/javascript">

  var productJson = <?php echo json_encode($mfPP); ?>;

  $('.proSearch').each(function() {

     var elem = $(this);
     elem.data('oldVal', elem.val()); // Save current value of element

     elem.bind("propertychange keyup input paste", function(event){ // Look for changes in the value

        if (elem.data('oldVal') != elem.val()) {  // If value has changed...

         elem.data('oldVal', elem.val()); // Updated stored value
         displayProducts(true); // Do action

       }
     });
   });


  <?php

    $imgP = array();
    $templatePhoto = 'images/product_template.gif';

    foreach($mfPP as $p){
      $photoPath = 'uploads/products/' . $principalId .'_'. $p['uid'] .'.jpg';
      if(is_file($ROOT.$PHPFOLDER . $photoPath)){
        $imgP[$p['uid']] = HOST_SURESERVER_AS_USER.$PHPFOLDER.$photoPath;
      } else {
        $imgP[$p['uid']] = HOST_SURESERVER_AS_USER.$PHPFOLDER.$templatePhoto;
      }
    }

    echo 'var productImg = ' . json_encode($imgP) . ';'
  ?>


  function openProductFinder(){

    var depot = $('select[name="DEPOT"] option:selected').val();
    if(depot == ""){

      alert('Please select a depot first!');

    } else {

      //reset filter options
      var i=0;
      $('#prodFilter option:first-child').each( function(){
        $(this).attr("selected", "selected");
      });
      $('.proSearch').attr('value','');
      displayProducts(false); //reset product list

      //display
      $('#productFinder').show();
      $('#productFinderBack').show();
    }
  }


  function depotChange(){
    $("td[id^=stockuidp_]").each(function() {

      wid = ($(this).attr('id'));

      pid = wid.replace('stockuidp_','');
      getStock(pid,wid);

    });
  }

  function closeProductFinder(){
    $('#productFinder').hide();
    $('#productFinderBack').hide();
  }

  function changeFilter(){
    displayProducts(true);
  }

  function submitAddProducts(){
    $(".selectProduct").each( function(){
      if($(this).attr("checked")!==undefined){
        if(!addProduct($(this).attr('jindex'), $(this).val())){
          alert('Selected Item failure...');
          return;
        }
      }
    })
    closeProductFinder();
  }

  function addProduct(jindex, uid){

    if(productJson[jindex]['uid']!=uid){
      return false;
    } else {
      var out = '<tr>';
      out += '<td width="40"><a href="javascript:;" onClick="$(this).parent().parent().remove();"><img src="<?php echo $DHTMLROOT.$PHPFOLDER  ?>images/delete-icon-small.png" border="0" alt="Delete Row" onclick="deleteRow(this.parentNode.parentNode.rowIndex-1);"></a></td>';
      out += '<td class="td">'+
                '<input type="hidden" name="PRODUCTID[]" id="prodUid_'+productJson[jindex]['uid']+'" value="'+productJson[jindex]['uid']+'">'+
                productJson[jindex]['product_code'] + ' - - ' + productJson[jindex]['product_description'] +
             '</td>';
      out += '<td><input type="text" size="6" maxlength="6" value="" name="QTY[]"></td>';
      out += '<td id="stockuidp_'+productJson[jindex]['uid']+'"><script>getStock('+productJson[jindex]['uid']+',"stockuidp_'+productJson[jindex]['uid']+'");<\/script></td>';
      out += '</tr>';

      if($('#prodUid_'+productJson[jindex]['uid']).attr('name')==undefined){
        $('#productRows').append(out);
      }
      return true;
    }
  }

  function selectAll(flag, className){
     $("."+className).each( function(){$(this).attr("checked",((flag == 1)?true:false));})
  }


  function resetForm(){
    if(confirm('Are you sure?')){
      document.getElementById("orderFrm").reset();
      $('#productRows').html('');
    }
  }


var alreadySubmitted=false;

  function submitContentForm(){

    if (alreadySubmitted) {
            alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
            return;
    }

    alreadySubmitted=true;


    var params = $('#orderFrm').serialize();

    //params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
    AjaxRefreshWithResult(params,
                          '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/custom/edge/clientCaptureSubmit.php',
                          'alreadySubmitted=false; if(msgClass.type=="S") successfullyProcessed(msgClass.identifier); ',
                          'Please wait while request is processed...');
  }


  function successfullyProcessed(){
    document.getElementById("orderFrm").reset();
    $('#productRows').html('');
  }

  function displayProducts(filterList){

    var searchStr = $.trim($('.proSearch').attr('value'));
    searchStr = searchStr.toLowerCase();

    var out = '<table width="100%" class="proList">'+
              '<thead><th width="40"><input type="checkbox"  onChange="selectAll( ( (this.checked==true)?1:0) , \'selectProduct\');"></th>'+
                      '<th width="220">Product Code</th><th width="450">Description</th><th width="150">Available Stock</th></thead>';

    for(var k = 0; k < productJson.length; k++){

      var skip = false;
      var lArr = productJson[k]['minor_category_lables_list'].split(';');
      var vArr = productJson[k]['minor_category_list'].split(';');

      if(filterList){

        var i = 0;
        $('#prodFilter option:selected').each( function(){

          var compare = '';
          if(vArr[i]!=undefined){
            compare = vArr[i];
          }
          var selected = '';
          if($(this).val()!=''){
            selected = $(this).text()
             if(selected!=compare){
               skip = true;
             }
          }
          i++;
        });
      }

      if(searchStr!='' && !skip){

        var proCode = productJson[k]['product_code'].toLowerCase();
        var proDesc = productJson[k]['product_description'].toLowerCase();
        if(proCode.search(searchStr) == -1 &&
           proDesc.search(searchStr) == -1){
          skip = true;
        }
      }

      if(!skip){

        var tooltip = '';
        for(var l = 0; l < lArr.length; l++){
          if(vArr[l]!=undefined)
            tooltip += lArr[l] + ' : ' + vArr[l]  + "<br>";
        }

        out += '<tr onmouseover="parent.displayTip(this,270,105,$(\'#ptooltip_'+productJson[k]['uid']+'\').html())" onmouseout="parent.hideTip();" class="hlr">';
          out +=  '<td>';
          out +=  '<span id="ptooltip_'+productJson[k]['uid']+'" style="display:none"><img src="'+productImg[productJson[k]['uid']]+'" border="0" alt="" width="150" height="150" style="float:left;margin-right:10px;">' + tooltip + '</span>';
          out += '<input type="checkbox" name="itemProduct[]" jindex="'+k+'" class="selectProduct" value="'+productJson[k]['uid']+'"></td>';
          out += '<td>'+productJson[k]['product_code']+'</td>';
          out += '<td>'+productJson[k]['product_description']+'</td>';
          out += '<td id="stockuidl_'+productJson[k]['uid']+'"><script>getStock('+productJson[k]['uid']+',"stockuidl_'+productJson[k]['uid']+'");<\/script></td>';
          out += '</tr>';
        }
      }

      out +=  '</table>';

      $('#productList').html(out);

      parent.$('#TipBox').css('opacity','0.8');
      $("table.proList tr.hlr").hover(
        function () { $(this).children("td").css("background-color","#FCFFB4");},
        function () {  $(this).children("td").css("background-color","");}
       );

  }


function getStock(PUId,renderID) {

        var depot=$('select[name="DEPOT"] option:selected').val();
        $('#'+renderID).html('loading...');

	params='PRODUCTID='+PUId+'&DEPOTID='+depot;

	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getStock.php",
	  global: false,
	  type: 'POST',
          data: params,
          dataType: 'html',
	  cache: false,
	  success: function(msg){
	  	try {
	  		//var innerIndex=index; // not really necessary beause this var is local so ajax will still be able to access same value
	  		eval(msg);
                        if (msgClass.type=="S") {
                          $('#'+renderID).html(msgClass.identifier);
                        } else {
                          $('#'+renderID).html(msgClass.description); ;
                        }
	  	} catch (e) { alert('an unexpected error occurred:'+e.description+' --- '+msg); }
	  	parent.hideMsgBoxSystemFeedback('Loading Stock ...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
	  	  var innerIndex=index;
	  	  stock[innerIndex].value="Failed to retrieve stock";
		  parent.hideMsgBoxSystemFeedback('Loading Stock ...');
	  }
  });
}

</script>