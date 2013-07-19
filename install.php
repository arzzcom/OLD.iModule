<?php
REQUIRE_ONCE './config/default.conf.php';

GetDefaultHeader('아이모듈 설치','',array(
	array('type'=>'css','css'=>$_ENV['dir'].'/css/install.css')
));

$checking_installed = file_exists('./config/db.conf.php') == false && file_exists('./config/key.conf.php') == false;
$checking_global = file_exists($_SERVER['DOCUMENT_ROOT'].'/iModule.conf.php') == false;
$checking_php = preg_match('/5\.(0|1|2|3)\.[0-9]+/',@phpversion()) == true;
$checking_mcrypt = function_exists('mcrypt_encrypt');
$checking_mb_string = function_exists('mb_strlen');
$checking_xml = class_exists('SimpleXMLElement');
$checking_json = function_exists('json_encode');
$checking_curl = function_exists('curl_init');
$checking_mysql = preg_match('/5\.[0-9]+\.[0-9]+/',@mysql_get_client_info()) == true;
$checking_config = is_dir('./config') == true && (substr(sprintf('%o',fileperms('./config')),2,3) == 707 || substr(sprintf('%o',fileperms('./config')),2,3) == 777);
$checking_userfile = is_dir($_ENV['userfilePath']) == true && (substr(sprintf('%o',fileperms($_ENV['userfilePath'])),2,3) == 707 || substr(sprintf('%o',fileperms($_ENV['userfilePath'])),2,3) == 777);

$checking = $checking_installed == true && $checking_config == true && $checking_userfile == true && $checking_mcrypt == true && $checking_mb_string == true && $checking_json == true && $checking_xml == true;
$is_notice = $checking_global == false || $checking_php == false || $checking_mysql == false || $checking_curl == false;

$step = Request('step') ? intval(Request('step')) : 1;
$step = $step > 1 && $step < 6 && $checking == false ? 2 : $step;

if ($step == 4) {
	$key = preg_match('/[a-zA-Z0-9\.]{16}/',urldecode(Request('key'))) == true ? urldecode(Request('key')) : Alertbox('암호화키를 영문, 숫자, 점(.)을 이용하여 16자리로 입력하여 주십시오.');
	$host = Request('host');
	$id = Request('id');
	$password = Request('password');
	$name = Request('name');
	
	@mysql_connect($host,$id,$password) or Alertbox('DB정보가 정확하지 않습니다.');
	@mysql_select_db($name) or Alertbox('DB명이 정확하지 않습니다.');

	$_ENV['db'] = array('type'=>'mysql','host'=>$host,'id'=>$id,'password'=>$password,'dbname'=>$name);
	$mDB = &DB::instance();
	foreach ($_ENV['table'] as $code=>$table) {
		if ($mDB->DBFind($table) == true) {
			Alertbox('아이모듈에서 사용하는 DB테이블명이 이미 해당 DB에 존재합니다.\\nDB코드를 변경하거나 테이블을 삭제한 뒤 진행하여 주십시오.');
		}
	}

	$_SESSION['key'] = $key;
	$_SESSION['db'] = ArzzEncoder(serialize(array($host,$id,$password,$name)),$key);
	
	Redirect('install.php?step=5','parent');
	GetDefaultFooter();
	exit;
}
?>
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="installTitle">
	<td><div><span class="version">Ver.2.0.0</span></div></td>
