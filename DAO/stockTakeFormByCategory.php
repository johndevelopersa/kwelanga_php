<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userCategory = $_SESSION['user_category'];
$principalId = $_SESSION['principal_id'];
$principalCode = $_SESSION['principal_code'];
$principalName = $_SESSION['principal_name'];
$systemId = $_SESSION['system_id'];
$systemName = $_SESSION['system_name'];
$postStockAdjIncrease = "postStockAdjIncrease";
$postStockAdjDecrease = "postStockAdjDecrease";

//only available to depot category users
if(!CommonUtils::isDepotUser()){  //available to only depot users - see above check!
  echo "<h3>Restricted Access</h3>Only Depot users are allowed to do stock take!";
  return;
}

//these session vars are only avail for depot users - check above.
$depotName = $_SESSION['depot_name'];
$depotId = $_SESSION['depot_id'];


$dbConn = new dbConnect();
$dbConn->dbConnection();


/*-----------------------*
 *      PERMISSIONS
 *-----------------------*/

if(!CommonUtils::isAdminUser()){  //super level.

  //role
  $adminDAO = new AdministrationDAO($dbConn);
  $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_STOCK_TAKE);
  if (!$hasRole) {
    echo "You do not have permissions to preform Stock Take";
    return;
  }

}


//already in stock mode.
$stockDAO = new StockDAO($dbConn);
$ProductDAO = new ProductDAO($dbConn);
$stockMode = $stockDAO->checkStockMode($principalId, $depotId);
$productCategories = json_encode($ProductDAO->getProductCategoryByPrincipleId($principalId));

?>
<HTML>
<HEAD>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/popup.js"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/po"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

<LINK href="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>css/1_default.css" rel='stylesheet' type='text/css'>

