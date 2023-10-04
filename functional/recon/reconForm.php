<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$userId = $_SESSION["user_id"];

$postOPTF1_HASHEADER=((isset($_POST["p_OPTF1_HASHEADER"]))?$_POST["p_OPTF1_HASHEADER"]:"Y");
$postOPTF2_HASHEADER=((isset($_POST["p_OPTF2_HASHEADER"]))?$_POST["p_OPTF2_HASHEADER"]:"Y");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$administrationDAO = new AdministrationDAO($dbConn);
$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_ELECTRONIC_RECONCILIATION);
if (!$hasRole) {
  echo 'You do not have permissions for Electronic Reconciliation';
  return;
}

// IE requires a doctype for display:table-cell to work
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
      <html>
      <head>

        <script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>
        <script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery-ui.1.10.3.min.js'></script>
	      <script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
	      <LINK href='".$ROOT.$PHPFOLDER."css/1_default.css' rel='stylesheet' type='text/css'>

        <style>
        table {font-family:verdana; font-size:12px;}
        td { border-right:1px dotted gray; }
	      .droppable-hover {
	       background-color:#70b440;
	      }
	      .droppable-active {
	       background-color:#70b440;
	       opacity:0.4;
	       filter:alpha(opacity=40); /* For IE8 and earlier */
	      }
	      .droppable-linked {
	       background-color: #70b440;
	       font-size:9px;
	       padding:3px;
	       text-align:center;
	      }
	      .center-objects {
	        /* Internet Explorer 10 */
          display:-ms-flexbox;
          -ms-flex-pack:center;
          -ms-flex-align:center;

          /* Firefox */
          display:-moz-box;
          -moz-box-pack:center;
          -moz-box-align:center;

          /* Safari, Opera, and Chrome */
          display:-webkit-box;
          -webkit-box-pack:center;
          -webkit-box-align:center;

          /* W3C */
          display:box;
          box-pack:center;
          box-align:center;
	      }
        </style>
      </head>
      <body style='font-family:verdana; font-size:12px; padding:10px;'>";

echo "<p class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <span style='font-size:14px;color:#004470;font-weight:bold;'>Online Electronic Reconciliation</span><br>
      <span style='font-size:11px;color:#1858a8;'>This Reconciliation allows you to essentially reconcile any two CSV files at the click of a button!</span>
      </p>";

$file1Exists=false;
$file1=$ROOT.$PHPFOLDER."uploads/recon/p{$principalId}_u{$userId}_recon1.csv";
if (file_exists($file1)) {
  $file1Exists=true;
  $size = filesize($file1);
  $fileMDate = date("Y-m-d H:i:s",filemtime($file1));
  $fileExistsText1 = "File is present on server with size:{$size} bytes ; last modified : {$fileMDate}";
} else $fileExistsText1="";

$file2Exists=false;
$file2=$ROOT.$PHPFOLDER."uploads/recon/p{$principalId}_u{$userId}_recon2.csv";
if (file_exists($file2)) {
  $file2Exists=true;
  $size = filesize($file2);
  $fileMDate = date("Y-m-d H:i:s",filemtime($file2));
  $fileExistsText2 = "File is present on server with size:{$size} bytes ; last modified : {$fileMDate}";
} else $fileExistsText2="";


echo "<div class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <p class='rounded-corners' style='text-align:left;background-color:#004470; padding:3px;padding-left:10px;color:white'>
      1. Please choose first source File:
      </p>

      <p style='text-align:left;'>
      <a href='#' onclick='uploadFile(1);'>[UPLOAD CSV FILE]</a>
      <span style='color:#999999;'>&nbsp;&nbsp;<i>{$fileExistsText1}</i></span>
      </p>

      <p style='text-align:left;'>
      File has Header in row 1 :
      <input type='radio' name='p_OPTF1_HASHEADER' value='Y' ".(($postOPTF1_HASHEADER=="Y")?"checked='checked'":"")." >Yes
      <input type='radio' name='p_OPTF1_HASHEADER' value='N' ".(($postOPTF1_HASHEADER=="N")?"checked='checked'":"")." >No
      </p>

      </div>";

echo "<br>";

