{$formStart}
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<tr class="viewbar">
	<td class="left"></td>
	<td class="title">신규 버전 등록하기</td>
	<td class="right"></td>
</tr>
</table>

<div class="postinfor">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="110" /><col width="100%" />
	<tr>
		<td>{if $post.logo}<img src="{$post.logo}" class="logo" />{else}<img src="{$skinDir}/images/nologo.gif" class="logo" />{/if}</td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="40%" /><col width="60%" />
			<tr>
				<td class="detail">
					<div><span class="bold">분류</span> : {if $post.category}{$post.category}{else}분류없음{/if}</div>
				</td>
				<td class="detail">
					<div><span class="bold">최초등록일</span> : {$post.reg_date|date_format:"%Y년 %m월 %d일"}</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="detail"><div class="dotted"></div></td>
			</tr>
			<tr>
				<td class="detail">
					<div><span class="bold">제작자</span> : {$post.nickname}</div>
				</td>
				<td class="detail">
					<div><span class="bold">지원홈페이지</span> : {if $post.homepage}<a href="{$post.homepage}" target="_blank">{$post.homepage}</a>{else}없음{/if}</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="detail"><div class="dotted"></div></td>
			</tr>
			<tr>
				<td class="detail">
					<div><span class="bold">라이센스</span> : {$post.license}</div>
				</td>
				<td class="detail">
					<div><span class="bold">프로그램가격</span> : {if $post.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$post.price|number_format}P</span>{/if}</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="detail"><div class="dotted"></div></td>
			</tr>
			<tr>
				<td class="detail">
					<div><span class="bold">최종버전</span> : {$post.last_version}</div>
				</td>
				<td class="detail">
					<div><span class="bold">다운로드(구매) / 추천</span> : <span class="bold tahoma blue">{$post.download|number_format}</span> / <span class="bold tahoma red">{$post.vote|number_format}</span></div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100" /><col width="100%" />
<tr>
	<td><img src="{$skinDir}/images/text_write_version.gif" /></td>
	<td>
		<input type="text" name="version" value="{$version.version}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox" blank="버전을 입력하여 주십시오." autosave="true" />
		<span class="infor">* (버전을 1.0 또는 1.0.0 형태로 입력하여 주십시오.)</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td colspan="2"><img src="{$skinDir}/images/text_write_history.gif" style="margin:5px 0px;" /></td>
</tr>
<tr>
	<td colspan="2">
		<textarea name="history" id="history" style="width:100%; height:400px;" blank="버전히스토리를 입력하여 주십시오." autosave="true" opserve="true">{$version.history}</textarea>
		<div class="height5"></div>
		{mRelease->PrintWysiwyg id="history"}
		<div class="height5"></div>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_file.gif" /></td>
	<td>
		<input type="file" name="file" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:90%;" class="inputbox" autosave="true" />
		<span class="infor">*</span>
		<div class="infor" style="padding:2px 0px 4px 10px;">파일을 변경하고자 할 경우에만 새로운 파일을 첨부하여 주십시오.</div>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
</table>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="10" /><col width="100%" /><col width="10" />
<tr class="mentthin">
	<td class="left"></td>
	<td></td>
	<td class="right"></td>
</tr>
</table>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="50%" />
<tr>
	<td class="innerimg">
		<a href="{$link.list}"><img src="{$skinDir}/images/btn_list.gif" style="margin-right:3px;" /></a>
	</td>
	<td class="innerimg right">
		<input type="image" src="{$skinDir}/images/btn_{$mode}.gif" />
	</td>
</tr>
</table>{$formEnd}