<style type="text/css">

  @import url("../../css/popup.css");

  :root {
      --primary: #180376;
      --secondary: #EA5D45;
      --muted: #a5a5a5;
      --muted-light: #E7E7E7;
      --muted-dark: #979797;
      --success: #75A602;
      --info: #3374B5;
      --warning: #E3E262;
      --danger: #e71d1d;
      --white: #ffffff;
      --dark: #000000;
      --lightdark: #898989;
      --light: #eeeeee;
  }

  .d-none { display: none }

  /*? ======== Flex Layout ======== ?*/
  .d-flex {display: flex;}
  .d-inline-flex {display: inline-flex;}
  .d-inline {display: inline-block;}
  .d-block {display: block;}
  .d-none {display: none;}

  .flex-row {flex-direction: row !important;}
  .flex-col {flex-direction: column !important;}

  .flex-wrap {flex-wrap: wrap;}
  .flex-wrap-reverse {flex-wrap: wrap-reverse;}

  .flex-center {justify-content: center;}
  .flex-between {justify-content: space-between;}
  .flex-even {justify-content: space-around;}
  .flex-align {align-items: center;}
  .flex-align-left {align-items: flex-start;}
  .flex-align-right {align-items: flex-end;}
  .flex-end {justify-content: flex-end;}
  .flex-start {justify-content: flex-start;}
  .flex-grow {flex-grow: 1}

  .wrap {width:280px;}
  .start, .bigbutton{
    display:block;
    margin-top:5px;
    padding:14px 0px;
    border:2px solid #DF0101;
    background:#FA5858;
    color:#fff;
    text-decoration:none;
    font-size:22px;
    font-weight:bold;
  }
  .bigbutton {
    border:2px solid lightskyblue;
    background:aliceBlue;
    color:#047;
  }
  .start.enable{background:lightskyblue;border-color:#047}
  .start:hover{background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .bigbutton:hover{color:#fff;background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .large-input{line-height:20px;height:20px;font-size:12px;padding:0px 2px;}
  #RowHighlight, #RowHighlight td{background:#FCFFB4;}
  .hasVariance, .hasVariance td{color:#B40404;}
  .hasVariance td a {color:#B40404;text-decoration:underline;}
  .hasVariance td a:hover {text-decoration:none;}


  #category-select-wrapper {
    width: 100%;
    margin-top: 20px;

    display: flex;
    justify-content: center;
    align-items: center;
  }

  #product-category-select {
    width: 32%;
    padding: 10px;
    color: var(--white);
    border-radius: 10px;
    position: relative;
    cursor: pointer;
    box-shadow: 0 0 8px -3px var(--primary);
    border: solid 2px transparent;
    background-color: var(--primary);
    transition: background-color 200ms ease-in-out;

    position: relative;
    display: flex;
    flex-direction: column;
  }

  #product-category-select:hover {
    color: var(--primary);
    border: solid 2px var(--primary);
    background-color: transparent;
  }

  #product-category-select .select-value {
    width: 100%;
    border-radius: 5px;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  #product-category-select .select-value .header {
    width: 100%;
    display: none;
  }

  #product-category-select .select-value .header.showing {
    display: block;
  }

  #product-category-select .select-value .selected-items-wrapper {
    width: 100%;
    max-width: 100%;
    padding: 10px 0;
    overflow-x: scroll;
    display: none;
    align-items: center;
    /* flex-wrap: wrap; */
  }

  #product-category-select .select-value .selected-items-wrapper::-webkit-scrollbar-track {
    border-radius: 10px;
    background-color: var(--white);
  }

  #product-category-select .select-value .selected-items-wrapper::-webkit-scrollbar {
    height: 5px;
    border-radius: 10px;
    background-color: var(--white);
  }

  #product-category-select .select-value .selected-items-wrapper::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background-color: var(--muted-light);
  }

  #product-category-select .select-value .selected-items-wrapper.showing {
    display: flex;
  }

  #product-category-select .select-value .selected-items-wrapper .selected-item {
    color: #000;
    min-width: 80px;
    max-width: 80px;
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 5px;
    background-color: var(--muted-light);
    margin-right: 10px;
    overflow-x: hidden;
    text-overflow: ellipsis;
  }

  #product-category-select .select-items-wrapper {
    width: 100%;
    max-height: 300px;
    overflow-y: scroll;
    overflow-x: hidden;
    margin-top: 5px;
    border-radius: 10px;
    background-color: var(--white);
    box-shadow: 0 0 6px -3px var(--dark);
    position: absolute;
    top: 100%;
    right: 0;
    left: 0;

    display: none;
    flex-direction: column;
  }

  #product-category-select .select-items-wrapper.showing {
    display: flex;
    position: absolute;
    top: 104%;
    border-radius: 5px
  }

  #product-category-select .select-items-wrapper .category-item {
    width: 100%;
    padding: 10px;
    cursor: pointer;
    color: var(--primary);

    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  #product-category-select .select-items-wrapper .category-item .value {
    width: 80%;
    text-align: left;
  }

  #product-category-select .select-items-wrapper .category-item .icon {
    width: 20%;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  #product-category-select .select-items-wrapper .category-item .icon img {
    width: 30%;
    display: none;
  }

  #product-category-select .select-items-wrapper .category-item.active img {
    display: block;
  }

  #product-category-select .select-items-wrapper .category-item:hover {
    background-color: var(--light);
  }

  #product-category-select .select-items-wrapper .category-item.active:hover {
    
  }
</style>

</HEAD>

<BODY id='body'>

