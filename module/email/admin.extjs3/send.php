<?php $mEmail = new ModuleEmail(); ?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript">
var SendCancel = function() {
	Ext.Msg.show({title:"안내",msg:"전송작업을 취소하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
		if (button == "ok") {
			execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/sms/exec/Admin.do.php?action=cancel";
			Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		}
	}});

	return false;
};

var ShowProgress = function(count,total) {
	if (!Ext.getCmp("ProgressWindow")) {
		new Ext.Window({
			id:"ProgressWindow",
			title:"전송진행률",
			width:500,
			modal:true,
			items:[
				new Ext.ProgressBar({
					border:false,
					text:"전송대기중",
					id:"ProgressBar",
					cls:"left-align",
					border:false
				})
			],
			listeners:{beforeclose:SendCancel}
		}).show();
	}

	if (count == total) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)");
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 완료되었습니다.<br />전송기록을 확인하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
			Ext.getCmp("ProgressWindow").close();
			if (button == "ok") {
				location.href = location.href.replace("category=send","category=log");
			}
		}});
	} else if (count > 0) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)");
	} else if (count == -1) {
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 취소되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() { Ext.getCmp("ProgressWindow").close(); }});
	}
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	var MemberStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"user_id","name","nickname","email"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"member",get:"email",keyword:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"메일발송",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:"border",
				border:false,
				items:[
					new Ext.Panel({
						title:"발신대상",
						region:"west",
						width:350,
						layout:"fit",
						margins:"5 5 5 5",
						items:[
							new Ext.TabPanel({
								tabPosition:"bottom",
								activeTab:0,
								border:false,
								items:[
									new Ext.grid.GridPanel({
										id:"MemberList",
										title:"회원목록",
										tbar:[
											new Ext.Button({
												id:"SelectAll",
												enableToggle:true,
												text:"전체회원선택",
												icon:"<?php echo $_ENV['dir']; ?>/module/sms/images/admin/icon_checkbox.png",
												handler:function(button) {
													if (button.pressed == true) {
														button.setIcon("<?php echo $_ENV['dir']; ?>/module/sms/images/admin/icon_checkbox_on.png");
														Ext.getCmp("Keyword").setValue("");
														MemberStore.baseParams.keyword = "";
														MemberStore.load({params:{start:0,limit:500}});
													} else {
														button.setIcon("<?php echo $_ENV['dir']; ?>/module/sms/images/admin/icon_checkbox.png");
													}
												}
											}),
											'|',
											new Ext.form.TextField({
												id:"Keyword",
												emptyText:"아이디, 이름, 닉네임",
												width:150
											}),
											' ',
											new Ext.Button({
												text:"검색",
												icon:"<?php echo $_ENV['dir']; ?>/module/sms/images/admin/icon_magnifier.png",
												handler:function() {
													MemberStore.baseParams.keyword = Ext.getCmp("Keyword").getValue();
													MemberStore.load({params:{start:0,limit:500}});
												}
											})
										],
										cm:new Ext.grid.ColumnModel([
											new Ext.grid.CheckboxSelectionModel(),
											{
												header:"아이디",
												dataIndex:"user_id",
												sortable:true,
												width:75
											},{
												header:"이름(닉네임)",
												dataIndex:"name",
												sortable:true,
												width:110,
												renderer:function(value,p,record) {
													return value+'('+record.data.nickname+')';
												}
											},{
												header:"이메일",
												dataIndex:"email",
												sortable:false,
												width:120
											}
										]),
										sm:new Ext.grid.CheckboxSelectionModel(),
										store:MemberStore,
										bbar:new Ext.PagingToolbar({
											pageSize:500,
											store:MemberStore,
											displayInfo:true,
											displayMsg:'{0} - {1} of {2}',
											emptyMsg:"데이터없음"
										})
									}),
									new Ext.form.FormPanel({
										title:"직접입력",
										style:"padding:5px;",
										defaults:{hideLabel:true},
										items:[
											new Ext.form.TextArea({
												id:"SendList",
												name:"list",
												emptyText:"엔터로 구분하여, 번호를 입력하여 주십시오."
											})
										],
										listeners:{resize:{fn:function(object) {
											Ext.getCmp("SendList").setSize(object.getWidth()-10,object.getHeight()-10);
										}}}
									})
								]
							})
						]
					}),
					new Ext.form.FormPanel({
						id:"EmailForm",
						title:"발신내용",
						region:"center",
						margins:"5 5 5 0",
						labelWidth:100,
						autoScroll:true,
						labelAlign:"right",
						items:[
							new Ext.form.FieldSet({
								title:"보내는 사람",
								autoWidth:true,
								autoHeight:true,
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.TextField({
										name:"fromName",
										width:100,
										allowBlank:false,
										fieldLabel:"보내는 사람",
										value:"<?php echo $mEmail->GetConfig('name'); ?>"
									}),
									new Ext.form.TextField({
										name:"fromEmail",
										width:200,
										allowBlank:false,
										fieldLabel:"보내는 메일주소",
										value:"<?php echo $mEmail->GetConfig('email'); ?>"
									})
								]
							}),
							new Ext.form.FieldSet({
								title:"SMTP 사용",
								autoWidth:true,
								autoHeight:true,
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.Checkbox({
										hideLabel:true,
										name:"is_smtp",
										allowBlank:false,
										boxLabel:"모듈설정의 SMTP서버를 통해 메일을 발송합니다. 체크해제시 로컬서버를 통해 발송됩니다.",
										checked:true
									})
								]
							}),
							new Ext.form.FieldSet({
								title:"메일내용",
								autoWidth:true,
								autoHeight:true,
								buttonAlign:"center",
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.Hidden({
										name:"to"
									}),
									new Ext.form.TextField({
										name:"subject",
										fieldLabel:"제목",
										allowBlank:false
									}),
									new Ext.form.TextArea({
										id:"wysiwyg-content",
										fieldLabel:"내용",
										name:"content",
										allowBlank:false,
										listeners:{render:{fn:function() {
											nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"wysiwyg-content",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?mode=simple",fCreator:"createSEditorInIFrame"});
										}}}
									}),
									new Ext.Panel({
										id:"FilePanel",
										border:false,
										style:"padding:0px 0px 5px 105px;",
										html:'<div id="uploader-content-area"></div><div id="uploader-content-image"></div>',
										listeners:{afterrender:{fn:function() {
											new AzUploader({
												id:"uploader-content",
												autoRender:false,
												flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
												uploadURL:"<?php echo $_ENV['dir']; ?>/module/email/exec/FileUpload.do.php?type=HTML&wysiwyg=wysiwyg-content",
												buttonURL:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_file_button.gif",
												width:75,
												height:20,
												moduleDir:"<?php echo $_ENV['dir']; ?>/module/email",
												wysiwygElement:"wysiwyg-content",
												formElement:Ext.getCmp("EmailForm").getForm().el.dom,
												allowType:"gif,jpg,jpeg,png",
												maxFileSize:0,
												maxTotalSize:100,
												listeners:{
													beforeLoad:AzUploaderBeforeLoad,
													onSelect:AzUploaderOnSelect,
													onProgress:AzUploaderOnProgress,
													onComplete:AzUploaderOnComplete,
													onLoad:AzUploaderOnLoad,
													onUpload:AzUploaderOnUpload,
													onError:AzUploaderOnError
												}
											}).render("uploader-content-area");
										}}}
									})
								],
								buttons:[
									new Ext.Button({
										text:"전송하기",
										handler:function() {
											oEditors.getById["wysiwyg-content"].exec("UPDATE_IR_FIELD",[]);
											var toList = new Array();
											if (Ext.getCmp("SelectAll").pressed == false) {
												var memberList = Ext.getCmp("MemberList").selModel.getSelections();
												for (var i=0, loop=memberList.length;i<loop;i++) {
													toList.push(memberList[i].get("email"));
												}
											} else {
												toList.push("ALL");
											}
											if (Ext.getCmp("SendList") && Ext.getCmp("SendList").getValue()) {
												var temp = Ext.getCmp("SendList").getValue().split("\n");
												for (var i=0, loop=temp.length;i<loop;i++) {
													if (temp[i]) toList.push(temp[i]);
												}
											}

											if (toList.length == 0) {
												Ext.Msg.show({title:"에러",msg:"전송할 대상자를 선택하거나 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}

											var isError = false;
											if (!Ext.getCmp("EmailForm").getForm().findField("fromName").getValue()) {
												Ext.getCmp("EmailForm").getForm().findField("fromName").markInvalid("값을 입력해주세요.");
												isError = true;
											}

											if (!Ext.getCmp("EmailForm").getForm().findField("fromEmail").getValue()) {
												Ext.getCmp("EmailForm").getForm().findField("fromEmail").markInvalid("값을 입력해주세요.");
												isError = true;
											}

											if (!Ext.getCmp("EmailForm").getForm().findField("subject").getValue()) {
												Ext.getCmp("EmailForm").getForm().findField("subject").markInvalid("값을 입력해주세요.");
												isError = true;
											}

											if (!Ext.getCmp("EmailForm").getForm().findField("content").getValue()) {
												Ext.getCmp("EmailForm").getForm().findField("to").markInvalid("값을 입력해주세요.");
												isError = true;
											}


											if (isError == false) {
												Ext.getCmp("EmailForm").getForm().findField("to").setValue(toList.join("\n"));

												if (toList[0] == "ALL") {
													var total = MemberStore.getTotalCount()+toList.length-1;
												} else {
													var total = toList.length;
												}

												Ext.Msg.show({title:"안내",msg:total+"명에게 메일을 전송하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "ok") {
														ShowProgress(0,total);
														Ext.getCmp("EmailForm").getForm().getEl().dom.action = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=send";
														Ext.getCmp("EmailForm").getForm().getEl().dom.target = "execFrame";
														Ext.getCmp("EmailForm").getForm().getEl().dom.method = "post";
														Ext.getCmp("EmailForm").getForm().getEl().dom.submit();
														//Ext.getCmp("EmailForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/sms/exec/Admin.do.php?action=send",waitMsg:"전송중입니다."});
													}
												}});
											}
										}
									})
								]
							})
						],
						listeners:{resize:{fn:function(object) {
							Ext.getCmp("EmailForm").getForm().findField("subject").setWidth(object.getWidth()-200);
							Ext.getCmp("EmailForm").getForm().findField("content").setSize(object.getWidth()-200,300);
							Ext.getCmp("FilePanel").setWidth(object.getWidth()-200);
						}}}
					})
				]
			})
		]
	});

	MemberStore.load({params:{start:0,limit:500}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>
<iframe name="execFrame" style="display:none;"></iframe>