echo "<div class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <p class='rounded-corners' style='text-align:left;background-color:#004470; padding:3px;padding-left:10px;color:white'>
      2. Please choose second source File:
      </p>

      <p style='text-align:left;'>
      <a href='javascript:uploadFile(2)'>[UPLOAD CSV FILE]</a>
      <span style='color:#999999;'>&nbsp;&nbsp;<i>{$fileExistsText2}</i></span>
      </p>

      <p style='text-align:left;'>
      File has Header in row 1 :
      <input type='radio' name='p_OPTF2_HASHEADER' value='Y' ".(($postOPTF2_HASHEADER=="Y")?"checked='checked'":"")." >Yes
      <input type='radio' name='p_OPTF2_HASHEADER' value='N' ".(($postOPTF2_HASHEADER=="N")?"checked='checked'":"")." >No
      </p>

      </div>";

echo "<br>";

echo "<div class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <p class='rounded-corners' style='text-align:left;background-color:#004470; padding:3px;padding-left:10px;color:white'>
      3. Link the columns :
      </p>

      <p>
      <span style='font-size:11px;color:#1858a8;'>Drag the field from the left column to the right column to Link<br>
                                                  Double-Click on the LHS tag to remove a link.<br>
                                                  You can also define the column join by clicking on the <img src= " . HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER."images/gear-small.png></span>
      </p>";

$file1FirstLineArr=$file2FirstLineArr=array();
if ($file1Exists && $file2Exists) {
  $file1Array = explode("\n",file_get_contents($file1));
  $file2Array = explode("\n",file_get_contents($file2));
  $file1FirstLineArr = str_getcsv(
                                  $file1Array[0], # Input line
                                  ',',   # Delimiter
                                  '"',   # Enclosure
                                  '\\'   # Escape char
                                 );
  $file2FirstLineArr = str_getcsv(
                                  $file2Array[0], # Input line
                                  ',',   # Delimiter
                                  '"',   # Enclosure
                                  '\\'   # Escape char
                                 );

  echo "<div id='canvasDiv'
             style='white-space:nowrap;'
             class='center-objects'>";
  // first column
  echo "<div id='lhsColumn' class='rounded-corners' style='display:table-cell;background-color:#1858a8;padding:15px;'>";
  $i=0;
  foreach ($file1FirstLineArr as $key=>$f) {
    // we need to record the initial position obj as the animation messes around with the position css when we need to get it
    echo "<div id='lhsCol{$i}'
               colindex='{$i}'
               class='rounded-corners draggable'
               style='height:45px;background-color:white; cursor:hand; padding:10px;text-align:left;'
               ondblclick='joins.removeLink($(this));'>
          <div style='white-space:nowrap;'><div class='eye-icon' title='Display this column on Report' onclick='toggleEye(this,{$i},\"LHS\",\"Auto\");' style='display:table-cell;background-image:url(".HOST_SURESERVER_AS_NEWUSER."{$PHPFOLDER}images/eye-small.png);background-repeat:no-repeat;width:22px;height:16px;'></div><div style='display:table-cell' class='copy-me'>&nbsp;&nbsp;".($key+1).". {$f}</div></div>
          <div class='link rounded-corners'></div>
          </div><br>";
    $i++;
  }
  echo "</div>";

  echo "<div style='display:table-cell;width:200px;background-color:transparent;'>&nbsp;</div>";

  // second column
  echo "<div class='rounded-corners' style='display:table-cell;background-color:#1858a8;padding:15px;'>";
  $i=0;
  foreach ($file2FirstLineArr as $key=>$f) {
    echo "<div  id='rhsCol{$i}'
                colindex='{$i}'
                class='rounded-corners droppable' style='height:45px;background-color:white; padding:10px;text-align:left;'>
          <div style='white-space:nowrap;'><div class='eye-icon' title='Display this column on Report' onclick='toggleEye(this,{$i},\"RHS\",\"Auto\");' style='display:table-cell;background-image:url(".HOST_SURESERVER_AS_NEWUSER."{$PHPFOLDER}images/eye-small.png);background-repeat:no-repeat;width:22px;height:16px;'></div><div style='display:table-cell' class='copy-me'>&nbsp;&nbsp;".($key+1).". {$f}</div></div>
          <div class='link rounded-corners'></div>
          </div><br>";
    $i++;
  }
  echo "</div>";

  echo "</div>";

} else {
  echo "<span style='font-size:11px;color:#1858a8;'>Please complete steps 1 & 2 above to continue...</span>";
}

echo "</div>";


echo "<br>";

echo "<div class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <p class='rounded-corners' style='text-align:left;background-color:#004470; padding:3px;padding-left:10px;color:white'>
      4. Run Reconciliation:
      </p>

      <p style='text-align:left;' class='center-objects' >
      <input type='submit' class='submit' value='Run Reconciliation' onclick='submitRecon();'>
      </p>

      </div>";

