<?php


include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


//------------------------------------------------------------
//
//  USEAGE:
//  new arrayToXMLschema($xmlSchema, $arrEx, array('debug'=>1...more options))
//
//  OPTIONS: array()
//  debug = 1  : Turn debugging on, echo arrays built
//  cardinal = 1 : Add a counter on the grouped tags.
//
//------------------------------------------------------------


class arrayToXMLschema{



  private   $rawXML;
  private   $XMLheader;
  private   $XMLbody;
  private   $XMLfooter;
  private   $XMLrootTag;
  private   $XMLloopTag;
  private   $XMLfChildLoopTag;
  private   $XMLstructureArr;
  private   $rawArray;
  private   $groupedArray = array();
  public    $resultXML;
  public    $resultARRAY;
  public    $errorTO;
  private   $xmlObj = false;

  //OPTIONS
  private   $debug = false;
  private   $displayCardinalAttr = false;

  //SYS VARIABLE NAMES
  const BASEWRAPPER = 'BASEWRAPPER';
  const SYSBREAKON = 'SYSBREAKON';
  const SYSCARDINALITY = 'SYSCARDINALITY';
  const SYSCALCEXPR = 'SYSCALCEXPR';
  const SYSCOLNAME = 'SYSCOLNAME';

  //SYS
  private   $sysBreakArr = array();
  private   $sysCardArr = array();
  private   $sysColNameArr = array();
  private   $sysExprArr = array();



