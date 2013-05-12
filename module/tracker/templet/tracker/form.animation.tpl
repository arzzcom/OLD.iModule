{if $mode == 'write' && $addmode == ''}
<table cellpadding="0" cellspacing="1" class="trackerFormTable">
<col width="110" /><col width="100%" />
<tr>
	<td class="header">자료검색</td>
	<td class="content">
		<div style="position:relative; width:600px;">
			<input type="text" id="TrackerGroupSearchInput" onfocus="TrackerGroupLiveSearchStart({$category},'daumanimation');" onblur="TrackerGroupLiveSearchEnd();" autocomplete="off" onkeydown="TrackerGroupLiveSearchListMove(event)" class="inputbox" style="width:594px;" artist="감독" subartist="" />
			<div id="TrackerGroupSearchCancel" style="display:none;" onclick="TrackerGroupLiveSearchCancel();"></div>
			<div id="TrackerGroupSearchList" style="width:600px; display:none;"></div>
		</div>
		
		<div class="formInfo">
			다음DB와 기존에 등록되어 있는 자료를 검색하여 기본정보를 자동으로 완성할 수 있습니다.<br />
			<span class="point">REGISTED</span>아이콘이 있는 자료는 이미 기본정보가 모두 입력되어 있는 자료로서, <span class="point">그룹정보를 입력하지 않아도 되므로 입력폼이 숨겨지게</span> 됩니다.<br />
			위의 검색폼에 <span class="point">제목을 입력하신 후 자동으로 완성된 목록을 마우스로 클릭하거나, 키보드 방향키 상/하를 눌러 원하는 목록을 찾은 뒤 엔터버튼</span>을 누르면 해당 정보로 자동으로 완성됩니다.
		</div>
	</td>
</tr>
</table>
<div class="height10"></div>
{/if}

{$formStart}

