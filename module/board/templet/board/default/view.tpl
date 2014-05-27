<article>
	<header>
		<table cellpadding="0" cellspacing="0" class="boardTable">
		<col width="80" /><col width="1" /><col width="140" /><col width="1" /><col width="80" /><col width="1" /><col width="100%" /><col width="1" /><col width="110" />
		<tr>
			<td colspan="6" class="sectionTitle">게시글 보기</td>
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
			<td colspan="7" class="viewTitleCell">
				<h4>{$data.title}</h4>
			</td>
			<td class="splitBar"></td>
			<td class="photo" rowspan="5"><img src="{$data.author.photo}" /></td>
		</tr>
		<tr class="splitBar">
			<td colspan="8"></td>
		</tr>
		<tr>
			<td class="headerCell left">분류</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.category}{$data.category}{else}분류없음{/if}</td>
			<td class="splitBar"></td>
			<td class="headerCell left">작성일</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.reg_date|date_format:"%Y년 %m월 %d일 %H시 %M분 %S초"}</td>
			<td class="splitBar"></td>
		</tr>
		<tr class="splitBar">
			<td colspan="8"></td>
		</tr>
		<tr>
			<td class="headerCell left">작성자</td>
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
			<td class="splitBar"></td>
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
	
	{if $data.select == true}
		<div class="btn btn-warning btn-sm pull-left" onclick="{$action.complete}">채택할 답변이 없다면, 이곳을 눌러 이 질문의 상태를 미해결완료로 처리할 수 있습니다.</div>
	{/if}
	
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
	
	<table cellpadding="0" cellspacing="0" class="boardTable">
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

{$ment}

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="boardTable">
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
		<a class="btn btn-success btn-sm" href="{$link.post}">{if $setup.use_select == 'TRUE'}새 질문작성하기{else}새글 작성하기{/if}</a>
	</td>
	<td class="right">
		<button class="btn btn-sm btn-warning" onclick="{$action.vote}">추천하기</button>
		<a href="{$link.modify}" class="btn btn-sm btn-info">수정하기</a>
		<a href="{$link.delete}" class="btn btn-sm btn-danger">삭제하기</a>
	</td>
</tr>
</table>