// this is necessary so you can call window.open and POST with it not GET
// form target must be same as in window.open call
echo "<form id='postForm' method='post' action='".HOST_SURESERVER_AS_USER. "systems/kwelanga_system/" .$PHPFOLDER."functional/recon/reconSubmit.php' target='myRecon'>
      <input type='hidden' id='p_JSONJOINS' name='p_JSONJOINS' value='' />
      </form>";

?>

<script type='text/javascript' defer>
var canvas;
$(document).ready(function(){
  assignDragDrop();

  // initialise settings for every col
  <?php
  foreach ($file1FirstLineArr as $key=>$col) {
   echo "colConfigs.lhs[{$key}]=new ColConfigTO();";
  }
  foreach ($file2FirstLineArr as $key=>$col) {
    echo "colConfigs.rhs[{$key}]=new ColConfigTO();";
  }
  ?>

});
// once the background-image is set to canvas you can't draw on it so you need to keep recreating it for all mods
// this does not at the moment cater for screen resizing - the canvas lines become detached.
function draw() {
  $(canvas).remove();
  canvas = document.createElement("canvas");

  // setting the attr sizes are necessary so that the background image is not stretched (and pixel positions get distorted)
  // and so thyat the default size doesnt apply which is too small and hides the content therefore
  canvas.width = ($('#canvasDiv').css('width')).replace('px','');
  canvas.height = ($('#canvasDiv').css('height')).replace('px','');
  var ctx = canvas.getContext("2d");

  for (var i=0; i<joins.length; i++) {

    if (joins[i]==undefined || joins[i].link=="undefined" ||
        joins[i].link==undefined || joins[i].link=="undefined" ||
        joins[i].link.isSet==false) continue;

    // position() returns absolute position and we want position within div so subtract (css.left returns auto unfortunately)
    var fromPos = joins[i].link.fromObj.position(),
        toPos = $(joins[i].link.toObj).position(),
        canvasDiv = $('#canvasDiv').position(),
        draggableWidth = $(joins[i].link.fromObj).outerWidth(),
        halfHeight = $(joins[i].link.fromObj).outerHeight()/2,
        connectorLength = 20,
        lhsColumnPadding = ($('#lhsColumn').outerWidth() - draggableWidth)/2,
        lhsX = (fromPos.left-canvasDiv.left)+draggableWidth+lhsColumnPadding,
        lhsY = (fromPos.top-canvasDiv.top)+halfHeight,
        rhsX = (toPos.left-canvasDiv.left)-lhsColumnPadding,
        rhsY = (toPos.top-canvasDiv.top)+halfHeight;

    ctx.beginPath(); // is necessary as otherwise when you set the line color later then everything drawn gets changed!
    ctx.moveTo(lhsX,lhsY);
    // if the connector line is slightly detached then increase the setTimeout in the assignDragDrop() func
    ctx.lineTo(lhsX+connectorLength,lhsY);
    ctx.lineTo(rhsX-connectorLength,rhsY);
    ctx.lineTo(rhsX,rhsY);

    // set the styles for everything since the last beginPath()
    ctx.lineWidth=2;
    ctx.strokeStyle = ((joins[i].link.joinType=="REQUIRED")?'green':'black');
    ctx.stroke();

  }

  $('#canvasDiv').css({'background-image':"url(" + canvas.toDataURL("image/png")+ ")", 'background-repeat':'no-repeat' });
 }
function uploadFile(fileNdx){
  parent.popBox('<div align="center" id="fileUpload" style="color:#444;"></div>','general');
  AjaxRefreshHTML("TYPE=RECONFILE&FILENDX="+fileNdx,
                  '<?php echo $ROOT.$PHPFOLDER ?>functional/general/uploadFile.php',
                  'fileUpload',
                  'Please wait while request is processed...',
                  '');
}
function refreshMe() {
  // ...
  parent.change_iframe_content('<?php echo $_SERVER["PHP_SELF"]?>');
}
function assignDragDrop() {
  $(function() {
    $( ".draggable" ).draggable({ revert: true });

    $( ".droppable" ).droppable({
      activeClass: "droppable-active",
      hoverClass: "droppable-hover",
      tolerance: "pointer",
      drop: function( event, ui ) {
        fromObj = ui.helper;
        toObj = $(this);

        joins.addLink(fromObj,toObj);

        // we need a delay because of animation affecting left and top css and it has a slight variance in outerWidth calc otherwise
        setTimeout(function(){draw();},750);
      }
    });
  });
}

