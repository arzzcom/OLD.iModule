<article>
	<header>
		<table cellpadding="0" cellspacing="0" class="releaseTable">
		<col width="154" /><col width="1" /><col width="100" /><col width="1" /><col width="150" /><col width="1" /><col width="120" /><col width="1" /><col width="100%" />
		<tr>
			<td colspan="6" class="sectionTitle">프로그램 보기</td>
			<td colspan="3" class="right tahoma f10">
				<div class="height10"></div>
				HIT <span class="blue bold">{$data.hit|number_format}</span>
				REPLY <span class="orange bold">{$data.ment|number_format}</span>
				VOTE <span class="red bold">{$data.vote|number_format}</span>
			</td>
		</tr>
		<tr class="sectionBar">
			<td colspan="9"></td>
		</tr>
		<tr>
			<td colspan="9" class="viewTitleCell">
				<h4>{$data.title}</h4>
			</td>
		</tr>
		<tr class="splitBar">
			<td colspan="9"></td>
		</tr>
		<tr>
			<td class="logoCell" rowspan="7">{if $data.logo}<img src="{$data.logo}" />{else}<img src="{$skinDir}/images/nologo.gif" />{/if}</td>
			<td class="splitBar" rowspan="7"></td>
			<td class="headerCell">분류</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.category}{$data.category}{else}분류없음{/if}</td>
			<td class="splitBar"></td>
			<td class="headerCell left">최초등록일</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.reg_date|date_format:"%Y년 %m월 %d일"}</td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr>
			<td class="headerCell">제작자</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.author.nickname}</td>
			<td colspan="4" class="bodyCell" style="padding-left:0px;">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="30" /><col width="60" /><col width="50%" /><col width="50%" />
				<tr>
					<td class="tahoma f10 bold">LV.<span class="orange">{if $data.author.level.lv < 10}0{/if}{$data.author.level.lv}</span></td>
					<td>
						<table cellpadding="0" cellspacing="0" class="exp">
						<col width="1" /><col width="{$data.author.level.exp/$data.author.level.next*50|string_format:"%d"}" /><col width="100%" /><col width="2" />
						<tr>
							<td class="start"></td>
							<td class="on"></td>
							<td class="off"></td>
							<td class="end"></td>
						</tr>
						</table>
					</td>
					<td>
						<div class="email">{if $data.author.email}<a href="mailto:{$data.author.email}">{$data.author.email}</a>{else}<span class="disabled">NONE</span>{/if}</div>
					</td>
					<td>
						<div class="homepage">{if $data.author.homepage}<a href="{$data.author.homepage}" target="_blank">{$data.author.homepage}</a>{else}<span class="disabled">NONE</span>{/if}</div>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr>
			<td class="headerCell">라이센스</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.license}</td>
			<td class="splitBar"></td>
			<td class="headerCell left">프로그램가격</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$data.price|number_format}P</span>{/if}</td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr>
			<td class="headerCell">최종버전</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.last_version}<span class="tahoma red bold">{$data.last_version}</span>{else}등록되지 않음{/if}</td>
			<td class="splitBar"></td>
			<td class="headerCell left">다운로드/추천</td>
			<td class="splitBar"></td>
			<td class="bodyCell"><span class="bold tahoma blue">{$data.download|number_format}</span> / <span class="bold tahoma red">{$data.vote|number_format}</span></td>
		</tr>
		<tr class="splitBar">
			<td colspan="9"></td>
		</tr>
		</table>
	</header>
	
	<div class="height10"></div>
	
	{$data.content}
	
	<div class="height10"></div>
	
	{if $data.last_modify.hit > 0}<div class="tahoma f10">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
	
	<div class="height10"></div>
	
	<div class="addthis_toolbox addthis_default_style addthis_32x32_style pull-right">
		<a class="addthis_button_facebook"></a>
		<a class="addthis_button_twitter"></a>
		<a class="addthis_button_email"></a>
		<a class="addthis_button_print"></a>
		<a class="addthis_button_compact"></a>
		<a class="addthis_counter addthis_bubble_style"></a>
	</div>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e733ebf2235bbcc"></script>
	
	<div style="clear:both;" class="height20"></div>
	
	<table cellpadding="0" cellspacing="0" class="boardTable">
	<tr class="splitBar">
		<td></td>
	</tr>
	<tr class="sectionEnd">
		<td><div></div></td>
	</tr>
	</table>
	
	{if $file}
	<div class="height10"></div>
	
	<table cellpadding="0" cellspacing="0" class="releaseTable">
	<col width="100%" /><col width="1" /><col width="120" /><col width="1" /><col width="100" />
	<tr>
		<td class="sectionTitle" colspan="2">첨부파일</td>
		<td class="right tahoma f10" colspan="3">
			<div class="height10"></div>
			FILES #<span class="blue bold">{$file|@sizeof}</span>
		</td>
	</tr>
	<tr class="sectionBar">
		<td colspan="5"></td>
	</tr>
	<tr>
		<td class="headerCell">파일명</td>
		<td class="splitBar"></td>
		<td class="headerCell">파일크기</td>
		<td class="splitBar"></td>
		<td class="headerCell">다운로드횟수</td>
	</tr>
	<tr class="splitBar">
		<td colspan="5"></td>
	</tr>
	{foreach name=file from=$file item=file}
	<tr>
		<td class="fileCell icon{$file.filetype}"><a href="{$file.link}" target="downloadFrame">{$file.filename}</a></td>
		<td class="splitBar"></td>
		<td class="fileCell right">{$file.filesize}</td>
		<td class="splitBar"></td>
		<td class="fileCell right">{$file.hit|number_format}</td>
	</tr>
	<tr class="splitBar">
		<td colspan="5"></td>
	</tr>
	{/foreach}
	<tr class="sectionEnd">
		<td colspan="5"><div></div></td>
	</tr>
	</table>
	{/if}