{if ($mode == 'write' && $addmode == '') || $mode == 'group'}
<div id="TrackerPostGroup">
	<div class="boxBlue">
		<span class="bold f14">그룹정보입력</span> <br />
		서로 다른 포맷의 자료를 하나의 애니메이션 제목으로 그룹핑하기 위해 기본정보를 입력합니다.<br />
		아래의 정보를 입력하기전에 가급적 위의 기존그룹검색 또는 다음검색으로 검색하여 정보를 자동으로 채우기를 권장합니다.
	</div>
	
	<div class="height10"></div>
	
	<table cellpadding="0" cellspacing="1" class="trackerFormTable">
	<col width="110" /><col width="100%" />
	<tr>
		<td class="header">타이틀 (한글)<span class="pointText">*</span><br />Title (Korean)<span class="pointText">*</span></td>
		<td class="content">
			<input type="text" name="title" value="{$group.title}" class="inputbox" style="width:594px;" />
			
			<div class="formInfo">
				프로그램 제목은 <span class="point">다음 TV프로그램 정보를 따릅니다. 가급적 다음TV검색을 사용하기를 권장</span>합니다. (예 : 원피스, 명탐정 코난)<br />
				다음TV프로그램에 없는 프로그램일 경우, <span class="point">원제목을 입력</span>합니다. (예 : Onepiece)<br />
				<span class="point">극장판 애니메이션의 경우 각각의 개별적인 애니메이션으로 간주하며, 타이틀을 극장판 타이틀로 입력</span>하셔야 합니다.
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">타이틀 (영문)<br />Title (English)</td>
		<td class="content">
			<input type="text" name="eng_title" value="{$group.eng_title}" class="inputbox" style="width:594px;" />
			
			<div class="formInfo">
				국내 애니메이션일 입력하지 않아도 무방하나, 정식 수출된 애니메이션의 경우 정식 영문 타이틀을 입력하여야 합니다.<br />
				국외 애니메이션일 경우, 국외 정식 애니메이션명을 입력하여야 합니다. 다음TV프로그램에 있는 경우 다음TV 애니메이션명을 우선시 합니다.<br />
				다음TV프로그램에 없고, 영문제목이 없는 외국 애니메이션일 경우, 해당국가의 원 제목을 원어로 입력합니다.
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">시즌<br />Season</td>
		<td class="content">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="60" /><col width="200" /><col width="100%" />
			<tr>
				<td><input type="text" name="season" value="{$group.season}" class="inputbox" style="width:50px;" onblur="TrackerWriteNumberOnly(this);" /></td>
				<td class="bold right">시즌 부제목(Season Subtitle) :&nbsp;&nbsp;</td>
				<td><input type="text" name="season_title" value="{$group.season_title}" class="inputbox" style="width:300px;" /></td>
			</tr>
			</table>
			
			<div class="formInfo">
				시즌으로 구분되어 있는 애니메이션의 경우 시즌을 <span class="point">숫자로만 입력</span>하여야 합니다.<br />
				해당 시즌에 대한 공식 부제목이 있는 경우 시즌 부제목도 선택적으로 입력할 수 있습니다.
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">감독<span class="pointText">*</span><br />Director<span class="pointText">*</span><input type="hidden" name="artist"></td>
		<td id="TrackerArtistSearchInputList" class="content">
			<div id="TrackerArtistSearchInputArea">
				<input id="TrackerArtistSearchInput" type="text" value="" onkeyup="TrackerArtistItemInsert(event);" onkeydown="return TrackerArtistLiveSearchListMove(event);" onblur="TrackerArtistItemComplete();" onfocus="TrackerArtistLiveSearchStart({$category});" class="inputbox" style="width:114px;" />
				<div id="TrackerArtistSearchList" style="width:120px; display:none;"></div>
			</div>
			
			<div class="formInfo">
				다음TV프로그램 DB를 참고하여, 애니메이션 감독을 띄워쓰기를 포함하여 한글로 적어주십시오. (예 : 김태호, 마이클 무어)<br />
				감독이 여러명일 경우 콤마(,)로 구분하여 입력할 수 있습니다. 가급적 감독명 자동완성기능을 통해 입력하여 주십시오.<br />
				위의 입력폼에 <span class="point">이름을 입력하신 후 자동으로 완성된 목록을 마우스로 클릭하거나, 키보드 방향키 상/하를 눌러 원하는 목록을 찾은 뒤 엔터버튼</span>을 누르면 입력됩니다.
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">제작년도<span class="pointText">*</span><br />Year<span class="pointText">*</span></td>
		<td class="content">
			<input type="text" name="year" class="inputbox center" maxlength="4" style="width:40px;" value="{$group.year}" />
			
			<div class="formInfo">
				제작년도를 알 경우 YYYY형식(예 : 2012)으로 입력하여 주십시오.
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">제작국가<br />Nation<input type="hidden" name="nation"></td>
		<td id="TrackerNationInputList" class="content">
			<div id="TrackerNationInputArea"><input id="TrackerNationInput" type="text" value="" onkeyup="TrackerNationItemInsert(event);" onblur="TrackerNationItemComplete();" class="inputbox" style="width:80px;" /></div>
			
			<div class="formInfo">
				제작국가를 콤마(,)로 구분하여 한글로 입력하여 주십시오.<br />
				(예 : 한국,미국,일본,영국,인도,중국,러시아,필리핀,프랑스 등)
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">프로그램소개<span class="pointText">*</span><br />Intro<span class="pointText">*</span></td>
		<td class="content">
			<textarea name="intro" class="textbox">{$group.intro}</textarea>
	
			<div class="formInfo">
				다음TV프로그램을 참고하여, 프로그램에 대한 소개를 입력하여 주십시오.<br />
				연속방영물의 경우, 각 회별 소개는 입력하지 않습니다.
				자료설명 중 참고할만한 URL이 있다면 포함하여 주십시오. (다음TV프로그램검색을 사용하였을 때는 다음영화 주소는 자동으로 입력됩니다.)
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">포스터<br />Poster</td>
		<td class="content">
			<input type="file" name="group_image" class="filebox" />
			{if $group.thumbnail}
			<div class="height10"></div>
			<div><input id="delete_group_image" type="checkbox" name="delete_group_image" value="true" /> <label for="delete_group_image">업로드 된 포스터이미지를 삭제합니다.</label></div>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="header">태그<span class="pointText">*</span><br />Tag<span class="pointText">*</span></td>
		<td class="content" style="height:24px;">
			{foreach name=taglist from=$taglist item=data}
				<div style="margin:5px; float:left;"><input type="checkbox" name="tag[]" value="{$data.idx}"{if $data.checked == true} checked="checked"{/if} /> {$data.title}</div>
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="header">유투브<br />Youtube</td>
		<td class="content">
			http://www.youtube.com/watch?v=<input type="text" name="field1" value="{$group.field1}" class="inputbox" style="width:100px;" />
			
			<div class="formInfo">
				유튜브(http://www.youtube.com)에 프로그램에 대한 예고편이 있다면, 예고편주소의 watch?v=[ ] 부분의 값을 입력하여 주십시오. (예 : TQB0CeXCBOc)
			</div>
		</td>
	</tr>
	</table>
</div>
<div class="height10"></div>
{/if}

{if $mode == 'write' || $mode == 'torrent' || $mode == 'episode'}
<div class="boxBlue">
	<span class="bold f14">개별 토렌트 정보</span> <br />
	공통된 그룹정보외에 등록하고자 하는 토렌트의 개별적인 정보를 입력합니다.
</div>

<div class="height10"></div>

<table cellpadding="0" cellspacing="1" class="trackerFormTable">
<col width="110" /><col width="100%" />
<tr>
	<td class="header">에피소드<br />Episode</td>
	<td class="content">
		<div id="TrackerEpisode" class="selectbox" style="width:600px; display:none; margin-bottom:5px;">
			<div onclick="InputSelectBox('TrackerEpisode')" clicker="TrackerEpisode">기존에 등록된 에피소드 선택 (Select Registed Episode)</div>
			<ul style="display:none;" clicker="TrackerEpisode"></ul>
		</div>
		
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="70" /><col width="220" /><col width="185" /><col width="20" /><col width="100%" />
		<tr>
			<td>
				<input type="text" name="episode" value="{$episode.episode}" class="inputbox" style="width:50px;" onblur="TrackerWriteNumberOnly(this);" searchFunction="TrackerListEpisodePrint" {if $episode.is_pack} disabled="disabled"{/if} />
			</td>
			<td class="bold right"><span id="TrackerListTitleLabel">에피소드 부제목(Episode Subtitle)</span> :&nbsp;&nbsp;</td>
			<td><input type="text" name="episode_title" value="{$episode.episode_title}" class="inputbox" style="width:170px;" /></td>
			<td><input type="checkbox" name="is_pack" value="true" onclick="TrackerWriteIsPackage(this);" is_pack_title="범위(Range of package)" is_unpack_title="에피소드 부제목(Episode Subtitle)"{if $episode.is_pack} checked="checked"{/if} /></td>
			<td>패키지(Package)</td>
		</tr>
		</table>
		
		<div class="formInfo">
			기존에 등록되어 있는 에피소드를 선택하거나, 새로운 에피소드를 입력할 수 있습니다.<br />
			에피소드는 <span class="point">숫자로만 입력</span>합니다.<br />
			해당 회차에 대한 공식 부제목이 있는 경우 시즌 부제목도 선택적으로 입력할 수 있습니다.<br />
			<span class="point">팩 묶음일 경우</span>(예 1~100회 묶음), <span class="point">패키지에 체크를 한 다음 범위를 입력</span>합니다. (예 : 1~100)
		</div>
	</td>
</tr>
<tr>
	<td class="header">방영일<br />Broadcast Date</td>
	<td class="content">
		<input type="text" name="episode_date" value="{$episode.date}" class="inputbox center" maxlength="10" size="10" style="width:75px;" />
		
		<div class="formInfo">
			국내TV에 방영된 프로그램일 경우 국내방영일자를 그렇지 않은 경우 국외공식방영일자를 입력합니다.<br />
			방영일은 YYYY-MM-DD형식(예 : 2012-01-01)으로 입력하여 주십시오.
		</div>
	</td>
</tr>
<tr>
	<td class="header">회차정보<br />Series Info</td>
	<td class="content">
		<textarea name="episode_intro" class="textbox">{$episode.intro}</textarea>
		
		<div class="formInfo">
			시리즈물일 경우 업로드하고자 하는 회차에 대한 간략한 정보를 입력할 수 있습니다.
		</div>
	</td>
</tr>
{if $mode == 'write' || $mode == 'torrent'}
<tr>
	<td class="header">자막<br />Subtitles</td>
	<td class="content">
		<input type="hidden" name="subtitles" value="{$torrent.subtitles}" />
		<div id="TrackerSubtitles" class="selectbox" style="width:200px;">
			<div onclick="InputSelectBox('TrackerSubtitles')" clicker="TrackerSubtitles">{if $torrent.subtitles}{$torrent.subtitles}{else}무자막(None){/if}</div>
			<ul style="display:none;" clicker="TrackerSubtitles">
				<li onclick="InputSelectBoxSelect('TrackerSubtitles','무자막(None)','',TrackerListSelectSubtitles)">무자막(None)</li>
				<li onclick="InputSelectBoxSelect('TrackerSubtitles','한글(Korean)','한글',TrackerListSelectSubtitles)">한글(Korean)</li>
				<li onclick="InputSelectBoxSelect('TrackerSubtitles','한글+영어(Korean+English)','한글+영어',TrackerListSelectSubtitles)">한글+영어(Korean+English)</li>
				<li onclick="InputSelectBoxSelect('TrackerSubtitles','영어(English)','영어',TrackerListSelectSubtitles)">영어(English)</li>
				<li onclick="InputSelectBoxSelect('TrackerSubtitles','원어(Origin)','원어',TrackerListSelectSubtitles)">원어(Origin)</li>
			</ul>
		</div>
		
		<div class="formInfo">
			토렌트 파일내에 자막이 포함되어 있는 경우 선택하여 주십시오.<br />
			자막은 토렌트 등록후 별도로 등록할 수 있습니다.
		</div>
	</td>
</tr>
<tr>
	<td class="header">해상도<span class="pointText">*</span><br />Resolution<span class="pointText">*</span></td>
	<td class="content">
		<input type="hidden" name="resolution" value="{$torrent.resolution}" />
		<div id="TrackerResolution" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('TrackerResolution')" clicker="TrackerResolution">{if $torrent.resolution}{$torrent.resolution}{else}선택(Select){/if}</div>
			<ul style="display:none;" clicker="TrackerResolution">
				<li onclick="InputSelectBoxSelect('TrackerResolution','SD','SD',TrackerListSelectResolution)">SD</li>
				<li onclick="InputSelectBoxSelect('TrackerResolution','720p','720p',TrackerListSelectResolution)">720p</li>
				<li onclick="InputSelectBoxSelect('TrackerResolution','1080p','1080p',TrackerListSelectResolution)">1080p</li>
				<li onclick="InputSelectBoxSelect('TrackerResolution','1080i','1080i',TrackerListSelectResolution)">1080i</li>
				<li onclick="InputSelectBoxSelect('TrackerResolution','NTSC','NTSC',TrackerListSelectResolution)">NTSC</li>
				<li onclick="InputSelectBoxSelect('TrackerResolution','포터블(Portable)','포터블',TrackerListSelectResolution)">포터블(Portable)</li>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td class="header">코덱<span class="pointText">*</span><br />Codec<span class="pointText">*</span></td>
	<td class="content">
		<input type="hidden" name="codec" value="{$torrent.codec}" />
		<div id="TrackerCodec" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('TrackerCodec')" clicker="TrackerCodec">{if $torrent.codec}{$torrent.codec}{else}선택(Select){/if}</div>
			<ul style="display:none;" clicker="TrackerCodec">
				<li onclick="InputSelectBoxSelect('TrackerCodec','XviD.DivX','XviD.DivX',TrackerListSelectCodec)">XviD.DivX</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','H.264.AVC','H.264.AVC',TrackerListSelectCodec)">H.264.AVC</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','MPEG1','MPEG1',TrackerListSelectCodec)">MPEG1</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','MPEG2','MPEG2',TrackerListSelectCodec)">MPEG2</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','WMV','WMV',TrackerListSelectCodec)">WMV</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','VC-1','VC-1',TrackerListSelectCodec)">VC-1</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','DVD5','DVD5',TrackerListSelectCodec)">DVD5</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','DVD9','DVD9',TrackerListSelectCodec)">DVD9</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','MP4','MP4',TrackerListSelectCodec)">MP4</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','Mixed','Mixed',TrackerListSelectCodec)">Mixed</li>
				<li onclick="InputSelectBoxSelect('TrackerCodec','Others','Others',TrackerListSelectCodec)">Others</li>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td class="header">소스<span class="pointText">*</span><br />Source<span class="pointText">*</span></td>
	<td class="content">
		<input type="hidden" name="source" value="{$torrent.source}" />
		<div id="TrackerSource" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('TrackerSource')" clicker="TrackerSource">{if $torrent.source}{$torrent.source}{else}선택{/if}</div>
			<ul style="display:none;" clicker="TrackerSource">
				<li onclick="InputSelectBoxSelect('TrackerSource','SDTV','SDTV',TrackerListSelectSource)">SDTV</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','HDTV','HDTV',TrackerListSelectSource)">HDTV</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','IPTV','IPTV',TrackerListSelectSource)">IPTV</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','DVD','DVD',TrackerListSelectSource)">DVD</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','Blu-Ray','Blu-Ray',TrackerListSelectSource)">Blu-Ray</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','BRRip','BRRip',TrackerListSelectSource)">BRRip</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','HD-DVD','HD-DVD',TrackerListSelectSource)">HD-DVD</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','CAM.TS','CAM.TS',TrackerListSelectSource)">CAM.TS</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','R5','R5',TrackerListSelectSource)">R5</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','Screener','Screener',TrackerListSelectSource)">Screener</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','WebRip','WebRip',TrackerListSelectSource)">WebRip</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','WebDL','WebDL',TrackerListSelectSource)">WebDL</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','Portable','Portable',TrackerListSelectSource)">Portable</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','Unknown','Unknown',TrackerListSelectSource)">Unknown</li>
				<li onclick="InputSelectBoxSelect('TrackerSource','Others','Others',TrackerListSelectSource)">Others</li>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td class="header">포맷<span class="pointText">*</span><br />File Format<span class="pointText">*</span></td>
	<td class="content">
		<input type="hidden" name="format" value="{$torrent.format}" />
		<div id="TrackerFormat" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('TrackerFormat')" clicker="TrackerFormat">{if $torrent.format}{$torrent.format}{else}선택(Select){/if}</div>
			<ul style="display:none;" clicker="TrackerFormat">
				<li onclick="InputSelectBoxSelect('TrackerFormat','AVI','AVI',TrackerListSelectFormat)">AVI</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','MKV','MKV',TrackerListSelectFormat)">MKV</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','WMV','WMV',TrackerListSelectFormat)">WMV</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','MPEG','MPEG',TrackerListSelectFormat)">MPEG</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','TP.TS','TP.TS',TrackerListSelectFormat)">TP.TS</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','MTS.M2TS','MTS.M2TS',TrackerListSelectFormat)">MTS.M2TS</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','MP4','MP4',TrackerListSelectFormat)">MP4</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','ISO','ISO',TrackerListSelectFormat)">ISO</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','VOB','VOB',TrackerListSelectFormat)">VOB</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','Mixed','Mixed',TrackerListSelectFormat)">Mixed</li>
				<li onclick="InputSelectBoxSelect('TrackerFormat','Others','Others',TrackerListSelectFormat)">Others</li>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td class="header">릴리즈그룹<br />Release Group</td>
	<td class="content">
		<input type="text" name="release" value="{$torrent.release}" class="inputbox" />
		
		<div class="formInfo">
			동영상을 릴리즈한 그룹명을 알고 있을 경우에만 릴리즈 그룹명을 입력하여 주십시오. (예 : HanRel)
		</div>
	</td>
