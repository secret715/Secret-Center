<?php
require_once('../config.php');
if(!session_id()) session_start();

function genColor($r, $br = 0, $g = 0, $bg = 0, $b = 0, $bb = 0, $a = 0){
	global $image;
	
	if($g == 0 && $b == 0){
		$b = $g = $r;
	}
	
	$_r = mt_rand($br, $r);
	$_g = mt_rand($bg, $g);
	$_b = mt_rand($bb, $b);
	
	return imagecolorallocatealpha($image, $_r, $_g, $_b, $a);
}

sc_captcha();

$width =150;
$height = 50;
$font=dirname(__FILE__) .'/DejaVuSans.ttf';
$image = imagecreatetruecolor($width, $height);

$text = $_SESSION['captcha'];
$bg = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 1, 1, $width - 2, $height - 2, $bg);

imagealphablending($image, true);

$pa=8;
for($a = 1; $a <= $pa; $a++){
	$p = $a / $pa;
	imageline($image, 1, $height * $p, $width - 2, $height * $p, imagecolorallocate($image, 127, 186, 190));
	imageline($image, $width * $p, 1, $width * $p, $height - 2, imagecolorallocate($image, 127, 186, 190));
}

for($i=0;$i<3;$i++){
	imagettftext(
		$image,
		$height * 0.3,
		0,
		mt_rand($width * (0.05+$i*0.3), $width * (0.25*$i+0.3)),
		mt_rand($height * 0.36, $height * 0.93),
		genColor(mt_rand(50,200)),
		$font,
		substr($text, $i*2, 2)
	);
}


for($i = 0; $i < 9; $i++){
	imagefilledarc($image, mt_rand($width * 0.15, $width * 0.8), mt_rand($height * 0.15, $height * 0.8), $height * 0.4, $height * 0.4, 0, 360, genColor(225, 120, 225, 100, 225, 90, 70), IMG_ARC_PIE);
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);