  public function __construct($XMLschema, $array, $params = false){


    $this->errorTO = new ErrorTO;  //errorTO
    libxml_use_internal_errors(true);  //Use internal errors for SimpleXML so we can return them


    //------------------------------------------------------------


    //BASIC VALIDATION
    if(empty($XMLschema) || trim($XMLschema) == ''){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'XML Schema Empty!';
      return;
    }

    if(empty($array) || !is_array($array)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Result is Empty / Not Array!';
      return;
    }

    $this->rawXML = trim(str_replace(array("\r","\n","\t"), array('','',''), $XMLschema));  //remove formatting
    $this->rawArray = $array;  //Result Set
    $this->debug = (isset($params['debug']) && $params['debug'] == 1) ? true : false;  //Debugging?
    $this->displayCardinalAttr = (isset($params['cardinal']) && $params['cardinal'] == 1) ? true : false;  //displayCardinalAttr


    if($this->debug){
      echo '<pre><div>DEBUG MODE</div>';
      echo 'Display Cardinal Numbering: ' , ($this->displayCardinalAttr===true)? 1 : 0;
    }


    //------------------------------------------------------------


    //ATTEMPT TO BREAKUP XML SCHEMA INTO : HEADER, BODY, FOOTER.
    $base = $this->splitXMLschema();
    if($base === false){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'No BaseWrapper Element Set!';
      return;
    }


    //------------------------------------------------------------


    //LOAD INTO SimpleXML - validates XML Structure ONLY BODY.
    $this->xmlObj = simplexml_load_string($this->rawXML);

    if($this->xmlObj === false){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;

      //Include Error Details from thrown by SimpleXML.
      $this->errorTO->description = 'Failed loading into SimpleXML - XML Invalid!';
      foreach(libxml_get_errors() as $error) {
        $this->errorTO->description .= '<br>' . $error->message;
      }
      return;
    }


    //------------------------------------------------------------


    //LOOP TAG
   // $this->XMLloopTag = 'x';// $this->getFirstChildofBaseWrapper();

    if($this->XMLloopTag == '')
      $this->XMLloopTag = $this->XMLrootTag;

    if($this->debug){
      echo '<hr>';
      echo '<div>D: Root Element: "' . htmlentities($this->XMLrootTag) . '"</div>';
      echo '<div>D: Loop Element: "'.$this->XMLloopTag.'"</div>';
      echo '<div>D: Loop Child Element: "'.$this->XMLfChildLoopTag.'"</div>';
    }


    //------------------------------------------------------------


    //AT THIS STAGE THE STRUCTURE OF THE XML SHOULD BE KNOW AND THESE MUST CONTAIN VALUES : FAIL IF NOT
    //header and footer can be empty :)
    if($this->XMLbody == '' || $this->XMLrootTag == '' || $this->XMLloopTag == ''){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'XML Schema Structure error, invalid header / body / footer / root tag / loop tag!';
      return;
    }


    //------------------------------------------------------------


    //SET THE SYS BREAK ON ARRAY
    $this->sysBreakArr = $this->getSysBreakArr();
    if($this->sysBreakArr === false){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'System Breaks On Failed, XML invalid / Failed XPATH!';
      return;
    }

    if($this->debug){
      echo '<hr>';
      if(!count($this->sysBreakArr)>0)
        echo '<div>D: System Breaks: NONE</div>';
      else
        echo '<div>D: System Breaks: ' . count($this->sysBreakArr) . ' => "'.JOIN(',', $this->sysBreakArr).'"</div>';
    }


    //------------------------------------------------------------
    //  RETURNS ARRAY STRUCTURE OF XML AND BUILD SYS ARRAYS WHILE LOOPING THROUGH XML
    /* ----------------------------------------------------------*/

    $this->XMLstructureArr = $this->getStructureToArray($this->xmlObj);
    $this->XMLstructureArr = $this->gotoRootLoopPos($this->XMLstructureArr);
    //THROW ERROR IF sysBreakArr has values and Cardinality has none
    if(!count($this->XMLstructureArr) > 0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Unable to build XML Structure Array!';
      return;
    }

    if(count($this->sysBreakArr) > 0 && count($this->sysCardArr) == 0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'System Breaks Set, with no System Cardinality!';
      return;
    }



    //DEBUG XML STRUCTURE
    if($this->debug){
      echo '<hr>';
      echo 'D: XML Schema Array:'.PHP_EOL;
      var_dump($this->XMLstructureArr);

      echo '<hr>';
      if(!count($this->sysCardArr)>0)
        echo '<div>D: System Cardinality: NONE</div>';
      else
        echo '<div>D: System Cardinality: ' . count($this->sysCardArr) . '<Br>' , var_dump($this->sysCardArr) , '</div>';

      echo '<hr>';
      if(!count($this->sysColNameArr)>0)
        echo '<div>D: System Colnames: NONE</div>';
      else
        echo '<div>D: System Colnames: ' . count($this->sysColNameArr) . '<Br>' , var_dump($this->sysColNameArr) , '</div>';


      echo '<hr>';
      if(!count($this->sysExprArr)>0)
        echo '<div>D: System Expr: NONE</div>';
      else
        echo '<div>D: System Expr: ' . count($this->sysExprArr) . '<Br>' , var_dump($this->sysExprArr) , '</div>';
    }


    //------------------------------------------------------------


    //BUILD Result set into XML array Structure, with Grouping
    $resultRxa = $this->buildResultXMLArray();
    if($resultRxa  !== true || !count($this->resultARRAY)>0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Converting Result Set to XML matched Array!';
      return;
    }


    $xmlR = $this->arrayToXML($this->resultARRAY);


    if($this->debug){
      echo '<hr>';
      echo htmlentities($this->resultXML);
      echo '<hr>';
    }

    if($xmlR === true){
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;  //SINGLE POINT OF SUCCESS
      $this->errorTO->description = 'Successful Result Set to XML Schema Conversion!';
      return;
    } else {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Final Array to XML!';
      return;
    }


  }

/* ------------------------------------------------------------
 *
 * 		GROUP RESULT SET, BASED ON SYSBREAKS
 * 		BASE : ARRAY => RESULT SETS FROM MySQL are always => 2 Dimensional
 *
 * ------------------------------------------------------------*/
  private function buildResultXMLArray(){


    $groupDataArr = array();
    $breakArr = array();


    //------------------------------------------------------------
    //BUILD : breakArr and uppercase keys
    foreach($this->rawArray as $k1 => $row){
      $a = array();  //Array containing break point values => keys match groups
      $urow = array();
      foreach($row as $k2 => $d){
        $urow[strtoupper($k2)] = $d;
        //IS A BREAKER KEY?
        if(in_array(str_replace(' ','',strtoupper($k2)), $this->sysBreakArr)){
          $a[strtoupper($k2)] = $d;
        }
      }
      $breakArr[] = $a;
      $this->rawArray[$k1] = $urow;
    }


    //------------------------------------------------------------
    //REMOVE DUP ENTRIES
    $breakGroupArr = array();
    foreach($breakArr as $m => $s){
      $s;  //Array.
      unset($breakArr[$m]);  //Remove from subloop
      $t = false;
      foreach($breakArr as $m2 => $s2){
        if($s == $s2)  //Compare Array.
          $t = true;
      }
      if(!$t)
        $breakGroupArr[] = $s;
    }


    //------------------------------------------------------------
    //DEBUG
    if($this->debug){
      echo '<hr>';
      echo 'D: Group Break Array: <Br>';
      var_dump($breakGroupArr);
      echo '<hr>';
    }


    //------------------------------------------------------------
    //LOOP THROUGH DATA => PUT INTO GROUPS
    $groupDataArr = array();
    foreach($this->rawArray as $k1 => $row){

      //compares all values in breakpoints... reduces wrong group allocation.
      foreach($breakGroupArr  as $bK => $bE){
        $breakPkA = array_keys($bE);
        //check each break point against group
        $ba = array();
        foreach($breakPkA as $bKi){
          if($bE[$bKi] == $row[$bKi]){
            $ba[] = 'Y';
          } else {
            $ba[] = 'N';
          }
        }
        if(!in_array('N',$ba)){

         break;
        }

      }
      $groupDataArr[$bK][] = $row;

    }

    //ksort($groupDataArr);

    if($this->debug){
      echo '<hr>';
      var_dump($groupDataArr);
    }


    //------------------------------------------------------------
    //BUILD FINAL ARRAY - USING GROUPED RESULT SETS.
    $n = 0;
    $fa = array();
    foreach($groupDataArr as $dKey => $dRow){
      $xmlStruct = (isset($this->XMLstructureArr[$this->XMLrootTag])) ? $this->XMLstructureArr[$this->XMLrootTag] : $this->XMLstructureArr;
      $fa['@Repeat'.$n] = $this->buildGroup($xmlStruct, $dRow);
      $n++;
    }
    $this->resultARRAY[$this->XMLrootTag] = $fa;


    if($this->debug){
      echo '<hr>';
      var_dump($this->resultARRAY);
    }

    return true;

  }



