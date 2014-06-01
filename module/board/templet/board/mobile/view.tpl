{if $smarty.env.isMobile == false}
	이 스킨은 모바일버전에서만 동작하도록 설계되었습니다.
{else}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">{$data.title}</h3>
		</div>

		<div class="panel-body">
			<div class="media">
				<div class="pull-left thumbnail">
					<img class="media-object" src="{$data.author.photo}" style="width:45px; height:45px;">
				</div>
				<div class="media-body">
					<div class="height5"></div>
					<div style="height:20px; line-height:20px;">{$data.author.nickname}</div>
					<div style="height:20px; line-height:20px;">
						<span class="label label-sm label-primary">{if $data.category}{$data.category}{else}분류없음{/if}</span>
						<span class="label label-sm label-default">{$data.reg_date|date_format:"%Y-%m-%d %H:%M"}</span>
					</div>
				</div>
			</div>
			
			<hr />
			
			{$data.content}
			
			{if $file}
			<div class="height10"></div>
			<div class="list-group">
				{foreach name=file from=$file item=file}
					<a href="{$file.link}" class="list-group-item" target="downloadFrame">{$file.filename} <span class="badge">{$file.filesize}</span></a>
				{/foreach}
				
			</div>
			{/if}
			
			<div class="addthis_toolbox addthis_default_style addthis_32x32_style pull-right">
				<a class="addthis_button_facebook"></a>
				<a class="addthis_button_twitter"></a>
				<a class="addthis_button_email"></a>
				<a class="addthis_button_print"></a>
				<a class="addthis_button_compact"></a>
				<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e733ebf2235bbcc"></script>
			
			<div style="clear:both;" class="height20"></div>

			<div class="height10"></div>
			{if $data.last_modify.hit > 0}<div class="tahoma f10 right">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
			
			<hr />
			
			{$ment}
		</div>
	</div>

	<div class="row">
		<div class="col-xs-5">
			<a href="{$link.list}" class="btn btn-default btn-sm">목록</a>
			<a class="btn btn-success btn-sm" href="{$link.post}">글쓰기</a>
		</div>
		<div class="col-xs-7 right">
			<button class="btn btn-sm btn-warning" onclick="{$action.vote}">추천</button>
			<a href="{$link.modify}" class="btn btn-sm btn-info">수정</a>
			<a href="{$link.delete}" class="btn btn-sm btn-danger">삭제</a>
		</div>
	</div>
{/if}