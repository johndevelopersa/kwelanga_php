<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');


class DatePickerElement {


  public static function getDatePicker($tagId, $defaultValue, $disabled = false, $desktop=true) {

    global $ROOT, $DHTMLROOT, $PHPFOLDER;
    include_once ($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

    $disabledTag = ($disabled===true) ? (' disabled ') : ('');
 
    echo '<span id="dId_',$tagId,'">';  //surrounding element for hide/show.

    // NB!! The fPopCalendar must have the field passed as parameter using this parentNode method and not getElementById because you could have many of these fields on form with same name/id and need to reference it uniquely
    // im not sure how it would handle if the date field was not in a table cell (parentNode) (2011-05-12 ~ it puts value into 1st field)
    // NB!! Also ONLY works if there is ONE Input box per cell due to [0] reference. If this problem occurs in future, then include table tags in this function !
    // it becomes a problem when same name is repeated, and within each table cell there are multiple

    if($disabled !== true){
      if ($desktop) {
        echo '<input id="',$tagId,'" name="',$tagId,'" type="text" size="10" maxlength="10" value="',$defaultValue,'" onchange="" '.$disabledTag.'>';
        echo "&nbsp;<A href='javascript:' onclick='fld1=document.getElementsByName(\"".$tagId."\"); fld2=this.parentNode.getElementsByTagName(\"input\"); if ((fld1.length>1) && (fld2.length>1)) alert(\"Warning: More than one date element per cell detected! Will result in incorrect value passback.\"); if (fld1.length==1) passbackFld=fld1[0]; else passbackFld=fld2[0]; fPopCalendar(passbackFld);'>
              <img src='".$DHTMLROOT.$PHPFOLDER."images/calendar-icon.gif' width='16px' height='16px' alt='' border='0' style='margin-bottom:-4px;' />
              </A>";
      } else {
        echo "<A href='javascript:' onclick='fld1=document.getElementsByName(\"".$tagId."\"); fld2=this.parentNode.getElementsByTagName(\"input\"); if ((fld1.length>1) && (fld2.length>1)) alert(\"Warning: More than one date element per cell detected! Will result in incorrect value passback.\"); if (fld1.length==1) passbackFld=fld1[0]; else passbackFld=fld2[0]; fPopCalendar(passbackFld);'>
              <img src='".$DHTMLROOT.$PHPFOLDER."images/calendar-icon.gif' width='".(($desktop)?"16":"24")."px' height='".(($desktop)?"16":"24")."px' alt='' border='0' style='margin-bottom:-4px;' />
              </A>
              <br><br>
              <input id='{$tagId}' name='{$tagId}' type='text' style='width:100%' maxlength='10' value='{$defaultValue}' onchange='' {$disabledTag}>";
      }
    }
    echo '</span>';
  }


  public static function getDatePickerLibs() {

    global $DHTMLROOT, $PHPFOLDER;

    echo '<link rel="stylesheet" href="',$DHTMLROOT,$PHPFOLDER,'css/cwcalendar.css" type="text/css" media="all" />';
    echo '<script type="text/javascript" defer>';
    echo 'var formatSplitter = "-";';
    echo 'var monthFormat = "mm";';
    echo 'var yearFormat = "yyyy";';
    echo 'var itype = "strict";'; //enforce range
    echo '</script>';
    echo '<script type="text/javascript" src="',$DHTMLROOT,$PHPFOLDER,'js/calendar.js" defer></script>';
    
  }


  public static function getToday() {

    return date(GUI_PHP_DATE_FORMAT);
  }

}
?>