  private function buildGroup($arr, $groupedRowData, $parentKey = false, $pos = 0){

    $len  = count($groupedRowData);

    foreach($arr as $k => $v){

      $repeator = false;
        foreach($this->sysCardArr as $ck => $ci){

        if(isset($ci['parent']) && isset($ci['element']) && strtoupper($k) ==  $ci['element'] && ($parentKey === false || $parentKey == $ci['parent'] || strtoupper($this->XMLloopTag) == $ci['parent'])){
           $repeator = true;

        }
      }

      $isAttrArr = false;
      if(isset($v['@attributes']) && isset($v['@attributes']['cardinal']) && count($v) == 1) {
        $isAttrArr = true;
      }

      if($repeator){

        $a = array();
        for($i = 0; $i < $len; $i++){

          if($isAttrArr === true){
            $a['@Repeat'.$i]['@attributes'] = array('cardinal'=>array($v['@attributes']['cardinal'] => ($i+1)));
            $a['@Repeat'.$i]['@value'] = $this->setValue($k, $groupedRowData, $i, $parentKey);
          } else if(is_array($v)){
            $a['@Repeat'.$i] = $this->buildGroup($v, $groupedRowData, strtoupper($k), $i);
          } else {
            $a['@Repeat'.$i] = $this->setValue($k, $groupedRowData, $i, $parentKey);
          }
        }

        $arr[$k] = $a;

      } else {

        if($k == '@attributes' && isset($v['cardinal'])){
          $arr[$k] = array('cardinal'=>array($v['cardinal'] => ($pos+1)));
        } else if(is_array($v)){
          $arr[$k] = $this->buildGroup($v, $groupedRowData, strtoupper($k), $pos);
        } else {
          $arr[$k] = $this->setValue($k, $groupedRowData, $pos, $parentKey);
        }
      }
    }

    return $arr;

  }

/* ------------------------------------------------------------
 *
 * 		Matches keys to a value... includes SYS FUNC :  ColName => Element and Expr => Value;
 * 		COMPELTE DATA SET IS PASSED TO FUNC TO LOOP THROUGH
 * 		BASE : looping
 *
 * ------------------------------------------------------------*/
  //
  private function setValue($key, $data, $rowNo, $parent){

    $value = '';  //Preset value to EMPTY!

     //Check if KEY is a ColName Item
    $colEleName = false;
    foreach($this->sysColNameArr as $cE){
      if(isset($cE['element']) && isset($cE['column']) && isset($cE['parent']) && $cE['element'] == $key && ($cE['parent'] == $parent || false == $parent)){
        $colEleName = $cE['column'];
      }
    }

    //Check if KEY is a Expr Item
    $expr = false;
    foreach($this->sysExprArr as $eX){
      if(isset($eX['element']) && isset($eX['expr']) && isset($eX['parent']) && $eX['element'] == $key && $eX['parent'] == $parent){
        $expr = strtoupper($eX['expr']);
      }
    }

    //Loop through row data
    foreach($data[$rowNo] as $k => $val){

      $k = str_replace(' ','',$k);

      if($expr){
        $expr = str_replace('@'.$k, $val, $expr);
      } else if($colEleName !== false && $colEleName == $k){
        $value = $val;
        break;
      } else if($k == strtoupper($key)){
        $value = $val;
        break;
      }
    }

    if($expr !== false){
      $v = false;
      @eval('$v =  @'.$expr.';');

      if($v !== false){
        $value = $v;
      }
    }

    return $value;

  }


/* ------------------------------------------------------------
 *
 * 		TAKES THE XML BODY (INNER OF THE BASEWRAPPER TAG) AND CONVERTS IT TO AN ARRAY
 * 		BASE : Array looping, Self Looping for full array depth
  *
 * 		ALSO GETS ALL SYS SETTINGS FROM XML
 * 			SYSCARDINALITY
 * 			SYSCALCEXPR
 * 			SYSCOLNAME
 *
 * ------------------------------------------------------------*/

