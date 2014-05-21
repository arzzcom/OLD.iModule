<article>
<header>
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<thead>
<tr class="viewbar">
	<td class="left"></td>
	<td class="title"><h4>{$data.title}</h4></td>
	<td class="right"></td>
</tr>
</thead>
<tbody>
<tr>
	<td></td>
	<td>
		<div class="postinfor">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="110" /><col width="100%" />
			<tr>
				<td>{if $data.logo}<img src="{$data.logo}" class="logo" />{else}<img src="{$skinDir}/images/nologo.gif" class="logo" />{/if}</td>
				<td>
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="40%" /><col width="60%" />
					<tr>
						<td class="detail">
							<div><span class="bold">분류</span> : {if $data.category}{$data.category}{else}분류없음{/if}</div>
						</td>
						<td class="detail">
							<div><span class="bold">최초등록일</span> : {$data.reg_date|date_format:"%Y년 %m월 %d일"}</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="detail"><div class="dotted"></div></td>
					</tr>
					<tr>
						<td class="detail">
							<div><span class="bold">제작자</span> : {$data.nickname}</div>
						</td>
						<td class="detail">
							<div><span class="bold">지원홈페이지</span> : {if $data.homepage}<a href="{$data.homepage}" target="_blank">{$data.homepage}</a>{else}없음{/if}</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="detail"><div class="dotted"></div></td>
					</tr>
					<tr>
						<td class="detail">
							<div><span class="bold">라이센스</span> : {$data.license}</div>
						</td>
						<td class="detail">
							<div><span class="bold">프로그램가격</span> : {if $data.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$data.price|number_format}P</span>{/if}</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="detail"><div class="dotted"></div></td>
					</tr>
					<tr>
						<td class="detail">
							<div><span class="bold">최종버전</span> : {if $data.last_version}{$data.last_version}{else}등록되지 않음{/if}</div>
						</td>
						<td class="detail">
							<div><span class="bold">다운로드(구매) / 추천</span> : <span class="bold tahoma blue">{$data.download|number_format}</span> / <span class="bold tahoma red">{$data.vote|number_format}</span></div>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</div>
	</td>
