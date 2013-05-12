<div class="outer">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="1" /><col width="100%" />
	<tr>
		<td class="header">
			메세지분류
		</td>
		<td class="spliter"></td>
		<td class="header">
			메세지보기 {$printmessageMethod}
		</td>
	</tr>
	<tr class="height1">
		<td colspan="3" class="spliter"></td>
	</tr>
	<tr>
		<td class="vTop">
			<div class="methods">
				<div id="MethodDate" class="{if $method == 'date'}open{else}close{/if}" onclick="ToggleMethod('Date');">
					<div class="namecard">날짜별</div>
				</div>

				<ul id="ListDate" class="methodDate"{if $method != 'date'} style="display:none;"{/if}>
					<li{if $method == 'date' && $finder == 'today'} class="select"{/if}><a href="{$link.method.today}">날짜 : <span class="bold">오늘</span></a></li>
					<li{if $method == 'date' && $finder == 'week'} class="select"{/if}><a href="{$link.method.week}">날짜 : <span class="bold">이번 주</span></a></li>
					<li{if $method == 'date' && $finder == 'lastweek'} class="select"{/if}><a href="{$link.method.lastweek}">날짜 : <span class="bold">지난 주</span></a></li>
					<li{if $method == 'date' && $finder == 'month'} class="select"{/if}><a href="{$link.method.month}">날짜 : <span class="bold">이번 달</span></a></li>
					<li{if $method == 'date' && $finder == 'lastmonth'} class="select"{/if}><a href="{$link.method.lastmonth}">날짜 : <span class="bold">지난 달</span></a></li>
					<li{if $method == 'date' && $finder == 'old'} class="select"{/if}><a href="{$link.method.old}">날짜 : <span class="bold">오래된 메세지</span></a></li>
					<li{if $method == 'date' && $finder == 'unread'} class="select"{/if}><a href="{$link.method.unread}">구분 : <span class="bold">읽지않은 메세지</span></a></li>
				</ul>

				<div class="height10"></div>
				<div id="MethodID" class="{if $method == 'id'}open{else}close{/if}" onclick="ToggleMethod('ID');">
					<div class="namecard">아이디별</div>
				</div>

				<ul id="ListID" class="methodID"{if $method != 'id'} style="display:none;"{/if}>
					{foreach from=$ids item=id}
						<li{if $method == 'id' && $finder == $id.idx} class="select"{/if}><a href="{$id.link}">{$id.nickname} <span class="count">[{$id.message|number_format}]</span></a></li>
					{foreachelse}
						<li>아이디목록이 없습니다.</li>
					{/foreach}
				</ul>

				<div id="MethodSent" class="{if $method == 'sent'}open{else}close{/if}" onclick="ToggleMethod('Sent');">
					<div class="namecard">보낸메세지</div>
				</div>

				<ul id="ListSent" class="methodDate"{if $method != 'sent'} style="display:none;"{/if}>
					<li{if $method == 'sent' && $finder == 'today'} class="select"{/if}><a href="{$link.method.sentToday}">날짜 : <span class="bold">오늘</span></a></li>
					<li{if $method == 'sent' && $finder == 'week'} class="select"{/if}><a href="{$link.method.sentWeek}">날짜 : <span class="bold">이번 주</span></a></li>
					<li{if $method == 'sent' && $finder == 'lastweek'} class="select"{/if}><a href="{$link.method.sentLastweek}">날짜 : <span class="bold">지난 주</span></a></li>
					<li{if $method == 'sent' && $finder == 'month'} class="select"{/if}><a href="{$link.method.sentMonth}">날짜 : <span class="bold">이번 달</span></a></li>
					<li{if $method == 'sent' && $finder == 'lastmonth'} class="select"{/if}><a href="{$link.method.sentLastmonth}">날짜 : <span class="bold">지난 달</span></a></li>
					<li{if $method == 'sent' && $finder == 'old'} class="select"{/if}><a href="{$link.method.sentOld}">날짜 : <span class="bold">오래된 메세지</span></a></li>
				</ul>
			</div>

			<div class="searchbox">
				<div class="searchinput">
					<input id="ModuleBoardkeyword_{$bid}" type="text" name="keyword" class="inputbox" autocomplete="off" value="{$keyword}" />
					<input type="submit" class="buttonbox" value="" />
				</div>
			</div>
		</td>
		<td class="spliter"></td>
		<td class="vTop">
			<div class="toolbar"><span class="pointer" onclick="SelectAll(true)">전체선택</span>&nbsp;&nbsp;&nbsp;<span class="pointer" onclick="SelectAll(false)">전체해제</span>&nbsp;&nbsp;&nbsp;<span class="delete" onclick="DeleteAll()">선택한 메세지 삭제</span></div>
			<div id="MessageList" class="messageArea">
				{foreach from=$message item=message}
				<div class="item">
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="200" /><col width="100%" /><col width="20" />
					<tr class="user">
						<td class="nickname">{$message.nickname}</td>
						<td class="date">{$message.reg_date|date_format:"%Y.%m.%d %I:%M:%S %p"}</td>
						<td class="right">{$message.checkbox}</td>
					</tr>
					<tr>
						<td colspan="3">
							{if $message.system}
								{if $message.system.module == 'board'}
									{if $message.system.type == 'select'}
										<div class="systemType">{$message.system.nickname}님이 회원님의 답변을 채택하였습니다.</div>
									{elseif $message.system.type == 'move'}
										<div class="systemType">{$message.system.nickname}님이 회원님의 게시물을 이동하였습니다.</div>
									{elseif $message.system.type == 'delete'}
										<div class="systemType">{$message.system.nickname}님이 회원님의 게시물을 삭제하였습니다.</div>
									{else}
										<div class="systemType">{$message.system.nickname}님이 회원님의 {if $message.system.type == 'post'}게시물{else}댓글{/if}에 댓글을 작성하였습니다.</div>
									{/if}
									<div class="systemParent">{$message.system.parent}</div>

									{$message.system.message}
								{/if}
								
								{if $message.system.module == 'kin'}
									{if $message.system.type == 'select'}
										<div class="systemType">{$message.system.nickname}님이 회원님의 답변을 채택하였습니다.</div>
									{/if}
									
									<div class="systemParent">{$message.system.parent}</div>

									{$message.system.message}
								{/if}

								{if $message.system.module == 'member'}
									{if $message.system.type == 'pointgift'}
										<div class="systemType">{if !$message.system.nickname}익명의 유저{else}{$message.system.nickname}{/if}님이 회원님에게 포인트를 선물하였습니다.</div>

										{$message.system.message}
									{/if}
								{/if}
							{else}
							<div class="msgbody">{$message.message}</div>
							{/if}
							{if $message.url}<div class="url">{$message.url}</div>{/if}
						</td>
					</tr>
					</table>
				</div>
				{foreachelse}
				<div class="item">
					<div class="nomessage">메세지가 없습니다.</div>
				</div>
				{/foreach}
			</div>

			<div class="navigator">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="100" /><col width="100%" /><col width="100" />
				<tr>
					<td class="button">
						{if $prevlist != false}
							<a href="{$link.page}{$prevlist}"><img src="{$skinDir}/images/btn_prev.gif" /></a>
						{else}
							<img src="{$skinDir}/images/btn_prev_off.gif" />
						{/if}
					</td>
					<td>
						<div class="pagenav">
						{if $prevpage != false}
							<a href="{$link.page}{$prevpage}">이전{$pagenum}페이지</a>
						{else}
							<span>이전{$pagenum}페이지</span>
						{/if}
						{foreach name=page from=$page item=page}
							{if $page == $p}
							<strong>{$page}</strong>
							{else}
							<a href="{$link.page}{$page}">{$page}</a>
							{/if}
						{/foreach}
						{if $nextpage != false}
							<a href="{$link.page}{$nextpage}">다음{$pagenum}페이지</a>
						{else}
							<span>다음{$pagenum}페이지</span>
						{/if}
						</div>
					</td>
					<td class="button right">
						{if $nextlist != false}
							<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
						{else}
							<img src="{$skinDir}/images/btn_next_off.gif" />
						{/if}
					</td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
	</table>
</div>