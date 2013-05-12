{$formStart}
<input type="hidden" name="search_option" value="detail" />
<input type="hidden" name="search_type" value="{if $search_type}{$search_type}{else}region{/if}" />
<input type="hidden" name="region1" value="{$region1}" />
<input type="hidden" name="region2" value="{$region2}" />
<input type="hidden" name="region3" value="{$region3}" />

<input type="hidden" name="university_parent" value="{$university_parent}" />
<input type="hidden" name="university_idx" value="{$university_idx}" />

<input type="hidden" name="subway_parent" value="{$subway_parent}" />
<input type="hidden" name="subway_idx" value="{$subway_idx}" />

<div class="SearchFormDetail">
	<div class="tabArea">
		<div id="TabRegion" class="tabOn" onclick="DetailToggleSearchType('Region');">지역별검색</div>
		<div id="TabUniversity" class="tabOff" onclick="DetailToggleSearchType('University');">대학가별검색</div>
		<div id="TabSubway" class="tabOff" onclick="DetailToggleSearchType('Subway');">역세권별검색</div>
	</div>
	<div class="tabContent">
		<div id="TabContentRegion">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="190" /><col width="190" /><col width="190" /><col width="100%" />
			<tr>
				<td>
					<div id="DetailRegionList1" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('DetailRegionList1')" clicker="DetailRegionList1">{if $region1}{mOneroom->GetRegionName region=$region1}{else}1차지역선택(구/군){/if}</div>
						<ul style="display:none;" clicker="DetailRegionList1">
							{foreach name=region from=$region item=data}
							<li onclick="InputSelectBoxSelect('DetailRegionList1','{$data.title}','{$data.idx}',DetailSelectRegion1)">{$data.title}</li>
							{/foreach}
						</ul>
					</div>
				</td>
				<td>
					<div id="DetailRegionList2" class="selectbox" style="width:180px;">
						<div onclick="InputSelectBox('DetailRegionList2')" clicker="DetailRegionList2">{if $region2}{mOneroom->GetRegionName region=$region2}{else}2차지역선택(읍/면/동){/if}</div>
						<ul style="display:none;" clicker="DetailRegionList2">
							<li onclick="InputSelectBoxSelect('DetailRegionList2','2차지역선택(읍/면/동)','',DetailSelectRegion2)">1차지역을 먼저 선택하세요.</li>
						</ul>
					</div>
				</td>
				<td>
					<div id="DetailRegionList3" class="selectbox" style="width:180px;">
						<div onclick="InputSelectBox('DetailRegionList3')" clicker="DetailRegionList3">{if $region3}{mOneroom->GetRegionName region=$region3}{else}3차지역선택{/if}</div>
						<ul style="display:none;" clicker="DetailRegionList3">
							<li onclick="InputSelectBoxSelect('DetailRegionList3','3차지역선택','',DetailSelectRegion3)">2차지역을 먼저 선택하세요.</li>
						</ul>
					</div>
				</td>
			</tr>
			</table>
		</div>
		
		<div id="TabContentUniversity" style="display:none;">
			<div>
				<div id="DetailUniversity" class="selectbox" style="width:180px;">
				<div onclick="InputSelectBox('DetailUniversity')" clicker="DetailUniversity">{if $university_parent}{mOneroom->GetUniversityName university=$university_parent}{else}지역구분{/if}</div>
					<ul style="display:none;" clicker="DetailUniversity">
						{foreach name=university from=$university item=data}
						<li onclick="InputSelectBoxSelect('DetailUniversity','{$data.title}','{$data.idx}',DetailSelectUniversity)">{$data.title}</li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="height5"></div>
			<div id="DetailUniversityList"><div class="alertBox">먼저 지역을 선택하여 주십시오.</div></div>
			<div class="layerClear"></div>
		</div>
		
		<div id="TabContentSubway" style="display:none;">
			<div>
				<div id="DetailSubway" class="selectbox" style="width:180px;">
				<div onclick="InputSelectBox('DetailSubway')" clicker="DetailSubway">{if $subway_parent}{mOneroom->GetSubwayName subway=$subway_parent}{else}지역/노선구분{/if}</div>
					<ul style="display:none;" clicker="DetailSubway">
						{foreach name=subway from=$subway item=data}
						<li onclick="InputSelectBoxSelect('DetailSubway','{$data.title}','{$data.idx}',DetailSelectSubway)">{$data.title}</li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="height5"></div>
			<div id="DetailSubwayList"><div class="alertBox">먼저 지역/노선구분을 선택하여 주십시오.</div></div>
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
							<td><input type="checkbox" name="is_buy" value="TRUE" onclick="DetailPriceFormToggle()"{if $is_buy == 'TRUE'} checked="checked"{/if} /></td>
							<td>매매</td>
							<td><input type="checkbox" name="is_rent_all" value="TRUE" onclick="DetailPriceFormToggle()"{if $is_rent_all == 'TRUE'} checked="checked"{/if} /></td>
							<td>전세</td>
							<td><input type="checkbox" name="is_rent_month" value="TRUE" onclick="DetailPriceFormToggle()"{if $is_rent_month == 'TRUE'} checked="checked"{/if} /></td>
							<td>월세</td>
							<td><input type="checkbox" name="is_rent_short" value="TRUE" onclick="DetailPriceFormToggle()"{if $is_rent_short == 'TRUE'} checked="checked"{/if} /></td>
							<td>단기임대</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr id="DetailPriceBuy" style="display:none;">
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
				<tr id="DetailPriceRentAll" style="display:none;">
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
				<tr id="DetailPriceRentDeposit" style="display:none;">
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
				<tr id="DetailPriceRentMonth" style="display:none;">
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
						<col width="465" /><col width="100%" />
						<tr>
							<td><input type="text" name="keyword" style="width:450px;" value="{$keyword}" /></td>
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

<script type="text/javascript">
DetailGetDefaultValues();
</script>
{$formEnd}