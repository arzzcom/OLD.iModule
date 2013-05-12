<div class="trackerList">
	{$searchFormStart}
	<div class="searchArea">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="70" /><col width="100%" />
		<tr>
			<td class="header">검색어 :</td>
			<td>
				<div style="position:relative; width:600px;">
					<input type="text" id="TrackerGroupSearchInput" name="keyword" value="{$keyword}" onfocus="TrackerGroupLiveSearchStart({$category},'daummovie');" onblur="TrackerGroupLiveSearchEnd();" autocomplete="off" onkeydown="TrackerGroupLiveSearchListMove(event)" class="inputbox" style="width:594px;" artist="감독" subartist="출연" />
					<div id="TrackerGroupSearchCancel" style="display:none;" onclick="TrackerGroupLiveSearchCancel();"></div>
					<div id="TrackerGroupSearchList" style="width:600px; display:none;"></div>
				</div>
			</td>
		</tr>
		<tr>
			<td class="header">카테고리 :</td>
			<td>
				{foreach name=categoryList1 from=$categoryList1 item=category}
				<div style="margin:5px; float:left;"><input type="checkbox" name="category1[]" value="{$category.idx}"{if $category.checked == true} checked="checked"{/if} /> <span class="pointer" onclick="TrackerTorrentSearchForCheckbox('category1',{$category.idx});">{$category.title}</span></div>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="2">{$searchFormInner}</td>
		</tr>
		</table>
	</div>
	
	<input type="submit" />
	{$searchFormEnd}

	<div class="height10"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="150" /><col width="100%" /><col width="150" />
	<tr>
		<td class="innerimg">
			{if $prevlist != false}
				<a href="{$link.page}{$prevlist}"><img src="{$skinDir}/images/btn_prev.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_prev_off.gif" />
			{/if}
		</td>
		<td class="pageinfo"><span class="bold">{$totalgroup}</span> groups / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
		<td class="innerimg right">
			{if $setup.use_mode == 'FALSE'}<a href="{$link.post}"><img src="{$skinDir}/images/btn_newpost.gif" style="margin-right:3px;" /></a>{/if}
			{if $nextlist != false}
				<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_next_off.gif" />
			{/if}
		</td>
	</tr>
	</table>
	
	<div class="height5"></div>
	
	<table cellspacing="0" cellpadding="0" class="layoutfixed">
	<col width="58" /><col width="100%" /><col width="50" /><col width="45" /><col width="45" />
	{foreach name=list from=$data item=data}
	<tr class="groupHeader">
		<td>
			<div class="split center innerimg">
				<img src="{$data.thumbnail}" style="width:48px; height:60px; border:1px; margin:5px;" />
			</div>
		</td>
		<td>
			<div class="split">
				<div class="groupInfo">
					<div class="categorybox category{$data.category1}">{$data.category1Name}</div>
					<div class="title">
						{if $data.category1 == '1'}{$data.artist} - {/if}<a href="{$data.titlelink}">{$data.title}{if $data.season} S{$data.season|string_format:"%02d"}{/if}</a>
						{if $data.episode || $data.episode_title} : 
							<a href="{$data.episodelink}">
							{if $data.is_pack}
								E{$data.episode.start|string_format:"%03d"}~E{$data.episode.end|string_format:"%03d"}.Pack
							{else}
								{if $data.episode}
									E{$data.episode|string_format:"%03d"}
									{if $data.episode_title} ({$data.episode_title}){/if}
								{else}
									{if $data.episode_title} {$data.episode_title}{/if}
								{/if}
								
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
							<span class="tahoma">{if $data.eng_title}{$data.eng_title} / {/if}{if $data.date != '0000-00-00'}{$data.date|date_format:"%B %d, %Y (%Z)"}{else}{$data.year}{/if}</span><br />
							{if $data.tag}{$data.tag}{/if}{if $data.tag && $data.subartist}, {/if}{if $data.subartist}{$data.subartist}{/if}
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
				<div class="hSplitContent">{$data.snatch|number_format}</div>
			</div>
		</td>
		<td>
			<div class="split">
				<div class="hSplit">Seeder</div>
				<div class="hSplitContent">{$data.seeder|number_format}</div>
			</div>
		</td>
		<td>
			<div class="split" style="border-right:1px solid #DCDCDC;">
				<div class="hSplit">Leecher</div>
				<div class="hSplitContent">{$data.leecher|number_format}</div>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="5">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100%" /><col width="20" /><col width="40" /><col width="60" /><col width="50" /><col width="45" /><col width="45" />
			{foreach name=torrent from=$data.torrent item=torrent}
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
	
	<div class="height10"></div>
	
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="150" /><col width="100%" /><col width="150" />
	<tr>
		<td class="innerimg">
			{if $prevlist != false}
				<a href="{$link.page}{$prevlist}"><img src="{$skinDir}/images/btn_prev.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_prev_off.gif" />
			{/if}
		</td>
		<td class="pageinfo"><span class="bold">{$totalgroup}</span> groups / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
		<td class="innerimg right">
			{if $setup.use_mode == 'FALSE'}<a href="{$link.post}"><img src="{$skinDir}/images/btn_newpost.gif" style="margin-right:3px;" /></a>{/if}
			{if $nextlist != false}
				<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_next_off.gif" />
			{/if}
		</td>
	</tr>
	</table>
	
	<div class="height10"></div>
	
	<div class="pagenav">
	{if $prevpage != false}
		<a href="{$link.page}{$prevpage}">이전{$pagenum}페이지</a>
	{else}
		<span>이전{$pagenum}페이지</span>
	{/if}
	{foreach name=page from=$page item=page}
		{if $page == $p}
		<strong>{$page}</strong>
		{else}
		<a href="{$link.page}{$page}">{$page}</a>
		{/if}
	{/foreach}
	{if $nextpage != false}
		<a href="{$link.page}{$nextpage}">다음{$pagenum}페이지</a>
	{else}
		<span>다음{$pagenum}페이지</span>
	{/if}
	</div>
</div>
<iframe name="downloadFrame" style="display:none;"></iframe>

<div class="height10"></div>