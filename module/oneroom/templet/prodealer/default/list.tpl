<div class="ProDealerDefault">
	<div class="innerimg"><img src="{$skinDir}/images/title.gif" /></div>
	{foreach name=prodealer from=$data item=data}
	<div class="listBox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="50" /><col width="100%" />
		<tr>
			<td><div class="imageView"><img src="{$data.dealer.photo}" /></div></td>
			<td>
				<div class="nameView">{$data.dealer.name} <span class="orange tahoma f10">({$data.item})</span></div>
				<div class="phoneView">{$data.dealer.cellphone.cellphone}</div>
				<div class="regionView">부산 {$data.region} 전문!</div>
			</td>
		</tr>
		</table>
		

	</div>
	{/foreach}
</div>