  private function getStructureToArray($xmlObj){

    $arr = array();

    foreach($xmlObj->children() as $key => $vObj){


      $addCardAttr = false;
      $attrObjArr = $vObj->attributes();
      $cardVal = '';

      foreach($attrObjArr as $attr){

        $attrName = $attr->getName();

        if(substr(strtoupper($attrName),0,3) == 'SYS' && isset($attr[0])){


          //THESE ARRAYS INCLUDE the parent element's name
          //reduces any same name elements in the xml, where they have conflicting element names but must contain different values
          /*
           * EXAMPLES:
           * <details> --parent
           * 	 <detail sysCardinality="n" >	--looping element
           * 		<...child elements...>
           * 		<detail></detail>	--parent is not details => not a cardinal element
           *   </detail>
           * </details>
           *
           * <details> --parent (<price colName="totalPrice"></price>)
           * 	 <detail sysCardinality="n" > --parent (<price colName="totalPrice"></price>)
           * 		<...child elements...>
           * 		<price colName="netPrice"></price>	--same name different value
           *   </detail>
           *   <price colName="totalPrice"></price>	--same name different value
           * </details>
           *
           */

          $val = $attr;  //weird but calling any string functions on it converts its from an OBJ -> String

          if(strtoupper($attrName) == self::SYSCARDINALITY){
            $this->sysCardArr[] = array('parent' => strtoupper($xmlObj->getName()), 'element' => str_replace(' ','',strtoupper($key)));
            $addCardAttr = ($this->displayCardinalAttr === true) ? true : false;
            $cardVal = trim($val);
          }

          //Expressions
          if(strtoupper($attrName) == self::SYSCALCEXPR)
            $this->sysExprArr[] = array('parent' => strtoupper($xmlObj->getName()), 'element' => $key, 'expr' => trim($val));

          //result set key to element
          if(strtoupper($attrName) == self::SYSCOLNAME)
            $this->sysColNameArr[] = array('parent' => strtoupper($xmlObj->getName()), 'element' => $key, 'column' => str_replace(' ','',strtoupper($val)));

        }
      }

      $depthOfXML = count(get_object_vars($vObj));

      if($depthOfXML > 0){
        $arr[$key] = $this->getStructureToArray($vObj);
      } else {
        $arr[$vObj->getName()] = '';
      }

      if($addCardAttr){
        $arr[$key]['@attributes'] = array('cardinal' => $cardVal);
      }

    }
    return (count($arr)>0) ? $arr : "";

  }

  //XML Array crawler returns final array :)
  private function gotoRootLoopPos($a, $start = true){

     foreach($a as $k => $v){

       if($start === true && ($k == $this->XMLfChildLoopTag || $k == $this->XMLloopTag)){
         return $a;
         break;
       }
       //echo $k . ' => ' .$v , var_dump($start) , '<br>';

       if($k == $this->XMLrootTag && (isset($v[$this->XMLloopTag]) || isset($v[$this->XMLfChildLoopTag]))){
         return $v;
         break;
       } else if(is_array($v)){
         return $this->gotoRootLoopPos($v, false);
       }
     }

  }



/* ------------------------------------------------------------
 *
 * 		RETRIEVE SYS BREAKS
 * 		BASE : XPATH
 *
 * ------------------------------------------------------------*/