<div align="center">

  <?php
  	GUICommonUtils::getSteps(array("Start",
                                       "Print Stock Items",
                                       "Stock Count<br>&amp; Variances",
                                       "Rollover")
                                 );
  ?>

  <div id='step1'>

    <?php if($stockMode){ ?>

    <a href="javascript:;" onClick="submitCategories()" class="wrap bigbutton rdCrn5">Continue</a>
    <div id="category-select-wrapper"><!-- Will be populated by JS --></div>
    <br>
    <strong style="color:#888;"><?php echo $principalName . "<br>at depot " . $depotName ?> is in stock take mode!</strong>

    <?php } else { ?>

    <br><br><br><br>
    Welcome to stock take
    <a href="javascript:;" onClick="startStockTake()" class="wrap start rdCrn5">Start Stock Take</a>

    <br><br>
    <div class="wrap" >
      <table class="tableReset"><tr>
      <td valign="top" style="padding:0px;width:30px"><input type="checkbox" id="stocktaketc"></td>
      <td style="padding:0px;color:#555" align="left">I understand that proceeding will freeze any transactions for <?php echo "<strong>" . $principalName . "</strong> at depot <strong>" . $depotName ?></strong></td>
      </tr></table>
    </div>

    <?php } ?>
  </div>


  <div id='step2'>

    <div id="proceed2" style="display:none;" class="wrap" >
      <br><br><br><br>
      click to print the product listing sheet
      <a href="javascript:;" class=" bigbutton rdCrn5" onClick="displayProductPrint('DISPLAYIMAGE')" >
      <img src="<?php echo $ROOT.$PHPFOLDER ?>/images/print-icon.png" width="32" height="32" border="0" alt="Print Stock Take Sheet" style="margin-left:20px;float:left">
       Print Product List
      <div style="clear:both"></div>
      </a>
      <br><div align="left"><input type="checkbox" id="DISPLAYIMAGE"> Include Product photos</div>


      <br><br><input type="submit" class="submit" onclick="displayProductList();enableStep(3);toggleSteps(3,'<?php echo $ROOT ?>');" value="Next Step">

   </div>
  </div>


    <div id='step3'>

      <div id="proceed3" style="display:none;">
        <Br>
        <div class="wrap" >
        Capture the counted stock amounts below:
        </div>
        <Br>
        <form id="stockCountForm"><!-- display product list here... /--></form>
        <Br>
        <a name="submit"></a>
        <input type="submit" class="submit" onclick="submitStockCount(null, false);" value="Submit Count">
        <input type="submit" class="submit" onclick="utility.resetCacheAndCookies();location.reload()" value="Reset Count">

        <?php
          // make sure the logged in user has permission to Increase/Decrease stock
          $testing = true;
          $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_AUTO_STOCK_ADJ);
          if ($hasRole || $testing) {
            echo "<input id='increaseStockButton' type='submit' class='submit d-none' onclick='Stock.sendOTP($userId, $principalId, \"$username\", \"$postStockAdjIncrease\")' value='Increase Stock'>";
            echo "<input id='decreaseStockButton' type='submit' class='submit d-none' onclick='Stock.sendOTP($userId, $principalId, \"$username\", \"$postStockAdjDecrease\")' value='Decrease Stock'>";
          }
        ?>
      </div>

    </div>


  <div id='step5'>
    <div id="proceed5" style="display:none;" class="wrap" >

      <br><br><br>
      <form id='theForm' style="display:none;" method='post' action='<?php echo $ROOT.$PHPFOLDER ?>functional/reports/downloadBase.php' target='StockMovementReport'>
        <input type='hidden' name='p1' value='<?php echo $depotId ?>'>
        <input type='hidden' name='p4' value='<?php echo $principalId ?>'>
        <input type='hidden' name='REPORTID' value='57'>
      </form>
      You need to download the stock movement report before you can rollover completely!
      <br>
      <a href="javascript:;" onClick="downloadMovement()" class="bigbutton rdCrn5">Download</a>

      <div id="rolloversubmit" style="display:none;">
      <br><br><br>
      To rollover your stock data, click the button below!
      <br>
      <a href="javascript:;" onClick="submitRollover()" class="wrap start rdCrn5">Stock Rollover</a>
      </div>
    </div>
  </div>


  <?php if($stockMode){

    echo '<div style="margin-top:30px;border-top:1px solid lightSkyblue;width:700px;padding-top:10px;" align="right">';
        echo '<a href="javascript:;" onclick="stopStockTake();" style="color:red;">cancel stock take</a>';
    echo '</div><br><br>';

  }?>

</div>