</article>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100%" /><col width="5" /><col width="220" />
<tr>
	<td class="sectionTitle" colspan="2">
		<div class="height5"></div>프로그램 다운로드
	</td>
	<td class="right">
		{if $permission.addversion}<a href="{$link.addversion}" class="btn btn-sm btn-success">신규 버전등록하기</a>{/if}
		<div class="height5"></div>
	</td>
</tr>
<tr class="sectionBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="sectionInfo">
		{if $data.price > 0}
		해당 프로그램은 유료프로그램으로 첫 다운로드시 {$data.price|number_format}포인트가 차감됩니다.<br />
		구매이후 재다운로드나 새 버전업데이트시에는 무료로 이용할 수 있습니다.<br />
		{if $data.payment == true}<span class="blue bold">회원님은 구매이력이 있으므로 무료로 다운로드 가능합니다.</span>{else}<span class="red bold">회원님은 현재 구매이력이 없으므로 포인트가 차감됩니다.</span>{/if}
		{else}
		해당 프로그램은 무료프로그램입니다.<br />
		명시된 라이센스에 따라 무료로 다운로드 및 설치하여 사용할 수 있습니다.
		{/if}
	</td>
	<td></td>
	<td>
		<div class="downloadButton" onclick="downloadFrame.location.href = '{$link.download_lastest}';"{if $data.price > 0 && $data.payment == false} onclick="return confirm('해당 프로그램은 유료프로그램입니다.\n프로그램 다운로드시 {$data.price|number_format}포인트가 소진됩니다.\n프로그램을 다운로드 받으시겠습니까?');"{/if}>
			<span class="bold">V.{$data.last_version}</span>
		</div>
	</td>
</tr>
<tr class="sectionBar" style="height:1px;">
	<td colspan="3"></td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100%" /><col width="1" /><col width="70" /><col width="1" /><col width="100" /><col width="1" /><col width="80" /><col width="1" /><col width="140" /><col width="1" /><col width="110" />
