{$formStart}
<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100" /><col width="1" /><col width="100%" /><col width="300" />
<tr>
	<td colspan="4" class="sectionTitle">{if $mode == 'modify'}버전 수정하기{else}신규버전 등록하기{/if}</td>
</tr>
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4" class="sectionInfo">
		이 페이지는 기존 프로그램에 신규로 버전을 추가하거나, 이미 등록된 버전의 정보를 수정하는 페이지입니다.<br />
		완전히 별도의 신규 프로그램을 등록하실려면 신규 프로그램 등록페이지를 이용하시기 바랍니다.
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="154" /><col width="1" /><col width="100" /><col width="1" /><col width="150" /><col width="1" /><col width="120" /><col width="1" /><col width="100%" />
<tr>
	<td class="logoCell" rowspan="7">{if $post.logo}<img src="{$post.logo}" />{else}<img src="{$skinDir}/images/nologo.gif" />{/if}</td>
	<td class="splitBar" rowspan="7"></td>
	<td class="headerCell">분류</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{if $post.category}{$post.category}{else}분류없음{/if}</td>
	<td class="splitBar"></td>
	<td class="headerCell left">최초등록일</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{$post.reg_date|date_format:"%Y년 %m월 %d일"}</td>
</tr>
<tr class="splitBar">
	<td colspan="7"></td>
</tr>
<tr>
	<td class="headerCell">제작자</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{$post.nickname}</td>
	<td colspan="4" class="bodyCell" style="padding-left:0px;">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="30" /><col width="60" /><col width="50%" /><col width="50%" />
		<tr>
			<td class="tahoma f10 bold">LV.<span class="orange">{if $post.member.level.lv < 10}0{/if}{$post.member.level.lv}</span></td>
			<td>
				<table cellpadding="0" cellspacing="0" class="exp">
				<col width="1" /><col width="{$post.member.level.exp/$post.member.level.next*50|string_format:"%d"}" /><col width="100%" /><col width="2" />
				<tr>
					<td class="start"></td>
					<td class="on"></td>
					<td class="off"></td>
					<td class="end"></td>
				</tr>
				</table>
			</td>
			<td>
				<div class="email">{if $post.email}<a href="mailto:{$post.email}">{$post.email}</a>{else}<span class="disabled">NONE</span>{/if}</div>
			</td>
			<td>
				<div class="homepage">{if $post.homepage}<a href="{$post.homepage}" target="_blank">{$post.homepage}</a>{else}<span class="disabled">NONE</span>{/if}</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr class="splitBar">
	<td colspan="7"></td>
</tr>
<tr>
	<td class="headerCell">라이센스</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{$post.license}</td>
	<td class="splitBar"></td>
	<td class="headerCell left">프로그램가격</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{if $post.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$post.price|number_format}P</span>{/if}</td>
</tr>
<tr class="splitBar">
	<td colspan="7"></td>
</tr>
<tr>
	<td class="headerCell">최종버전</td>
	<td class="splitBar"></td>
	<td class="bodyCell">{if $post.last_version}<span class="tahoma red bold">{$post.last_version}</span>{else}등록되지 않음{/if}</td>
	<td class="splitBar"></td>
	<td class="headerCell left">다운로드/추천</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><span class="bold tahoma blue">{$post.download|number_format}</span> / <span class="bold tahoma red">{$post.vote|number_format}</span></td>
</tr>
<tr class="splitBar">
	<td colspan="9"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="9"><div></div></td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100" /><col width="1" /><col width="100%" /><col width="300" />
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">버전</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="text" name="version" value="{$version.version}" style="width:100px;" blank="버전을 입력하여 주십시오." />
	</td>
	<td class="gray right">버전정보는 1.0 또는 1.0.0 형태로 입력</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">프로그램파일</td>
	<td class="splitBar"></td>
	<td class="bodyCell" colspan="2">
		<input type="file" name="file" style="width:90%;" />
		
		{if $mode == 'modify'}<div class="gray" style="padding:2px 0px 4px 0px;">기존에 등록한 파일을 변경하고자 할 경우에만 새로운 파일을 선택하여 주십시오.</div>{/if}
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="9"><div></div></td>
</tr>
<tr class="height10">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4" class="sectionTitle">버전 히스토리</td>
</tr>
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4">
		<textarea name="history" id="history" style="width:100%; height:400px;" blank="버전히스토리를 입력하여 주십시오.">{$version.history}</textarea>
		<div class="height5"></div>
		{mRelease->PrintWysiwyg id="history"}
	</td>
</tr>
<tr>
	<td colspan="4">
		<input type="submit" class="btn btn-sm btn-primary btn-block" value="{if $mode == 'modify'}버전 수정하기{else}신규버전 등록하기{/if}" />
		<div class="height5"></div>
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="9"><div></div></td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="50%" />
<tr>
	<td>
		<a href="{$link.list}" class="btn btn-sm btn-default">목록보기</a>
	</td>
	<td></td>
</tr>
</table>
{$formEnd}