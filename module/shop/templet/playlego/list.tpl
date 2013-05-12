<div id="ShopList">
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="180" /><col width="10" /><col width="100%" />
<tr>
	<td class="vTop">
		{mShop->PrintCategory}
		<div class="height5"></div>
		<script type="text/javascript"><!--
		google_ad_client = "pub-3210736654114323";
		/* 180x150, 작성됨 09. 7. 7 */
		google_ad_slot = "2615473197";
		google_ad_width = 180;
		google_ad_height = 150;
		//-->
		</script>
		<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
		<div class="height5"></div>
		<div class="innerimg"><img src="{$skinDir}/images/custom_info.gif" /></div>
	</td>
	<td></td>
	<td class="vTop">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="5" /><col width="100%" /><col width="5" />
		<tr class="navbar">
			<td class="left"></td>
			<td class="navtitle">
				<a href="{$link.main}">메인</a>{foreach name=categorys from=$categorys item=data} <span class="tahoma">&gt;</span> {if $c == $data.idx}<span class="bold">{$data.title}</span>{else}<a href="{$data.link}">{$data.title}</a>{/if}{/foreach}
			</td>
			<td class="right"></td>
		</tr>
		</table>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		{section name=cols start=0 loop=$rownum step=1}<col width="100%" />{/section}
		{foreach name=items from=$item item=data}
			{if $smarty.foreach.items.index%$rownum == 0}<tr>{/if}
			<td class="vTop">
				<div class="center" style="margin:10px 0px 10px 0px;">
					<div class="listimg"><a href="{$data.itemlink}"><img src="{$data.list_image}" alt="{$data.title|replace:'"':'\"'}" /></a></div>
					<div class="title">
						<a href="{$data.itemlink}">{$data.title}</a>
						<span class="icon">
						{if $data.is_hot == 'TRUE'}<img src="{$skinDir}/images/icon_hot.gif" />{/if}
						{if $data.is_new == 'TRUE'}<img src="{$skinDir}/images/icon_new.gif" />{/if}
						{if $data.is_package == 'TRUE'}<img src="{$skinDir}/images/icon_package.gif" />{/if}
						{if $data.is_sale == 'TRUE'}<img src="{$skinDir}/images/icon_sale.gif" />{/if}
						{if $data.is_soldout == 'TRUE' || $data.remain == '0'}<img src="{$skinDir}/images/icon_soldout.gif" />{/if}
						</span>
					</div>

					<div class="price">{$data.price|number_format}{if $data.saletype == '2'}포인트{else}원{/if}</div>
					{if $data.point > 0}<div class="point">(포인트 <span class="bold">{$data.point}%</span> 적립)</div>{/if}
				</div>
			</td>
			{if $smarty.foreach.items.index%$rownum == $rownum-1}</tr>{/if}
		{/foreach}

		{if $smarty.foreach.items.index%$rownum != $rownum-1}
			{section name=fits start=$smarty.foreach.items.index%$rownum loop=$rownum-1 step=1}<td></td>{/section}
		</tr>
		{/if}
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

		<div class="height10"></div>
	</td>
</tr>
</table>
</div>