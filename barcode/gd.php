<?Php
if (extension_loaded('gd')) {
echo "<br>GD support is loaded ";
}else{
echo "<br>GD support is NOT loaded ";
}
if(function_exists('gd_info')){
echo "<br>GD function support is available ";
}else{
echo "<br>GD function support is NOT available ";
}
?>