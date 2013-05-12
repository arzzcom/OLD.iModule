<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<thead>
<tr class="viewbar">
	<td class="left"></td>
	<td class="title">{$data.title}</td>
	<td class="right"></td>
</tr>
</thead>
<tbody>
<tr>
	<td></td>
	<td>
		<div class="postinfor">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="70" /><col width="100%" />
			<tr>
				<td><img src="{$data.photo}" class="photo" /></td>
				<td class="detail">
					<div><span class="bold">{$data.nickname}</span>님이 <span class="bold">{$data.reg_date|date_format:"%Y년 %m월 %d일 %H시 %M분 %S초"}</span>에 등록하셨습니다.</div>
					<div class="dotted"></div>
					<div>이메일 : {if $data.email}<a href="mailto:{$data.email}">{$data.email}</a>{else}<span class="disable">등록된 이메일이 없습니다.</span>{/if}, 홈페이지 : {if $data.homepage}<a href="{$data.homepage}" target="_blank">{$data.homepage}</a>{else}<span class="disable">등록된 홈페이지주소가 없습니다.</span>{/if}</div>
					<div>카테고리 : {if $data.category}{$data.category}{else}<span class="disable">카테고리가 지정되지 않았습니다.</span>{/if}</div>
				</td>
			</tr>
			</table>
		</div>
	</td>
</tr>
<tr>
	<td></td>
	<td>
		<div class="height10"></div>
		{if $data.field1}
		<div class="boxDefault">
			이 게시물은 <span class="bold">{if $data.field1 == 'todayhumor'}오늘의유머 (<a href="http://www.todayhumor.co.kr" target="_blank">http://www.todayhumor.co.kr</a>){elseif $data.field1 == 'ilbe'}일간베스트(<a href="http://www.ilbe.com" target="_blank">http://www.ilbe.com</a>){elseif $data.field1 == 'simsimhe'}심심해닷컴(<a href="http://www.simsimhe.com" target="_blank">http://www.simsimhe.com</a>){elseif $data.field1 == 'humoruniv'}웃긴대학(<a href="http://www.humoruniv.com" target="_blank">http://www.humoruniv.com</a>){/if}</span> 사이트에서 수집된 게시물입니다.<br />
			원본게시물 : <a href="{$data.field2}" target="_blank">{$data.field2}</a>
		</div>
		<div class="height10"></div>
		{/if}
		
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


	</td>
</tr>
<tr>
	<td colspan="3">
		{$ment}
	</td>
</tr>
</tbody>
</table>

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