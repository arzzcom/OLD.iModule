<?php
class AntiSpam {

	function GetAntiSpamCode() {
		$rand1 = rand(10,99);
		$rand2 = rand(1,9);

		if (rand(0,99) < 50) {
			$_SESSION['AntiSpam'] = $rand1-$rand2;
			$spamCode = $this->ArzzEncoder($rand1.' 빼기 '.$rand2);
		} else {
			$_SESSION['AntiSpam'] = $rand1+$rand2;
			$spamCode = $this->ArzzEncoder($rand1.' 더하기 '.$rand2);
		}

		return '<img src="'.$_ENV['dir'].'/class/AntiSpam.class.php?code='.$spamCode.'" style="border:1px solid #CCCCCC;" class="AntiSpamImage" />';
	}

	function GetImage($code) {
		$text = $this->ArzzDecoder($code).'는?';
		$font = "../font/malgun.ttf"; //폰트경로
		$img = imagecreate(150,60);
		$bg = ImageColorAllocate($img,255,255,255);
		ImageFilledRectangle($img,0,0,200,200,$bg);
		$color = ImageColorAllocate ($img, 0, 133, 73);
		$angle = rand(-10,10);
		ImageTTFText ($img,12,$angle,20,35,$color,$font,$text);
		header("Content-type:image/jpeg");
		$q = rand(60,100);
		imagejpeg($img,'',$q);
		imagedestroy($img);
	}

	function CheckAntiSpam($code) {
		return isset($_SESSION['AntiSpam']) == true && $_SESSION['AntiSpam'] && $code && $code == $_SESSION['AntiSpam'];
	}

	function ArzzEncoder($code) {
		$code = base64_encode('*'.base64_encode($code).'*');
		if (preg_match('/==/',$code) == true) {
			return 'A'.str_replace('==','',$code);
		} else if (preg_match('/=/',$code) == true) {
			return 'B'.str_replace('=','',$code);
		} else {
			return 'C'.$code;
		}
	}

	function ArzzDecoder($str) {
		if (substr($str,0,1) == 'A') {
			return base64_decode(substr(base64_decode(substr($str,1).'=='),1,-1));
		} elseif (substr($str,0,1) == 'B') {
			return base64_decode(substr(base64_decode(substr($str,1).'='),1,-1));
		} else {
			return base64_decode(substr(base64_decode(substr($str,1)),1,-1));
		}
	}
}

if (isset($_ENV['path']) == false && isset($_GET['code']) == true) {
	$mAntiSpam = new AntiSpam();
	$mAntiSpam->GetImage($_GET['code']);
}
?>