<div class="trackerView">
	<div class="viewTitle {$viewmode}Icon">
		<div class="title">
			<a href="{$group.link}">{$group.title}{if $group.season} S{$group.season|string_format:"%02d"}{/if}{if $group.season_title} ({$group.season_title}){/if}</a>
			{if $viewmode == 'episode' || $viewmode == 'torrent'}
				{if $episode.episode || $episode.episode_title}
					 : <a href="{$episode.link}">
					{if $episode.is_pack == true}
						E{$episode.episode.start|string_format:"%03d"}~E{$episode.episode.end|string_format:"%03d"}
					{else}
						{if $episode.episode}
							E{$episode.episode|string_format:"%03d"}
							{if $episode.episode_title}<span class="normal">({$episode.episode_title})</span>{/if}
						{else}
							{if $episode.episode_title}<span class="bold">{$episode.episode_title}</span>{/if}
						{/if}
					{/if}
					</a>
				{/if}
			{/if}
		</div>
		<div class="subinfo">
			<span class="tahoma">
				{if $group.eng_title}{$group.eng_title}, {/if}
				{if $group.date != '0000-00-00'}{$group.date|date_format:"%B %d, %Y (%Z)"}{else}{$group.year}{/if}
			</span>
		</div>
	</div>

	<div class="height10"></div>
	
	<div class="areaTitle">
		<div class="inner">
			<div class="text">그룹정보 (Group Intro)</div>
			<div for="TrackerGroupInfoArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
			<a href="{$group.editlink}" class="edit"></a>
		</div>
	</div>
	
	<div id="TrackerGroupInfoArea">
		<div class="viewContent">
			<table cellpadding="0" cellspacing="1" class="infoTable">
			<col width="100" /><col width="100%" />
			<tr>
				<td class="header">타이틀</td>
				<td class="content">{$group.title}</td>
			</tr>
			<tr>
				<td class="header">타이틀(영문)</td>
				<td class="content">{if $group.eng_title}{$group.eng_title}{else}영문타이틀이 없습니다.{/if}</td>
			</tr>
			<tr>
				<td class="header">제작국가</td>
				<td class="content">{if $group.nation}{$group.nation}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">제작년도</td>
				<td class="content">{if $group.year}{$group.year}년{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">개봉일</td>
				<td class="content">{if $group.date != '0000-00-00'}{$group.date|date_format:"%B %d, %Y (%Z)"}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">감독</td>
				<td class="content">{if $group.artist}{$group.artist}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">주연배우</td>
				<td class="content">{if $group.subartist}{$group.subartist}{else}알수없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">카테고리</td>
				<td class="content">{if $group.category}{$group.category}{else}없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td class="header">태그</td>
				<td class="content">{if $group.tag}{$group.tag}{else}없음{/if}&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" class="content">
					{$group.intro}
					{if $group.daummovie}
						<br /><br />
						<a href="http://movie.daum.net/moviedetail/moviedetailMain.do?movieId={$group.daummovie.idx}" target="_blank">http://movie.daum.net/moviedetail/moviedetailMain.do?movieId={$group.daummovie.idx}</a>
					{/if}
					
					{if $group.daumtv}
						<br /><br />
						<a href="http://movie.daum.net/tv/detail/main.do?tvProgramId={$group.daumtv.idx}" target="_blank">http://movie.daum.net/tv/detail/main.do?tvProgramId={$group.daumtv.idx}</a>
					{/if}
				</td>
			</tr>
			</table>
			{if $group.field1}
			<div class="height10"></div>
			<iframe title="YouTube Video Player" class="youtube-player" type="text/html" width="535" height="330" src="http://www.youtube.com/embed/{$group.field1}?wmode=opaque" frameborder="0" allowFullScreen></iframe>
			{/if}
		</div>
	
		<div class="viewSidebar">
			<div class="thumbnail">
				{if $group.thumbnail.middle}
				<img src="{$group.thumbnail.middle}" />
				{/if}
			</div>
			
			<div class="viewPoint">
			<div class="star"><div class="on" style="width:{if $group.vote_user == 0}0{else}{$group.vote_point/$group.vote_user*10}{/if}%;"></div></div>
			<div class="point">{if $group.vote_user == 0}no score{else}{$group.vote_point/$group.vote_user|string_format:"%0.2f"}<span style="color:#666666; font-weight:normal;"> / {$group.vote_user|number_format}</span>{/if}</div>
			</div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	
	{if $episodemode == 'episode' && ($viewmode == 'episode' || $viewmode == 'torrent')}
	<div class="height10"></div>
	
	<div class="areaTitle">
		<div class="inner">
			<div class="text">에피소드정보 (Episode Intro)</div>
			<div for="TrackerEpisodeArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
			<a href="{$episode.editlink}" class="edit"></a>
		</div>
	</div>
	
	<div id="TrackerEpisodeArea">
		<table cellpadding="0" cellspacing="1" class="infoTable">
		<col width="100" /><col width="100%" />
		<tr>
			<td class="header">에피소드명</td>
			<td class="content">{if $episode.is_pack}E{$episode.episode.start|string_format:"%03d"}~E{$episode.episode.end|string_format:"%03d"}.Pack{else}{if $episode.episode}E{$episode.episode|string_format:"%03d"}{/if}{if $episode.episode_title} {$episode.episode_title}{/if}{/if}</td>
		</tr>
		<tr>
			<td class="header">제작년도</td>
			<td class="content">{if $episode.year}{$episode.year}년{else}알수없음{/if}&nbsp;</td>
		</tr>
		<tr>
			<td class="header">개봉일</td>
			<td class="content">{if $episode.date != '0000-00-00'}{$episode.date|date_format:"%B %d, %Y (%Z)"}{else}알수없음{/if}</td>
		</tr>
		<tr>
			<td colspan="2" class="content">
				{if $episode.intro}{$episode.intro}{else}에피소드 정보가 없습니다.{/if}
			</td>
		</tr>
		</table>
	</div>
	{/if}
	
	<div class="height10"></div>
	
	{if $viewmode == 'episode' || $viewmode == 'torrent'}
	<div class="areaTitle">
		<div class="inner">
			<div class="text">토렌트 (Torrents)</div>
			<div for="TrackerTorrentListArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
			<a href="{$link.addtorrent}" class="addtorrent"></a>
		</div>
	</div>

	<div id="TrackerTorrentListArea">
		<table cellpadding="0" cellspacing="1" class="torrentList">
		<col width="100%" /><col width="80" /><col width="50" /><col width="45" /><col width="45" />
		<tr class="tHead">
			<td><div>Torrents</div></td>
			<td><div>Filesize</div></td>
			<td><div>Snatch</div></td>
			<td><div>Seeder</div></td>
			<td><div>Leecher</div></td>
		</tr>
		{foreach name=torrent from=$torrent item=torrent}
		<tr class="tBody">
			<td class="torrentTitle">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100%" /><col width="20" />
				<tr>
					<td>
						<div class="torrentText pointer" onclick="TrackerViewToggleTorrent({$torrent.idx});">
						{if $torrent.subtitles}{$torrent.subtitles} /{/if}
						{if $torrent.resolution}{$torrent.resolution} /{/if}
						{if $torrent.source}{$torrent.source} /{/if}
						{if $torrent.codec}{$torrent.codec} /{/if}
						{if $torrent.format}{$torrent.format} /{/if}
						{if $torrent.edition}{$torrent.edition} /{/if}
						{if $torrent.release}{$torrent.release} /{/if}
						<span class="reg_date">{$torrent.reg_date|date_format:"%h %d, %Y, %I:%M%P %Z"}</span>
						</div>
						{if $torrent.is_freeleech}<div class="icon_freeleech"></div>{/if}
						{if $torrent.is_halfleech}<div class="icon_halfleech"></div>{/if}
						{if $torrent.is_doubleupload}<div class="icon_doubleupload"></div>{/if}
						{if $torrent.is_exclusive}<div class="icon_exclusive"></div>{/if}
					</td>
					<td class="center"><a href="{$torrent.downloadlink}" target="downloadFrame"><img src="{$skinDir}/images/icon_disk.png" alt="download" /></a></td>
				</tr>
				</table>
			</td>
			<td class="center tahoma f11">{$torrent.filesize}</td>
			<td class="count">{$torrent.snatch|number_format}</td>
			<td class="count">{$torrent.seeder|number_format}</td>
			<td class="count">{$torrent.leecher|number_format}</td>
		</tr>
		<tr id="TrackerViewTorrent-{$torrent.idx}" style="display:{if $torrent.is_select == false}none{/if};">
			<td class="torrentContent" colspan="5">
				<div class="height5"></div>
				
				<div class="areaTitle">
					<div class="inner">
						<div class="text">파일목록 (Files)</div>
						<div for="TrackerFileListArea-{$torrent.idx}" class="toggle off" onclick="TrackerViewToggleTorrentFile(this,{$torrent.idx});"></div>
					</div>
				</div>
				
				<div id="TrackerFileListArea-{$torrent.idx}" style="display:none;">
					<div class="loadingbox">파일목록을 불러오고 있습니다. 잠시만 기다려주십시오.<br />Now Loading... Please, Wait.</div>
				</div>
				
				<div class="height5"></div>
				
				<div class="areaTitle">
					<div class="inner">
						<div class="text">피어목록 (Peers)</div>
						<div for="TrackerPeerListArea-{$torrent.idx}" class="toggle off" onclick="TrackerViewToggleTorrentPeer(this,{$torrent.idx});"></div>
					</div>
				</div>
				
				<div id="TrackerPeerListArea-{$torrent.idx}" style="display:none;">
					<div class="loadingbox">피어목록을 불러오고 있습니다. 잠시만 기다려주십시오.<br />Now Loading... Please, Wait.</div>
				</div>
				
				<div class="height10"></div>
				
				<div class="areaTitle">
					<div class="inner">
						<div class="text">토렌트정보 (Torrent Intro)</div>
						<div for="TrackerTorrentInfoArea-{$torrent.idx}" class="toggle off" onclick="TrackerViewToggleArea(this);"></div>
					</div>
				</div>
				
				<div id="TrackerTorrentInfoArea-{$torrent.idx}" class="f12">
					<div class="height10"></div>
					{if $torrent.screenshot}
					<div class="bold" style="height:14px; line-height:14px; background:url({$skinDir}/images/bullet_screenshot.png) no-repeat 0 50%; padding-left:18px;">스크린샷 (Screenshot, Original Resolution)</div>
					<div class="innerimg" style="padding:10px;">
						<img src="{$torrent.screenshot.filepath}" style="width:100%;" onclick="TrackerShowImage('{$addimage.filepath}');" />
					</div>
					<div class="height10"></div>
					{/if}
					
					{if $torrent.snapshot}
					<div class="bold" style="height:14px; line-height:14px; background:url({$skinDir}/images/bullet_snapshot.png) no-repeat 0 50%; padding-left:18px;">스냅샷 (Snapshot)</div>
					<div class="innerimg" style="padding:10px;">
						<img src="{$torrent.snapshot.filepath}" style="width:100%;" onclick="TrackerShowImage('{$addimage.filepath}');" />
					</div>
					<div class="height10"></div>
					{/if}
					
					{if $torrent.image}
					<div class="bold" style="height:14px; line-height:14px; background:url({$skinDir}/images/bullet_snapshot.png) no-repeat 0 50%; padding-left:18px;">추가스크린샷 (Addition Screenshots)</div>
					<div class="innerimg" style="padding:10px;">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
						{foreach name=addimage from=$torrent.image item=addimage}
							{if $smarty.foreach.addimage.index % 4 == 0}<tr>{/if}
							<td class="center"><img src="{$addimage.thumbnail}" style="border:2px solid #666666; cursor:pointer;" onclick="TrackerShowImage('{$addimage.filepath}');" /></td>
							{if $smarty.foreach.addimage.index % 4 == 3}</tr><tr class="height10"><td colspan="4"></td></tr><tr>{/if}
						{/foreach}
						{if $smarty.foreach.addimage.total%4 != 0}
						{section name=idx start=$smarty.foreach.addimage.total%4-4 step=1 loop=4}
							<td></td>
						{/section}
						</tr>
						{/if}
						</table>
					</div>
					<div class="height10"></div>
					{/if}
				
					<div class="bold" style="height:14px; line-height:14px; background:url({$skinDir}/images/bullet_mediainfo.png) no-repeat 0 50%; padding-left:18px;">미디어정보 (Media Info)</div>
					<div style="padding:10px; line-height:1.6;">{$torrent.mediainfo}</div>
				</div>
				
				<div class="areaButton">
					<a href="{$torrent.editlink}"><img src="{$skinDir}/images/btn_edit_torrent.png" /></a>
					<img src="{$skinDir}/images/btn_del_torrent.png" />
					<img src="{$skinDir}/images/btn_report_torrent.png" />
				</div>
				
				<div class="height10"></div>
				
				<div>{$torrent.mentlist}</div>
			</td>
		</tr>
		{/foreach}
		</tbody>
		</table>
	</div>
	{else if $viewmode == 'group'}
	<div class="areaTitle">
		<div class="inner">
			<div class="text">에피소드 (Episodes)</div>
			<div for="TrackerGroupListArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
			<a href="{$link.addepisode}" class="addepisode"></a>
		</div>
	</div>
	
	<div id="TrackerTorrentListArea">
		<table cellpadding="0" cellspacing="1" class="torrentList">
		<col width="100%" /><col width="80" /><col width="50" /><col width="45" /><col width="45" />
		<tr class="tHead">
			<td><div>Episodes</div></td>
			<td><div>Filesize</div></td>
			<td><div>Snatch</div></td>
			<td><div>Seeder</div></td>
			<td><div>Leecher</div></td>
		</tr>
		{foreach name=episode from=$episode item=episode}
		<tr class="tBody">
			<td class="episodeTitle" colspan="5">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100%" /><col width="130" />
				<tr>
					<td>
						<a href="{$episode.link}">
						{if $episode.is_pack == true}
							E{$episode.episode.start|string_format:"%03d"}~E{$episode.episode.end|string_format:"%03d"}.Pack
						{else}
							{if $episode.episode}E{$episode.episode|string_format:"%03d"}
								{if $episode.episode_title}<span class="normal">({$episode.episode_title})</span>{/if}
							{else}
								{if $episode.episode_title}<span class="bold">{$episode.episode_title}</span>{/if}
							{/if}
						{/if}
						</a>
					</td>
					<td><a href="{$episode.addlink}"><img src="{$skinDir}/images/btn_add_torrent.gif" /></a></td>
				</tr>
				</table>
			</td>
		</tr>
		{foreach name=torrent from=$episode.torrent item=torrent}
		<tr class="tBody">
			<td class="torrentTitle">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100%" /><col width="20" />
				<tr>
					<td>
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
					</td>
					<td class="center"><a href="{$torrent.downloadlink}" target="downloadFrame"><img src="{$skinDir}/images/icon_disk.png" alt="download" /></a></td>
				</tr>
				</table>
			</td>
			<td class="center tahoma f11">{$torrent.filesize}</td>
			<td class="count">{$torrent.snatch|number_format}</td>
			<td class="count">{$torrent.seeder|number_format}</td>
			<td class="count">{$torrent.leecher|number_format}</td>
		</tr>
		{/foreach}
		{/foreach}
		</table>
	</div>
	{/if}
	
	<div class="height10"></div>
	
	<div class="areaTitle">
		<div class="inner">
			<div class="text">그룹 통합 댓글 (Group Comments)</div>
			<div for="TrackerGroupMentListArea" class="toggle on" onclick="TrackerViewToggleArea(this);"></div>
		</div>
	</div>
	
	<div id="TrackerGroupMentListArea">
		{$group.mentlist}
	</div>
</div>