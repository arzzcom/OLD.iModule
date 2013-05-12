<div id="sForm">
	<div style="padding-top:64px;"><img src="{$skinDir}/images/top.png"></div>
	<div style="position:relative; margin:0 auto;"><img src="{$skinDir}/images/top_2.png"></div>

	<div style="float:left; vertical-align:bottom; padding-top:20px; "><img src="{$skinDir}/images/top_id.png" style="padding:0px 255px 10px 7px;"><img src="{$skinDir}/images/top_pw.png" style="padding:0px 0px 10px 0px;"></div>

	<div style="width:430px; height:200px; border:1px solid #dedede; background:#f9f9f9; float:left; margin:0px 20px 0px 8px;">
		<div style="vertical-align:top; padding:50px 0px 0px 30px;"><img src="{$skinDir}/images/name.png" style="padding-right:52px;"> <input type="text" name="name" style="width:116px; height:19px; border:1px sollid #ababab"></div>
		<div style="vertical-align:top; padding:10px 0px 0px 30px;"><img src="{$skinDir}/images/email.png" style="padding-right:40px;"> <input type="text" name="email" style="width:216px; height:19px; border:1px solid #ababab"></div>
		<div id="FindUserIDInfo" style="padding:19px 0px 0px 70px;"><img src="{$skinDir}/images/txt.png"></div>

		<div style="text-align:center; padding-top:70px; margin-left:00px;"><input id="FindPasswordButton" type="image" src="{$skinDir}/images/submit.png" onclick="SetFindPassword()"></div>
	</div>

	<div style="width:430px; height:200px; border:1px solid #dedede; background:#f9f9f9; float:left;">
		<div style="vertical-align:top; padding:35px 0px 0px 30px;"><img src="{$skinDir}/images/id.png" style="padding-right:52px;"> <input type="text" name="name" style="width:116px; height:19px; border:1px sollid #ababab"></div>
		<div style="vertical-align:top; padding:10px 0px 0px 30px;"><img src="{$skinDir}/images/name.png" style="padding-right:63px;"> <input type="text" name="email" style="width:116px; height:19px; border:1px solid #ababab"></div>
		<div style="vertical-align:top; padding:10px 0px 0px 30px;"><img src="{$skinDir}/images/email.png" style="padding-right:51px;"> <input type="text" name="email" style="width:216px; height:19px; border:1px solid #ababab"></div>
		<div id="FindPasswordInfo" style="padding:19px 0px 0px 70px;"><img src="{$skinDir}/images/txt2.png"></div>

		<div style="text-align:center; padding-top:55px; margin-left:00px;">
		<img id="FindPasswordNextButton" src="{$skinDir}/images/submit.png" alt="확인" class="pointer" onclick="FindPassword()" />
		<img id="SendPasswordButton" src="{$skinDir}/images/btn_sendpassword.gif" alt="패스워드발급" class="pointer" onclick="SendFindPassword()" style="display:none; margin-left:3px;" />
		</div>
	</div>
</div>


<table border="0" style="padding-top:80px;"><tr><td><img src="{$skinDir}/images/footer_bar.png"></td></tr></table>