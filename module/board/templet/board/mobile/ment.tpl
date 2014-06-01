{if $select}
<div class="alert alert-success">
	<b>이 답변은 작성자에 의하여 채택된 답변입니다!</b>
	
	<div class="height10"></div>
	<div class="media">
		<div class="pull-left thumbnail">
			<img class="media-object" src="{$select.author.photo}" style="width:45px; height:45px;">
		</div>
		<div class="media-body">
			<div class="height2"></div>
			<div style="height:25px; line-height:20px;">{$select.author.nickname}</div>

			{$select.content}
			
			{if $select.file}
			<div class="height10"></div>
			<div class="list-group">
				{foreach name=file from=$select.file item=file}
					<a href="{$file.link}" class="list-group-item" target="downloadFrame">{$file.filename} <span class="badge">{$file.filesize}</span></a>
				{/foreach}
				
			</div>
			{/if}
			
			{if $select.last_modify.hit > 0}<div class="tahoma f10 gray right">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
			
			<div class="right" style="line-height:20px;">
				<span class="label label-sm label-default">{$select.reg_date|date_format:"%Y-%m-%d %H:%M"}</span>
			</div>
		</div>
	</div>
</div>
{/if}

{$mentStart}
{foreach name=data from=$data item=data}
	{$data.replyStart}
	
	<div class="media">
		<div class="pull-left thumbnail">
			<img class="media-object" src="{$data.author.photo}" style="width:45px; height:45px;">
		</div>
		<div class="media-body">
			<div class="height2"></div>
			<div style="height:25px; line-height:20px;">{$data.author.nickname}</div>

			{$data.content}
			
			{if $data.file}
			<div class="height10"></div>
			<div class="list-group">
				{foreach name=file from=$data.file item=file}
					<a href="{$file.link}" class="list-group-item" target="downloadFrame">{$file.filename} <span class="badge">{$file.filesize}</span></a>
				{/foreach}
				
			</div>
			{/if}
		</div>
		
		{if $data.select == true}
		<div class="right">
			<div class="height10"></div>
			<span class="btn btn-sm btn-warning" onclick="{$data.action.select}">이곳을 눌러 답변을 채택할 수 있습니다.</span>
			<div class="height10"></div>
		</div>
		{/if}
		
		{if $data.last_modify.hit > 0}<div class="tahoma f10 gray right">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
		
		<div class="right" style="line-height:20px;">
			<span class="label label-sm label-default">{$data.reg_date|date_format:"%y.%m.%d %H:%M"}</span>
			<span class="label label-success label-sm pointer" onclick="{$data.action.reply}">답변</span>
			<a href="{$data.link.modify}" style="text-decoration:none;"><span class="label label-info label-sm pointer">수정</span></a>
			<a href="{$data.link.delete}" style="text-decoration:none;"><span class="label label-danger label-sm pointer">삭제</span></a>
		</div>
	</div>
	
	<hr />
	
	{$data.reply}
	{$data.replyEnd}
{/foreach}

{if $permission.ment == true}
	{$ment_write}
{/if}

{$mentEnd}