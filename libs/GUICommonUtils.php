<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');


class GUICommonUtils {

	// output filter fields
	// inclOptions ~ list of extra params you want along top such as status
	public static function getFilterFields ($fldFiltername,$usage,$fieldSize,$value,$destArea,$extraParams,$url,$inclOptions=array(1)) {
		// the buttons
		print("<TR class='odd'>");
		print("<TD style='padding-left:8px;' colspan=".(sizeof($fieldSize)).">");
		print("<input class='btn btn-info btn-small' type='submit' value='Submit Filter' onclick='AjaxRefresh({$fldFiltername}JSgetFilter(),\"".$url."\",\"".$destArea."\",\"Please wait while filter request is processed...\",\"\");'/>");
		print("<input class='btn btn-info btn-small' type='submit' value='Clear Filter' onclick='".$fldFiltername."JSclearFilter(); AjaxRefresh(".$fldFiltername."JSgetFilter(),\"".$url."\",\"".$destArea."\",\"Please wait while filter request is retrieved...\",\"\");'/>");
		echo "</TD></TR>
			  <TR><TD colspan=".(sizeof($fieldSize))." height='35'>

			  	<TABLE class='tableReset' style='text-align:left;font-size:8pt;' ><TR>";

				// 1. Status Type
				if (in_array(1,$inclOptions)) {

			        if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType="A";

					echo "<TD>Status:</TD>
					<TD style='text-align:left;'>";
					BasicInputElement::getCSS3RadioHorizontal($fldFiltername.'PAGETYPE','Active,Deleted','A,D',$pageType);
	                echo '</TD>';

	                //Break Col
	                echo "<TD style='width:30px;'></TD>";
				}

				// 2. Status Type
				if (in_array(2,$inclOptions)) {

			        if (isset($_POST["PAGEOWNEDBY"])) $pageOwnedBy=$_POST["PAGEOWNEDBY"]; else if (isset($_GET["PAGEOWNEDBY"])) $pageOwnedBy=$_GET["PAGEOWNEDBY"]; else $pageOwnedBy="P";

					echo "<TD>Owned By:</TD>
					<TD style='text-align:left;'>";

					BasicInputElement::getCSS3RadioHorizontal($fldFiltername.'PAGEOWNEDBY','Principal, Vendor & Principal','P,B',$pageOwnedBy);
	                echo '</TD>';

	                //Break Col
	                echo "<TD style='width:30px;'></TD>";
				}


			  	//LIMIT ROWS
				echo "<TD style='text-align:right;'>Limit Rows Returned: </TD>
				<TD style='text-align:left;'>";

				if (isset($_POST["PAGESIZE"])) $pageSize=$_POST["PAGESIZE"]; else if (isset($_GET["PAGESIZE"])) $pageSize=$_GET["PAGESIZE"]; else $pageSize=$_SESSION["up_dps"];
				BasicInputElement::getCSS3RadioHorizontal($fldFiltername.'PAGESIZE','10,50,100,250,500,1000,Unlimited','10,50,100,250,500,1000,0',$pageSize);

		        echo "</TD>";

		echo '</TR>';
		echo '</TABLE>';
		echo '</TD>
			  </TR>';

		if(isset($_POST["RBNAME"])){
		 $parmValues = "";
		}

		// script for above functions
		echo '<script type="text/javascript">';
		?>
                    function <?php echo $fldFiltername; ?>JSgetFilter(){
                      var filter='FILTERLIST='+encodeURIComponent(convertElementToArray(document.getElementsByName('<?php echo $fldFiltername; ?>')))<?php echo $extraParams; ?>+'&PAGESIZE='+convertElementToArray(document.getElementsByName('<?php echo $fldFiltername; ?>PAGESIZE'))+'&PAGETYPE='+convertElementToArray(document.getElementsByName('<?php echo $fldFiltername; ?>PAGETYPE'))+'&PAGEOWNEDBY='+convertElementToArray(document.getElementsByName('<?php echo $fldFiltername; ?>PAGEOWNEDBY'))<?php echo $parmValues; ?>;
                      return filter;
                    }
                    function <?php echo $fldFiltername; ?>JSclearFilter(){
                            var fld=document.getElementsByName('<?php echo $fldFiltername; ?>');
                            for(var i=0;i<(fld.length);i++){fld[i].value = '';}
                    }
		<?php
		echo '</script>';

		//
		print("<TR class='odd'>");
		if (sizeof($usage)!=sizeof($fieldSize)) { print("ERROR: getFilterArray sizeof differs"); return; }
		for ($i=1; $i<=sizeof($usage); $i++) {
			if (is_array($value)) {
			  $val=$value[$i-1]; //zero based
			} else $val='';
			if ($usage[$i]=="Y") print("<TD class=''><input type='text' name='".$fldFiltername."' value='".$val."' size=".$fieldSize[$i]." /></TD>");
			else print("<TD class=''><input type='hidden' name='".$fldFiltername."' value='' size=".$fieldSize[$i]." /></TD>");
		}
		print("</TR>");
	}