// ColConfigs are initialised one for every column on page load
var ColConfigTO = function() {
  this.displayOnResult = 'N';
}
var colConfigs = {
    lhs:[],
    rhs:[]
    }; // store individual col settings not related to joins

var JoinFormatTO = function(){
  this.ignoreLeadingZeros = 'N';
  this.ignoreSign = 'N';
  this.fieldType = 'TEXT';
  this.consolidate = 'N';
  this.dateFormat = 'YYYY-MM-DD';
}
var LinkTO = function(){
  this.isSet=false;
  this.fromObj=false;
  this.toObj=false;
  this.fromCol=false;
  this.toCol=false;
  this.joinType = 'REQUIRED';
  this.priority = 1;
  this.joinFormat = {
                     lhs : new JoinFormatTO(),
                     rhs : new JoinFormatTO()
                    };
}
var JoinTO = function() {
  this.link = new LinkTO();
}
var joins = []; // store LHS column settings
joins.initialise = function (ndx) {
  // rem JS array fill in gaps in the index range and .length excludes non numeric indexes/functions
  if (
      (joins[ndx]>=joins.length) ||
      (joins[ndx]==undefined) ||
      (joins[ndx]=="undefined")
     ) {
    jTO = new JoinTO();
    joins[ndx]=jTO;
  }
}
joins.addLink = function (fromObj,toObj) {
  var fromNdx = parseInt($(fromObj).attr('colindex'),10),
      toNdx = parseInt($(toObj).attr('colindex'),10);
  joins.initialise(fromNdx);
  if((joins[fromNdx].link.isSet==true) || (joins.rhsIsLinked(toNdx))) {
    alert('Atleast one of the columns on either side are already linked!');
  } else if (joins[fromNdx].link.isSet==false) {

    var fromT = $(fromObj).find('.copy-me').html(),
        toT = $(toObj).find('.copy-me').html();
    $( toObj )
      .find( ".link" )
        .addClass("droppable-linked")
        .html( "<a href='#' onclick='showSettings("+toNdx+",\"RHS\");'><img src='<?php echo HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER ?>images/gear-small.png'></a>&nbsp;&nbsp;Linked:"+fromT )
        .find(".do-not-copy")
          .remove();
    $( fromObj )
      .find( ".link" )
        .addClass("droppable-linked")
        .html( "<a href='#' onclick='showSettings("+fromNdx+",\"LHS\");'><img src='<?php echo HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER ?>images/gear-small.png'></a>&nbsp;&nbsp;Linked:"+toT )
        .find(".do-not-copy")
        .remove();

    joins[fromNdx].link.isSet = true;
    joins[fromNdx].link.fromObj = fromObj;
    joins[fromNdx].link.toObj = toObj;
    joins[fromNdx].link.fromCol = fromNdx;
    joins[fromNdx].link.toCol = toNdx;

    toggleEye($(fromObj).find('.eye-icon'),fromNdx,'LHS','On');
    toggleEye($(toObj).find('.eye-icon'),toNdx,'RHS','On');

  }
}
joins.removeLink = function (lhsObj) {
  var ndx = parseInt($(lhsObj).attr('colindex'),10);
  joins.initialise(ndx);
  // rem JS array fill in gaps in the index range and .length excludes non numeric indexes/functions
  if (joins[ndx].link.isSet==false) {
    return;
  } else {
    $( joins[ndx].link.fromObj )
      .find( ".link" )
        .removeClass("droppable-linked")
        .html("");
    $( joins[ndx].link.toObj )
      .find( ".link" )
        .removeClass("droppable-linked")
        .html("");
    joins[ndx].link = new LinkTO();
    draw();
  }
}
joins.rhsIsLinked = function (rhsColIndex) {
  for (var i=0; i<joins.length; i++) {
    if (
        (joins[i]==undefined) ||
        (joins[i]=="undefined") ||
        (joins[i].link.isSet==false)
       ) {
      continue;
    } else if(joins[i].link.isSet==true &&
              joins[i].link.toCol==rhsColIndex) {
      return true;
    }
  }
  return false;
}
joins.getLinkedLHSColIndex = function (rhsColIndex) {
  for (var i=0; i<joins.length; i++) {
    if (
        (joins[i]==undefined) ||
        (joins[i]=="undefined") ||
        (joins[i].link.isSet==false)
       ) {
      continue;
    } else if(joins[i].link.toCol==rhsColIndex) {
      return i;
    }
  }
  return -1;
}

