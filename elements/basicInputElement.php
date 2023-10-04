<?php
class BasicInputElement {
	public static function getGeneralFieldInteger($tagId,$value,$type,$dispLength,$limitLength,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {
		$permission=""; $style="text-align:right; ";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		print("<input type='".$type."' id='".$tagId."' style='".$style."' size='".$dispLength."' maxlength='".$limitLength."' value='".$value."' ".$permission."/>");
	}

	public static function getGeneralFieldString($tagId,$value,$type,$dispLength,$limitLength,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {
		$permission=""; $style="";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		print("<input type='".$type."' id='".$tagId."' style='".$style."' size='".$dispLength."' maxlength='".$limitLength."' value='".$value."' ".$permission."/>");
	}

	public static function getGeneralFieldCurrency($tagId,$value,$type,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {
		$permission=""; $style="text-align:right; ";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		print("<input type='".$type."' id='".$tagId."' style='".$style."' size='10' maxlength='10' value='".$value."' ".$permission."/>");
	}

	public static function getGeneralReference($tagId,$value,$type,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {
		$permission=""; $style="";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style="background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		print("<input type='".$type."' id='".$tagId."' style='".$style."' size='45' maxlength='35' value='".$value."' onchange='".$onChange." this.value=this.value.trim();' ".$permission."/>");
	}

	public static function getGeneralHorizontalRB($tagId,$lable,$value,$chosenValue,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {
		$permission=""; $style="";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" disabled=\"disabled\" ";
			$style="background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		$valueArr=explode(",",$value);
		$lableArr=explode(",",$lable);
		echo "<div>";
		for ($i=0; $i<sizeof($valueArr); $i++) {
			echo "<label for='".$tagId.$i."' style='display: block; padding-right: 15px; min-width: 90px; white-space:nowrap; border:0; text-align:left;'>";
			if ($chosenValue==$valueArr[$i]) print("<input type='radio' name='".$tagId."' style='".$style."' value='".$valueArr[$i]."' onchange='".$onChange."' onclick='".$onClick."' ".$permission." CHECKED >&nbsp;".$lableArr[$i]);
			else print("<input type='radio' id='".$tagId.$i."' name='".$tagId."' style='border-style:none;' style='".$style."' value='".$valueArr[$i]."' onchange='".$onChange."' onclick='".$onClick."' ".$permission." >&nbsp;".$lableArr[$i]);
			echo "</label>";
		}
		echo "</div>";
	}

	public static function getGeneralHorizontalCB($tagId,$lable,$value,$chosenValues,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$minWidth) {
		$permission=""; $style="";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" disabled=\"disabled\" ";
			$style="background-color:transparent; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}
		$valueArr=explode(",",$value);
		$lableArr=explode(",",$lable);
		$cvArr=explode(",",$chosenValues);
		echo "<table><tr>";
		for ($i=0; $i<sizeof($valueArr); $i++) {
			echo "<td style='min-width:".$minWidth."; padding-left:0px;'>";
			if (in_array($valueArr[$i],$cvArr)) print("<input type='checkbox' name='".$tagId."' style='".$style."' value='".$valueArr[$i]."' onchange='".$onChange."' ".$permission." CHECKED >&nbsp;".$lableArr[$i]);
			else print("<input type='checkbox' id='".$tagId.$i."' name='".$tagId."' style='border-style:none;' style='".$style."' value='".$valueArr[$i]."' onchange='".$onChange."' ".$permission." >&nbsp;".$lableArr[$i]);
			echo "</td>";
		}
		echo "</tr></table>";
	}


	public static function getCSS3RadioHorizontal($tagId,$label,$value,$chosenValue,$disabled = false,$onChangeJS="", $cssStyleSize = 0) {

	  $valueArr=explode(",",$value);
	  $labelArr=explode(",",$label);

/*
	<a class="btn btn-info">Left</a>
	<a class="btn">Middle</a>
	<a class="btn">Right</a>

 */

          echo '<div class="btn-group '.$tagId.'" data-toggle="buttons-checkbox">';
          $totLabels = count($labelArr);

          if($totLabels == count($valueArr)){

            for ($i=0; $i<$totLabels; $i++) {
              $checked = ($chosenValue==$valueArr[$i])?(true):(false);
                    echo '<a href="javascript:;" class="btn btn-mini ',(!$disabled)?(''):(''),' ',($checked)?((!$disabled)?('btn-info'):('disabled"')):((!$disabled)?(''):('disabled')),'" >'.$labelArr[$i].'<input type="radio" name="'.$tagId.'" value="'.$valueArr[$i].'" ',($checked)?('checked'):(''),' ' , ($disabled)?('disabled'):('') , ' style="display:none;" ></a>';
            }

          } else {
            echo 'ERROR: Label to Values Mismatch!';
          }

          echo '</div>';

          if(!$disabled){
          echo '<div style="clear:both;"></div><!-- clear floats //-->
            <script type="text/javascript" defer>
            function nul(){}
            $(document).ready(function(){
                $(\'.'.$tagId.' a\').click(function(){
                    $(\'.'.$tagId.' a\').removeClass("btn-info");
                    $(this).addClass("btn-info");
                    $(this).children("input").prop("checked",true);
                    '.$onChangeJS.'
                });
            });
            function css3_reset_by_pos_'.$tagId.'(pos) {
              document.getElementsByName("'.$tagId.'")[pos].checked=true;
              $(".'.$tagId.' a").removeClass("btn-info");
              $(".'.$tagId.' a:eq(pos)").addClass("btn-info");
            }
            function css3_reset_by_val_'.$tagId.'(val) {
              var fld=document.getElementsByName("'.$tagId.'");
              for (var i=0; i<fld.length; i++) {
                if (fld[i].value==val) {
                  $(".'.$tagId.' a").removeClass("btn-info");
                  $(".'.$tagId.' a:eq("+i+")").addClass("btn-info");
                  fld[i].checked=true;
                }
              }
            }
            </script>';
          }
	}

}
?>
