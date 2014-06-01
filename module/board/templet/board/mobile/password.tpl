{$formStart}
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">패스워드를 입력하여 주십시오.</h3>
	</div>

	<div class="panel-body">
		{$msg}
		<hr />
		<input type="password" name="password" class="form-control" placeholder="패스워드를 입력하여 주십시오." />
		<div class="height10"></div>
		<input type="submit" class="btn btn-primary btn-block" value="확인">
		<a href="{$link.back}" class="btn btn-default btn-block">뒤로가기</a>
	</div>
</div>
{$formEnd}