</tr>
<!-- tr>
	<td class="header">NFO파일<br />NFO File</td>
	<td class="content">
		<input type="file" name="nfo" class="filebox" />
	</td>
</tr -->
<tr>
	<td class="header">스크린샷<span class="pointText">*</span><br />ScreenShot<span class="pointText">*</span></td>
	<td class="content">
		<input type="file" name="screenshot" class="filebox" />
		
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		{if $torrent.screenshot}<col width="80" />{/if}<col width="100%" />
		<tr>
			{if $torrent.screenshot}<td><img src="{$torrent.screenshot.thumbnail}" style="width:70px; margin-top:5px; border:1px solid #CCCCCC;" /></td>{/if}
			<td>
				<div class="formInfo">
					동영상 원본크기와 동일한 스크린샷 1장은 반드시 포함되어야 합니다.<br />
					Screenshot 1 videos original size and same chapter must be included.
					{if $torrent.screenshot}<br />이미지를 변경하려면, 새로운 이미지를 선택하세요.(To change the image, and select a new image.){/if}
				</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="header">스냅샷<span class="pointText">*</span><br />SnapShot<span class="pointText">*</span></td>
	<td class="content">
		<input type="file" name="snapshot" class="filebox" />
		
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		{if $torrent.snapshot}<col width="80" />{/if}<col width="100%" />
		<tr>
			{if $torrent.snapshot}<td><img src="{$torrent.snapshot.thumbnail}" style="width:70px; margin-top:5px; border:1px solid #CCCCCC;" /></td>{/if}
			<td>
				<div class="formInfo">
					9컷 이상의 스냅샷은 반드시 포함되어야 합니다. 스냅샷을 만드는 방법은 도움말을 참고하여 주십시오.<br />
					Snapshot cut more than 9 must be included.
					{if $torrent.snapshot}<br />이미지를 변경하려면, 새로운 이미지를 선택하세요.(To change the image, and select a new image.){/if}
				</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="header">추가스크린샷<br />Addition ScreenShot</td>
	<td class="content">
		{mTracker->PrintUploader type="addition" form="TrackerPost" id="uploader" skin="default"}
		<div class="formInfo">
			파일첨부 버튼을 눌러 여러장의 이미지 파일을 한번에 선택하여 업로드할 수 있습니다.<br />
			이 기능을 사용하기 위해서는 플래시 플레이어가 설치되어 있어야 합니다.
		</div>
	</td>
</tr>
{if $mode == 'write'}
<tr>
	<td class="header">토렌트파일<span class="pointText">*</span><br />Torrent File<span class="pointText">*</span></td>
	<td class="content">
		<input type="file" name="torrent" class="filebox" />
	</td>
</tr>
{/if}
<tr>
	<td class="header">미디어정보<span class="pointText">*</span><br />Media Info<span class="pointText">*</span></td>
	<td class="content">
		<textarea name="mediainfo" class="textbox" allowBlank="false">{$torrent.media_info}</textarea>
		
		<div class="formInfo">
			MediaInfo를 이용한 인코딩 정보는 필수적으로 포함되어야 합니다.
		</div>
	</td>
</tr>
{/if}
</table>
{/if}

<div class="height10"></div>
<div class="center">
	<input type="image" src="{$skinDir}/images/btn_submit.png" />
</div>
<br /><br />
{$formEnd}