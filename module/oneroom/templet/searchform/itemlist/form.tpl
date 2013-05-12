{$formStart}
<input type="hidden" name="search_option" value="Itemlist" />
<input type="hidden" name="search_type" value="{if $search_type}{$search_type}{else}region{/if}" />
<input type="hidden" name="region1" value="{$region1}" />
<input type="hidden" name="region2" value="{$region2}" />
<input type="hidden" name="region3" value="{$region3}" />

<input type="hidden" name="university_parent" value="{$university_parent}" />
<input type="hidden" name="university_idx" value="{$university_idx}" />

<input type="hidden" name="subway_parent" value="{$subway_parent}" />
<input type="hidden" name="subway_idx" value="{$subway_idx}" />

<div class="SearchFormItemlist">
	<div class="tabArea">
		<div id="TabRegion" class="tabOn" onclick="ItemlistToggleSearchType('Region');">지역별검색</div>
		<div id="TabUniversity" class="tabOff" onclick="ItemlistToggleSearchType('University');">대학가별검색</div>
		<div id="TabSubway" class="tabOff" onclick="ItemlistToggleSearchType('Subway');">역세권별검색</div>
	</div>
	<div class="tabContent">
		<div id="TabContentRegion">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="190" /><col width="190" /><col width="190" /><col width="100%" />
			<tr>
				<td>
					<div id="ItemlistRegionList1" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('ItemlistRegionList1')" clicker="ItemlistRegionList1">{if $region1}{mOneroom->GetRegionName region=$region1}{else}1차지역선택(구/군){/if}</div>
						<ul style="display:none;" clicker="ItemlistRegionList1">
							{foreach name=region from=$region item=data}
							<li onclick="InputSelectBoxSelect('ItemlistRegionList1','{$data.title}','{$data.idx}',ItemlistSelectRegion1)">{$data.title}</li>
							{/foreach}
						</ul>
					</div>
				</td>
				<td>
					<div id="ItemlistRegionList2" class="selectbox" style="width:180px;">
						<div onclick="InputSelectBox('ItemlistRegionList2')" clicker="ItemlistRegionList2">{if $region2}{mOneroom->GetRegionName region=$region2}{else}2차지역선택(읍/면/동){/if}</div>
						<ul style="display:none;" clicker="ItemlistRegionList2">
							<li onclick="InputSelectBoxSelect('ItemlistRegionList2','2차지역선택(읍/면/동)','',ItemlistSelectRegion2)">1차지역을 먼저 선택하세요.</li>
						</ul>
					</div>
				</td>
				<td>
					<div id="ItemlistRegionList3" class="selectbox" style="width:180px;">
						<div onclick="InputSelectBox('ItemlistRegionList3')" clicker="ItemlistRegionList3">{if $region3}{mOneroom->GetRegionName region=$region3}{else}3차지역선택{/if}</div>
						<ul style="display:none;" clicker="ItemlistRegionList3">
							<li onclick="InputSelectBoxSelect('ItemlistRegionList3','3차지역선택','',ItemlistSelectRegion3)">2차지역을 먼저 선택하세요.</li>
						</ul>
					</div>
				</td>
			</tr>
			</table>
		</div>
		
		<div id="TabContentUniversity" style="display:none;">
			<div>
				<div id="ItemlistUniversity" class="selectbox" style="width:180px;">
				<div onclick="InputSelectBox('ItemlistUniversity')" clicker="ItemlistUniversity">{if $university_parent}{mOneroom->GetUniversityName university=$university_parent}{else}지역구분{/if}</div>
					<ul style="display:none;" clicker="ItemlistUniversity">
						{foreach name=university from=$university item=data}
						<li onclick="InputSelectBoxSelect('ItemlistUniversity','{$data.title}','{$data.idx}',ItemlistSelectUniversity)">{$data.title}</li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="height5"></div>
			<div id="ItemlistUniversityList"><div class="alertBox">먼저 지역을 선택하여 주십시오.</div></div>
			<div class="layerClear"></div>
		</div>
		
		<div id="TabContentSubway" style="display:none;">
			<div>
				<div id="ItemlistSubway" class="selectbox" style="width:180px;">
				<div onclick="InputSelectBox('ItemlistSubway')" clicker="ItemlistSubway">{if $subway_parent}{mOneroom->GetSubwayName subway=$subway_parent}{else}지역/노선구분{/if}</div>
					<ul style="display:none;" clicker="ItemlistSubway">
						{foreach name=subway from=$subway item=data}
						<li onclick="InputSelectBoxSelect('ItemlistSubway','{$data.title}','{$data.idx}',ItemlistSelectSubway)">{$data.title}</li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="height5"></div>
			<div id="ItemlistSubwayList"><div class="alertBox">먼저 지역/노선구분을 선택하여 주십시오.</div></div>
			<div class="layerClear"></div>
		</div>
		
		<div class="searchForm">
			<div class="panel">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100" /><col width="100%" />
				<tr>
					<td class="formHeader">가격구분</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="18" /><col width="100" /><col width="18" /><col width="100" /><col width="18" /><col width="100" /><col width="18" /><col width="100%" />
						<tr>
							<td><input type="checkbox" name="is_buy" value="TRUE" onclick="ItemlistPriceFormToggle()"{if $is_buy == 'TRUE'} checked="checked"{/if} /></td>
							<td>매매</td>
							<td><input type="checkbox" name="is_rent_all" value="TRUE" onclick="ItemlistPriceFormToggle()"{if $is_rent_all == 'TRUE'} checked="checked"{/if} /></td>
							<td>전세</td>
							<td><input type="checkbox" name="is_rent_month" value="TRUE" onclick="ItemlistPriceFormToggle()"{if $is_rent_month == 'TRUE'} checked="checked"{/if} /></td>
							<td>월세</td>
							<td><input type="checkbox" name="is_rent_short" value="TRUE" onclick="ItemlistPriceFormToggle()"{if $is_rent_short == 'TRUE'} checked="checked"{/if} /></td>
							<td>단기임대</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr id="ItemlistPriceBuy" style="display:none;">
					<td class="formHeader">매매가격</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="80" /><col width="50" /><col width="80" /><col width="100%" />
						<tr>
							<td><input type="text" name="price_buy1" value="{$price_buy1}" style="width:65px; text-align:right;" /></td>
							<td>만원 부터 </td>
							<td><input type="text" name="price_buy2" value="{$price_buy2}" style="width:65px; text-align:right;" /></td>
							<td>만원 까지 <span class="blue">(0을 입력하면 검색하지 않습니다.)</span></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr id="ItemlistPriceRentAll" style="display:none;">
					<td class="formHeader">전세가격</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="80" /><col width="50" /><col width="80" /><col width="100%" />
						<tr>
							<td><input type="text" name="price_rent_all1" value="{$price_rent_all1}" style="width:65px; text-align:right;" /></td>
							<td>만원 부터 </td>
							<td><input type="text" name="price_rent_all2" value="{$price_rent_all2}" style="width:65px; text-align:right;" /></td>
							<td>만원 까지 <span class="blue">(0을 입력하면 검색하지 않습니다.)</span></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr id="ItemlistPriceRentDeposit" style="display:none;">
					<td class="formHeader">보증금</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="80" /><col width="50" /><col width="80" /><col width="100%" />
						<tr>
							<td><input type="text" name="price_rent_deposit1" value="{$price_rent_deposit1}" style="width:65px; text-align:right;" /></td>
							<td>만원 부터 </td>
							<td><input type="text" name="price_rent_deposit2" value="{$price_rent_deposit2}" style="width:65px; text-align:right;" /></td>
							<td>만원 까지 <span class="blue">(0을 입력하면 검색하지 않습니다.)</span></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr id="ItemlistPriceRentMonth" style="display:none;">
					<td class="formHeader">월세가격</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="80" /><col width="50" /><col width="80" /><col width="100%" />
						<tr>
							<td><input type="text" name="price_rent_month1" value="{$price_rent_month1}" style="width:65px; text-align:right;" /></td>
							<td>만원 부터 </td>
							<td><input type="text" name="price_rent_month2" value="{$price_rent_month2}" style="width:65px; text-align:right;" /></td>
							<td>만원 까지 <span class="blue">(0을 입력하면 검색하지 않습니다.)</span></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="formHeader">세부옵션</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="18" /><col width="100" /><col width="18" /><col width="100" /><col width="18" /><col width="100%" />
						<tr>
							<td><input type="checkbox" name="is_double" value="TRUE"{if $is_double == 'TRUE'} checked="checked"{/if} /></td>
							<td>복층원룸</td>
							<td><input type="checkbox" name="is_under" value="TRUE"{if $is_under == 'TRUE'} checked="checked"{/if} /></td>
							<td>반지하/지하</td>
							<td><input type="checkbox" name="is_parkings" value="TRUE"{if $is_parkings == 'TRUE'} checked="checked"{/if} /></td>
							<td>주차공간</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="formHeader">매물명</td>
					<td class="formContent">
						<table cellpadding="0" cellspacing="0" class="layoutfixed">
						<col width="315" /><col width="100%" />
						<tr>
							<td><input type="text" name="keyword" style="width:300px;" value="{$keyword}" /></td>
							<td><input type="image" src="{$skinDir}/images/btn_search.gif" style="border:0px; padding:0px; background:transparent; height:20px;" /></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="height10"></div>

<script type="text/javascript">
ItemlistGetDefaultValues();
</script>
{$formEnd}