	// output filter fields - NON AJAX
	public static function getFilterFieldsNonAjax ($fldFiltername,$usage,$fieldSize,$value,$extraParams,$url) {
		// the buttons
		print("<TR class='odd' >");
		print("<TD style='padding-left:8px;' colspan=".sizeof($fieldSize).">");
		print("<input class='btn btn-info btn-small' type='submit' value='Submit Filter' onclick='window.location=\"".$_SERVER['PHP_SELF']."?\"+".$fldFiltername."JSgetFilter();' />");
		print("<input class='btn btn-info btn-small' type='submit' value='Clear Filter'  onclick='".$fldFiltername."JSclearFilter(); window.location=\"".$_SERVER['PHP_SELF']."?\"+".$fldFiltername."JSgetFilter();'/>");
		echo "</TD></TR><TR><TD colspan=".sizeof($fieldSize)." height='35'>";

		echo "<TABLE class='tableReset' style='text-align:left;font-size:8pt;' ><TR>";
		  echo "<TD style='text-align:right;'>Limit Rows Returned: </TD>";
		  echo "<TD style='text-align:left;'>";
		    if (isset($_POST["PAGESIZE"])) $pageSize=$_POST["PAGESIZE"]; else if (isset($_GET["PAGESIZE"])) $pageSize=$_GET["PAGESIZE"]; else $pageSize=$_SESSION["up_dps"];
		    BasicInputElement::getCSS3RadioHorizontal($fldFiltername.'PAGESIZE','10,50,100,250,500,1000,Unlimited','10,50,100,250,500,1000,0',$pageSize);
          echo '</TD>';
        echo '</TR>';
        echo '</TABLE>';

		echo '<script type="text/javascript" defer>';
		?>
                    $(document).ready(function() {
                        if(typeof parent.hideMsgBoxSystemFeedback == 'function') parent.hideMsgBoxSystemFeedback();
                    });
                    function <?php echo $fldFiltername ?>JSgetFilter(){
                            var filter='FILTERLIST='+encodeURIComponent(convertElementToArray(document.getElementsByName('<?php echo $fldFiltername; ?>')))+'&PAGESIZE='+convertElementToArray(document.getElementsByName('<?php echo $fldFiltername ?>PAGESIZE'))<?php echo $extraParams; ?>;
                            if(typeof parent.showMsgBoxSystemFeedback == 'function') { parent.showMsgBoxSystemFeedback("Please wait while filtering..."); }
                            return filter;
                    }
                    function <?php echo $fldFiltername ?>JSclearFilter(){
                            var fld=document.getElementsByName('<?php echo $fldFiltername; ?>');
                            for(var i=0;i<(fld.length);i++){fld[i].value = '';}
                    }
		<?php
		print("</scr"."ipt>");
		print("</TD>");

		print("</TR>");
		// legend line
		print("<TR class='odd'><TD colspan=".sizeof($fieldSize)." style='padding:0'>");
		print("<span style='color:green; font-size:9px;'>* For numeric columns, use &gt;,&lt;,&gt;=,&lt;=,=</span>");
		print("</TD></TR>");
		//
		print("<TR class='odd'>");
		if (sizeof($usage)!=sizeof($fieldSize)) { print("ERROR: getFilterArray sizeof differs"); return; }
		for ($i=1; $i<=sizeof($usage); $i++) {
			if (is_array($value)) {
			  $val=$value[$i-1]; //zero based
			} else $val='';
			if ($usage[$i]=="Y") print("<TD class=''><input type='text' name='".$fldFiltername."' value='".$val."' size=".$fieldSize[$i]." /></TD>");
			else print("<TD class=''><input type='hidden' name='".$fldFiltername."' value='' size=".$fieldSize[$i]." /></TD>");
		}
		print("</TR>");
	}

	// apply Filter
	public static function applyFilter ($table,$filter,$stripHTMLTags=true) {

		$tableCopy=$table;

		if (is_array($filter)) {

		  	foreach($filter as $k => $f){
		      $filter[$k] = trim($f);
		    }

			foreach($table as $key=>$row) {  // for each row
				$j=-1; // remember that filter is zero based because it came from javascript screen

				foreach($row as $fld){// for each field in row

					if ($stripHTMLTags) {
						$field=strtoupper(strip_tags($fld));
					} else {
						$field=strtoupper($fld);
					}

					$j++;
					$filterVal=$filter[$j];
					if ($filterVal!="") {
						// check for special operation
						$sign=preg_replace("/[^><=]/","",$filter[$j]);
						$number=preg_replace("/[^0-9]/","",$filter[$j]);
						if ((is_numeric($field)) && ($sign!="")) {
								if ($sign==">") { if (!($field > $number)) { unset($tableCopy[$key]); break; } }
								if ($sign=="<") { if (!($field < $number)) { unset($tableCopy[$key]); break; } }
								if ($sign=="=") { if (!($field == $number)) { unset($tableCopy[$key]); break; } }
								if (($sign==">=") || ($sign=="=>")) { if (!($field >= $number)) { unset($tableCopy[$key]); break; } }
								if (($sign=="<=") || ($sign=="=<")) { if (!($field <= $number)) { unset($tableCopy[$key]); break; } }
						} else	if (strpos($field,strtoupper($filterVal))===false) { unset($tableCopy[$key]); break; }
					}
				}
			}
		}
		return $tableCopy;
	}

