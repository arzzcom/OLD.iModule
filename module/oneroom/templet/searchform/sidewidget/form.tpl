{$formStart}
<input type="hidden" name="search_option" value="default" />
<input type="hidden" name="region1" />
<input type="hidden" name="region2" />
<input type="hidden" name="price_type" />
<input type="hidden" name="price1" />
<input type="hidden" name="price2" />

<div class="sideWidget">
<table cellpadding="0" cellspacing="1" class="tableView">
<tr>
	<td class="titleRow">테마검색/빠른매물검색</td>
</tr>
<tr>
	<td class="contentRow">
		<ul class="theme">
			<li style="background-image:url({$skinDir}/images/icon_university.png)"><a href="/main/search.imo?option=university">대학가별 원룸검색</a></li>
			<li style="background-image:url({$skinDir}/images/icon_subway.png)"><a href="/main/search.imo?option=subway">역세권별 원룸검색</a></li>
			<li style="background-image:url({$skinDir}/images/icon_double.png)"><a href="/main/search.imo?option=double">복층 원룸검색</a></li>
			<li style="background-image:url({$skinDir}/images/icon_date.png)"><a href="/main/search.imo?option=short">단기임대 원룸검색</a></li>
		</ul>
		<div class="bar"></div>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<tr>
			<td>
				<div id="SideWidgetRegionList1" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('SideWidgetRegionList1')" clicker="SideWidgetRegionList1">1차지역선택(구/군)</div>
					<ul style="display:none;" clicker="SideWidgetRegionList1">
						{foreach name=region from=$region item=data}
						<li onclick="InputSelectBoxSelect('SideWidgetRegionList1','{$data.title}','{$data.idx}',SideWidgetSelectRegion1)">{$data.title}</li>
						{/foreach}
					</ul>
				</div>
			</td>
		</tr>
		<tr class="height5"><td></td></tr>
		<tr>
			<td>
				<div id="SideWidgetRegionList2" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('SideWidgetRegionList2')" clicker="SideWidgetRegionList2">2차지역선택(읍/면/동)</div>
					<ul style="display:none;" clicker="SideWidgetRegionList2">
						<li onclick="InputSelectBoxSelect('SideWidgetRegionList2','2차지역선택(읍/면/동)','',SideWidgetSelectRegion2)">1차지역을 먼저 선택하세요.</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr class="height5"><td></td></tr>
		<tr class="height1">
			<td>
				<div style="border-top:1px dotted #CCCCCC; height:1px; overflow:hidden;"></div>
			</td>
		</tr>
		<tr class="height5"><td></td></tr>
		<tr>
			<td>
				<div id="SideWidgetPriceType" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('SideWidgetPriceType')" clicker="SideWidgetPriceType">가격구분</div>
					<ul style="display:none;" clicker="SideWidgetPriceType">
						<li onclick="InputSelectBoxSelect('SideWidgetPriceType','매매','1',SideWidgetSelectPriceType)">매매</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPriceType','전세','2',SideWidgetSelectPriceType)">전세</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPriceType','월세(단기임대)','3',SideWidgetSelectPriceType)">월세(단기임대)</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="SideWidgetPriceAreaBar" class="height5" style="display:none;"><td></td></tr>
		<tr id="SideWidgetPriceArea1" style="display:none;">
			<td>
				<div id="SideWidgetPrice1" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('SideWidgetPrice1')" clicker="SideWidgetPrice1">매매가격</div>
					<ul style="display:none;" clicker="SideWidgetPrice1">
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','5천만원이하','0-5000',SideWidgetSelectPrice1)">5천만원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','5천만~1억원이하','5000-10000',SideWidgetSelectPrice1)">5천만~1억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','1억~2억원이하','10000-20000',SideWidgetSelectPrice1)">1억~2억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','2억~5억원이하','20000-50000',SideWidgetSelectPrice1)">2억~5억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','5억~10억원이하','50000-100000',SideWidgetSelectPrice1)">5억~10억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','10억~20억원이하','100000-200000',SideWidgetSelectPrice1)">10억~20억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice1','20억원이상','200000-',SideWidgetSelectPrice1)">20억원이상</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="SideWidgetPriceArea2" style="display:none;">
			<td>
				<div id="SideWidgetPrice2" class="selectbox" style="width:180px;">
					<div onclick="InputSelectBox('SideWidgetPrice2')" clicker="SideWidgetPrice2">전세가격</div>
					<ul style="display:none;" clicker="SideWidgetPrice2">
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','5백만원이하','0-500',SideWidgetSelectPrice2)">5백만원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','5백만~1천만원이하','500-1000',SideWidgetSelectPrice2)">5백만~1천만원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','1천만~2천만원이하','1000-2000',SideWidgetSelectPrice2)">1천만~2천만원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','2천만~5천만원이하','2000-5000',SideWidgetSelectPrice2)">2천만~5천만원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','5천만~1억원이하','5000-10000',SideWidgetSelectPrice2)">5천만~1억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','1억~3억원이하','10000-30000',SideWidgetSelectPrice2)">1억~3억원이하</li>
						<li onclick="InputSelectBoxSelect('SideWidgetPrice2','3억원이상','30000-',SideWidgetSelectPrice2)">3억원이상</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="SideWidgetPriceArea3" style="display:none;">
			<td>
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<tr>
					<td>
						<div id="SideWidgetPrice3" class="selectbox" style="width:180px;">
							<div onclick="InputSelectBox('SideWidgetPrice3')" clicker="SideWidgetPrice3">보증금</div>
							<ul style="display:none;" clicker="SideWidgetPrice3">
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','3백만원이하','0-300',SideWidgetSelectPrice3)">3백만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','3백만~5백만원이하','300-500',SideWidgetSelectPrice3)">3백만~5백만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','5백만~1천만원이하','300-500',SideWidgetSelectPrice3)">5백만~1천만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','1천만~2천만원이하','300-500',SideWidgetSelectPrice3)">1천만~2천만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','2천만~5천만원이하','2000-5000',SideWidgetSelectPrice3)">2천만~5천만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','5천만~1억원이하','5000-10000',SideWidgetSelectPrice3)">5천만~1억원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','1억~3억원이하','10000-30000',SideWidgetSelectPrice3)">1억~3억원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice3','3억원이상','30000-',SideWidgetSelectPrice3)">3억원이상</li>
							</ul>
						</div>
					</td>
				</tr>
				<tr class="height5"><td></td></tr>
				<tr>
					<td>
						<div id="SideWidgetPrice4" class="selectbox" style="width:180px;">
							<div onclick="InputSelectBox('SideWidgetPrice4')" clicker="SideWidgetPrice4">월 임대료</div>
							<ul style="display:none;" clicker="SideWidgetPrice4">
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','20만원이하','0-20',SideWidgetSelectPrice4)">20만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','20만~40만원이하','20-40',SideWidgetSelectPrice4)">20만~40만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','40만~60만원이하','40-60',SideWidgetSelectPrice4)">40만~60만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','60만~80만원이하','60-80',SideWidgetSelectPrice4)">60만~80만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','80만~1백만원이하','80-100',SideWidgetSelectPrice4)">80만~1백만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','1백만~2백만원이하','100-200',SideWidgetSelectPrice4)">1백만~2백만원이하</li>
								<li onclick="InputSelectBoxSelect('SideWidgetPrice4','2백만원이상','200-',SideWidgetSelectPrice4)">2백만원이상</li>
							</ul>
						</div>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr class="height5"><td></td></tr>
		<tr>
			<td class="center"><input type="image" src="{$skinDir}/images/btn_search.gif" /></td>
		</tr>
		</table>
	</td>
</tr>
</table>
</div>
{$formEnd}