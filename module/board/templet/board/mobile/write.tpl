{if $smarty.env.isMobile == false}
	이 스킨은 모바일버전에서만 동작하도록 설계되었습니다.
{else}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">{if $mode == 'modify'}게시물 수정하기{else}게시물 등록하기{/if}</h3>
		</div>

		<div class="panel-body">
			{$formStart}
			
			{if $member.idx == 0}
			<input type="text" name="name" class="form-control input-sm" placeholder="작성자" value="{$post.name}">
			<div class="height5"></div>
			<input type="password" name="password" class="form-control input-sm" placeholder="패스워드">
			<div class="height5"></div>
			<input type="text" name="email" class="form-control input-sm" placeholder="이메일" value="{$post.email}">
			<div class="height5"></div>
			<input type="text" name="homepage" class="form-control input-sm" placeholder="홈페이지" value="{$post.homepage}">
			<hr />
			{/if}
			
			<input type="text" name="title" class="form-control input-sm" placeholder="제목" value="{$post.title}">
			<div class="height5"></div>
			
			{if $setup.use_category != 'FALSE'}
			<input type="hidden" name="category">
			<div class="btn-group">
				<div class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
					<span class="categoryName">{if $categoryName}{$categoryName}{else}카테고리{/if}</span> <span class="caret"></span>
				</div>
				<ul class="dropdown-menu" role="menu">
					{if $setup.use_category == 'OPTION'}<li><a href="javascript:WriteSelectCategory('',''); $('.categoryName').text('분류없음');">분류없음</a></li>{/if}
					{foreach from=$categoryList item=categoryList}
					<li><a href="javascript:WriteSelectCategory('','{$categoryList.idx}'); $('.categoryName').text('{$categoryList.category}');">{$categoryList.category}</a></li>
					{/foreach}
				</ul>
			</div>
			<div class="height5"></div>
			{/if}
			
			<textarea name="content" id="content" style="width:100%; height:200px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
			<div class="height5"></div>
			{mBoard->PrintWysiwyg id="content"}
			{mBoard->PrintUploader type="post" form=$formName id="uploader" skin="mobile" wysiwyg="content"}
			<div class="height5"></div>
			
			{if $antispam}
			<div class="alert alert-warning">
				스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.
				<div class="height5"></div>
				{$antispam}
				<div class="height5"></div>
				<input type="text" name="antispam" class="form-control input-sm" />
			</div>
			{/if}
			
			<input type="submit" class="btn btn-sm btn-primary btn-block" value="{if $mode == 'modify'}게시물 수정하기{else}게시물 등록하기{/if}" />
			{$formEnd}
		</div>
	</div>
	
	<div class="row">
		<div class="col-xs-5">
			<a href="{$link.list}" class="btn btn-default btn-sm">목록</a>
		</div>
		<div class="col-xs-7 right">
		</div>
	</div>
{/if}