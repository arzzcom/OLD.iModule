<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="70" /><col width="100%" />
{if $taglist}
<tr>
	<td class="header">태그 :</td>
	<td>
		{foreach name=taglist from=$taglist item=tag}
		<div style="margin:5px; float:left;"><input type="checkbox" name="tag[]" value="{$tag.idx}"{if $tag.checked == true} checked="checked"{/if} /> <span class="pointer" onclick="TrackerTorrentSearchForCheckbox('tag',{$tag.idx});">{$tag.title}</span></div>
		{/foreach}
	</td>
</tr>
{/if}
<tr>
	<td class="header">상세검색 :</td>
	<td style="padding-top:3px;">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="170" /><col width="135" /><col width="105" /><col width="105" /><col width="100%" />
		<tr>
			<td>
				<input type="hidden" name="subtitles" value="{$subtitles}" />
				<div id="TrackerSubtitles" class="selectbox" style="width:165px;">
					<div onclick="InputSelectBox('TrackerSubtitles')" clicker="TrackerSubtitles">{if $subtitles}{$subtitles}{else}자막(Subtitles){/if}</div>
					<ul style="display:none;" clicker="TrackerSubtitles">
						<li onclick="InputSelectBoxSelect('TrackerSubtitles','자막(Subtitles)','',TrackerListSelectSubtitles)">전체(All)</li>
						<li onclick="InputSelectBoxSelect('TrackerSubtitles','한글(Korean)','한글',TrackerListSelectSubtitles)">한글(Korean)</li>
						<li onclick="InputSelectBoxSelect('TrackerSubtitles','한글+영어(Korean+English)','한글+영어',TrackerListSelectSubtitles)">한글+영어(Korean+English)</li>
						<li onclick="InputSelectBoxSelect('TrackerSubtitles','영어(English)','영어',TrackerListSelectSubtitles)">영어(English)</li>
						<li onclick="InputSelectBoxSelect('TrackerSubtitles','원어(Origin)','원어',TrackerListSelectSubtitles)">원어(Origin)</li>
					</ul>
				</div>
			</td>
			<td>
				<input type="hidden" name="resolution" value="{$resolution}" />
				<div id="TrackerResolution" class="selectbox" style="width:130px;">
					<div onclick="InputSelectBox('TrackerResolution')" clicker="TrackerResolution">{if $resolution}{$resolution}{else}해상도(Resolution){/if}</div>
					<ul style="display:none;" clicker="TrackerResolution">
						<li onclick="InputSelectBoxSelect('TrackerResolution','해상도(Resolution)','',TrackerListSelectResolution)">전체(All)</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','SD','SD',TrackerListSelectResolution)">SD</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','720p','720p',TrackerListSelectResolution)">720p</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','1080p','1080p',TrackerListSelectResolution)">1080p</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','1080i','1080i',TrackerListSelectResolution)">1080i</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','NTSC','NTSC',TrackerListSelectResolution)">NTSC</li>
						<li onclick="InputSelectBoxSelect('TrackerResolution','포터블(Portable)','포터블',TrackerListSelectResolution)">포터블(Portable)</li>
					</ul>
				</div>
			</td>
			<td>
				<input type="hidden" name="codec" value="{$codec}" />
				<div id="TrackerCodec" class="selectbox" style="width:100px;">
					<div onclick="InputSelectBox('TrackerCodec')" clicker="TrackerCodec">{if $codec}{$codec}{else}코덱(Codec){/if}</div>
					<ul style="display:none;" clicker="TrackerCodec">
						<li onclick="InputSelectBoxSelect('TrackerCodec','코덱(Codec)','',TrackerListSelectCodec)">전체(All)</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','XviD.DivX','XviD.DivX',TrackerListSelectCodec)">XviD.DivX</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','H.264.AVC','H.264.AVC',TrackerListSelectCodec)">H.264.AVC</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','MPEG1','MPEG1',TrackerListSelectCodec)">MPEG1</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','MPEG2','MPEG2',TrackerListSelectCodec)">MPEG2</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','WMV','WMV',TrackerListSelectCodec)">WMV</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','VC-1','VC-1',TrackerListSelectCodec)">VC-1</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','DVD5','DVD5',TrackerListSelectCodec)">DVD5</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','DVD9','DVD9',TrackerListSelectCodec)">DVD9</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','MP4','MP4',TrackerListSelectCodec)">MP4</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','Mixed','Mixed',TrackerListSelectCodec)">Mixed</li>
						<li onclick="InputSelectBoxSelect('TrackerCodec','Others','Others',TrackerListSelectCodec)">Others</li>
					</ul>
				</div>
			</td>
			<td>
				<input type="hidden" name="source" value="{$source}" />
				<div id="TrackerSource" class="selectbox" style="width:100px;">
					<div onclick="InputSelectBox('TrackerSource')" clicker="TrackerSource">{if $source}{$source}{else}소스(Source){/if}</div>
					<ul style="display:none;" clicker="TrackerSource">
						<li onclick="InputSelectBoxSelect('TrackerSource','소스(Source)','',TrackerListSelectSource)">전체(All)</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','SDTV','SDTV',TrackerListSelectSource)">SDTV</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','HDTV','HDTV',TrackerListSelectSource)">HDTV</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','IPTV','IPTV',TrackerListSelectSource)">IPTV</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','DVD','DVD',TrackerListSelectSource)">DVD</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','Blu-Ray','Blu-Ray',TrackerListSelectSource)">Blu-Ray</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','BRRip','BRRip',TrackerListSelectSource)">BRRip</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','HD-DVD','HD-DVD',TrackerListSelectSource)">HD-DVD</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','CAM.TS','CAM.TS',TrackerListSelectSource)">CAM.TS</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','R5','R5',TrackerListSelectSource)">R5</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','Screener','Screener',TrackerListSelectSource)">Screener</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','WebRip','WebRip',TrackerListSelectSource)">WebRip</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','WebDL','WebDL',TrackerListSelectSource)">WebDL</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','Portable','Portable',TrackerListSelectSource)">Portable</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','Unknown','Unknown',TrackerListSelectSource)">Unknown</li>
						<li onclick="InputSelectBoxSelect('TrackerSource','Others','Others',TrackerListSelectSource)">Others</li>
					</ul>
				</div>
			</td>
			<td>
				<input type="hidden" name="format" value="{$format}" />
				<div id="TrackerFormat" class="selectbox" style="width:100px;">
					<div onclick="InputSelectBox('TrackerFormat')" clicker="TrackerFormat">{if $format}{$format}{else}포맷(Format){/if}</div>
					<ul style="display:none;" clicker="TrackerFormat">
						<li onclick="InputSelectBoxSelect('TrackerFormat','포맷(Format)','',TrackerListSelectFormat)">전체(All)</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','AVI','AVI',TrackerListSelectFormat)">AVI</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','MKV','MKV',TrackerListSelectFormat)">MKV</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','WMV','WMV',TrackerListSelectFormat)">WMV</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','MPEG','MPEG',TrackerListSelectFormat)">MPEG</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','TP.TS','TP.TS',TrackerListSelectFormat)">TP.TS</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','MTS.M2TS','MTS.M2TS',TrackerListSelectFormat)">MTS.M2TS</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','MP4','MP4',TrackerListSelectFormat)">MP4</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','ISO','ISO',TrackerListSelectFormat)">ISO</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','VOB','VOB',TrackerListSelectFormat)">VOB</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','Mixed','Mixed',TrackerListSelectFormat)">Mixed</li>
						<li onclick="InputSelectBoxSelect('TrackerFormat','Others','Others',TrackerListSelectFormat)">Others</li>
					</ul>
				</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>