var objRef; // used so that settings popup can access LHS or RHS of object for index
function showSettings(colIndex,side) {
  var lhsIndex, rhsIndex;
  if (side=='LHS') {
    lhsIndex = colIndex;
    if (joins[lhsIndex].link.isSet==false) {
      alert('You can only specify join settings if the column is linked');
      return;
    }
    rhsIndex = joins[lhsIndex].link.toCol;
  } else {
    rhsIndex = colIndex;
    lhsIndex = joins.getLinkedLHSColIndex(rhsIndex);
    if (lhsIndex==-1) {
      alert('Error : could not getLinkedLHSColIndex('+colIndex+':'+rhsIndex+')');
      return;
    }
    if (joins.rhsIsLinked(colIndex)==false) {
      alert('You can only specify join settings if the column is linked');
      return;
    }
  }
  joins.initialise(lhsIndex);

  var ignoreLeadingZeros,
      joinType,
      priority,
      fieldType,
      consolidate,
      dateFormat;
  if (side=='LHS') {
    objRef = joins[lhsIndex].link.joinFormat.lhs;
  } else {
    objRef = joins[lhsIndex].link.joinFormat.rhs;
  }
  ignoreLeadingZeros = objRef.ignoreLeadingZeros;
  joinType = joins[lhsIndex].link.joinType;
  priority = joins[lhsIndex].link.priority;
  fieldType = objRef.fieldType;
  consolidate = objRef.consolidate;
  dateFormat = objRef.dateFormat;
  ignoreSign = objRef.ignoreSign;

  var content = 'Join Type : '+
                '<input type="radio" name="f_JOINTYPE" onclick="parent.content.joins['+lhsIndex+'].link.joinType=\'REQUIRED\';parent.content.draw();" value="REQUIRED"'+((joinType=='REQUIRED')?'checked="checked"':'')+' >Required as Minimum Link'+
                '<input type="radio" name="f_JOINTYPE" onclick="parent.content.joins['+lhsIndex+'].link.joinType=\'SECONDARY\';parent.content.draw();" value="SECONDARY"'+((joinType=='SECONDARY')?'checked="checked"':'')+' >Secondary for value comparison'+

                '<br><br>Secondary priority : '+
                '<input type="radio" name="f_PRIORITY" onclick="parent.content.joins['+lhsIndex+'].link.priority=1;" value="1"'+((priority==1)?'checked="checked"':'')+' >1'+
                '<input type="radio" name="f_PRIORITY" onclick="parent.content.joins['+lhsIndex+'].link.priority=2;" value="2"'+((priority==2)?'checked="checked"':'')+' >2'+
                '<input type="radio" name="f_PRIORITY" onclick="parent.content.joins['+lhsIndex+'].link.priority=3;" value="3"'+((priority==3)?'checked="checked"':'')+' >3'+
                '<input type="radio" name="f_PRIORITY" onclick="parent.content.joins['+lhsIndex+'].link.priority=4;" value="4"'+((priority==4)?'checked="checked"':'')+' >4'+
                '<input type="radio" name="f_PRIORITY" onclick="parent.content.joins['+lhsIndex+'].link.priority=5;" value="5"'+((priority==5)?'checked="checked"':'')+' >5'+

                '<hr>'+

                '<br><br>Ignore Leading Zeros : '+
                '<input type="radio" name="f_IGNORELEADINGZEROS" onclick="parent.content.objRef.ignoreLeadingZeros=\'Y\';" value="Y"'+((ignoreLeadingZeros=='Y')?'checked="checked"':'')+' >Yes'+
                '<input type="radio" name="f_IGNORELEADINGZEROS" onclick="parent.content.objRef.ignoreLeadingZeros=\'N\';" value="Y"'+((ignoreLeadingZeros=='N')?'checked="checked"':'')+' >No'+

                '<br><br>Ignore sign if number : '+
                '<input type="radio" name="f_IGNORESIGN" onclick="parent.content.objRef.ignoreSign=\'Y\';" value="Y"'+((ignoreSign=='Y')?'checked="checked"':'')+' >Yes'+
                '<input type="radio" name="f_IGNORESIGN" onclick="parent.content.objRef.ignoreSign=\'N\';" value="Y"'+((ignoreSign=='N')?'checked="checked"':'')+' >No'+

                '<br><br>Consolide (Sum) for value comparison: '+
                '<input type="radio" name="f_CONSOLIDATE" onclick="parent.content.objRef.consolidate=\'Y\';" value="Y"'+((consolidate=='Y')?'checked="checked"':'')+' >Yes'+
                '<input type="radio" name="f_CONSOLIDATE" onclick="parent.content.objRef.consolidate=\'N\';" value="Y"'+((consolidate=='N')?'checked="checked"':'')+' >No'+

                '<br><br>Field Type : '+
                '<input type="radio" name="f_FIELDTYPE" onclick="parent.content.objRef.fieldType=\'TEXT\';" value="TEXT"'+((fieldType=='TEXT')?'checked="checked"':'')+' >TEXT'+
                '<input type="radio" name="f_FIELDTYPE" onclick="parent.content.objRef.fieldType=\'DATE\';" value="DATE"'+((fieldType=='DATE')?'checked="checked"':'')+' >DATE'+

                '<br><br>Date Format (sep. does not matter) : '+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'YYYY-MM-DD\';" value="YYYY-MM-DD"'+((dateFormat=='YYYY-MM-DD')?'checked="checked"':'')+' >YYYY-MM-DD'+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'YYYY-DD-MM\';" value="YYYY-DD-MM"'+((dateFormat=='YYYY-DD-MM')?'checked="checked"':'')+' >YYYY-DD-MM'+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'YYYY-MON-DD\';" value="YYYY-MON-DD"'+((dateFormat=='YYYY-MON-DD')?'checked="checked"':'')+' >YYYY-MON-DD'+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'DD-MM-YYYY\';" value="DD-MM-YYYY"'+((dateFormat=='DD-MM-YYYY')?'checked="checked"':'')+' >DD-MM-YYYY'+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'DD-MON-YYYY\';" value="DD-MON-YYYY"'+((dateFormat=='DD-MON-YYYY')?'checked="checked"':'')+' >DD-MON-YYYY'+
                '<input type="radio" name="f_DATEFORMAT" onclick="parent.content.objRef.dateFormat=\'MM-DD-YYYY\';" value="MM-DD-YYYY"'+((dateFormat=='MM-DD-YYYY')?'checked="checked"':'')+' >MM-DD-YYYY';

  parent.popBox('<div align="center" id="fileUpload" style="color:#444;">'+
                '<b>( '+side+' ) Join Conditions</b> :<hr><br><br>'+
                content+
                '</div>','general');
}