</tr>
<tr>
	<td>
		<?php if ($step == 1) { ?>
		<div class="installBox">
			아이모듈을 설치합니다.<br /><br />
			아이모듈은 GPL V2 라이센스를 따르며, 아이모듈로 인한 어떠한 손실에 대해 법적책임을 지지 않습니다.<br />
			라이센스에 대한 자세한 설명은 아이모듈 홈페이지를 참고하여 주십시오.<br /><br />
			[최신버전안내]<br />
			아이모듈의 최신버전은 GitHub에서 commit 받을 수 있습니다.<br />
			(https://github.com/arzzcom/iModule)<br /><br />
			[포함된 공개라이브러리 안내]<br />
			ExtJS3 / ExtJS4 : GPL V3<br />
			네이버 스마트 에디터 : LGPL V3<br />
			에이지업로더 : CopyRights (아이모듈에 한하여 자유롭게 개작/배포할 수 있습니다.)
		</div>
		
		<div id="installButton"><a href="install.php?step=2"><img src="./images/install/btn_install.gif" alt="설치" title="아이모듈 설치" /></a></div>
		<?php } ?>

		<?php if ($step == 2) { ?>
		<div class="installBox">
			아이모듈을 설치하기 위한 각종 서버설정값을 확인합니다.

			<div class="height20"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" /><col width="20" />
			<tr>
				<td colspan="2" class="checkpoint">이전 설치정보를 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_installed == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_installed == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					<?php echo str_replace('install.php','config/db.conf.php',__FILE__); ?>파일과 <?php echo str_replace('install.php','config/key.conf.php',__FILE__); ?>파일을 서버에서 삭제한 뒤 계속 진행하여 주십시오.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">글로벌 설정파일 존재여부를 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_global == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/error.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_global == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					글로벌 설정파일(<?php echo $_SERVER['DOCUMENT_ROOT'].'/iModule.conf.php'; ?>)이 존재합니다.<br />글로벌 설정파일에 DB정보나, KEY정보가 정의되어 있다면, 해당 설정을 최우선적으로 적용합니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP 버전을 확인합니다. (<?php echo @phpversion(); ?>)</td>
				<td class="checkicon">
					<?php echo $checking_php == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/error.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_php == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					아이모듈은 PHP 5.0.x ~ PHP 5.3.x 버전에서 동작을 보증합니다. 서버의 PHP버전을 변경하여 주십시오.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP mcrypt 모듈을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_mcrypt == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_mcrypt == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					PHP 컴파일을 통해 mcrypt 모듈을 설치하여 주시기 바랍니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP mb_string 모듈을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_mb_string == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_mb_string == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					PHP 컴파일을 통해 mb_string 모듈을 설치하여 주시기 바랍니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP json 모듈을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_json == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_json == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					PHP 컴파일을 통해 json 모듈을 설치하여 주시기 바랍니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP XML 모듈을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_xml == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_xml == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					PHP 컴파일을 통해 XML 모듈을 설치하여 주시기 바랍니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">PHP CURL 모듈을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_curl == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/error.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_curl == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					PHP 컴파일을 통해 CURL 모듈을 설치하여 주시기 바랍니다.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">MySQL 버전을 확인합니다. (<?php echo @mysql_get_client_info(); ?>)</td>
				<td class="checkicon">
					<?php echo $checking_mysql == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/error.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_mysql == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					서버의 MySQL버전을 확인할 수 없거나, MySQL버전이 너무 낮습니다.<br />
					아이모듈은 MySQL 5.x 버전에서 동작을 보증합니다. 서버의 MySQL 버전을 확인하여 주십시오.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">config 폴더의 퍼미션을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_config == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_config == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					<?php echo str_replace('install.php','',__FILE__); ?>경로에 있는 config 폴더의 권한을 707 또는 777로 설정하여 주십시오.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint">userfile 폴더의 퍼미션을 확인합니다.</td>
				<td class="checkicon">
					<?php echo $checking_userfile == true ? '<img src="./images/install/tick.png" />' : '<img src="./images/install/cross.png" />'; ?>
				</td>
			</tr>
			<?php if ($checking_userfile == false) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					<?php echo str_replace('install.php','',__FILE__); ?>경로에 userfile 폴더를 생성한 뒤 해당 폴더의 퍼미션을 707 또는 777로 설정하여 주십시오.
				</td>
			</tr>
			<?php } ?>
			
			
			<tr>
				<td colspan="2" class="checkpoint" style="color:yellow;">아이모듈 설치가능여부 확인결과</td>
				<td class="checkicon">
					<?php echo $checking == true ? '<img src="./images/install/tick.png" />' : ($checking == true && $is_notice == true ? '<img src="./images/install/error.png" />' : '<img src="./images/install/cross.png" />'); ?>
				</td>
			</tr>
			<?php if ($checking == false || $is_notice == true) { ?>
			<tr>
				<td></td>
				<td colspan="2" class="checkerror">
					<?php if ($checking == false) { ?>
					빨간색 엑스표로 표시된 항목이 있을 경우 설치진행이 불가능합니다.<br />
					해당항목아래에 나와있는 설명을 참고하여 수정하여 주시기 바랍니다.
					<?php } elseif ($is_notice == true) { ?>
					노란색 느낌표로 표시된 항목이 있는 경우 설치는 가능하나, 사용상 문제가 있을 수 있습니다.
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			
			<tr class="height20">
				<td colspan="3"></td>
			</tr>
			</table>
		</div>
		
		<div id="installButton"><?php echo $checking == true ? '<a href="install.php?step='.($step+1).'"><img src="./images/install/btn_next.gif" alt="다음" title="다음" /></a>' : '<a href="install.php?step='.($step+1).'"><img src="./images/install/btn_reload.gif" alt="새로고침" title="새로고침" /></a>'; ?></div>
		<?php } ?>

		
		<?php if ($step == 3) { ?>
		<form action="./install.php?step=4" method="post" target="installFrame">
		<div class="installBox">
			<table cellpadding="0" cellspacing="5" class="layoutfixed">
			<col width="100" /><col width="10" /><col width="100%" />
			<tr>
				<td>암호화키</td>
				<td>:</td>
				<td><input type="text" name="key" class="inputbox" /></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" class="info">아이모듈은 중요정보를 RSA 128비트 방식으로 암호화하여 저장합니다. RSA 128비트 암호화에 사용될 16자리의 암호화키를 입력하여 주십시오. (영문, 숫자, 점(.))</td>
			</tr>
			<tr>
				<td>DB호스트</td>
				<td>:</td>
				<td><input type="text" name="host" class="inputbox" value="localhost" /></td>
			</tr>
			<tr>
				<td>DB아이디</td>
				<td>:</td>
				<td><input type="text" name="id" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td>DB패스워드</td>
				<td>:</td>
				<td><input type="password" name="password" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td>DB명</td>
				<td>:</td>
				<td><input type="text" name="name" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" class="info">DB호스트는 일반적으로 localhost 이며, DB아이디 및 DB패스워드는 호스팅업체에 문의하시면 확인가능합니다.<br />DB명은 보통 DB아이디와 동일하거나 user_DB아이디 형태로 많이 사용되고 있습니다.</td>
			</tr>
			<tr>
				<td>DB코드</td>
				<td>:</td>
				<td><input type="text" name="code" class="inputbox" value="<?php echo $_ENV['code']; ?>" readonly="readonly" /></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" class="info">
					DB테이블명 앞에 고정적으로 붙는 값입니다. 예를들어 DB코드가 imodule일때, 모듈테이블은 imodule_member_table 으로 생성됩니다.<br />
					DB코드는 글로벌설정파일(<?php echo $_SERVER['DOCUMENT_ROOT']; ?>/iModule.conf.php)에서 $_ENV['code'] = '코드명'; 으로 정의하거나 <?php echo $_ENV['path']; ?>/config/default.conf.php 파일의 12번째 줄을 수정하여 변경할 수 있습니다.</td>
			</tr>
			</table>
		</div>
		
		<div id="installButton"><input type="image" src="./images/install/btn_next.gif" /></div>
		
		</form>
		<?php } ?>
		
		
		<?php if ($step == 5) { ?>
		<script type="text/javascript">
		function StartInstall() {
			document.getElementById("button").style.display = "none";
			document.getElementById("message").style.display = "";
		}
		
		function ErrorInstall() {
			document.getElementById("button").style.display = "";
			document.getElementById("message").style.display = "none";
		}
		</script>
		<form action="./exec/install.do.php?action=install" method="post" target="installFrame">
		<div class="installBox">
			<table cellpadding="0" cellspacing="5" class="layoutfixed">
			<col width="100" /><col width="10" /><col width="100%" />
			<tr>
				<td>관리자아이디</td>
				<td>:</td>
				<td><input type="text" name="user_id" class="inputbox" /></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" class="info">영문, 숫자, 언더바(_)를 이용한 4~20자리</td>
			</tr>
			<tr>
				<td>패스워드</td>
				<td>:</td>
				<td><input type="password" name="password" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td>이름</td>
				<td>:</td>
				<td><input type="text" name="name" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td>닉네임</td>
				<td>:</td>
				<td><input type="text" name="nickname" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td>이메일주소</td>
				<td>:</td>
				<td><input type="text" name="email" class="inputbox" value="" /></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" class="info">입력한 정보는 차후 회원정보수정에서 변경가능합니다.</td>
			</tr>
			</table>
		</div>
		
		<div id="installButton"><div id="message" style="display:none;">아이모듈을 설치중입니다. 설치가 완료될때까지 페이지이동을 삼가하여 주십시오.</div><input id="button" type="image" src="./images/install/btn_next.gif" onclick="StartInstall();" /></div>
		
		</form>
		<?php } ?>
		
		<?php if ($step == 6) { ?>
		<div class="installBox">
		아이모듈 코어 설치가 모두 완료되었습니다.<br />
		관리자페이지로 이동하여 필요한 모듈설치 및 모듈설정을 진행하여주시기 바랍니다.
		</div>
		
		<div id="installButton"><a href="./admin/index.php"><img src="./images/install/btn_complete.gif" /></a></div>
		<?php } ?>
	</td>
</tr>
</table>
<iframe name="installFrame" style="display:none;"></iframe>
<?php
GetDefaultFooter();
?>