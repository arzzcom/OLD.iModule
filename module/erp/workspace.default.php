<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>현장정보보기</title>
<link rel="shortcut icon" href="<?php echo $this->moduleDir; ?>/favicon.ico" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $this->moduleDir; ?>/css/default.css" type="text/css" title="style" />
</head>
<body class="skyblue">

<div id="WorkspaceDefault">
	<div class="title">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="350" /><col width="100%" />
		<tr>
			<td rowspan="2">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="50" /><col width="100%" />
				<tr>
					<td><img src="<?php echo $this->moduleDir; ?>/images/workspace/logo_default.gif" /></td>
					<td><script type="text/javascript">GetEmbed("menu","<?php echo $this->moduleDir; ?>/flash/workspace_title.swf?rnd="+Math.random(),300,50,"title=<?php echo urlencode($this->workspace['title']); ?>");</script></td>
				</tr>
				</table>
			</td>
			<td style="height:20px;" class="right"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?wno=<?php echo $this->wno; ?>&mode=manager" title="현장관리프로그램 접속"><img src="<?php echo $this->moduleDir; ?>/images/workspace/btn_connect_manager.gif" style="margin:2px 5px 0px 0px;" /></a></td>
		</tr>
		<tr>
			<td style="height:30px;"></td>
		</tr>
		</table>
	</div>

	<div class="content">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="5"><col width="260" />
		<tr>
			<td style="background:#000000;" class="vTop">
				<script type="text/javascript">
				var imagesIDX = 0;
				var imagesList = new Array();
				var scrollInterval = null;

				<?php
				$date = Request('date');
				$find = "where `wno`={$this->wno}";
				if ($date != null) $find.= " and `date`='$date'";

				$images = $this->mDB->DBfetchs($this->table['workspace_image'],array('idx','filepath'),$find);
				if (sizeof($images) > 0) {
					$defaultImage = $_ENV['dir'].$images[0]['filepath'];
					$check = @getimagesize($_ENV['path'].$images[0]['filepath']);
					$defaultWidth = $check[0] > 550 ? 550 : $check[0];
				} else {
					$defaultImage = $this->moduleDir.'/images/workspace/noworkspaceimage.gif';
					$defaultWidth = 550;
				}

				for ($i=0, $loop=sizeof($images);$i<$loop;$i++) {
					$check = @getimagesize($_ENV['path'].$images[$i]['filepath']);
					$width = $check[0] > 550 ? 550 : $check[0];
					echo 'imagesList['.$i.'] = ["'.$_ENV['dir'].$images[$i]['filepath'].'",'.$width.'];'."\n";
				}
				?>
				function ImageMove(dir) {
					document.getElementById("ImageListArea").getElementsByTagName("div")[imagesIDX].className = "imagesListoff";
					if (dir == "left") imagesIDX--;
					else imagesIDX++;

					imagesIDX = imagesIDX == imagesList.length ? 0 : imagesIDX;
					imagesIDX = imagesIDX < 0 ? imagesList.length - 1 : imagesIDX;

					document.getElementById("ImageListArea").getElementsByTagName("div")[imagesIDX].className = "imagesListon";
					document.images["WorkspaceImage"].src = imagesList[imagesIDX][0];
					document.images["WorkspaceImage"].style.width = imagesList[imagesIDX][1];
				}

				function ImageListScroll(dir) {
					if (scrollInterval == null) {
						scrollInterval = setInterval("ImageListScrollInner('"+dir+"')",10);
					}
				}

				function ImageListScrollInner(dir) {
					var object = document.getElementById("ImageListArea");

					if (dir == "left") object.scrollLeft = object.scrollLeft - 5;
					else object.scrollLeft = object.scrollLeft + 5;
				}

				function ImageListStop() {
					clearInterval(scrollInterval);
					scrollInterval = null;
				}
				</script>

				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="50%" /><col width="550" /><col width="50%" />
				<tr style="height:50px; background:#303030;">
					<td colspan="3"></td>
				</tr>
				<tr style="background:#303030; height:500px; overflow:hidden;">
					<td class="center"><img src="<?php echo $this->moduleDir; ?>/images/workspace/btn_image_left.gif" class="pointer" onclick="ImageMove('left')" /></td>
					<td><img name="WorkspaceImage" src="<?php echo $defaultImage; ?>" style="width:<?php echo $defaultWidth; ?>px;" /></td>
					<td class="center"><img src="<?php echo $this->moduleDir; ?>/images/workspace/btn_image_right.gif" class="pointer" onclick="ImageMove('right')" /></td>
				</tr>
				<tr style="height:50px; background:#303030;">
					<td></td>
					<td colspan="2" class="right" style="padding-right:10px;">
						<select name="month" class="goMonth" onchange="if (this.value) location.href='<?php echo $_SERVER['PHP_SELF']; ?><?php echo GetQueryString(array('mode'=>'default','date'=>''),'',false); ?>&date='+this.value;">
						<option value="">월별보기</option>
						<?php
						if ($this->workspace['workstart_date'] != '1970-01-01' && $this->workspace['workend_date'] != '1970-01-01') {
							$StartDate = explode('-',$this->workspace['workstart_date']);
							$EndDate = explode('-',$this->workspace['workend_date']);

							for ($i=0; $thisMonth != $StartDate[0].'-'.$StartDate[1];$i++) {
								$thisDate = mktime(0,0,0,$EndDate[1]-$i,1,$EndDate[0]);
								$thisMonth = date('Y-m',$thisDate);
								if ($thisDate < time()) echo '<option value="'.$thisMonth.'"'.($date == $thisMonth ? ' selected="selected"' : '').'>'.date('Y년 m월',$thisDate).'</option>'."\n";
							}
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="height:100px; padding:5px; background:#000000;">
						<script type="text/javascript">
						function ImageList(idx,object,mode) {
							if (mode == "over") {
								if (imagesIDX != idx) object.className = "imagesListover";
							} else if (mode == "out") {
								if (imagesIDX != idx) object.className = "imagesListoff";
							} else {
								object.className = "imagesListon";
								document.images["WorkspaceImage"].src = imagesList[idx][0];
								document.images["WorkspaceImage"].style.width = imagesList[idx][1];

								document.getElementById("ImageListArea").getElementsByTagName("div")[imagesIDX].className = "imagesListoff";
								imagesIDX = idx;
							}
						}
						</script>
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="30" /><col width="100%" /><col width="30" />
						<tr>
							<td class="center"><img src="<?php echo $this->moduleDir; ?>/images/workspace/btn_imglist_arrow_left.gif" class="pointer" onmouseover="ImageListScroll('left');" onmouseout="ImageListStop();" /></td>
							<td>
								<div id="ImageListArea" style="overflow:hidden;">
								<table cellpadding="0" cellspacing="5" class="fixed">
								<tr>
									<?php for ($i=0, $loop=sizeof($images);$i<$loop;$i++) { ?>
									<td style="width:106px;"><div class="imagesList<?php echo $i == 0 ? 'on' : 'off'; ?>" onmouseover="ImageList(<?php echo $i; ?>,this,'over')" onmouseout="ImageList(<?php echo $i; ?>,this,'out')" onclick="ImageList(<?php echo $i; ?>,this,'click')"><img src="<?php echo $_ENV['dir']; ?>/userfile/erp/workspace/thumbneil/<?php echo $images[$i]['idx']; ?>.thm" /></div><span class="ImagesListDate"><?php echo GetTime('Y.m.d',$images[$i]['reg_date']); ?></span></td>
									<?php } ?>
								</tr>
								</table>
								</div>
							</td>
							<td class="center"><img src="<?php echo $this->moduleDir; ?>/images/workspace/btn_imglist_arrow_right.gif" class="pointer" onmouseover="ImageListScroll('right');" onmouseout="ImageListStop();" /></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
			<td></td>
			<td class="vTop">
				<div class="innerBorder" style="height:700px;">
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_location.gif" alt="대지위치" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo array_shift(explode('||',$this->workspace['workspace_address'])); ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_zone.gif" alt="지역/지구" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['zone']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_area.gif" alt="대지면적/건축면적" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['area']; ?>㎡ / <?php echo $this->workspace['buildarea']; ?>㎡</td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_size.gif" alt="건물규모" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['size']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_structure.gif" alt="건축구조" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['structure']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_buildpercent.gif" alt="용적율/건폐율" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['buildpercent']; ?>% / <?php echo $this->workspace['buildingcoverage']; ?>%</td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_purpose.gif" alt="건물용도" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['purpose']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_workdate.gif" alt="공사기간" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['workstart_date']; ?> ~ <?php echo $this->workspace['workend_date']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_workspacephone.gif" alt="현장번호" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['master']['name']; ?> / <?php echo $this->workspace['workspace_telephone']; ?></td>
					</tr>
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_architects.gif" alt="설계사무소" /></td>
					</tr>
					<tr>
						<td class="rightText"><?php echo $this->workspace['architects']; ?></td>
					</tr>
					</table>

					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<tr>
						<td class="rightTitle"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_map.gif" alt="주변지도보기" /></td>
					</tr>
					<tr>
						<td>
							<div id="workspaceMap" style="width:240px; height:240px;"></div>
							<script type="text/javascript" src="http://map.naver.com/js/naverMap.naver?key=<?php echo $this->module['mapapi']; ?>"></script>
							<?php
							if ($this->workspace['workspace_address']) {
								$mCrawler = new Crawler();
								$dummyData = $mCrawler->GetURLString('http://map.naver.com/api/geocode.php?key='.$this->module['mapapi'].'&query='.urlencode(str_replace('||',' ',$this->workspace['workspace_address'])));
								$pointX = $mCrawler->GetSubString($dummyData,'<x>','</x>');
								$pointY = $mCrawler->GetSubString($dummyData,'<y>','</y>');
							}
							?>

							<script type="text/javascript">
							/*지도 개체 생성 */
							var opts = {width:240,height:240,mapMode:1};
							var mapObj = new NMap(document.getElementById('workspaceMap'),opts);

							/* 지도 좌표, 축적 수 준 초기화 */
							mapObj.setCenterAndZoom(new NPoint(<?php echo $pointX; ?>,<?php echo $pointY; ?>),3);

							/* 지도 컨트롤 생성 */
							var zoom = new NZoomControl();

							zoom.setAlign("right");
							zoom.setValign("top");
							mapObj.addControl(zoom);

							/* 지도 모드 변경 버튼 생성 */
							var mapBtns = new NMapBtns();
							mapBtns.setAlign("right");
							mapBtns.setValign("top");
							mapObj.addControl(mapBtns);
							</script>
						</td>
					</tr>
					</table>
				</div>
			</td>
		</tr>
		</table>
	</div>

	<div class="footer">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="150" />
		<tr>
			<td class="copyment">고객을 위한 최적의 공간 - 변화와 도전 정신이 살아 숨쉬는 광흥건설이 함께하겠습니다.</td>
			<td>
				<select name="workspace" class="goWorkspace" onchange="if (this.value) location.href='<?php echo $_SERVER['PHP_SELF']; ?><?php echo GetQueryString(array('mode'=>'default','wno'=>''),'',false); ?>&wno='+this.value;">
				<option value="">다른현장바로가기</option>
				<?php
				$data = $this->mDB->DBfetchs($this->table['workspace'],array('idx','title'));
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					echo '<option value="'.$data[$i]['idx'].'">'.$data[$i]['title'].'</option>'."\n";
				}
				?>
				</select>
			</td>
		</tr>
		</table>
	</div>
</div>

</body>
</html>