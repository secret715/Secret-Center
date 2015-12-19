<?php
if(!session_id()) session_start();

function captcha(){
	$_array = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9);//去除 L 、 l 、 O 跟 0
	$captcha = '';
	
	for($i = 0; $i < 6; $i++){
		$captcha .= $_array[mt_rand(0, count($_array) - 1)];
	}
	
	$captcha = strtoupper($captcha);
    $_SESSION['captcha'] = $captcha;
}

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

captcha();

$width = 300;
$height = 75;
$image = imagecreatetruecolor($width, $height);

$text = $_SESSION['captcha'];
$bg = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 1, 1, $width - 2, $height - 2, $bg);

imagealphablending($image, true);

for($a = 1; $a <= 8; $a++){
	$p = $a / 8;
	imageline($image, 1, $height * $p, $width - 2, $height * $p, imagecolorallocate($image, 127, 186, 190));
	imageline($image, $width * $p, 1, $width * $p, $height - 2, imagecolorallocate($image, 127, 186, 190));
}

imagettftext(
	$image,
	$height * ((30+rand(0,5))*0.01),
	0,
	mt_rand($width * 0.1, $width * 0.3),
	mt_rand($height * 0.3, $height * 0.85),
	genColor(80),
	'./captchafont.ttf',
	substr($text, 0, 2)
);

imagettftext(
	$image, 
	$height * ((30+rand(0,5))*0.01), 
	0, 
	mt_rand($width * 0.4, $width * 0.55), 
	mt_rand($height * 0.3, $height * 0.85),
	genColor(100), 
	'./captchafont.ttf', 
	substr($text, 2, 2)
);

imagettftext(
	$image, 
	$height * ((30+rand(0,5))*0.01), 
	0, 
	mt_rand($width * 0.6, $width * 0.8), 
	mt_rand($height * 0.3, $height * 0.85), 
	genColor(100),
	'./captchafont.ttf', 
	substr($text, 4, 2)
);

for($i = 0; $i < 8; $i++){
	imagefilledarc($image, mt_rand($width * 0.15, $width * 0.8), mt_rand($height * 0.15, $height * 0.8), $height * 0.4, $height * 0.4, 0, 360, genColor(255, 180, 255, 110, 255, 90, 110), IMG_ARC_PIE);
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);