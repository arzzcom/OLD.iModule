{$formStart}
<input type="hidden" name="vote" />
<div class="trackerMent">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="120" /><col width="100%" /><col width="80" />
	<tr>
		<td>
			<div id="TrackerSelectVotePreview" class="selectVote" onclick="TrackerSelectVote();">평점선택</div>
			<div id="TrackerSelectVoteList" class="selectVoteList" style="display:none;">
				<ul>
					<!-- li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(-1);">
						선택안함
					</li -->
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(10);">
						<div class="star"><div class="on" style="width:100%;"></div></div>
						<div class="point">10.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(9);">
						<div class="star"><div class="on" style="width:90%;"></div></div>
						<div class="point">9.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(8);">
						<div class="star"><div class="on" style="width:80%;"></div></div>
						<div class="point">8.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(7);">
						<div class="star"><div class="on" style="width:70%;"></div></div>
						<div class="point">7.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(6);">
						<div class="star"><div class="on" style="width:60%;"></div></div>
						<div class="point">6.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(5);">
						<div class="star"><div class="on" style="width:50%;"></div></div>
						<div class="point">5.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(4);">
						<div class="star"><div class="on" style="width:40%;"></div></div>
						<div class="point">4.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(3);">
						<div class="star"><div class="on" style="width:30%;"></div></div>
						<div class="point">3.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(2);">
						<div class="star"><div class="on" style="width:20%;"></div></div>
						<div class="point">2.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(1);">
						<div class="star"><div class="on" style="width:10%;"></div></div>
						<div class="point">1.00</div>
					</li>
					<li onmouseover="this.className='select';" onmouseout="this.className='';" onclick="TrackerSelectVotePoint(0);">
						<div class="star"><div class="on" style="width:0%;"></div></div>
						<div class="point">0.00</div>
					</li>
				</ul>
			</div>
		</td>
		<td>
			<div class="inputarea">
				<textarea name="content" class="textbox"></textarea>
			</div>
		</td>
		<td><input type="image" src="{$skinDir}/images/btn_ment_submit.gif" /></td>
	</tr>
	</table>
	
	<div class="height5"></div>
	
	<div class="boxDefault f11">
		<span class="bold red f11">[!]</span> 개별평점은 통합적으로 합산되어 평점으로 적용됩니다.
	</div>

	<div class="height10" style="border-bottom:1px solid #CCCCCC; width:100%;"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="60" /><col width="100%" />
	{foreach name=list from=$data item=data}
	<tr>
		<td class="vTop"><img src="{$data.member.photo}" style="width:50px; height:50px; border:2px solid #CCCCCC; margin:5px 0px 5px 0px;" /></td>
		<td class="vTop">
			<div class="height1" style="margin-top:8px;"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100" /><col width="80" /><col width="100%" /><col width="200" />
			<tr>
				<td><div class="listName">{$data.member.user_id}</div></td>
				<td class="listVote">
					{if $data.vote != '-1'}
					<div class="star"><div class="on" style="width:{$data.vote*10}%;"></div></div>
					<div class="point">{$data.vote}.0</div>
					{else}
					<div class="point">no score</div>
					{/if}
				</td>
				<td>
					<div class="listTorrent">
						{if $data.torrent.episode}
							{if $data.torrent.is_pack}
								E{$data.torrent.episode.start|string_format:"%03d"}~E{$data.torrent.episode.end|string_format:"%03d"}.Pack
							{else}
								{if $data.torrent.episode}E{$data.torrent.episode|string_format:"%03d"}{/if}
							{/if}
							</a>
						{/if}
						{if $data.torrent.resolution}/ {$data.torrent.resolution}{/if}
						{if $data.torrent.source}/ {$data.torrent.source}{/if}
						{if $data.torrent.codec}/ {$data.torrent.codec}{/if}
						{if $data.torrent.format}/ {$data.torrent.format}{/if}
						{if $data.torrent.edition}/ {$data.torrent.edition}{/if}
						{if $data.torrent.release}/ {$data.torrent.release}{/if}
					</div>
				</td>
				<td class="listDate">{$data.reg_date|date_format:"%B %d, %Y, %I:%M%P %Z"}</td>
			</tr>
			</table>
			<div class="listContent">{$data.content}</div>
		</td>
	</tr>
	<tr class="height1">
		<td colspan="3" style="background:#CCCCCC;"></td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" style="padding:10px 0px 10px 0px;">
			<div class="boxGray center">
				아직까지 등록된 댓글 및 평점이 없습니다.<br />
				제일 먼저 평가 및 의견을 남겨주세요.
			</div>
		</td>
	</tr>
	{/foreach}
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
		<td class="pageinfo"><span class="bold">{$totalpost}</span> ments / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
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
{$formEnd}