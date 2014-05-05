<div class="height10"></div>

<div class="titleFont"><span class="orange">최근</span> 가장 인기 있는 <span class="red">BEST 10</span></div>

<div style="height:5px; border-top:1px dashed #666666; overflow:hidden; margin-top:5px;"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100%" /><col width="10" /><col width="350" />
<tbody>
<tr>
	<td class="vTop">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="95" /><col width="100%" />
		{section name=idx start=0 loop=$data max=2}
		<tr>
			<td colspan="2" class="rPostTitle bold"><a href="{$data[idx].postlink}">{$data[idx].title|cutstring:28:true}</a> <span class="rComment">({$data[idx].ment}{if $data[idx].is_newment == true}+{/if})</span></td>
		</tr>
		<tr>
			<td class="vTop">
				<div class="rPostImageArea">
					<div class="rPostIcon"><a href="{$data[idx].postlink}"><img src="{$skinDir}/images/{$smarty.section.idx.index+1}.png" /></a></div>
					<div class="rPostImage">
						{if $data[idx].image}<img src="{$data[idx].image}" style="width:90px;" />{else}<img src="{$skinDir}/images/noimage.gif" />{/if}
					</div>
				</div>
			</td>
			<td class="rPostContent">{if $data[idx].content}{$data[idx].content|cutstring:50:true}{else}이 게시물은 내용없이 이미지 또는 동영상으로만 이루어져 있습니다.<br />제목을 클릭하시면 이미지 또는 동영상을 보실 수 있습니다.{/if}<div class="height5"></div><span class="rPostDate">Post At <span class="bold">{$data[idx].reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</span></span></td>
		</tr>
		{/section}
		</table>
	</td>
	<td></td>
	<td class="vTop">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="40" /><col width="100%" /><col width="60" />
		{section name=idx start=2 loop=$data}
		<tr>
			<td class="right bold">{if $smarty.section.idx.index+1 <= 5}<span class="{if $smarty.section.idx.index+1 == 3}red{else}orange{/if}">{$smarty.section.idx.index+1}위</span>{else}{$smarty.section.idx.index+1}위{/if}.</td>
			<td class="rPostTitle"><a href="{$data[idx].postlink}">{$data[idx].title|cutstring:20:true}</a> <span class="rComment">({$data[idx].ment}{if $data[idx].is_newment == true}+{/if})</span></td>
			<td class="rPostDate right">{$data[idx].reg_date|date_format:"%Y.%m.%d"}</td>
		</tr>
		{/section}
		</table>
	</td>
</tr>
</table>