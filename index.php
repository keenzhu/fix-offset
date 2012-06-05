<?php
/*
   
代码功能：校正地图。
使用方法：index.php?lat=39.914914&lon=116.460633
*/
header("Content-Type:text/html; charset=utf-8");
define('__dat_db__' , 'offset.dat' );

define('datmax' , 9813675 );
$start=microtime();

$lon=$_GET['lon'];
$lat=$_GET['lat'];
$tmplon=intval($lon * 100);
$tmplat=intval($lat * 100);

 
$offset =xy_tz($tmplon.$tmplat);
$off=explode('|',$offset);
$lngPixel=lngToPixel($lon,18)-$off[0];
$latPixel=latToPixel($lat,18)-$off[1];

echo (microtime()-$start).'<br>';
 
echo pixelToLat($latPixel,18).",".pixelToLng($lngPixel,18);

function lngToPixel($lng,$zoom) {
	return ($lng+180)*(256<<$zoom)/360;
}
function pixelToLng($pixelX,$zoom){
	return $pixelX*360/(256<<$zoom)-180;
}
function latToPixel($lat, $zoom) {
	$siny = sin($lat * pi() / 180);
	$y=log((1+$siny)/(1-$siny));
	return (128<<$zoom)*(1-$y/(2*pi()));
}

function pixelToLat($pixelY, $zoom) {
	$y = 2*pi()*(1-$pixelY /(128 << $zoom));
	$z = pow(M_E, $y);
	$siny = ($z -1)/($z +1);
	return asin($siny) * 180/pi();
}
 
function xy_tz( $number ){
	$fp = fopen(__dat_db__,"rb"); 
	$xy=$number;
	$left = 0;
	$right = datmax;
 
	while($left <= $right){
		$recordCount =(floor(($left+$right)/2))*8; 
		@fseek ( $fp, $recordCount , SEEK_SET ); 
		$c = fread($fp,8); 
		$lon = unpack('s',substr($c,0,2));
		$lat = unpack('s',substr($c,2,2));
		$x = unpack('s',substr($c,4,2));
		$y = unpack('s',substr($c,6,2));
		$pyl=$lon[1].$lat[1];
		
		if ($pyl==$xy){
		   fclose($fp);
		   return $x[1]."|".$y[1];
		   break;
		}else if($pyl<$xy){
		   $left=($recordCount/8) +1;
		}else if($pyl>$xy){
		   $right=($recordCount/8) -1;
		}

	}
	fclose($fp);
}
?>