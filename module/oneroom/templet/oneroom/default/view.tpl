<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/photo_title.gif" /></td>
		<td class="right">총 <span class="pointText1 bold">{$totalimage|number_format}</span>장의 이미지가 있습니다.</td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<table cellspacing="1" cellpadding="0" class="viewTable">
<col width="100%" /><col width="170" />
<tr>
	<td style="background:#4F4F4F;">
		<div id="ImageViewer" style="height:420px; overflow:hidden;">
			<div id="ImageViewerLoader">이미지 로딩중 ...</div>
			{foreach name=list from=$image item=img}
				<div id="ImageViewerImage{$img.idx}" class="originimglayer"><img src="{$img.image}" class="originimg" /></div>
			{/foreach}
		</div>
	</td>
	<td style="background:#FFFFFF;">
		<div class="viewImageList">
		{foreach name=list from=$image item=img}
			<img src="{$img.thumbnail}" onmouseover="ShowImageViewer({$img.idx})" />
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="height10"></div>

<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/default_title.gif" /></td>
		<td class="right"></td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<table cellspacing="1" cellpadding="0" class="viewTable">
<col width="88" /><col width="50%" /><col width="88" /><col width="50%" />
<tr>
	<td class="viewHeader">매물명</td>
	<td class="viewBody bold" colspan="3">{$data.title}</td>
</tr>
<tr>
	<td class="viewHeader">면적</td>
	<td class="viewBody">{$data.areasize}</td>
	<td class="viewHeader">실면적</td>
	<td class="viewBody">{$data.real_areasize}</td>
</tr>
<tr>
	<td class="viewHeader">방갯수</td>
	<td class="viewBody">{$data.rooms}개</td>
	<td class="viewHeader">층</td>
	<td class="viewBody">{$data.floor}</td>
</tr>
<tr>
	<td class="viewHeader">가격</td>
	<td class="viewBody">
		{if $data.is_rent_all == 'TRUE'}<div class="priceRentAll">{$data.price_rent_all|number_format}만</div>{/if}
		{if $data.is_rent_month == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_deposit|number_format}만/{$data.price_rent_month|number_format}만</div>{/if}
		{if $data.is_rent_short == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_month|number_format}만 (단기)</div>{/if}
		{if $data.is_buy == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_month|number_format}만 (단기)</div>{/if}
	</td>
	<td class="viewHeader">관리비</td>
	<td class="viewBody priceText">{$data.price_price_maintenance|number_format}만</td>
</tr>
<tr>
	<td class="viewHeader">매물상태</td>
	<td class="viewBody">계약가능 > 계약금납입 > 계약완료</td>
	<td class="viewHeader">입주가능일</td>
	<td class="viewBody"><span class="pointText1">{if $data.movein_date == '0000-00-00'}즉시입주가능{else}{$data.movein_date}{/if}</span> / <span class="pointText2">{$data.build_year}년 준공</span></td>
</tr>
</table>

<div class="height10"></div>

<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/region_title.gif" /></td>
		<td class="right"></td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<col width="100%" /><col width="350" />
<tr>
	<td class="vTop">
		<table cellspacing="1" cellpadding="0" class="viewTable">
		<col width="88" /><col width="100%" />
		<tr style="height:40px;">
			<td class="viewHeader">기본주소</td>
			<td class="viewBody">{$data.region}</td>
		</tr>
		<tr style="height:40px;">
			<td class="viewHeader">우편번호</td>
			<td class="viewBody">{$data.zipcode}</td>
		</tr>
		<tr style="height:65px; overflow:hidden;">
			<td class="viewHeader">세부주소</td>
			<td class="viewBody">{$data.address}</td>
		</tr>
		<tr style="height:40px;">
			<td class="viewHeader">인근대학</td>
			<td class="viewBody">{if $data.university}<span class="pointText3">{$data.university}</span>{else}없음{/if}</td>
		</tr>
		<tr style="height:40px;">
			<td class="viewHeader">인근지하철</td>
			<td class="viewBody">{if $data.subway}<span class="pointText1">{$data.subway}</span>{else}없음{/if}</td>
		</tr>
		<tr style="height:40px;">
			<td class="viewHeader">주차공간</td>
			<td class="viewBody">{if $data.parkings == 0}주차불가{else}{$data.parkings}대 가능{/if}</td>
		</tr>
		</table>
	</td>
	<td>
		<div style="height:270px; border:1px solid #CCCCCC; border-left:0px;">{$map}</div>
	</td>
</tr>
</table>

<div class="height10"></div>

<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/option_title.gif" /></td>
		<td class="right"></td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<table cellspacing="1" cellpadding="0" class="viewTable">
<col width="88" /><col width="100%" />
{foreach name=option from=$option item=opt}
<tr>
	<td class="viewHeader">{$opt.title}</td>
	<td class="viewBody">
		{foreach name=select from=$opt.select item=sel}
		<div class="{if $sel.checked == true}viewOptionChecked{else}viewOption{/if}">{$sel.title}</div>
		{/foreach}
	</td>
</tr>
{/foreach}
</table>

<div class="height10"></div>

<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/detail_title.gif" /></td>
		<td class="right"></td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<div class="viewDetail">{$data.detail}</div>

<div class="height10"></div>

<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/dealer_title.gif" /></td>
		<td class="right"></td>
	</tr>
	</table>
</div>
<div class="height5"></div>
<table cellspacing="1" cellpadding="0" class="viewTable">
<col width="75" /><col width="50%" /><col width="75" /><col width="50%" />
<tr>
	<td class="viewHeader">담당자</td>
	<td class="viewBody" colspan="3">{$dealer.name}</td>
</tr>
<tr>
	<td class="viewHeader">전화번호</td>
	<td class="viewBody">{$dealer.cellphone.cellphone}</td>
	<td class="viewHeader">이메일</td>
	<td class="viewBody">{$dealer.email}</td>
</tr>
</table>

<div class="height5"></div>

<div class="viewButton">
	<!-- <img src="{$skinDir}/images/btn_addfav.gif" /> -->
</div>