<script type="text/javascript">


  $("div[id*='step']").css({display:'none'});
  $("#step1").css({display:'block'});

  // update-ables
  var alreadySubmitted = false;

  // constants
  const useCacheForStockTake = true;
  const cache_key = "CACHE_PARAM";
  const cache_key_selected_categories = "SELECTED_CATEGORIES";
  let loaded_categories = <?php echo $productCategories?>;
  let selected_categories = [];

  // list of util methods
  const utility = {
    /**
     * Will check if an object is inside an array
     * @param {Object<Any>} obj
     * @param { Array } array
     * @param { String } key
     */
    isObjectInArray: (obj, array, key) => {
      const found = array.filter(category => category[key] == obj[key]);
      if (found.length !== 0) return true;
      return false;
    },

    /**
     * Gets the index of an object within an array
     * @param {Object<Any>} object
     * @param { Array } array
     * @param { String } key
     */
    indexOfObject: (object, array, key, value) => array.map(object => object[key]).indexOf(value),

    /**
     * Will return a random number within a min and max value
     * @returns Number
     */
    getRandomNumber: (min, max) => {
      min = Math.ceil(min);
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min + 1)) + min;
    },

    /**
     * will convert the cache string param to an object
     * @param { String } cache_param
     * @returns Object
     */
    convertCacheParamToObject: (cache_param) => {
      const object = {};
      const list = cache_param.replace(/%5D/g, ']').replace(/%5B/g, '[').split("&");
      list.forEach(string_param => object[string_param.split("=")[0]] = string_param.split("=")[1]);
      return object;
    },

    /**
     * Converts an object to a URL query param
     * @param { Object<Any> } object
     * @returns { String }
     */
    convertObjectToQueryParam: (object) => new URLSearchParams(object).toString(),

    /**
     * Converts an array to an object
     * @param { Array<Any> } array
     * @returns { String }
     */
    convertArrayToObject: (array) => {
      const object = {};
      array.forEach(element => {
        console.log(element);
        debugger;
      });
      return object;
    },

    /**
     * Clears out any cache or cookies for the param string
     */
    resetCacheAndCookies: () => {
      if (useCacheForStockTake) {
        localStorage.removeItem(cache_key);
        localStorage.removeItem(cache_key_selected_categories);
        console.log('CACHE :: Cleared');
      }
      else {
        utility.setCookie(cache_key, "");
        utility.setCookie(cache_key_selected_categories, "");
        console.log('COOKIES :: Cleared');
      }
    },

    /**
     * Retrieves a cookie
     * @param { String } cname
     * @returns { String }
     */
    getCookie: (cname) => {
      let name = cname + "=";
      let decodedCookie = decodeURIComponent(document.cookie);
      let ca = decodedCookie.split(';');
      for(let i = 0; i <ca.length; i++) {
          let c = ca[i];
          while (c.charAt(0) == ' ') {
          c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
          }
      }
      return "";
    },

    /**
     * Sets a cookie
     * @param { String } cookieName
     * @param { any } cookieValue
     */
    setCookie: (cookieName, cookieValue) => {
      const today = new Date();
      const expire = new Date();
      expire.setTime(today.getTime() + 3600000*24*14);
      document.cookie = cookieName+"="+JSON.stringify(cookieValue) + ";expires="+expire.toGMTString();
    },
  };

  // methods to do with the multi category select
  const multiCategorySelect = {
    /**
     * used for showing and hiding the category list
     */
    showing: false,

    /**
     * Initializes the multi select dropdown
     */
    initialize: () => {
      // render the dropdown HTML
      loaded_categories = [{ uid: "all-products", description: "All Products", status: "A" }, ...loaded_categories];
      multiCategorySelect.render(loaded_categories);

      // render the styles
      // multiCategorySelect.addStyles();
      
      // select the last past selections if any
      const cache_selections = JSON.parse(localStorage.getItem(cache_key_selected_categories));
      if (cache_selections) cache_selections.forEach(category => multiCategorySelect.addSelection(category));
    },


    toggleCategories: () => {
      // get the wrapper element
      const select_items_wrapper = document.querySelector("#product-category-select .select-items-wrapper");

      // toggle select
      if (!multiCategorySelect.showing) select_items_wrapper.classList.add('showing');
      else select_items_wrapper.classList.remove('showing');

      // update showing value
      multiCategorySelect.showing = !multiCategorySelect.showing;
    },

    toggleDisplayValue: () => {
      const heading = document.querySelector('#selectHeader');
      const multi_select_value = document.querySelector('#multi_select_value');
      
      if (selected_categories.length >= 1) {
        heading.classList.remove('showing')
        multi_select_value.classList.add('showing')
      }
      else {
        heading.classList.add('showing')
        multi_select_value.classList.remove('showing')
      }
    },

    /**
     * Called when you choose a category to be removed
     * @param {{ uid: String, description: String, status: String }} category
     */
    removeSelection: (category) => {
      // called to update UI  
      const done = (_category) => {
        // remove the selected element from the UI
        const element_to_remove = document.querySelector(`#selected-item-${_category.uid}`);
        if (element_to_remove) element_to_remove.remove();

        // remove the 'active class
        const category_item = document.querySelector(`#category-item-${_category.uid}`);
        if (category_item) category_item.classList.remove('active');

        // update cache with new selected array
        localStorage.setItem(cache_key_selected_categories, JSON.stringify(selected_categories));
      };

      // deselect all categories
      if (category.uid == "all-products") {
        // reset the selected categories to empty
        loaded_categories.forEach(category => done(category));

        // remove selections from all categories
        selected_categories = [];

        // update cache with new selected array
        localStorage.setItem(cache_key_selected_categories, JSON.stringify(selected_categories));

        multiCategorySelect.toggleDisplayValue();
        return;
      }

      // remove category from list of selected categories
      selected_categories.splice(utility.indexOfObject(category, selected_categories, 'uid', category.uid), 1);
      
      // update the UI and save in cache
      done(category);
      multiCategorySelect.toggleDisplayValue();
    },

    /**
     * Called when you choose a category to be selected
     * @param {{ uid: String, description: String, status: String }} category
     */
    addSelection: (category) => {
      // called to update UI
      const done = (_category) => {
        // update the UI to show you have selected a category
        multiCategorySelect.toggleDisplayValue()
        multiCategorySelect.renderSelection(_category)
        
        // add the 'active' class
        const category_item = document.querySelector(`#category-item-${_category.uid}`);
        if (category_item) category_item.classList.add('active');

        // update cache with new selected array
        localStorage.setItem(cache_key_selected_categories, JSON.stringify(selected_categories));
      };
      
      // select all categories if 'All' options is selected
      if (category.uid == "all-products") {
        if (!utility.isObjectInArray(category, selected_categories, 'uid', category.uid)) {
          loaded_categories.forEach(category => {
            // select all categories
            selected_categories.push(category);

            // render selections
            done(category);
          })
        }
        else multiCategorySelect.removeSelection(category);
      }

      // selection of a single category
      else {
        // make sure you can't select twice
        if (!utility.isObjectInArray(category, selected_categories, 'uid', category.uid)) {
          // add category to list of selected categories
          selected_categories.push(category);

          // update the UI and save in cache
          done(category);
        }
        // remove the selection if clicked again
        else multiCategorySelect.removeSelection(category);
      }
    },

    /**
     * Creates the categories multi select dropdown
     * @param { Array<{uid: String, description: String, status: String}> } categories
     * @constructor
     */
    render: (categories) => {
      // default item to
      let items = '';

      // generate each category item
      categories.forEach(category => {items += `
        <div 
          id="category-item-${category.uid}" 
          class="category-item"
          onclick='multiCategorySelect.addSelection(${JSON.stringify(category)})'
        >
          <div class="value">${category.description}</div>
          <div id="category-item-${category.uid}" class="icon"><img src="../../images/tick.png"></div>
        </div>`;
      });

      // build up the select html structure
      const product_category_select = `
      <div id="product-category-select">
        <div onclick="multiCategorySelect.toggleCategories()" class="select-value">
          <div id="selectHeader" class="header">Please choose a product category(s)</div>
          <div id="multi_select_value" class="selected-items-wrapper"></div>
        </div>
        <div class="select-items-wrapper">${items}</div>
      </div>`;
      
      // render the new multi select
      const category_select_wrapper = document.querySelector("#category-select-wrapper");
      if (category_select_wrapper) {
        category_select_wrapper.innerHTML = product_category_select;
        multiCategorySelect.toggleDisplayValue();
      }
    },

    /**
     * Updates the UI to show user you have selected a category
     * @param {{ uid: String, description: String, status: String }} category
     */
    renderSelection: (category) => {
      const newSelectedCategory = `<div 
                      id="selected-item-${category.uid}" 
                      class="selected-item"
                    >
                      ${category.description}
                    </div>`;

      // get the render element
      const multi_select_value = document.querySelector('#multi_select_value');
      if (multi_select_value) multi_select_value.innerHTML += newSelectedCategory;
      else console.error("Failed to find #multi_select_value")
    },
  };

  // stock adjustments
  const Stock = {

    /**
     * Populated with the last return value from the submission of the stock
     */
    stockProductListDataReturn: null,

    /**
     * Populated with the last return value from the submission of the stock update
     */
    stockAdjustmentDataReturn: null,

    /**
     * Types used to tell if you are increasing or decreasing stock
     */
    TYPE_INCREASE: "postStockAdjIncrease",
    TYPE_DECREASE: "postStockAdjDecrease",

    /**
     * Populated with the generated OTP's
     */
    OTP: null,

    /**
     * Will attempt to make the stock adjustments
     * @param { Number } userId
     * @param { Number } principalId
     * @param { String } username
     * @param { String } requiredData
     */
    update: (userId, principalId, username, requiredData) => {

      /**
       * Makes the request to make said adjustments
       */
      const makeStockAdjustment = () => {
        // build up the params to send to the API
        const params = {
          "PRINCIPLEID": principalId,
          "USERID": userId,
          "USERNAME": username,
          "REQUIREDDATA": requiredData,
          "REFERENCENUMBER": `${principalId} - ${username}`,
          "DETAILLINES": [],
        };

        // populate the detail lines with the stock increase/decrease
        for (const key in Stock.stockProductListDataReturn) {
          const product = Stock.stockProductListDataReturn[key];
          const addToDetails = (_product) => params.DETAILLINES.push({ productUid: key, quantity: _product.v });

          // if the product has a variances add it to detail lines
          if (product.v !== "N") {
            // stock increase
            if (requiredData === Stock.TYPE_INCREASE && product.v > 0) addToDetails(product)

            // stock decrease
            if (requiredData === Stock.TYPE_DECREASE && product.v < 0) addToDetails(product)
          }
        }

        // convert detail lines to a url
        params.DETAILLINES = JSON.stringify(params.DETAILLINES);

        console.log(`STOCK UPDATE (${requiredData})`, params);

        // make the request to API
        Stock.makeRequestWithReturn({
          url: '<?php echo $ROOT.$PHPFOLDER ?>functional/ws/api/apiCallAdjustment.php',
          params: utility.convertObjectToQueryParam(params)
        }, (res) => {
          console.log('Stock Update Success :: ', res);
        })
      };

      const popup_inputs = Popup.GetInfo({
          id: "tracking-link-popup",
          renderTo: "body",
          inputs: [
              {
                  id: "otp",
                  type: "text",
                  value: "",
                  label: `Please enter the OTP sent to (${username})`,
                  placeholder: "Enter otp here..."
              }
          ],
          mainAction: {
              id: "otp-dismiss",
              text: "Submit",
              action: () => {
                  Popup.Close();
                  const otpEntered = popup_inputs["otp"]();
                  if (otpEntered == Stock.OTP) {
                    // reset the otp value for the next time
                    Stock.OTP = null;

                    // adjust stock
                    makeStockAdjustment();
                  }
              }
          }
      });
    },

    /**
     * Sends off an sms with OTP for the stock adjustments
     * @param { Number } userId
     * @param { Number } principalId
     * @param { String } username
     * @param { String } requiredData
     */
    sendOTP: (userId, principalId, username, requiredData) => {
      Stock.OTP = utility.getRandomNumber(1001, 9999);
      const params = {
        "ACTION": "OTP",
        "USERNAME": username,
        "OTP": Stock.OTP,
        "DEPOTNAME": '<?php echo $depotName ?>',
        "PRINCIPLENAME": '<?php echo $principalName ?>',
        "PRINCIPLECODE": <?php echo $principalCode ?>,
        "PRINCIPALID": <?php echo $principalId ?>,
        "DEPOTID": <?php echo $depotId ?>,
      };
      
      // make the request to API
      Stock.makeRequest({
        url: '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',
        params: utility.convertObjectToQueryParam(params)
      })

      console.log(`OTP Sent (${Stock.OTP})`);

      Stock.update(userId, principalId, username, requiredData);
    },

    /**
     * Shows the stock adjustment buttons
     */
    show: () => {
      const increase = document.querySelector('#increaseStockButton');
      const decrease = document.querySelector('#decreaseStockButton');
      if (increase && decrease) {
        increase.classList.remove('d-none');
        decrease.classList.remove('d-none');
      }
    },

    /**
     * Hides the stock adjustment buttons
     */
    hide: () => {
      const increase = document.querySelector('#increaseStockButton');
      const decrease = document.querySelector('#decreaseStockButton');
      if (increase && decrease) {
        increase.classList.add('d-none');
        decrease.classList.add('d-none');
      }
    },

    /**
     * Will make a request to a URL without a return value
     * @param {{ url: String, params: String }} options
     */
    makeRequest: (options) => AjaxRefresh(options.params, options.url, "stockCountForm", "Please wait whilst page is refreshed...", ""),

    /**
     * Will make a request to a URL with a return value
     * @param {{ url: String, params: String }} options
     */
    makeRequestWithReturn: (options, callback) => {
      Stock.Callback = callback;

      // setup the callback method
      Request_Callback = `
      alreadySubmitted=false;
      if(msgClass.type=="S") console.log(msgClass);
      else console.error(msgClass);`;

      // make request
      AjaxRefresh(
        options.params, 
        options.url, 
        "stockCountForm", 
        "Please wait whilst page is refreshed...", Request_Callback)
    },
  };

  // initialize the category dropdown
  multiCategorySelect.initialize();

  /**
   * checks for last count and inserts it into the relevant inputs
   */
  const loadLastProductCount = () => {
    // get param from cache
    const cache_param = localStorage.getItem(cache_key);

    // if param was found in cache
    if (cache_param) {
      // convert string param to object
      const cache_param_converted = utility.convertCacheParamToObject(cache_param)

      // insert last count values
      for (const key in cache_param_converted) {
        const element = document.querySelector(`input[name="${key}"]`);
        if (element) element.value = cache_param_converted[key];     
      }
    }
  }

  /**
   * Called when continue button is clicked
   */
  function submitCategories() {
    // make sure you have selected 1 or more categories
    if (selected_categories.length !== 0 || loaded_categories.length === 0) {
      disableStep(5);
      disableStep(3);
      enableStep(2);
      toggleSteps(2,'<?php echo $ROOT.$PHPFOLDER ?>');
    }
    else {
      alert("Please select at least 1 category before continuing")
    }
  }

  function startStockTake(){

    var tc = $('#stocktaketc').attr('checked')==undefined ? false : true;
    if(!tc){
      parent.popBox('Please read and tick the terms and conditions below and try again!','error');
    } else {

      if (alreadySubmitted) {
        alert('You have already clicked on submit... you may click submit again after 2 minutes.');
        return;
      }

      alreadySubmitted=true;

      AjaxRefreshWithResult('ACTION=MODE&SWITCH=1&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>',
                            '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                            'alreadySubmitted=false; if(msgClass.type=="S") {utility.resetCacheAndCookies(); location.reload();}',
                            'Please wait while request is processed...');

    }


  }


  function downloadMovement(){
    $('#rolloversubmit').slideDown();

    window.open('about:blank', 'StockMovementReport','scrollbars=yes,width=300,height=200,resizable=yes');
    document.getElementById('theForm').submit();
  }


  function submitRollover(){

    if (alreadySubmitted) {
      alert('You have already clicked on submit... you may click submit again after 2 minutes.');
      return;
    }

    alreadySubmitted=true;

    // get the selected categories
    const categories = (selected_categories.length !== 0 || loaded_categories.length === 0)? JSON.stringify(selected_categories) : false;

    // setup params for request
    const params = {
      "ACTION": "ROLLOVER",
      "CATEGORIES": encodeURIComponent(categories),
      "PRINCIPALID": <?php echo $principalId ?>,
      "DEPOTID": <?php echo $depotId ?>,
      "PROJSON": [],
    };

    // setup the callback method
    Request_Callback = `
    alreadySubmitted=false; 
    if(msgClass.type=="S") window.location.reload();`;

    // make request
    AjaxRefreshWithResult(
      utility.convertObjectToQueryParam(params),
      '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
      Request_Callback,
      'Please wait while request is processed...'
    );

  }


  function stopStockTake(){

    if(confirm("Are sure you want to stop this stock take?")){
        AjaxRefreshWithResult('ACTION=MODE&SWITCH=0&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>',
                            '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                            'alreadySubmitted=false; if(msgClass.type=="S") {utility.resetCacheAndCookies(); location.reload();}',
                            'Please wait while request is processed...');
    }
  }

  /**
   * Updates the cache/cookies with the current count and submits it
   * @param json
   * @param { Boolean } updateCountOnly
   */
  function submitStockCount(json, updateCountOnly=false){

    let count_params = null;
    let cache_param = null;
    const serialized_form = $('#stockCountForm').serialize();
    const categories = (selected_categories.length !== 0 || loaded_categories.length === 0)? JSON.stringify(selected_categories) : false;

    // choose to use either cache or cookies
    if (useCacheForStockTake) cache_param = localStorage.getItem(cache_key);
    else cache_param = utility.getCookie(cache_key)

    // if cache param exists and is the same as the current form
    if (cache_param && (cache_param == serialized_form)) count_params = cache_param;
    else {
      count_params = serialized_form;
      if (useCacheForStockTake) localStorage.setItem(cache_key, count_params);
      else utility.setCookie(cache_key, count_params);
    }

    // only submit if (updateCountOnly) is false
    if (!updateCountOnly) {
      console.log('Submitting Count');

      // setup params for request
      const params = {
        "ACTION": "COUNT",
        "CATEGORIES": encodeURIComponent(categories),
        "PRINCIPALID": <?php echo $principalId ?>,
        "DEPOTID": <?php echo $depotId ?>,
        "PROJSON": (json != undefined)? json : [],
      };

      // setup the callback method
      Request_Callback = `
      alreadySubmitted=false;

      if(msgClass.type=="S") successfulCount(msgClass);
      else displayVariances(msgClass);

      console.log("Request :: (submitStockCount) is now done");`;

      // make request
      AjaxRefreshWithResult(
        utility.convertObjectToQueryParam(params)+`&${count_params}`,
        '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
        Request_Callback,
        'Please wait while request is processed...'
      );
    }

  }

  //enable rollover
  function successfulCount(msgClass){
    disableStep(2);
    disableStep(3);
    disableStep(4);
    enableStep(5);
    toggleSteps(5,'<?php echo $ROOT.$PHPFOLDER ?>');
  }

  function displayVariances(msgClass){
    Stock.show();
    Stock.stockProductListDataReturn = JSON.parse(msgClass.identifier2);
    displayProductList(msgClass.identifier2);
  }

  function displayProductPrint(imagesCheckBoxId, list){

    var param = (list != undefined)?"&FILTERPID=" + list:"";

    param += ($('#'+imagesCheckBoxId).attr('checked')==undefined)?"":"&IMAGES=1";

    if(imagesCheckBoxId == 'VARDISPLAYIMAGE'){
      param += '&VARIANCE=1';
    }

    window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationHandler.php?TYPE=stockcount' + param,'ProductList','scrollbars=yes,width=750,height=600,resizable=yes');
  }

  function displayPrintVariances(){


    var html = "<html>"
        html += "<head>";
        html += "<title>Variance Count</title>";
        html += '<LINK href="<?php ECHO HOST_SURESERVER_AS_NEWUSER . $PHPFOLDER ?>css/default.css" rel="stylesheet" type="text/css">';
        html += '<script src="<?php ECHO HOST_SURESERVER_AS_NEWUSER . $PHPFOLDER ?>js/jquery.js"><\/script>';
        html += "</head>";
        html += "<body onload='jQuery(\"input\").attr(\"disabled\", true);'>";
        html += '<a href="javascript:window.print();" style="text-align:center;display:block;border:1px solid #ccc;padding:0px 8px;line-height:30px;width:100px;background:yellow;text-decoration:none;color:#666;font-weight:bold;"><img src="../../images/print-icon.png" border="0" alt="" align="left" style="margin:2px 0px;"> Print</a>';
        html += "<h2>Stock count : Variance </h2><h4 style='color:red'>*** Print ONLY for internal office use ***</h4>";
        html += $('#product-variance-table').html();
        html += "</body></html>";
    var blob = new Blob([html], {type: 'text/html'});
    window.open(window.URL.createObjectURL(blob),'','scrollbars=yes,width=750,height=600,resizable=yes');

    //window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationHandler.php?TYPE=stockcount' + param,'ProductList','scrollbars=yes,width=750,height=600,resizable=yes');
  }

  function displayProductList(json){
    // get the selected categories
    const categories = (selected_categories.length !== 0 || loaded_categories.length === 0)? JSON.stringify(selected_categories) : false;

    // setup params for request
    const params = {
      "CATEGORIES": encodeURIComponent(categories),
      "PRINCIPALID": <?php echo $principalId ?>,
      "DEPOTID": <?php echo $depotId ?>,
      "PROJSON": (json != undefined)? json : [],
    };

    Request_Callback = `
    highlight();
    loadLastProductCount();`;

    // make request
    const url = utility.convertObjectToQueryParam(params);
    AjaxRefresh(
      url,                      // Params to pass to the file
      '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeProductList.php', // URL to file
      "stockCountForm",                                               // Where to render the html return
      "Please wait whilst page is refreshed...",                      // Request start message
      Request_Callback                        // Code to run after request
    );

  }

  function displayProductAudit(pid){

    //display all transactions for product since last stock take...
    window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeProductAudit.php?PRODUCTID=' + pid,'Product Audit','scrollbars=yes,width=600,height=500,resizable=yes');

  }

  function highlight(){

    //row highlisting for inputing values
    $(".highlightMe").focus(function() {
        $(this).closest("tr").attr('id','RowHighlight');
    })
    .blur(function() {
        $(this).closest("tr").attr('id','');
    });
  }



  function enableStep(id){
    $('#proceed' + id).show();
  }
  function disableStep(id){
    $('#proceed' + id).hide();
  }

  function successFreeze(msgClass){
    //nothing to do... do something?
  }
</script>

</BODY>
</HTML>