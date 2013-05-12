<div id="ShopView">
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
				{$data.title}
				<span class="icon">
				{if $data.is_hot == 'TRUE'}<img src="{$skinDir}/images/icon_hot.gif" />{/if}
				{if $data.is_new == 'TRUE'}<img src="{$skinDir}/images/icon_new.gif" />{/if}
				{if $data.is_package == 'TRUE'}<img src="{$skinDir}/images/icon_package.gif" />{/if}
				{if $data.is_sale == 'TRUE'}<img src="{$skinDir}/images/icon_sale.gif" />{/if}
				</span>
			</td>
			<td class="right"></td>
		</tr>
		</table>
		<div class="height5"></div>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="320" /><col width="100%" />
		<tr>
			<td class="vTop">
				<div class="categoryview">
				{foreach name=categorys from=$categorys item=category}{if $Smarty.foreach.categorys.index != 0} <span class="tahoma">&gt;</span> {/if}<a href="{$category.link}">{$category.title}</a>{/foreach}
				</div>
				<div class="viewimg"><img src="{$data.view_image}" /></div>
			</td>
			<td class="viewinfo">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="90" /><col width="100%" />
				<tr>
					<td><img src="{$skinDir}/images/text_price.gif" /></td>
					<td class="{if $data.is_sale == 'TRUE'}saleprice{else}price{/if}">{if $data.is_sale == 'TRUE'}{$data.saleprice|number_format}{else}{$data.price|number_format}{/if}{if $data.saletype == '2'}포인트{else}원{/if}</td>
				</tr>
				{if $data.is_sale == 'TRUE'}
				<tr>
					<td><img src="{$skinDir}/images/text_saleprice.gif" /></td>
					<td class="price">{$data.price|number_format}{if $data.saletype == '2'}포인트{else}원{/if}</td>
				</tr>
				{/if}
				<tr>
					<td><img src="{$skinDir}/images/text_code.gif" /></td>
					<td class="code">{$data.code}</td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_type.gif" /></td>
					<td class="text">
						{if $data.type == '1'}국내발매{/if}
						{if $data.type == '2'}직접수입{/if}
						{if $data.type == '3'}국내정식수입{/if}
					</td>
				</tr>
				<tr>
					<td colspan="2"><div class="line"></div></td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_point.gif" /></td>
					<td class="text">결제액의 <span class="bold">{$data.point}%</span>적립, <span class="pointtext">{$data.pointcalc|number_format}포인트</span></td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_delivery.gif" /></td>
					<td class="text">{if $data.delivery_price == '0'}무료배송{else}{$data.delivery_price|number_format}원{/if}</td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_remain.gif" /></td>
					<td class="text">
						{if $data.is_soldout == 'TRUE'}
							<img src="{$skinDir}/images/icon_soldout.gif" />
						{else}
							{if $data.remain == '-1'}재고있음{else}<span class="bold">{$data.remain|number_format}</span> EA{/if}
							{if $data.remain == '0'}<img src="{$skinDir}/images/icon_soldout.gif" />{/if}
						{/if}
					</td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_limit.gif" /></td>
					<td class="text">{if $data.limit == '0'}구매제한수량이 없습니다. 단, 재고에 따라 제한될 수 있습니다.{else}<span class="bold">1회</span> 주문에 <span class="pointtext bold">{$data.limit|number_format}EA</span>만 주문가능합니다.{/if}</td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_with.gif" /></td>
					<td class="text">{if $data.pay_with == 'TRUE'}장바구니의 다른상품과 함께 주문할 수 있습니다.{else}다른상품과 함께 주문할 수 없습니다.{/if}</td>
				</tr>
				<tr>
					<td colspan="2"><div class="line"></div></td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_option.gif" /></td>
				</tr>
				</table>

				<div id="OptionList">
					<div id="OptionList0" class="option">
						<table cellspacing="0" cellpadding="0" class="layoutfixed">
						<col width="80" /><col width="100%" /><col width="150" />
						{foreach name=options from=$data.option item=option}
						<tr>
							<td>옵션{$option.loopnum}</td>
							<td colspan="2">
								<select name="option{$option.loopnum}[]" style="width:100%;"{if $option.disable == true} disabled="disabled"{/if}>
								{if $option.disable == true}
								<option value="-1">선택할 수 있는 옵션이 없습니다.</option>
								{else}
								{foreach name=optionlists from=$option.list item=optionlist}
								<option value="{$optionlist.idx}">{$optionlist.value}</option>
								{/foreach}
								{/if}
								</select>
							</td>
						</tr>
						{/foreach}
						<tr>
							<td>수량</td>
							<td>
								<table cellpadding="0" cellspacing="0" class="layoutfixed">
								<col width="40" /><col width="16" /><col width="100%" />
								<tr>
									<td><input type="text" name="ea[]" value="1" readonly="readonly" /></td>
									<td><img src="{$skinDir}/images/btn_plus.gif" OptionID="0" onclick="ShopAddEA(this)" class="pointer" /></td>
									<td><img src="{$skinDir}/images/btn_minus.gif" OptionID="0" onclick="ShopDelEA(this)" class="pointer" /></td>
								</tr>
								</table>
							</td>
							<td class="innerimg right">
								<img src="{$skinDir}/images/btn_option_del.gif" class="pointer" onclick="ShopDelOption(this)" style="margin-right:3px;" />
								<img src="{$skinDir}/images/btn_option_add.gif" class="pointer" onclick="ShopAddOption(this)" />
							</td>
						</tr>
						</table>
					</div>
				</div>

				<script type="text/javascript">
				document.getElementById("OptionList0").getElementsByTagName("img")[2].style.display = "none";
				</script>

				<div class="button">
					<img src="{$skinDir}/images/btn_buy.gif" />
					<img src="{$skinDir}/images/btn_cart.gif" onclick="ShopAddCart();" />
					<img src="{$skinDir}/images/btn_fav.gif" />
				</div>
			</td>
		</tr>
		</table>

		<div class="height10"></div>
		<a name="detail"></a>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="5" /><col width="100%" /><col width="5" />
		<tr class="navbar">
			<td class="left"></td>
			<td>
				<div class="tabOn" onclick="location.href='#detail';">상세설명</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#qna';">상품문의(<span class="pointtext">{$data.qnanum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#property';">상품평(<span class="pointtext">{$data.propertynum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#as';">반품/환불/AS안내</div>
			</td>
			<td class="right"></td>
		</tr>
		</table>

		<div class="content">
		{$data.content}
		</div>


		<div class="height10"></div>
		<a name="qna"></a>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="5" /><col width="100%" /><col width="5" />
		<tr class="navbar">
			<td class="left"></td>
			<td>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#detail';">상세설명</div>
				<div class="tabOn" onclick="location.href='#qna';">상품문의(<span class="pointtext">{$data.qnanum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#property';">상품평(<span class="pointtext">{$data.propertynum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#as';">반품/환불/AS안내</div>
			</td>
			<td class="right"></td>
		</tr>
		</table>


		<div class="height10"></div>
		<a name="property"></a>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="5" /><col width="100%" /><col width="5" />
		<tr class="navbar">
			<td class="left"></td>
			<td>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#detail';">상세설명</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#qna';">상품문의(<span class="pointtext">{$data.qnanum|number_format}</span>)</div>
				<div class="tabOn" onclick="location.href='#property';">상품평(<span class="pointtext">{$data.propertynum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#as';">반품/환불/AS안내</div>
			</td>
			<td class="right"></td>
		</tr>
		</table>

		<div class="height10"></div>
		<a name="as"></a>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="5" /><col width="100%" /><col width="5" />
		<tr class="navbar">
			<td class="left"></td>
			<td>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#detail';">상세설명</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#qna';">상품문의(<span class="pointtext">{$data.qnanum|number_format}</span>)</div>
				<div class="tabOff" onmouseover="this.className='tabOver';" onmouseout="this.className='tabOff';" onclick="location.href='#property';">상품평(<span class="pointtext">{$data.propertynum|number_format}</span>)</div>
				<div class="tabOn" onclick="location.href='#as';">반품/환불/AS안내</div>
			</td>
			<td class="right"></td>
		</tr>
		</table>

		<div class="content">
			<div style="font-family:돋움; font-size:14px; font-weight:bold; background:#EEEEEE; padding:5px; margin:10px 0px 10px 0px;">배송결제</div>

			<div style="line-height:150%; font-family:돋움; font-size:12px; padding-left:10px;">
			주문수량과 금액에 상관없이 배송비는 일괄 4,000원을 부과합니다.<br />
			배송은 대한통운으로 실시되며, 매주 월, 수, 금요일에 배송합니다.<br />
			도서/산간 지역의 경우, 대도시보다 배송이 2-3일 정도 추가 소요되는 경우가 있으며, 배송시 추가적으로 발생하는 배송비는 구매자가 부담해야 합니다.

			</div>

			<div style="font-family:돋움; font-size:14px; font-weight:bold; background:#EEEEEE; padding:5px; margin:10px 0px 10px 0px;">반품/교환문의</div>


			<div style="line-height:150%; font-family:돋움; font-size:12px; padding-left:10px;">
			교환/반품신청의 기준<br /><br />

			상품이 공급된 날로부터 3일 이내에 교환/반품을 신청하실 수 있습니다.(아크릴케이스 제품의 경우 5일 이내) 그러나, 다음의 각 내용에 해당하는 경우에는 교환/반품 신청이 받아들여지지 않을 수 있습니다.<br /><br />

			&nbsp;&nbsp;1. 소비자의 책임 있는 사유로 상품 등이 멸실/훼손된 경우(단순 확인을 위한 포장 개봉 제외)<br />
			&nbsp;&nbsp;2. 소비자의 사용/소비에 의해 상품 등의 가치가 현저히 감소한 경우<br />

			&nbsp;&nbsp;3. 시간의 경과에 의해 재판매가 곤란할 정도로 상품 등의 가치가 현저히 감소한 경우<br />
			&nbsp;&nbsp;4. 복제가 가능한 상품 등의 포장을 훼손한 경우<br />
			&nbsp;&nbsp;5. 판매/생산방식의 특성상, 교환/반품 시 판매자에게 회복할 수 없는 손해가 발생하는 경우<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(주문생산, 개별생산, 맞춤 제작 등)
			</div>

			<div style="font-family:돋움; font-size:14px; font-weight:bold; background:#EEEEEE; padding:5px; margin:10px 0px 10px 0px;">반품배송비</div>

			<div style="line-height:150%; font-family:돋움; font-size:12px; padding-left:10px;">
			&nbsp;&nbsp;1. 단순 구매변심에 의한 교환/반품 시 왕복 배송비는 구매자가 부담합니다.<br />
			&nbsp;&nbsp;2. 오배송에 의한 교환/반품 시 왕복 배송비는 판매자가 부담합니다. <br />
			&nbsp;&nbsp;3. 반품은 발송지로 반송하셔야 정상적인 처리가 가능합니다.<br /><br />

			환불은 판매자가 상품 등을 확인한 후에 처리됩니다.
			</div>

			<div style="font-family:돋움; font-size:14px; font-weight:bold; background:#EEEEEE; padding:5px; margin:10px 0px 10px 0px;">구매 시 주의사항</div>

			<div style="line-height:150%; font-family:돋움; font-size:12px; padding-left:10px;">
			본 제품의 이미지는 실제 상품과 다를 수 있으므로 정확한 모델명을 확인하신 뒤 선택해 주시기 바랍니다.<br />
			주문이 체결된 후 품절 등으로 판매자가 상품을 공급할 수 없게 된 경우, 주문은 취소될 수 있으며 고객님께 안내연락이 취해집니다.<br />
			미성년자가 법정대리인의 동의 없이 구매계약을 체결한 경우, 미성년자와 법정대리인은 구매계약을 취소할 수 있습니다.<br />
			주문이 체결된 후에는 취소를 접수하셔도 상품이 발송 될 수 있으며, 발송된 상품의 반품 배송비는 구매자가 부담합니다.<br />
			반품상품은 상품을 보낸 판매자의 주소로 보내셔야 합니다.
			</div>
		</div>
		<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
	</td>
</tr>
</table>
</div>