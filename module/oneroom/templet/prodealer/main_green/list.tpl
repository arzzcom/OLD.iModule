<div class="ProDealerMainGreen">
	<div class="header">
		<div class="headerRight">
			<div class="title"><img src="{$skinDir}/images/title.gif" /></div>
			<div class="info"></div>
		</div>
	</div>
	
	<div class="bodyer">
		<div class="bodyerInner">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
			{foreach name=prodealer from=$data item=data}
				{if $smarty.foreach.list.index % 5 == 0}<tr>{/if}
				<td class="pointer" onclick="location.href='{$data.dealerlink}'">
					<div class="listBox" style="margin-right:1px;">
						<div class="imageView">
							<img src="{$data.dealer.photo}" style="width:100%;" />
						</div>
						
						<div class="nameView">{$data.dealer.name} <span class="orange tahoma f10">({$data.item})</span> </div>
						<div class="phoneView">{$data.dealer.cellphone.cellphone}</div>
						<div class="regionView">부산 {$data.region}</div>
					</div>
				</td>
				{if $smarty.foreach.prodealer.index % 5 == 4}</tr>{/if}
			{/foreach}
			</table>
		</div>
	</div>
	
	<div class="footer">
		<div class="footerRight"></div>
	</div>
</div>