  private function getSysBreakArr(){

    //local SimpleXML => Why? attribute is case-insensitive,
    //load full raw XML - sysbreak might at in root tag (moved to header xml)
    $upperCaseXML = simplexml_load_string(strtoupper($this->rawXML));

    if($upperCaseXML === false){
      return false;
    }

    $r = $upperCaseXML->xpath('//@'.self::SYSBREAKON);

    //Use first Sysbreak found in XML.
    $arr = (isset($r[0][0])) ? (explode(',', preg_replace('/\s*/','',strtoupper($r[0][0])))) : (false);

    //remove blanks
    if($arr !== false){
      foreach($arr as $k=>$i){
        if(empty($i)){
          unset($arr[$k]);
        } else {
          $arr[$k] = str_replace(' ','',$i);
        }
      }
    }

    return ($arr !== false) ? $arr : array();

  }



/* ------------------------------------------------------------
 *
 * 		HEADER, BODY & FOOTER
 * 		BASE : STRING FUNCTIONS
 *
 * ------------------------------------------------------------*/

  private function splitXMLschema(){

    //FAILS IF NO BASEWRAPPER FIND.
    $basePos = strpos(strtoupper(trim($this->rawXML)), self::BASEWRAPPER);  //POS of wrapper attribute.

    if($basePos === false){
      return false;
    }

    //Get the start and end of the tag using < > as points from location of basewrapper
    $basePosTagStart = strrpos(substr($this->rawXML, 0, $basePos), '<');  //Get start of element
    $basePosTagEnd = strpos(substr($this->rawXML, $basePos), '>') + 1 + $basePos;  //Get end of Element
    $basePosTag = substr($this->rawXML, $basePosTagStart, $basePosTagEnd - $basePosTagStart );  //Whole Tag

    //PARSE root tag
    $baseElement = str_replace(array('<'), array(''), $basePosTag);
    $baseElement = explode(' ', $baseElement);
    $baseElement = $baseElement[0];


    //LOCATE START OF FOOTER.
    $baseFootStart = strpos($this->rawXML, '</' . $baseElement . '>');
    if($baseFootStart === false){
      $baseFootStart = strpos($this->rawXML, '</' . $baseElement . ' ');
    }
    $baseFootStart = 1 + $baseFootStart + strpos(substr($this->rawXML, $baseFootStart ), '>');

    //SET HEADER
    $this->XMLheader = substr($this->rawXML, 0, $basePosTagStart);

    //SET BODY
    $this->XMLbody = substr($this->rawXML, $basePosTagEnd, $baseFootStart - $basePosTagEnd);
    $this->XMLbody = preg_replace('/>\s*</', '><', $this->XMLbody);

    //SET FOOTER
    $this->XMLfooter = trim(substr($this->rawXML, $baseFootStart));

    //SET ROOT
    $this->XMLrootTag = $baseElement;

    $loopPos = strpos(strtoupper(trim($this->rawXML)), self::SYSBREAKON);  //POS of sysbreakon
    $loopTagPos = strrpos(trim(substr(trim($this->rawXML), 0, $loopPos)), '<');
    $loopTag = substr(trim($this->rawXML), 1+$loopTagPos);
    $loopTag = explode(' ', $loopTag);
    $this->XMLloopTag = $loopTag[0];

    $fChildOfRoot = 1 + $loopPos + strpos(substr($this->rawXML, $loopPos), '>');
    $fChildOfRoot = explode('>',(substr($this->rawXML, $fChildOfRoot)));
    $fChildOfRoot = explode(' ',$fChildOfRoot[0]);
    $this->XMLfChildLoopTag  = str_replace('<','',$fChildOfRoot[0]);

    //DEBUG
    if($this->debug){
      echo '<hr>';
      echo '<div>D: Header:<br>' . htmlentities($this->XMLheader) . '</div>';
      echo '<div>D: Body:<br>' . htmlentities($this->XMLbody) . '</div>';
      echo '<div>D: Footer:<br>' . htmlentities($this->XMLfooter) . '</div>';
    }

    return true;

  }



/* ------------------------------------------------------------
 *
 * 		ARRAY TO XML BUILDING
 * 		BASE : Array Looping,
 * 		DEPANDANTS: arrayToXMLloop child function => to parse the full depth of an array
 *
 * ------------------------------------------------------------*/