	public static function translateStatus ($statusCode) {
		switch ($statusCode) {
			case FLAG_STATUS_ACTIVE:
				return "Active";
			case FLAG_STATUS_DELETED:
				return "Deleted";
			case FLAG_STATUS_QUEUED:
				return "Queued";
			case FLAG_STATUS_ERROR:
				return "Error";
			case FLAG_STATUS_SUSPENDED:
				return "Suspended";
			case FLAG_STATUS_CLOSED:
				return "Closed";
			case "Y":
				return "Yes";
			case "N":
				return "No";
			case "":
				return "";
			default:
				return "Unknown Status";
		}
	}

	public static function translateResult ($statusCode) {
		switch ($statusCode) {
			case FLAG_ERRORTO_ERROR:
				return "Error";
			case FLAG_ERRORTO_SUCCESS:
				return "Success";
			case FLAG_STATUS_QUEUED:
				return "Queued";
			case "":
				return "";
			default:
				return "Unknown Status";
		}
	}

	public static function translateCategoryUser ($statusCode) {
		switch ($statusCode) {
			case "P":
				return "Principal User";
			case "D":
				return "Depot User";
			case "A":
				return "Sales Agent User";
			default:
				return "Unknown User Category";
		}
	}

	public static function translateScheduleType ($statusCode) {
		switch ($statusCode) {
			case "R":
				return "Report";
			case "SR":
				return "System Report";
			default:
				return "Unknown Schedule Type";
		}
	}



	public static function translateScheduleDestinationType ($outputCode) {
		switch ($outputCode) {
			case SCD_DT_EMAIL:
				return "Email";
			case SCD_DT_FTP:
				return "FTP";
			default:
				return "Unknown";
		}
	}

	public static function translateDealType ($dealId) {
		switch ($dealId) {
			case VAL_DEALTYPE_NETT_PRICE:
				return "Nett price";
			case VAL_DEALTYPE_AMOUNT_OFF:
				return "Amount Off";
			case VAL_DEALTYPE_PERCENTAGE:
				return "Percentage";
			default:
				return "Unknown Deal Type";
		}
	}

	public static function translateCumulativeType ($value) {
		switch ($value) {
			case DPCT_DISCOUNTS_CUMULATIVE:
				return "Cumulative";
			case DPCT_DISCOUNTS_ZERO:
				return "Only apply if list price discount zero";
			case DPCT_NETT_PRICE:
				return "Only Apply if Item Price is Nett Price";
			case DPCT_DISCOUNTS_OVERRIDE:
				return "Override List Price Discounts";
			default:
				return "Unknown Cumulative Type";
		}
	}

	public static function translateDocumentPricingLevel ($value) {
		switch ($value) {
			case DPL_DOCUMENT:
				return "Invoice Level";
			case DPL_ITEM:
				return "Item Lines";
			case DPL_DOCUMENT_ITEM:
				return "Item Lines, triggered by Inv Totals";
			default:
				return "Unknown Document Pricing Level";
		}
	}

	public static function translateUnitPriceType ($value) {
		switch ($value) {
			case UPT_CASES:
				return "Cases";
			case UPT_CHARGE:
				return "Invoice Amount";
			default:
				return "Unknown Unit Price Type";
		}
	}

	public static function translateDocumentStatusType ($documentStatusUId) {
		switch ($documentStatusUId) {
			case DST_ACCEPTED:
				return "Accepted";
			case DST_CANCELLED:
				return "Cancelled";
			case DST_DELIVERED_POD_OK:
				return "Delivered & POD OK";
			case DST_DIRTY_POD:
				return "Dirty POD";
			case DST_INPICK:
				return "In-Pick";
			case DST_INVOICED:
				return "Invoiced";
			case DST_PROCESSED:
				return "Processed";
			case DST_QUEUED:
				return "Queued for Processing";
			case DST_UNACCEPTED:
				return "Unaccepted";
			case DST_WAITING_DISPATCH:
				return "Waiting Dispatch";
			default:
				return "Unknown Document Status";
		}
	}

	public static function translateDeliveryDay ($deliveryDayUId) {
		switch ($deliveryDayUId) {
			case "1":
				return "Monday";
			case "2":
				return "Tuesday";
			case "3":
				return "Wednesday";
			case "4":
				return "Thursday";
			case "5":
				return "Friday";
			case "6":
				return "Saturday";
			case "7":
				return "Sunday";
			case "8":
				return "Unknown";
			default:
				return "Unknown Delivery UId";
		}
	}

	public static function translateWeekdayFromString ($var) {
		$arr = explode(",",$var);
		$str="";
		foreach ($arr as $a) {
			switch ($a) {
				case "0":
					$day="Sunday";
					break;
				case "1":
					$day="Monday";
					break;
				case "2":
					$day="Tuesday";
					break;
				case "3":
					$day="Wednesday";
					break;
				case "4":
					$day="Thursday";
					break;
				case "5":
					$day="Friday";
					break;
				case "6":
					$day="Saturday";
					break;
				case "":
					$day="";
					break;
				default:
					$day="Unknown";
			}
			if ($day!="") {
				if ($str=="") $str=$day;
				else $str.=",".$day;
			}
		}
		return $str;
	}