var alreadySubmitted=false;
function submitRecon() {
  if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;

	var hdr1=convertElementToArray(document.getElementsByName('p_OPTF1_HASHEADER')),
	    hdr2=convertElementToArray(document.getElementsByName('p_OPTF2_HASHEADER'));

  var tempJoins = [];
  for (var i=0; i<joins.length; i++) {
      if (
          (joins[i]==undefined) ||
          (joins[i]=="undefined") ||
          (joins[i].link.isSet==false)
         ) {
        continue;
      }

      tempJoins.push({fromCol:joins[i].link.fromCol,
                  toCol:joins[i].link.toCol,
                  joinType:joins[i].link.joinType,
                  priority:joins[i].link.priority,
                  joinFormat:joins[i].link.joinFormat});
  }
  var struc = {
          config : {hasHeader1:hdr1,hasHeader2:hdr2,colConfigs:colConfigs},
          joins : tempJoins
         }
  json = JSON.stringify({struc: struc}); // only available in later browser versions not <= IE8

  $('#p_JSONJOINS').val(json);
	window.open("","myRecon","scrollbars=yes,width=750,height=600,resizable=yes,status=no"); // window name MUST be same as declared in Form.action for it to work as POST in new window
	document.getElementById('postForm').submit();
	alreadySubmitted=false;

}

function toggleEye(iconEle,colIndex,side,manualSwitch) {
  var obj;
  if (side=='LHS') {
    obj = colConfigs.lhs[colIndex];
  } else {
    obj = colConfigs.rhs[colIndex];
  }

  if (((obj.displayOnResult=="N") && manualSwitch=="Auto") || (manualSwitch=="On")) {
    obj.displayOnResult="Y";
    $(iconEle).css({'background-position':'0px -16px'});
  } else {
    obj.displayOnResult="N";
    $(iconEle).css({'background-position':'0px 0px'});
  }
}

</script>

<?php

echo "</body>
      </html>";
?>