  private function arrayToXML($array) {

    //Convert body array to body XML
    $xmlBody = $this->arrayToXMLloop($array);

    if($xmlBody != ''){
      //Append Final XML Structure for output
      $this->resultXML = $this->XMLheader . trim($xmlBody) . $this->XMLfooter;
      return true;
    } else {
      return false;
    }

  }


/* ------------------------------------------------------------
 *
 * 		CONVERTS AND ARRAY INTO XML, includes formatting of newlines and tabs for easy reading
 * 		BASE : Array Looping,
 * 		PARENT : arrayToXML
 * 		NOTE: IF KEY FOUND = [@Repeat0] LOOPS THROUGH CHILD WITH PARENT AS WRAPPER
 *
 * ------------------------------------------------------------*/

  //EXAMPLE:
  /* ----------------------------------------------------
   *
   * ARRAY
   *
   * ----------------------------------------------------
   * array('key' => array(	[@Repeat0] => array('element' => 'value1'),
   * 						[@Repeat1] => array('element' => 'value2'),
   * 						[@Repeat2] => array('element' => 'value3')
   * 					 )
   *	  );
   * ----------------------------------------------------
   *
   * OUTPUT:
   *
   * ----------------------------------------------------
   * <key>
   * 	<element>value1</element>
   * </key>
   * <key>
   * 	<element>value2</element>
   * </key>
   * <key>
   * 	<element>value3</element>
   * </key>
   * ----------------------------------------------------*/

  private function arrayToXMLloop($theArray, $tabCount = 1) {

    $tabSpace = "";
    $xml = "";
    for ($i = 0; $i<$tabCount-1; $i++) {
      $tabSpace .= "\t";
    }

    $tabCount++;

    foreach($theArray as $tag => $val) {


      $looper = (is_array($val) && isset($val['@Repeat0'])) ? true : false;  //Check if is a Key Looper.

      if($looper === true){

        //CURRENTLY attributes only at cardinal lvl setup.
        $attr = array();
        foreach($val as $kat => $vat){

             if(isset($vat['@attributes']) && isset($vat['@attributes']['cardinal'])){

               $attrName = array_keys($vat['@attributes']['cardinal']);
               $attrName = $attrName[0];
               $attr[$kat] = ' ' . $attrName . '="' . $vat['@attributes']['cardinal'][$attrName] . '"';
               unset($val[$kat]['@attributes']);
             }
        }

        if (is_array($val['@Repeat0'])){

          if($tag == $this->XMLrootTag){
              $xml .= PHP_EOL . $tabSpace . '<' . $tag . '>' . $this->arrayToXMLloop($val, $tabCount);
              $xml .= PHP_EOL . $tabSpace . '</' . $tag . '>';
          } else {
            foreach($val as $k => $v){
              if(!isset($v['@value'])){
                $xml .= PHP_EOL . $tabSpace . '<' . $tag . ((isset($attr[$k]))?$attr[$k]:'') . '>' . $this->arrayToXMLloop($val[$k], $tabCount);
                $xml .= PHP_EOL . $tabSpace . '</' . $tag . '>';
              } else {
                $xml .= PHP_EOL . $tabSpace . '<' . $tag . ((isset($attr[$k]))?$attr[$k]:'') .'>' . htmlentities($v['@value']) . '</' . $tag . '>';
              }
            }
          }

        } else {

          foreach($val as $k => $v){
            $xml .= PHP_EOL . $tabSpace . '<' . $tag . ((isset($attr[$k]))?$attr[$k]:'') . '>' . htmlentities($val[$k]) . '</' . $tag . '>';
          }

        }

      } else {

        if (!is_array($val)) {
            $xml .= PHP_EOL . $tabSpace . '<'.$tag.'>' . htmlentities($val) . '</' . $tag . '>';
        } else if(substr($tag,0,7) == '@Repeat'){
            $xml .= $this->arrayToXMLloop($val, $tabCount-1);
        } else {
            $xml .= PHP_EOL . $tabSpace . '<' . $tag . '>' . $this->arrayToXMLloop($val, $tabCount);
            $xml .= PHP_EOL . $tabSpace . '</' . $tag . '>';
        }
      }
    }

    return $xml;

  }

}