	public static function translateOHExceptionStatus ($status) {
		switch ($status) {
				case "1.0":
					return "No store special field id was designated for lookup";
				case "2.0":
					return "Store was not found on lookup, and could not create store automatically as the Depot could not be determined";
				case "2.1":
					return "Store was not found on lookup, and could not create store automatically as no special store field was configured";
				case "2.2":
					return "Store was not found on lookup, and could not create store automatically as supplied store lookup value in file was empty";
				case "2.3":
					return "Could not update special field from EDI automatically";
				case "2.4":
					return "Special Field Value is set to update masterfiles from EDI, but supplied value is blank";
				case "2.5":
					return "Supplied Depot Lookup validated differently to Store Master Depot.";
				case "2.6":
					return "Store could not be updated with supplied Depot Lookup.";
				case "2.7":
					return "Store could not be updated with supplied fields.";
				case "3.0":
					return "Store was not found on lookup, and could not create store automatically as the Chain could not be determined";
				case "3.1":
					return "Store was not found on lookup, and could not create store automatically as no special chain field was configured";
				case "3.2":
					return "Store was not found on lookup, and could not create store automatically as supplied chain lookup value in file was empty";
				case "4.0":
					return "Error occurred creating the store. Please contact RT support";
				case "4.1":
					return "Cannot create the store automatically because atleast one of the lookup values must be part of store detail";
				case "4.2":
					return "Cannot create the store automatically as the generic chain could not be found";
				case "5.0":
					return "Invalid Capture Date";
				case "5.1":
					return "Invalid Order Date";
				case "5.2":
					return "Invalid Delivery Date";
				case "5.2":
					return "Invalid Invoice Date";
				case "6.0":
					return "Store could not be determined";
				case "6.1":
					return "No detail lines found";
				case "7.0":
					return "Product could not be determined";
        case "7.1":
          return "Quantities cannot have decimal places";
        case "7.2":
          return "Product Calculated VAT Rate could not be determined";
				case "8.0":
					return "Supplied Vendor pricing does not compute";
				case "8.1":
					return "Supplied Vendor nett-price is negative";
				case "9.0":
					return "Principal is configured to use RT pricing, but no pricing was found in the RT System";
				case "9.1":
					return "Principal is configured to HALT on pricing discrepancy. A pricing discrepancy has occurred.";
				case "9.2":
					return "Please contact RT support. The pricing discrepancy flag could not be set.";
				case "10.0":
					return "Invalid Document Type for PnP Goods Inward Movement";
				case "99":
					return "SYSTEM ERROR! Please contact RT support";
				case "E":
					return "Posting Error Occurred.";
				case "S":
					return "Successfully Processed";
				case "D":
					return "Deleted";
        case "R.A":
          return "Requires approval. This document will be suspended until user explicitly approves it.";
        case "R.A.MP":
          return "Requires approval - Multiple Principals derived! This document will be suspended until user explicitly approves it.";
        case "SUSP":
            return "This Document has been put on hold by Retail Trading";
				default:
					return "Unknown Status";
			}
	}

