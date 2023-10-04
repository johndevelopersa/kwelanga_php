<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// get the overhead top level services
// Roles can be loaded under multiple principals, so is necessary to do distinct select this way otherwise rows are duplicated on menu
//the below is a fix to allow for multiple row checks for a given menu item. This is in preference to redesigning the table ERD, but the tradeoff
// is that roles must be hardcoded if alternative 2ndary exists to check for.
// the roles are concatenated with a comma separator.


echo '<div id="thirdpane" class="menu_list">';
echo '<div id="lvl2popup" ></div>';
echo '<a href="javascript:parent.change_iframe_content(\''.$ROOT.$PHPFOLDER.'functional/main/content.php\');" class="imenu home_icon fleft" title="Home"></a>';

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))? true:false;

if ($adminUser=="Y") {
	$dbConn->dbQuery("select distinct a.uid, a.role_uid mroleid, a.description as description, a.level, -1 role_uid, a.url, a.parent, a.override_path, a.target, -1 user_role_check
						        from   menu_role a
                    inner join system_menu s on a.uid = s.menu_role_uid and s.system_uid = '".$systemId."'
						        where level <= 2
						        group  by 	a.uid, a.description, a.level, a.url, a.parent, a.override_path, a.target
						        order  by   if (a.order is null,999,a.order)");
} else {
	$dbConn->dbQuery("select distinct a.uid, a.role_uid mroleid, a.description as description, a.level, group_concat(b.role_id order by b.role_id) role_uid, a.url, a.parent, a.override_path, a.target, b.user_id user_role_check
						        from   menu_role a
                    inner join system_menu s on a.uid = s.menu_role_uid and s.system_uid = '".$systemId."'
        						left join (select distinct user_id, entity_uid, role_id from user_role) b on b.user_id='".$userId."' and
        								              (b.entity_uid='".$principalId."' or b.entity_uid is null) and
	                                    (a.role_uid=b.role_id or (a.uid=8 and b.role_id=23))
						        where level <= 2
						        group  by 	a.uid, a.description, a.level, a.url, a.parent, a.override_path, a.target, b.user_id
						        order  by   if (a.order is null,999,a.order)");
}
$menu   = $dbConn->dbQueryResult;
$num    = mysqli_num_rows($menu);
$ires   = array();

for ($i=0; $i<$num; $i++) {
	

	  $ires = $dbConn->mysqli_result($menu, $i);
		if ($ires["mroleid"]=="" || ($adminUser=="Y") || (($ires["mroleid"]!="") && ($ires["user_role_check"]!=""))) {
		    $parentNode     = $ires["parent"];
		    $parentNodeName = $ires["description"];

		    if ($parentNode == 0) {
			      $parentNodeUID = $ires["uid"];

			      $hasSubItems=false;
			      for ($j=0; $j<$num; $j++) {
			      	$jres = $dbConn->mysqli_result($menu, $j);
			      	if (($jres['parent']==$parentNodeUID) && (($jres['mroleid'] == "") || (($jres['mroleid']!="") && ($jres['user_role_check']!="")))) { 
			      		  $hasSubItems=true; break; 
			      	}
			      }
			      
			      if (($hasSubItems) || ($ires['url']!="")) {

			         $dbURL = (($ires['override_path']=="")? $ires['url']: $ires['override_path']);

			         // javascript or normal url
			         if (strpos($dbURL, "window.open")===false) {
  				          $rootURL = $ROOT.$PHPFOLDER.$dbURL;
  				          if (strpos($rootURL,"m_id=")===false) {
  					          if (strpos($rootURL,"?")===false) $rootURL.="? m_id=".$parentNodeUID;
  					          else $rootURL.="&m_id=".$parentNodeUID;
  				          } 
			         } else {
			              $rootURL = "javascript:".str_replace(array('"','$ROOT','$PHPFOLDER'),array("'",$ROOT,$PHPFOLDER),$dbURL);
			         }

				       $rootTarget = $ires['target'];

				       echo "<div style='white-space: nowrap;' class=\"fleft\">";
				       echo "<p class=\"menu_head ".((!$hasSubItems)?'no_arrow':'')."\">";

				       // ignore blank URLs to prevent directory listing
				       
				       if (($dbURL=="") || ($dbURL=="#")) echo GUICommonUtils::systemParseMenuItem($parentNodeName)."&nbsp;&nbsp;&nbsp;";
				       else echo "<a href=\"".$rootURL."\" target=\"".$rootTarget."\">" .GUICommonUtils::systemParseMenuItem($parentNodeName)."&nbsp;&nbsp;&nbsp;</a>";

				       echo "</p>";

               if(!$hasSubItems){
                  echo "</div>";
               continue;
               }

				       echo "<div class=\"menu_body\" >";
				       //get child nodes
				       for ($j=0; $j<$num; $j++) {
				       	   $jres = $dbConn->mysqli_result($menu, $j);
				       	   $childNode = $jres['parent'];
					         if (($childNode == $parentNodeUID) && ((($jres['mroleid']!="") && ($jres['user_role_check']!="")) || ($jres['mroleid']==""))) {

						           $childNodeName = $jres['description'];
						           $menuUid = $jres['uid'];
						           if ($jres['override_path']==""){

							             $addURL = $jres['url'];
							             
							             
							             if(strpos($_SERVER['PHP_SELF'],$PHPFOLDER) === false){
								               $childURL = ($_SERVER['REQUEST_SCHEME']??'https') . '://'.$_SERVER['SERVER_NAME'] . '/'.$PHPFOLDER.'/'.$addURL;
							             } else {
								               $childURL = ($_SERVER['REQUEST_SCHEME']??'https') . '://'.$_SERVER['SERVER_NAME'] .substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],$PHPFOLDER)+strlen($PHPFOLDER)).$addURL;
							             }

						           } else {
								          $childURL = $jres['override_path'].$jres['url'];
						           }

						           if (strpos($childURL,"m_id=")===false) {
						            	if (strpos($childURL,"?")===false) {
							              	if($childURL!=''){
								                 $childURL.="?m_id=".$menuUid;
								              }
							            } else {
								              $childURL.="&m_id=".$menuUid;
							            }
						           }
					             $childTarget = $jres['target'];

					            // check if has level 2 sub items
					            $hasLvl2SubItems=false;
					            $parentLvl1Node=$jres['uid'];
                      $subIno = 0;
						          for ($k=0; $k<$num; $k++) {
						          	  $kres = $dbConn->mysqli_result($menu, $k);
                          if ($kres['parent']==$parentLvl1Node){
                              $subIno++;
                          }
							        if (($kres['parent']==$parentLvl1Node) && (($kres['mroleid'] == "") || (($kres['mroleid']!="") && ($kres['user_role_check']!="")))) { 
							        	   $hasLvl2SubItems=true; break; }
                      }

                      //if subcount greater then zero and display is false dont show anything

						          // show level 1
					            if (!$hasLvl2SubItems){
					            	  if($subIno==0){
                            echo "<a class='aaa' href=\"".$childURL."\" target=\"".$childTarget."\"><span style='float:left;'>".GUICommonUtils::systemParseMenuItem($childNodeName)."</span><span style='float:right;'></span><div style=\'clear:both\'></div></a>\n";
                          }
                      } else {  // show level 2
					    	            echo "<a class='menu_head2' href=\"#\" target=\"\" ><span style='float:left;'>".GUICommonUtils::systemParseMenuItem($childNodeName)."</span><span class='hasChildIcon'></span><div style=\"clear:both\"></div></a>";
					    	           echo "<div class=\"menu_body2\" >";
					    	      for ($k=0; $k<$num; $k++) {
					    	      	 $kres = $dbConn->mysqli_result($menu, $k);
					    	      	 if (($kres['parent']==$parentLvl1Node) && (($kres['mroleid'] == "") ||(($kres['mroleid']!="") && ($kres['user_role_check']!="")))) {
								   		      $childNodeName = $kres['description'];
										        $menuUid = $kres['uid'];
										        if ($kres['override_path']=="") $childURL = $ROOT.$PHPFOLDER.$kres['url'];
										        else $childURL = $kres['override_path'].$kres['url'];
										        if (strpos($childURL,"m_id=")===false) {
											         if (strpos($childURL,"?")===false) $childURL.="?m_id=".$menuUid;
											         else $childURL.="&m_id=".$menuUid;
										        }
									          $childTarget = $kres['target'];
										        echo "<p style=\"margin:0px;\"><a class='aaa' href=\"".$childURL."\" target=\"".$childTarget."\" >".GUICommonUtils::systemParseMenuItem($childNodeName)."</a></p>";
									       }
							       } // end k loop
					    	     echo '</div>'; // menu_body2 div
					            } // end level 2
                   }
				       }
				       echo '</div></div>';;
			      } // end if subitems
			  }
		}  
}	



echo '<div class="fclear"></div>';
echo '</div>';

?>
