<div class="ProDealerMain">
	<div class="titlebar"><img src="{$skinDir}/images/title.gif" /></div>
	
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
	{foreach name=prodealer from=$data item=data}
		{if $smarty.foreach.list.index % 4 == 0}<tr>{/if}
		<td>
			<div class="listBox" style="margin-right:1px;">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="50" /><col width="100%" />
				<tr>
					<td><div class="imageView"><img src="{$data.dealer.photo}" /></div></td>
					<td>
						<div class="nameView">{$data.dealer.name} <span class="orange tahoma f10">({$data.item})</span> </div>
						<div class="phoneView">{$data.dealer.cellphone.cellphone}</div>
						<div class="regionView">부산 {$data.region}</div>
					</td>
				</tr>
				</table>
			</div>
		</td>
		{if $smarty.foreach.list.index % 4 == 3}</tr>{/if}
	{/foreach}
	</table>
</div>