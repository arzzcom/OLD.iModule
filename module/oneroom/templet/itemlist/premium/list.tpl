<div class="ItemListPremium">
	<div class="header">
		<div class="headerRight">
			<div class="title"><img src="{$skinDir}/images/title.gif" /></div>
			<div class="info"></div>
		</div>
	</div>
	
	<div class="bodyer">
		<div class="bodyerInner">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
			{foreach name=list from=$data item=data}
				{if $smarty.foreach.list.index % 4 == 0}<tr>{/if}
				<td>
					<div class="imageView pointer" onclick="location.href='/{if $data.category1 == '1'}oneroom{elseif $data.category1 == '2'}multiroom{elseif $data.category1 == '3'}officetel{elseif $data.category1 == '4'}limit{elseif $data.category1 == '5'}full{elseif $data.category1 == '6'}apt{else}shop{/if}?mode=view&idx={$data.idx}';">{if $data.image}<img src="{$data.image}" style="width:100%;" />{/if}</div>
					<div class="titleView">
						<a href="/{if $data.category1 == '1'}oneroom{elseif $data.category1 == '2'}multiroom{elseif $data.category1 == '3'}officetel{elseif $data.category1 == '4'}limit{elseif $data.category1 == '5'}full{elseif $data.category1 == '6'}apt{else}shop{/if}?mode=view&idx={$data.idx}">{$data.title|cutstring:14:false}</a>
					</div>
					<div class="infoView"><span class="category">{$data.category}</span> {if $data.region2 != '0'}{mOneroom->GetRegionName region=$data.region2}{else}{mOneroom->GetRegionName region=$data.region1}{/if} ({$data.areasize1}평)</div>
					<div class="infoView">
						{if $data.is_rent_all == 'TRUE'}<div class="priceRentAll">{$data.price_rent_all|number_format}만</div>{/if}
						{if $data.is_rent_month == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_deposit|number_format}만/{$data.price_rent_month|number_format}만</div>{/if}
						{if $data.is_rent_short == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_month|number_format}만(단기)</div>{/if}
						{if $data.is_buy == 'TRUE'}<div class="priceBuy">{$data.price_buy|number_format}만</div>{/if}
					</div>
				</td>
				{if $smarty.foreach.list.index % 4 == 3}</tr>{/if}
			{/foreach}
			</table>
		</div>
	</div>
	
	<div class="footer">
		<div class="footerRight"></div>
	</div>
</div>