	public static function translateDateRangeValue ($v) {

	    //explode parm to get actual constant set.
        $pVArr = explode(':',$v);
        $pV = $pVArr[0];
        $param = (isset($pVArr[1])) ? $pVArr[1] : 1;

	    //check if a date range param.
          switch ($pV) {
            case DR_YESTERDAY:
                $v = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - $param), date('Y')));
                break;
            case DR_LAST_WEEK_START:  //LAST WEEK : MONDAY - SUNDAY
                $v = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d') - date('w') - 6), date('Y')));
                break;
            case DR_LAST_WEEK_END:
                $v = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d') - date('w')), date('Y')));
                break;
            case DR_CURRENT_WEEK_START:  //CURRENT WEEK : MONDAY - SUNDAY
                $v = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d') - date('w') + 1), date('Y')));
                break;
            case DR_CURRENT_WEEK_END:
                $v = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d') - date('w') + 7), date('Y')));
                break;
            case DR_NO_MONTH_START:
                $v = date('Y-m-d',mktime(0, 0, 0, (date('m') - $param), 1, date('Y')));
                break;
            case DR_NO_MONTH_END:
                $v = date('Y-m-d', mktime(0, 0, 0, (date('m') - ($param - 1)), 0, date('Y')));
                break;
            case DR_CURRENT_MONTH_START:
                $v = date('Y-m-d',mktime(0, 0, 0, date('m'), 1, date('Y')));
                break;
            case DR_CURRENT_MONTH_END:
                $v = date('Y-m-d', mktime(0, 0, 0, (date('m') + 1), 0, date('Y')));
                break;
          }

          return $v;  //IF A NONE DATE RANGE VALUE - supplied value is returned with no change.

	}


	public static function requiredField () {
		print("<span style='color:red;'>*</span>");
	}

	// must include data 1st column as selector; header must NOT contain selector
	// type : radio, tick
	public static function outputRBTable ($headers,$data,$nameRB,$valuesRB,$callBack,$type,$tdExtraCol, $tdExtraRow, $rowHighLighting = true) {
		$isMultiple=false;
		switch ($type) {
			case "radio":
				$iType="radio";
				break;
			case "tick":
				$iType="checkbox";
				$isMultiple=true;
				break;
			default:
				$iType="radio";
		}

		// show the select all / unselect all toolbar
		if ($isMultiple) {
			print("<TR class='odd' style='border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>");
			print("<TH colspan='".(sizeof($headers)+1)."'><input type='submit' class='btn btn-info btn-mini' value='Select All' onclick='".$nameRB."selectAll(true);'/><input type='submit' class='btn btn-info btn-mini' value='UnSelect All' onclick='".$nameRB."selectAll(false);'/></TH>");
			print("</TR>");
		}

		// limit the list if necessary
		if (isset($_POST["PAGESIZE"])) $postPAGESIZE=$_POST["PAGESIZE"]; else if (isset($_GET["PAGESIZE"])) $postPAGESIZE=$_GET["PAGESIZE"]; else $postPAGESIZE=$_SESSION["up_dps"];
		if ($postPAGESIZE=="") $postPAGESIZE=VAL_GUI_MAX_ROWS_RETURNED;
		if (($postPAGESIZE!="0") && (sizeof($data)>$postPAGESIZE)) {
			 print("<TR><TD colspan=".(sizeof($headers)+1)."><span style='color:red'>Warning! Query returned <B>".sizeof($data)."</B> rows. <BR>Only first <B>{$postPAGESIZE}</B> are displayed according to your preferences. Please use filters to further refine list if your entry is not showing.</span></TD>");
			 $RS=array_splice($data,0,$postPAGESIZE);
		} else $RS=$data;

		print("<TR class='odd' style='border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>");
		if ($type=="radio") print("<TH><input type='".$iType."' name='".$nameRB."' value='' onclick='".$callBack."' CHECKED/></TH>");
		else print("<TH></TH>");
		foreach ($headers as $field) {
                  print("<TH class=''>".$field."</TH>");
		}
		print("</TR>");
		$i=0;
		foreach ($RS as $userRow) {

			$i++;
			if ($i & 1) $class="even"; else $class="odd";
			print("<TR class='".$class." hlr' >");
			$j=0;

			foreach ($userRow as $field) {
				$j++;
				if (isset($tdExtraRow[$i-1])) $tdRow=$tdExtraRow[$i-1]; else $tdRow="";
				if (isset($tdExtraCol[$j-1])) $tdCol=$tdExtraCol[$j-1]; else $tdCol="";
                $isMultiChecked = ($isMultiple && isset($_POST['PAGESELECTED']) && in_array($valuesRB[$i-1], explode(',', $_POST['PAGESELECTED']))) ? true : false;

				if ($j==1) {
					print("<TD class='standardData".(($i%2)? " odd": " even")."' ".$tdCol." ".$tdRow." >");
							// FIRST Field always assumed to be the selector
							if ($field===true){
							  print("<input type='".$iType."' name='".$nameRB."' value='".$valuesRB[$i-1]."' onclick='' CHECKED/>");
							} else {
							  $rowSelector = ($isMultiple)?("rowSelected(this);"):("");
							  echo "<input type='".$iType."' name='".$nameRB."' value='".$valuesRB[$i-1]."' " , (!empty($callBack) || !empty($rowSelector))?("onclick='".$callBack.";$rowSelector'"):("") , " " , (($isMultiChecked)?('CHECKED="CHECKED"'):('')) , " />";
							}
					print("</TD>");
				}
				// note: the lookup for index only works if rb value is unique
				else print("<TD class='".(($i%2)? " odd": " even")."' style='border-right-style:solid; border-right-color:#DDDDDD; border-width:1px;' ".$tdCol." ".$tdRow."><div id='".$nameRB."col".$j."row".$i."'>".$field."</div></TD>");
			}
			print("</TR>");
		}
		// detail row
		print("<TR ><TD colspan='".(sizeof($headers)+1)."' style='border-top-style:solid; border-color:white; border-width:1px; '>".sizeof($RS)." row(s) found.</TD></TR>");

		// output JAVASCRIPT TO get col val
		echo "<SCR"."IPT type='text/javascript' defer>";
		echo "function ".$nameRB."getCol(col,val) {";
		// val is the value to look for in the array to get the indexOf
		echo " var ndx=findFormFldIndex(document.getElementsByName(\"".$nameRB."\"),val); ";
		echo " fld=document.getElementById(\"".$nameRB."col\"+col+\"row\"+ndx); ";
		echo " return fld.innerHTML;";
		echo "}";
		echo "function ".$nameRB."selectAll(select) {";
		echo " fld=document.getElementsByName(\"".$nameRB."\"); ";
		echo " for (i=0; i<fld.length; i++) select?fld[i].checked=true:fld[i].checked=false;";
		echo "};";

		echo "var selColour = '#A9F5A9';";
		echo "function rowSelected(obj){
    			if(obj.checked == true){
    				$(obj).parent().parent().children('td').css('background-color',selColour);
    			} else {
    				$(obj).parent().parent().children('td').css('background-color','');
    			}
			}";

		if($rowHighLighting == true){
		  $rowSelectorHigh = ($isMultiple)?('if($(this).children("td").eq(0).children("input:['.$nameRB.']").attr("checked")!=true)'):('');
  		  echo '$(document).ready(function(){
          	$("tbody tr.hlr").hover(
          		function () { '.$rowSelectorHigh.' $(this).children("td").css("background-color","#FCFFB4");},
              	function () { '.$rowSelectorHigh.' $(this).children("td").css("background-color","");}
        	  );
          	});';

  		  if($isMultiple){

  		   echo  '$("tbody tr.hlr").each(function(){
  		   			 if($(this).children("td").eq(0).children("input:['.$nameRB.']").attr("checked")==true){
  		   			   //$(this).css("background-color",selColour);
  		   			   $(this).children("td").css("background-color",selColour);
  		   			 }
  		  		  });';
  		  }
		}
        echo '</script>';
	}

	// all fields are arrays
	// NB !! Beaware that id=OTHdr and may cause error if multiple ids...
	public static function outputTable ($headers,$data,$tdExtraCol,$tdExtraRow) {
		print("<TR class='odd' style='border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>");
		foreach ($headers as $field) {
			print("<TH class='' style='border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>".$field."</TH>");
		}
		print("</TR>");
		// this is repeated to be able to float the heading
		print("<TR id='OTHdr' class='odd' style='display:none;border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>");
		foreach ($headers as $field) {
			print("<TH class='' style='border-bottom:2px; border-bottom-style:solid; border-bottom-color:black;'>".$field."</TH>");
		}
		print("</TR>");

		// limit the list if necessary
		if (isset($_POST["PAGESIZE"])) $postPAGESIZE=$_POST["PAGESIZE"]; else if (isset($_GET["PAGESIZE"])) $postPAGESIZE=$_GET["PAGESIZE"]; else $postPAGESIZE=$_SESSION["up_dps"];
		if ($postPAGESIZE=="") $postPAGESIZE=VAL_GUI_MAX_ROWS_RETURNED;
		if (($postPAGESIZE!="0") && (sizeof($data)>$postPAGESIZE)) {
			 print("<TR><TD colspan=".sizeof($headers)."><span style='color:red'>Warning! Query returned <B>".sizeof($data)."</B> rows. <BR>Only first <B>{$postPAGESIZE}</B> are displayed according to your preferences. Please use filters to further refine list if your store is not showing.</span></TD>");
			 $RS=array_splice($data,0,$postPAGESIZE);
		} else $RS=$data;

		$i=0;
		foreach ($RS as $userRow) {
			$i++;
			if ($i & 1) $class="even"; else $class="odd";
			print("<TR class='".$class."'>");
			$j=0;
			foreach ($userRow as $field) {
				$j++;
				if (isset($tdExtraRow[$i-1])) $tdRow=$tdExtraRow[$i-1]; else $tdRow="";
				if (isset($tdExtraCol[$j-1])) $tdCol=$tdExtraCol[$j-1]; else $tdCol="";
				print("<TD style='border-right-style:solid; border-right-color:#DDDDDD; border-width:1px;' ".$tdCol." ".$tdRow.">".$field."</TD>");
			}
			print("</TR>");
		}
		// detail row
		print("<TR ><TD colspan='".(sizeof($headers)+1)."' style='border-top-style:solid; border-color:white; border-width:1px; '>".sizeof($RS)." row(s) found.</TD></TR>");
	}

	// output step icons
	public static function getSteps($labelsArr)  {
		global $ROOT; global $PHPFOLDER;
		print("<CENTER>");
		print("<div id='intervals' style='background:#fcffb4;border:1px solid #F7D358;padding:4px 0px;margin:5px 0px 15px 0px;'>");
		print("<table class='tableReset'>");
		print("<tr class=''>");
		$i=0;
		foreach ($labelsArr as $label) {
			$i++;
			if ($i & 1) $class="even"; else $class="odd";
			print("<td class=''><table class='tableReset tabDivText'><tr><td>".$label."</td><td><div style='width:35px;' class='tabDivStep' id='tds".$i."' onclick='toggleSteps(".$i.",\"".$ROOT.$PHPFOLDER."\");' onmouseover='$(this).css({cursor:\"hand\"});'></div></td></tr></table></td>");
		}
		print("</tr>");
		print("</table>");
		print("</div>");
		print("</CENTER>");

	}

	// output tab icons
	public static function getTabs($userId,$entityId,$menuId,$menuSubFilter,$dbConn)  {


          global $ROOT, $PHPFOLDER;
          include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');


          if (!isset($_SESSION)) session_start();
          $systemId = $_SESSION['system_id'];
          $tabsDiv = 'tabDiv_'.$menuId;

          $administrationDAO = new AdministrationDAO($dbConn);
          $menuActionsArr = $administrationDAO->getUserMenuActions($userId,$systemId,$entityId,$menuId,$menuSubFilter);

          if(count($menuActionsArr)==0){
            echo 'no menu items found!';
            return;
          }

          echo '<div id="tracking-tabs" class="tracking-tabs-container '.$tabsDiv.'">'; //append menu id for multiple menu tabs on the same screen.
            echo '<ul>';

            foreach ( $menuActionsArr as $row ) {

              //parse url.
              $url = str_replace(array(".\$ROOT", "\$ROOT", ".\$PHPFOLDER", "\$PHPFOLDER"),
                                 array($ROOT, $ROOT, $PHPFOLDER, $PHPFOLDER),
                                 $row['url']);

              echo '<li>' .
                      '<a href="javascript:;" onClick=\'' . $url . '\' id="navid_' . $row['uid'] . '">' .
                        GUICommonUtils::systemParseMenuItem($row['description']) .  //parse description/title, using system naming conventions.
                      '</a>' .
                   '</li>';
            }

            echo  '</ul>';
          echo '</div>';

          ?>
          <SCRIPT type='text/javascript'>

            $(document).ready(function(){

              $("a").click(function(){
                $('.<?php echo $tabsDiv ?> li').removeClass("active");  //remove all active
                $(this).parent('li').addClass('active');  //high the selected menu item.
              });

            });

          </SCRIPT>
          <?php
	}

	// $menuSubFilter passed as eg. "1,2,5" to filter menu uids further
	public static function getActionsMenu($userId,$entityId,$menuId,$menuSubFilter,$actionBtnCallback,$default,$dbConn) {

                global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
                if (!isset($_SESSION)) session_start();
                $systemId = $_SESSION['system_id'];

		$administrationDAO = new AdministrationDAO($dbConn);
		$menuActionsArr = $administrationDAO->getUserMenuActions($userId,$systemId,$entityId,$menuId,$menuSubFilter);

		echo "<TABLE width=\"400\" cellpadding='0' cellspacing='5'>\n";
		echo "<tr class=\"odd\" align=\"left\"><td>\n";
		echo "<td>Select action:</td>\n";
		echo "<td><SELECT id='menu_uid' name='menu_uid'>";
		foreach ( $menuActionsArr as $row ) {
		      echo "<OPTION value='".$row['uid']."'";
		      if ($default==$row['uid']) echo " SELECTED";
		      echo ">".$row['description']."</OPTION>\n";
		}

		echo "</select>\n</td>\n";
		echo "<td><input type='button' value='Action' class='submit' onclick='".$actionBtnCallback."'/></td></tr>\n";
		echo "</table>\n";
	}

	// array or RS $dbConn
	public static function outputRS($RS) {
		echo "<style>
				.lineHighlight { background: #fcf8c0;}
			  </style>";

		$rowStyle="even";
		echo "<TABLE style='font-family:calibri; font-size:11' cellspacing=\"0\">";
		$i=0;
		$arr=array();
		if (!is_array($RS)) {
			while ($row=mysql_fetch_assoc($RS->dbQueryResult)) {
				$arr[]=$row;
			}
		} else $arr=$RS;

		foreach ($arr as $row) {
			// output headers
			if ($i==0) {
				echo "<TR class='".GUICommonUtils::styleEO($rowStyle)."'>";
				foreach ($row as $key=>$flds) {
					echo "<TD><B>".$key."</B></TD>";
				}
				echo "</TR>";
				$i++;
			}
			// output data lines
			$rowStyle=GUICommonUtils::styleEO($rowStyle);
			echo "<TR class='{$rowStyle}' onmouseover='this.setAttribute(\"className\", \"lineHighlight\");'
										  onmouseout='this.setAttribute(\"className\", \"{$rowStyle}\");'>";
			foreach ($row as $flds) {
				echo "<TD>".$flds."</TD>";
			}
			echo "</TR>";
		}
		echo "</TABLE>";
	}

	//set styling for class / odd or even.
    public static function styleEO(&$v){
      $v = ($v=='odd') ? ('even') : ('odd');
      return $v;
    }


    //CSS3 Blocks
    public static function outputBlkBlue($innerHTML,$width=false,$height=false) {

     echo '<div style="font-size:0.8em;display:block;',($width != false)?('width:'.$width.'px'):(''),';background:WhiteSmoke;
                       border:1px solid DarkGray;
                       -webkit-border-radius:6px;
                       -moz-border-radius:6px;
                       border-radius:6px;
                       -webkit-box-shadow: 0px 0px 8px rgba(0,0,0,.3);
                       -moz-box-shadow: 0px 0px 8px rgba(0,0,0,.3);
                       box-shadow: 0px 0px 8px rgba(0,0,0,.3);
                       font-family:Verdana,Arial,Helvetica,sans-serif;">
            <div style="border:1px solid #fff;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;">
            <div style="',($height != false)?('height:'.$height.'px'):(''),';border:1px solid silver;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;padding:10px 10px 10px 10px;">';
     echo $innerHTML.'</div></div></div>';

    }

    public static function outputBlkGreen($innerHTML,$width=false,$height=false) {

    	echo '<div style="font-size:0.8em;display:block;',($width != false)?('width:'.$width.'px'):(''),';background:#85c86e;color:#fff;border:1px solid #1e570a;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow: 0px 0px 8px rgba(0,0,0,.3);-moz-box-shadow: 0px 0px 8px rgba(0,0,0,.3);box-shadow: 0px 0px 8px rgba(0,0,0,.3);font-family:Verdana,Arial,Helvetica,sans-serif;">
		<div style="border:1px solid #fff;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;">
		<div style="',($height != false)?('height:'.$height.'px'):(''),';border:1px solid #4FA92F;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;padding:10px 10px 10px 10px;">';
    	echo $innerHTML.'</div></div></div>';

    }

    public static function outputBlkRed($innerHTML,$width=false,$height=false) {

    	echo '<div style="font-size:0.8em;display:block;',($width != false)?('width:'.$width.'px'):(''),';background:#db7373;color:#fff;border:1px solid #9d1b1b;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow: 0px 0px 8px rgba(0,0,0,.3);-moz-box-shadow: 0px 0px 8px rgba(0,0,0,.3);box-shadow: 0px 0px 8px rgba(0,0,0,.3);font-family:Verdana,Arial,Helvetica,sans-serif;">
		<div style="border:1px solid #fff;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;">
		<div style="',($height != false)?('height:'.$height.'px'):(''),';border:1px solid #cd3b3b;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;padding:10px 10px 10px 10px;">';
    	echo $innerHTML.'</div></div></div>';

    }

    public static function outputBlkInobtrusive($innerHTML,$width=false,$height=false) {

      echo '<div style="opacity:0.3;font-size:0.8em;display:block;',($width != false)?('width:'.$width.'px'):(''),';background:#e0e5d9;color:#fff;border:1px solid #adafaa;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow: 0px 0px 8px rgba(0,0,0,.3);-moz-box-shadow: 0px 0px 8px rgba(0,0,0,.3);box-shadow: 0px 0px 8px rgba(0,0,0,.3);font-family:Verdana,Arial,Helvetica,sans-serif;"
                 onmouseover="$(this).css(\'opacity\',\'1\');"
                 onmouseout="$(this).css(\'opacity\',\'0.3\');">
    <div style="border:1px solid #fff;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;">
    <div style="',($height != false)?('height:'.$height.'px'):(''),';border:1px solid #adafaa;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;padding:10px 10px 10px 10px;">';
      echo $innerHTML.'</div></div></div>';

    }

    public static function showHideField($prefArr, $field, &$class = false, $inline = false, $return = true){

      $s = '';

        foreach($prefArr as $arr){
          if(in_array($field, $arr)){
            self::styleEO($class);
            if($inline){
              $s = ';display:none;background:red;';
            } else {
              $s = ' style="display:none;"';
            }
            break;
          }
        }

      if($return){
        return $s;
      } else {
        echo $s;
      }


    }


    public static function translateUploadError($error_code) {

      switch ($error_code) {
          case UPLOAD_ERR_INI_SIZE:
              return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
          case UPLOAD_ERR_FORM_SIZE:
              return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
          case UPLOAD_ERR_PARTIAL:
              return 'The uploaded file was only partially uploaded';
          case UPLOAD_ERR_NO_FILE:
              return 'No file was uploaded';
          case UPLOAD_ERR_NO_TMP_DIR:
              return 'Missing a temporary folder';
          case UPLOAD_ERR_CANT_WRITE:
              return 'Failed to write file to disk';
          case UPLOAD_ERR_EXTENSION:
              return 'File upload stopped by extension';
          default:
              return 'Unknown upload error';
      }
    }


    public static function systemParseMenuItem($str){

      global $ROOT, $PHPFOLDER;
      include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
      CommonUtils::getSystemConventions();

      $m = array();
      @preg_match_all('/\[@SNC::(\w+)\]/', $str, $m);
      $a = array();
      if(count($m)>0 && isset($m[0]) && isset($m[1])){
        foreach($m[0] as $i=>$rstr){
          $str = str_replace($rstr, @constant("SNC::{$m[1][$i]}"), $str);
        }
      }
      return $str;
    }


    public static function systemFieldPreferenceFilter($screen, $systemId, $principalId, &$filterListUsageArr, &$filterListSizeArr, &$columnArr, &$dataArr){

      global $ROOT, $PHPFOLDER, $dbConn;
      include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
      $adminDAO = new AdministrationDAO($dbConn);
      $fldPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, $screen);

      $x = '';
      $no = 1;
      foreach($columnArr as $key => $col){

        if(GUICommonUtils::showHideField($fldPref, $key, $x, false)!=''){

          unset($columnArr[$key]);

          if($filterListUsageArr != false && isset($filterListUsageArr[$no])){
            unset($filterListUsageArr[$no]);
            $filterListUsageArr = array_values(array(0=>'',)+$filterListUsageArr);
            unset($filterListUsageArr[0]);
          }

          if($filterListSizeArr != false && isset($filterListSizeArr[$no])){
            unset($filterListSizeArr[$no]);
            $filterListSizeArr = array_values(array(0=>'',)+$filterListSizeArr);
            unset($filterListSizeArr[0]);
          }

          if(isset($dataArr[0][$key])){
            foreach($dataArr as $k => $row){
              unset($dataArr[$k][$key]);
            }
          }


        }
        $no++;

      }

      return true;

    }




}
?>
