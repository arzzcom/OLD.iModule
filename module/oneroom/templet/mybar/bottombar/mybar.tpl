<div class="BottomMyBar">
	<div class="barContent">
		<div id="BottomMyBarHistory" class="menuoff" onclick="BottomMyBarToggle('History');">
			<div class="toggleArea">
				<div id="BottomMyBarHistoryArea" class="toggleList" style="display:none;">
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="70" /><col width="100%" />
					{foreach name=history from=$history item=data}
					<tr class="pointer" onclick="location.href='/{if $data.category1 == '1'}oneroom{elseif $data.category1 == '2'}multiroom{elseif $data.category1 == '3'}officetel{elseif $data.category1 == '4'}limit{elseif $data.category1 == '5'}full{elseif $data.category1 == '6'}apt{else}shop{/if}?mode=view&idx={$data.idx}';">
						<td>
							<div class="imageView">{if $data.image}<img src="{$data.image}" />{/if}</div>
						</td>
						<td>
							<div class="titleView">{$data.title}</div>
							<div class="infoView"><span class="category">{$data.category}</span> {if $data.region2 != '0'}{mOneroom->GetRegionName region=$data.region2}{else}{mOneroom->GetRegionName region=$data.region1}{/if} ({$data.areasize1}평)</div>
							<div class="infoView">
								{if $data.is_rent_all == 'TRUE'}<div class="priceRentAll">{$data.price_rent_all|number_format}만</div>{/if}
								{if $data.is_rent_month == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_deposit|number_format}만/{$data.price_rent_month|number_format}만</div>{/if}
								{if $data.is_rent_short == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_month|number_format}만(단기)</div>{/if}
								{if $data.is_buy == 'TRUE'}<div class="priceBuy">{$data.price_buy|number_format}만</div>{/if}
							</div>
						</td>
					</tr>
					<tr class="height5"><td colspan="2"></td></tr>
					{/foreach}
					</table>
				</div>
			</div>
			<div class="menuinner">
				최근 본 매물 ({$totalhistory|number_format})
			</div>
		</div>
		
		<div class="menuoff">
			<div class="menuinner">
				찜한 매물 (0)
			</div>
		</div>
		
		<div class="menubar"></div>
	</div>
</div>