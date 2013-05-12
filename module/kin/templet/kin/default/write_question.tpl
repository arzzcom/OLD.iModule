{$formStart}

<div class="writeBoxTitle">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="80" /><col width="100%" />
	<tr>
		<td class="right"><img src="{$skinDir}/images/text_question.gif" /></td>
		<td class="right"><input type="text" name="title" class="inputTitle" blank="제목을 입력하여 주십시오." autosave="true" value="{$post.title}" /></td>
	</tr>
	</table>
</div>

<div>
	<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
	{mKin->PrintWysiwyg id="content"}
</div>

<div class="writeBox">
	{mKin->PrintUploader type='question' form=$formName id="uploader" skin="kin" wysiwyg="content"}
</div>

<div class="writeBox">
	<input type="hidden" name="category1" value="{$post.category1}" />
	<input type="hidden" name="category2" value="{$post.category2}" />
	<input type="hidden" name="category3" value="{$post.category3}" />
	<div class="boxTitle">질문 카테고리 선택</div>
	<div class="boxContent">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="32%" /><col width="2%" /><col width="32%" /><col width="2%" /><col width="32%" />
		<tr class="height20">
			<td class="gray f12">1차 카테고리</td>
			<td></td>
			<td class="gray f12">2차 카테고리</td>
			<td></td>
			<td class="gray f12">3차 카테고리</td>
		</tr>
		<tr>
			<td>
				<div id="category1" class="categoryBox">
					<ul>
					{foreach name=categoryList1 from=$categoryList1 item=categoryList1}
						<li onmouseover="if (this.className != 'select') this.className='over';" onmouseout="if (this.className != 'select') this.className='';" onclick="SelectCategory(this,1,{$categoryList1.idx});"{if $post.category1 == $categoryList1.idx} class="select"{/if}>{$categoryList1.category}</li>
					{/foreach}
					</ul>
				</div>
			</td>
			<td></td>
			<td>
				<div id="category2" class="categoryBox">
					<ul>
					{foreach name=categoryList2 from=$categoryList2 item=categoryList2}
						<li onmouseover="if (this.className != 'select') this.className='over';" onmouseout="if (this.className != 'select') this.className='';" onclick="SelectCategory(this,2,{$categoryList2.idx});"{if $post.category2 == $categoryList2.idx} class="select"{/if}>{$categoryList2.category}</li>
					{/foreach}
					</ul>
				</div>
			</td>
			<td></td>
			<td>
				<div id="category3" class="categoryBox">
					<ul>
					{foreach name=categoryList3 from=$categoryList3 item=categoryList3}
						<li onmouseover="if (this.className != 'select') this.className='over';" onmouseout="if (this.className != 'select') this.className='';" onclick="SelectCategory(this,3,{$categoryList3.idx});"{if $post.category3 == $categoryList3.idx} class="select"{/if}>{$categoryList3.category}</li>
					{/foreach}
					</ul>
				</div>
			</td>
		</tr>
		</table>
	</div>
</div>

<div class="writeBox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="120" /><col width="100%" />
	<tr>
		<td><div class="formHeader">추가포인트</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100" /><col width="100%" />
			<tr>
				<td>
					<input type="hidden" name="point" value="{$post.point}" />
					<div id="SelectPointBox" class="selectbox" style="width:90px;">
						<div onclick="InputSelectBox('SelectPointBox')" clicker="SelectPointBox">{if $post.point == '0'}추가안함{else}{$post.point}포인트{/if}</div>
			
						<ul style="display:none;" clicker="SelectPointBox">
							<li onclick="InputSelectBoxSelect('SelectPointBox','추가안함','',SelectPoint)">추가안함</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','5포인트','5',SelectPoint)">5포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','10포인트','10',SelectPoint)">10포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','15포인트','15',SelectPoint)">15포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','20포인트','20',SelectPoint)">20포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','25포인트','25',SelectPoint)">25포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','30포인트','30',SelectPoint)">30포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','35포인트','35',SelectPoint)">35포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','40포인트','40',SelectPoint)">40포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','45포인트','45',SelectPoint)">45포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','50포인트','50',SelectPoint)">50포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','60포인트','60',SelectPoint)">60포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','70포인트','70',SelectPoint)">70포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','80포인트','80',SelectPoint)">80포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','90포인트','90',SelectPoint)">90포인트</li>
							<li onclick="InputSelectBoxSelect('SelectPointBox','100포인트','100',SelectPoint)">100포인트</li>
						</ul>
					</div>
				</td>
				<td class="f11 gray">포인트를 채택된 답변자에게 드립니다. (답변채택시, 질문자에게도 포인트의 50%를 돌려드립니다.)</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">비밀글</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input type="checkbox" name="is_secret" value="TRUE"{if $post.is_secret == true} checked="checked"{/if} /></td>
				<td class="f12 gray">작성자 및 관리권한을 가진 회원만 확인할 수 있는 <span class="pointText">"비밀글"상태로 질문을 등록합니다.</span></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">익명등록</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_hidename" type="checkbox" name="is_hidename" value="1"{if $post.is_hidename == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_hidename">질문을 작성한 작성자 정보를 공개하지 않습니다. (단 질문마감률은 공개됩니다.)</label></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">쪽지알림받기</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_msg" type="checkbox" name="is_msg" value="1"{if $post.is_msg == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_msg">답변등록시 쪽지로 해당 답변을 받아봅니다.</label></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">메일알림받기</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_email" type="checkbox" name="is_email" value="1"{if $post.is_email == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_email">답변등록시 이메일로 해당 답변을 받아봅니다.</label></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

<div class="writeBox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="120" /><col width="100%" />
	{if $member.idx == 0}
	<tr>
		<td><div class="formHeader">작성자이름</div></td>
		<td><input type="text" name="name" value="{$post.name}" class="inputShort" /></td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">이메일주소</div></td>
		<td><input type="text" name="email" value="{$post.email}" class="inputLong" /></td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">패스워드</div></td>
		<td><input type="password" name="password" value="" class="inputShort" /> <span class="f11 gray">{if $post.password}패스워드를 수정하려면 입력하여 주십시오.{else}답변을 채택하거나, 질문수정 및 질문마감시에 필요합니다.{/if}</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	{/if}
	<tr>
		<td><div class="formHeader">이용동의</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_agree" type="checkbox" name="is_agree"{if $post.is_agree == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_agree">답변등록시 질문을 삭제하거나, 질문내용을 삭제하거나 수정할 수 없다는 것에 동의합니다.</label></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

{if $antispam}
<div class="writeBox padding10">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="170" /><col width="100%" />
	<tr>
		<td>{$antispam}</td>
		<td class="antispam">
			스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.<br />
			회원로그인을 하시면, 좀 더 편리하게 게시물을 작성할 수 있습니다.<br />
			<input type="text" name="antispam" style="width:100px;" />
		</td>
	</tr>
	</table>
</div>
{/if}

<div class="height10"></div>

<div class="center">
	<input type="image" src="{$skinDir}/images/btn_write_question.gif" />
	<a href="{$link.list}"><img src="{$skinDir}/images/btn_cancel.gif" /></a>
</div>

<div class="height10"></div>
{$formEnd}