</tr>
</tbody>
</table>
</header>
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<tbody>
<tr>
	<td></td>
	<td>
		<div class="height10"></div>
		{$data.content}
		<div class="height10"></div>
		<div class="addthis_toolbox addthis_default_style addthis_32x32_style" style="float:right;">
			<a class="addthis_button_facebook"></a>
			<a class="addthis_button_twitter"></a>
			<a class="addthis_button_email"></a>
			<a class="addthis_button_print"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
		</div>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e733ebf2235bbcc"></script>

		<div class="height5" style="clear:both;"></div>

		{if $data.last_modify.hit > 0}<div class="lastModify">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
		{if $file}
		<ul class="filelist">
			{foreach name=file from=$file item=file}
				<li><a href="{$file.link}" target="downloadFrame">{$file.filename}</a> ({$file.filesize}, {$file.hit|number_format} Hit{if $file.hit > 1}s{/if})</li>
			{/foreach}
		</ul>
		<div class="height10"></div>
		{/if}
		<div class="height5"></div>
		
		<table cellpadding="0" cellspacing="0" class="download">
		<col width="100%" /><col width="5" /><col width="220" />
		<tr>
			<td colspan="2" class="sectionTitle">프로그램 다운로드</td>
			<td class="right">
				{if $permission.addversion}<a href="{$link.addversion}"><img src="{$skinDir}/images/btn_add_version.gif" /></a>{/if}
			</td>
		</tr>
		<tr class="sectionBar">
			<td colspan="3"></td>
		</tr>
		{if $data.last_version}
		<tr>
			<td>
				<div class="boxGray" style="height:50px; line-height:1.4;">
					{if $data.price > 0}
					해당 프로그램은 유료프로그램으로 첫 다운로드시 {$data.price|number_format}포인트가 차감됩니다.<br />
					구매이후 재다운로드나 새 버전업데이트시에는 무료로 이용할 수 있습니다.<br />
					{if $data.payment == true}<span class="blue bold">회원님은 구매이력이 있으므로 무료로 다운로드 가능합니다.</span>{else}<span class="red bold">회원님은 현재 구매이력이 없으므로 포인트가 차감됩니다.</span>{/if}
					{else}
					해당 프로그램은 무료프로그램입니다.<br />
					명시된 라이센스에 따라 무료로 다운로드 및 설치하여 사용할 수 있습니다.
					{/if}
				</div>
			</td>
			<td></td>
			<td>
				<a class="downloadButton" href="{$link.download_lastest}" target="downloadFrame"{if $data.price > 0 && $data.payment == false} onclick="return confirm('해당 프로그램은 유료프로그램입니다.\n프로그램 다운로드시 {$data.price|number_format}포인트가 소진됩니다.\n프로그램을 다운로드 받으시겠습니까?');"{/if}>
					<span class="bold">V.{$data.last_version}</span>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div class="height10"></div>
				
				<div class="downloadHeader">
					<table cellspacing="0" cellspacing="0">
					<col width="100%" /><col width="70" /><col width="80" /><col width="200" /><col width="55" /><col width="115" />
					<tr style="height:31px;">
						<td>프로그램명</td>
						<td>버전</td>
						<td>용량</td>
						<td>해시값</td>
						<td>다운수</td>
						<td>배포일</td>
					</tr>
					</table>
				</div>
				
				<div class="downloadList">
					<table cellspacing="0" cellspacing="0">
					<col width="100%" /><col width="70" /><col width="80" /><col width="200" /><col width="55" /><col width="115" />
					{foreach name=version from=$version item=version}
					<tr{if $smarty.foreach.version.index % 2 == 0} class="bgGray"{/if}>
						<td class="title"><a href="{$version.link.download}" target="downloadFrame" target="downloadFrame"{if $data.price > 0 && $data.payment == false} onclick="return confirm('해당 프로그램은 유료프로그램입니다.\n프로그램 다운로드시 {$data.price|number_format}포인트가 소진됩니다.\n프로그램을 다운로드 받으시겠습니까?');"{/if}>{$version.filename}</a></td>
						<td class="center pointer" onclick="$('#history{$version.idx}').toggle();">{$version.version}</td>
						<td class="center">{$version.filesize|GetFileSize}</td>
						<td class="hash">{$version.hash}</td>
						<td class="right">{$version.download}</td>
						<td class="date">{$version.reg_date|date_format:"%B %d, %Y"}&nbsp;</td>
					</tr>
					<tr id="history{$version.idx}"{if $smarty.foreach.version.index > 0} style="display:none;"{/if}>
						<td colspan="6">
							<div class="historyBox">
								{$version.history}
								
								{if $permission.modify || $permission.delete}
								<div class="height5"></div>
								<div class="right" style="font:0/0 arial;">
									{if $permission.modify}<a href="{$version.link.modify}"><img src="{$skinDir}/images/btn_modify.gif" /></a>{/if}
									{if $permission.delete}<a href="{$version.link.delete}"><img src="{$skinDir}/images/btn_delete.gif" /></a>{/if}
								</div>
								{/if}
							</div>
						</td>
					</tr>
					{/foreach}
					</table>
				</div>
				
				<div class="boxSubText">
					* 버전을 클릭하면, 해당 버전에 따른 릴리즈정보를 확인 및 수정할 수 있습니다.
				</div>
				
				<div class="height10"></div>
			</td>
		</tr>
		{else}
		<tr>
			<td colspan="3">
				<div class="boxGray center">이 프로그램에 등록된 최신버전이 없습니다.<br />프로그램 등록자라면 신규버전등록 버튼을 클릭하여 버전등록을 하여주십시오.</div>
			</td>
		</tr>
		{/if}
		<tr class="sectionEnd">
			<td colspan="3"></td>
		</tr>
		</table>
	</td>
</tr>
</tbody>
</table>
</article>

{$ment}

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="10" /><col width="100%" /><col width="10" />
<tr class="mentthin">
	<td class="left"></td>
	<td></td>
	<td class="right"></td>
</tr>
</table>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="50%" />
<tr>
	<td class="innerimg">
		{if $setup.use_mode == 'FALSE'}
		<a href="{$link.list}"><img src="{$skinDir}/images/btn_list.gif" style="margin-right:3px;" /></a>
		<a href="{$link.post}"><img src="{$skinDir}/images/btn_newpost.gif" style="margin-right:3px;" /></a>
		{/if}
	</td>
	<td class="innerimg right">
		<img src="{$skinDir}/images/btn_vote.gif" style="margin-left:3px;" class="pointer" onclick="{$action.vote}" />
		<a href="{$link.modify}"><img src="{$skinDir}/images/btn_modify.gif" style="margin-left:3px;" /></a>
		<a href="{$link.delete}"><img src="{$skinDir}/images/btn_delete.gif" style="margin-left:3px;" /></a>
	</td>
</tr>
</table>