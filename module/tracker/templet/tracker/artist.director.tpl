<div class="trackerArtist">
	<div class="viewTitle directorIcon">
		<div class="title">
			{$artist.name} : {if $artist.nation}{$artist.nation}{else}Unknown{/if}
		</div>
		<div class="subinfo">
			<span class="tahoma">
				{if $artist.eng_name}{$artist.eng_name}{else}Unknown{/if},
				{if $artist.date != '0000-00-00'}{$artist.date|date_format:"%B %d, %Y (%Z)"}{else}Unknown{/if}
			</span>
		</div>
	</div>

	<div class="height10"></div>
	
	{if $mode == 'artist'}
	<div class="areaTitle">
		<div class="inner">
			<div class="text">{if $artist.type == 'MAIN'}감독정보 (Director Intro){else}배우정보 (Actor Intro){/if}</div>
			<div for="TrackerGroupInfoArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
			<a href="{$artist.editlink}" class="edit"></a>
		</div>
	</div>
	
	<div id="TrackerGroupInfoArea">
		<div class="viewContent">
			<table cellpadding="0" cellspacing="1" class="infoTable">
			<col width="100" /><col width="100%" />
			<tr>
				<td class="header">{if $artist.type == 'MAIN'}감독{else}배우{/if}명</td>
				<td class="content">{$artist.name}</td>
			</tr>
			<tr>
				<td class="header">{if $artist.type == 'MAIN'}감독{else}배우{/if}명(영문)</td>
				<td class="content">{if $artist.eng_name}{$artist.eng_name}{else}영문타이틀이 없습니다.{/if}</td>
			</tr>
			<tr>
				<td class="header">국적</td>
				<td class="content">{if $artist.nation}{$artist.nation}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">출생일</td>
				<td class="content">{if $artist.date != '0000-00-00'}{$artist.date|date_format:"%B %d, %Y (%Z)"}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">성별</td>
				<td class="content">{if $artist.gender == 'MALE'}남성(MALE){elseif $artist.gender == 'FEMALE'}여성(FEMALE){else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" class="content">
					{if $artist.intro}{$artist.intro}{else}소개가 없습니다.<br />정보수정을 통해 정보를 입력하여 주십시오.{/if}
					{if $artist.daummovie}
						<br /><br />
						<a href="http://movie.daum.net/movieperson/Summary.do?personId={$artist.daummovie}" target="_blank">http://movie.daum.net/movieperson/Summary.do?personId={$artist.daummovie}</a>
					{/if}
				</td>
			</tr>
			</table>
		</div>
	
		<div class="viewSidebar">
			<div class="thumbnail">
				{if $artist.photo}
				<img src="{$artist.photo}" />
				{else}
				<div class="noimage">
					사진이<br />등록되지 않았습니다.
				</div>
				{/if}
			</div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	
	<div class="height10"></div>
	
	<div class="areaTitle">
		<div class="inner">
			<div class="text">토렌트 (Torrents)</div>
			<div for="TrackerTorrentListArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
		</div>
	</div>
	
	<div id="TrackerTorrentListArea" class="torrentList">
		<table cellspacing="0" cellpadding="0" class="layoutfixed">
		<col width="58" /><col width="100%" /><col width="50" /><col width="45" /><col width="45" />
		{foreach name=list from=$episode item=episode}
		<tr class="groupHeader">
			<td>
				<div class="split center innerimg">
					<img src="{$episode.thumbnail}" style="width:48px; height:60px; border:1px; margin:5px;" />
				</div>
			</td>
			<td>
				<div class="split">
					<div class="groupInfo">
						<div class="categorybox category{$episode.category1}">{$episode.category1Name}</div>
						<div class="title">
							<a href="{$episode.titlelink}">{$episode.title}{if $episode.season} S{$episode.season|string_format:"%02d"}{/if}</a>
							{if $episode.episode} : 
								<a href="{$episode.episodelink}">
								{if $episode.is_pack}
									E{$episode.episode.start|string_format:"%03d"}~E{$episode.episode.end|string_format:"%03d"}.Pack
								{else}
									{if $episode.episode}E{$episode.episode|string_format:"%03d"}{/if}
									{if $episode.episode_title} ({$episode.episode_title}){/if}
								{/if}
								</a>
							{/if}
						</div>
					</div>
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="100%" /><col width="40" /><col width="60" />
					<tr>
						<td class="vTop">
							<div class="subtitle">
								<span class="tahoma">{if $episode.eng_title}{$episode.eng_title} / {/if}{if $episode.date != '0000-00-00'}{$episode.date|date_format:"%B %d, %Y (%Z)"}{else}{$episode.year}{/if}</span><br />
								{if $episode.tag}{$episode.tag}{/if}{if $episode.tag && $episode.subartist}, {/if}{if $episode.subartist}{$episode.subartist}{/if}
							</div>
						</td>
						<td>
							<div class="groupSplit">files</div>
						</td>
						<td>
							<div class="groupSplit">sizes</div>
						</td>
					</tr>
					</table>
				</div>
			</td>
			<td>
				<div class="split">
					<div class="hSplit">Snatches</div>
					<div class="hSplitContent">{$episode.snatch|number_format}</div>
				</div>
			</td>
			<td>
				<div class="split">
					<div class="hSplit">Seeder</div>
					<div class="hSplitContent">{$episode.seeder|number_format}</div>
				</div>
			</td>
			<td>
				<div class="split" style="border-right:1px solid #DCDCDC;">
					<div class="hSplit">Leecher</div>
					<div class="hSplitContent">{$episode.leecher|number_format}</div>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="5">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100%" /><col width="20" /><col width="40" /><col width="60" /><col width="50" /><col width="45" /><col width="45" />
				{foreach name=torrent from=$episode.torrent item=torrent}
				<tr onmouseover="this.style.background='#F4F4F4';" onmouseout="this.style.background='#FFFFFF';">
					<td>
						<div class="torrentInfo">
							<div class="torrentText">
							<a href="{$torrent.torrentlink}">{if $torrent.subtitles}{$torrent.subtitles} /{/if}
							{if $torrent.resolution}{$torrent.resolution} /{/if}
							{if $torrent.source}{$torrent.source} /{/if}
							{if $torrent.codec}{$torrent.codec} /{/if}
							{if $torrent.format}{$torrent.format} /{/if}
							{if $torrent.edition}{$torrent.edition} /{/if}
							{if $torrent.release}{$torrent.release} /{/if}
							<span class="reg_date">{$torrent.reg_date|date_format:"%h %d, %Y, %I:%M%P %Z"}</span></a>
							</div>
							{if $torrent.is_freeleech}<div class="icon_freeleech"></div>{/if}
							{if $torrent.is_halfleech}<div class="icon_halfleech"></div>{/if}
							{if $torrent.is_doubleupload}<div class="icon_doubleupload"></div>{/if}
							{if $torrent.is_exclusive}<div class="icon_exclusive"></div>{/if}
						</div>
					</td>
					<td>
						{if $torrent.is_exclusive == false}<a href="{$torrent.downloadlink}" target="downloadFrame"><img src="{$skinDir}/images/icon_disk.png" alt="download" /></a>{/if}
					</td>
					<td>
						<div class="torrentSplit">{$torrent.file|number_format}</div>
					</td>
					<td>
						<div class="torrentSplit">{$torrent.filesize}</div>
					</td>
					<td>
						<div class="torrentSplit">{$torrent.snatch|number_format}</div>
					</td>
					<td>
						<div class="torrentSplit">{$torrent.seeder|number_format}</div>
					</td>
					<td>
						<div class="torrentSplit" style="border-right:1px solid #DCDCDC;">{$torrent.leecher|number_format}</div>
					</td>
				</tr>
				{/foreach}
				</table>
			</td>
		</tr>
		{/foreach}
		<tr>
			<td colspan="5" style="background:#DCDCDC; height:1px; overflow:hidden;"></td>
		</tr>
		</table>
	</div>
	{else}
	{$formStart}
	<table cellpadding="0" cellspacing="1" class="infoTable">
	<col width="100" /><col width="100%" />
	<tr>
		<td class="header">감독명</td>
		<td class="content"><input type="text" name="name" value="{$artist.name}" class="inputbox" style="width:150px;" /></td>
	</tr>
	<tr>
		<td class="header">감독명(영문)</td>
		<td class="content"><input type="text" name="eng_name" value="{$artist.eng_name}" class="inputbox" style="width:300px;" /></td>
	</tr>
	<tr>
		<td class="header">국적</td>
		<td class="content"><input type="text" name="nation" value="{$artist.nation}" class="inputbox" style="width:150px;" /></td>
	</tr>
	<tr>
		<td class="header">출생일</td>
		<td class="content"><input type="text" name="date" value="{if $artist.date != '0000-00-00'}{$artist.date}{else}{/if}" class="inputbox center" style="width:80px;" /> (YYYY-MM-DD)</td>
	</tr>
	<tr>
		<td class="header">성별</td>
		<td class="content">
			<input type="hidden" name="gender" value="{$artist.gender}" />
			<div id="TrackerGender" class="selectbox" style="width:150px;">
				<div onclick="InputSelectBox('TrackerGender')" clicker="TrackerGender">{if $artist.gender == 'MALE'}남성(MALE){elseif $artist.gender == 'FEMALE'}여성(FEMALE){else}선택(Select){/if}</div>
				<ul style="display:none;" clicker="TrackerFormat">
					<li onclick="InputSelectBoxSelect('TrackerGender','남성(MALE)','MALE',TrackerListSelectGender)">남성(MALE)</li>
					<li onclick="InputSelectBoxSelect('TrackerGender','여성(FEMALE)','FEMALE',TrackerListSelectGender)">여성(FEMALE)</li>
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<td class="header">소개</td>
		<td class="content">
			<textarea name="intro" class="textbox">{$artist.intro}</textarea>
		</td>
	</tr>
	<tr>
		<td class="header">사진</td>
		<td class="content">
			<input type="file" name="photo" class="filebox" />
			{if $artist.photo}
			<div class="height5"></div>
			<div><input id="delete_photo" type="checkbox" name="delete_photo" value="true" /> <label for="delete_photo">업로드 된 사진을 삭제합니다.</label></div>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="header">다음인물URL</td>
		<td class="content">
			http://movie.daum.net/movieperson/Summary.do?personId=<input type="text" name="daummovie" class="inputbox" style="width:60px;" value="{$artist.daummovie}" />
		</td>
	</tr>
	</table>
	
	<div class="height10"></div>
	
	<div class="center"><input type="image" src="{$skinDir}/images/btn_submit.png" /></div>
	{$formEnd}
	{/if}
</div>