<tr>
	<td class="headerCell">프로그램명</td>
	<td class="splitBar"></td>
	<td class="headerCell">버전</td>
	<td class="splitBar"></td>
	<td class="headerCell">용량</td>
	<td class="splitBar"></td>
	<td class="headerCell">다운수</td>
	<td class="splitBar"></td>
	<td class="headerCell">배포일</td>
	<td class="splitBar"></td>
	<td class="headerCell">다운로드</td>
</tr>
<tr>
	<td class="splitBar" colspan="11"></td>
</tr>
{foreach name=version from=$version item=version}
<tr class="unselect pointer">
	<td class="bodyCell" onclick="$('.history{$version.idx}').toggle();">{$version.filename}</td>
	<td class="splitBar"></td>
	<td class="bodyCell center" onclick="$('.history{$version.idx}').toggle();">{$version.version}</td>
	<td class="splitBar"></td>
	<td class="bodyCell right" onclick="$('.history{$version.idx}').toggle();">{$version.filesize|GetFileSize}</td>
	<td class="splitBar"></td>
	<td class="bodyCell right" onclick="$('.history{$version.idx}').toggle();">{$version.download}</td>
	<td class="splitBar"></td>
	<td class="bodyCell center" onclick="$('.history{$version.idx}').toggle();">{$version.reg_date|date_format:"%B %d, %Y"}&nbsp;</td>
	<td class="splitBar"></td>
	<td class="center" style="padding:3px;">
		<a class="btn btn-sm btn-{if $data.price > 0 && $data.payment == false}warning{elseif $data.price > 0 && $data.payment == false}success{else}primary{/if} btn-block" href="$version.link.download}" target="downloadFrame"{if $data.price > 0 && $data.payment == false} onclick="return confirm('해당 프로그램은 유료프로그램입니다.\n프로그램 다운로드시 {$data.price|number_format}포인트가 소진됩니다.\n프로그램을 다운로드 받으시겠습니까?');"{/if}>{if $data.price > 0 && $data.payment == false}{$data.price|number_format}P 구매{elseif $data.price > 0 && $data.payment == false}다운로드(구매함){else}무료다운로드{/if}</a>
	</td>
</tr>
<tr class="history{$version.idx}"{if $smarty.foreach.version.index > 0} style="display:none;"{/if}>
	<td class="splitBar" colspan="11"></td>
</tr>
<tr class="history{$version.idx}"{if $smarty.foreach.version.index > 0} style="display:none;"{/if}>
	<td colspan="11" class="sectionInfo">
		<div class="historyBox">
			{$version.history}

			{if $permission.modify || $permission.delete}
			<div class="height5"></div>
			<div class="right">
				{if $permission.modify}<a href="{$version.link.modify}" class="btn btn-sm btn-default">이 버전 수정하기</a>{/if}
				{if $permission.delete}<a href="{$version.link.delete}" class="btn btn-sm btn-danger">이 버전 삭제하기</a>{/if}
			</div>
			{/if}
		</div>
	</td>
</tr>
<tr>
	<td class="splitBar" colspan="11"></td>
</tr>
{/foreach}
</table>

<div class="height10"></div>

{$ment}

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="50%" /><col width="50%" />
<tr class="splitBar">
	<td colspan="2"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="2"><div></div></td>
</tr>
<tr class="height10">
	<td colspan="2"></td>
</tr>
<tr>
	<td>
		<a href="{$link.list}" class="btn btn-default btn-sm">목록보기</a>
		<a class="btn btn-success btn-sm" href="{$link.post}">신규 프로그램 등록하기</a>
	</td>
	<td class="right">
		<button class="btn btn-sm btn-warning" onclick="{$action.vote}">추천하기</button>
		<a href="{$link.modify}" class="btn btn-sm btn-info">수정하기</a>
		<a href="{$link.delete}" class="btn btn-sm btn-danger">삭제하기</a>
	</td>
</tr>
</table>