<?php $mEmail = new ModuleEmail(); ?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript">
var gKey = "<?php echo md5($_SERVER['REMOTE_ADDR'].'.'.time()); ?>";

var SendCancel = function() {
	Ext.Msg.show({title:"안내",msg:"전송작업을 취소하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
		if (button == "yes") {
			execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=cancel&key="+gKey;
			Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		}
	}});

	return false;
};

var ShowProgress = function(count,total) {
	if (!Ext.getCmp("ProgressWindow")) {
		new Ext.Window({
			id:"ProgressWindow",
			width:500,
			title:"메일발송",
			modal:true,
			closable:true,
			resizable:false,
			draggable:false,
			bodyPadding:"5 5 5 5",
			items:[
				new Ext.ProgressBar({
					id:"ProgressBar",
					text:"메일발송 대기중..."
				})
			],
			listeners:{beforeclose:SendCancel}
		}).show();
	}

	if (count == total) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)",true);
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 완료되었습니다.<br />전송기록을 확인하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
			Ext.getCmp("ProgressWindow").close();
			if (button == "ok") {
				location.href = location.href.replace("category=send","category=log");
			}
		}});
	} else if (count > 0) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)",true);
	} else if (count == -1) {
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 취소되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() { Ext.getCmp("ProgressWindow").close(); }});
	}
}

function SendEmail() {
	Ext.getCmp("WriteTab").setActiveTab(0);
	var receiver = parseInt(Ext.getCmp("ReceiverList").title.replace('받는사람 (','').replace(')','').replace(',',''));
	
	if (receiver == 0) {
		Ext.Msg.show({title:"에러",msg:"받는사람을 1명이상 선택/입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
		return;
	}
	
	oEditors.getById["EmailFormWysiwyg-inputEl"].exec("UPDATE_IR_FIELD",[]);
	
	Ext.Msg.show({title:"확인",msg:"총 "+GetNumberFormat(receiver)+"명에게 메일을 발송하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
		if (button == "yes") {
			Ext.getCmp("EmailForm").getForm().submit({
				url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=send&do=save&key="+gKey,
				submitEmptyText:false,
				waitTitle:"잠시만 기다려주십시오.",
				waitMsg:"메일내용을 저장하고 있습니다.",
				success:function(form,action) {
					if (action.result.count > 0) {
						ShowProgress(0,action.result.count);
						execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=send&do=send&repto="+action.result.idx+"&is_smtp="+(Ext.getCmp("is_smtp").checked == true ? 'true' : 'false');
						Ext.Msg.hide();
					} else {
						Ext.Msg.show({title:"에러",msg:"메일을 발송할 인원이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
					}
				},
				failure:function(form,action) {
					if (Ext.getCmp("EmailForm").getForm().findField("body").getValue() == "<br>" || Ext.getCmp("EmailForm").getForm().findField("body").getValue() == "") {
						Ext.Msg.show({title:"에러",msg:"메일 본문을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						return;
					}
					Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				}
			});
		}
	}});
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	var MemberAll = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"member",keyword:"",group:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"ASC"}],
		autoLoad:true,
		pageSize:100,
		fields:[{name:"idx",type:"int"},"user_id","name","nickname","email"]
	});
	
	var ReceiverStore = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"receiver",keyword:"",key:gKey}
		},
		remoteSort:true,
		autoLoad:true,
		sorters:[{property:"email",direction:"ASC"}],
		pageSize:100,
		fields:["name","email"],
		listeners:{load:{fn:function(store) {
			if (store.getProxy().extraParams.keyword == "") {
				Ext.getCmp("ReceiverList").setTitle("받는사람 ("+GetNumberFormat(store.getTotalCount())+"명)");
			}
			Ext.getCmp("ReceiverKeyword").setValue(store.getProxy().extraParams.keyword);
		}}}
	});
	
	<?php
	$group = $mDB->DBfetchs($_ENV['table']['group'],'*','','sort,asc');
	for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
	?>
	var Member<?php echo $group[$i]['group']; ?> = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"member",get:"list",keyword:"",group:"<?php echo $group[$i]['group']; ?>"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"ASC"}],
		autoLoad:true,
		pageSize:100,
		fields:[{name:"idx",type:"int"},"user_id","name","nickname","email"]
	});
	<?php } ?>

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"메일쓰기/발송",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:{type:"hbox",align:"stretch"},
				border:false,
				items:[
					new Ext.Panel({
						title:"발송대상선택",
						width:400,
						layout:"fit",
						margin:"5 5 5 5",
						items:[
							new Ext.TabPanel({
								id:"ListTab",
								tabPosition:"bottom",
								activeTab:0,
								border:false,
								tbar:[
									new Ext.form.TextField({
										id:"Keyword",
										width:180,
										emptyText:"아이디, 이름/닉네임, 메일주소"
									}),
									new Ext.Button({
										text:"검색",
										icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_magnifier.png",
										handler:function() {
											Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
											Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
										}
									}),
									'-',
									new Ext.Button({
										text:"선택추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_tick.png",
										handler:function() {
											var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"받는사람에 추가할 회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												return false;
											}
											
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											
											Ext.Msg.show({title:"확인",msg:GetNumberFormat(checked.length)+"명을 받는사람에 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "yes") {
													Ext.Msg.wait("받는사람을 추가하고 있습니다.","잠시만 기다려주십시오.");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php",
														success:function(response) {
															var data = Ext.JSON.decode(response.responseText);
															if (data.success == true) {
																Ext.Msg.show({title:"안내",msg:"중복되는 메일주소를 제외하고 총 "+GetNumberFormat(data.count)+"명을 받는사람에 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																	ReceiverStore.loadPage(1);
																}});
															} else {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															}
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
														},
														params:{"action":"send","do":"receiver_add","mode":"select","idx":idxs.join(","),"key":gKey}
													});
												}
											}});
										}
									}),
									new Ext.Button({
										text:"전체추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox_on.png",
										handler:function(button) {
											var store = Ext.getCmp("ListTab").getActiveTab().getStore();
											Ext.Msg.show({title:"확인",msg:GetNumberFormat(store.getTotalCount())+"명을 받는사람에 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "yes") {
													Ext.Msg.wait("받는사람을 추가하고 있습니다.","잠시만 기다려주십시오.");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php",
														success:function(response) {
															var data = Ext.JSON.decode(response.responseText);
															if (data.success == true) {
																Ext.Msg.show({title:"안내",msg:"중복되는 메일주소를 제외하고 총 "+GetNumberFormat(data.count)+"명을 받는사람에 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																	ReceiverStore.loadPage(1);
																}});
															} else {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															}
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
														},
														params:{"action":"send","do":"receiver_add","mode":"store","keyword":store.getProxy().extraParams.keyword,"group":store.getProxy().extraParams.group,"key":gKey}
													});
												}
											}});
										}
									})
								],
								items:[
									new Ext.grid.GridPanel({
										id:"ListAll",
										title:"전체그룹",
										border:false,
										columns:[
											{
												header:"아이디",
												dataIndex:"user_id",
												width:80,
												renderer:function(value,p,record) {
													return '<span style="font-family:tahoma;">'+value+'</span>';
												}
											},{
												header:"이름/닉네임",
												dataIndex:"name",
												width:120,
												renderer:function(value,p,record) {
													return value+"("+record.data.nickname+")";
												}
											},{
												header:"이메일",
												dataIndex:"email",
												flex:1,
												renderer:function(value) {
													return '<div style="font-family:tahoma;">'+value+'</div>';
												}
											}
										],
										columnLines:true,
										selModel:new Ext.selection.CheckboxModel(),
										store:MemberAll,
										bbar:new Ext.PagingToolbar({
											store:MemberAll,
											displayInfo:true
										})
									})
									<?php
									$group = $mDB->DBfetchs($_ENV['table']['group'],'*','','sort,asc');
									for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
									?>,
									new Ext.grid.GridPanel({
										id:"List-<?php echo $group[$i]['group']; ?>",
										title:"<?php echo $group[$i]['title']; ?>",
										border:false,
										columns:[
											{
												header:"아이디",
												dataIndex:"user_id",
												width:80,
												renderer:function(value,p,record) {
													return '<span style="font-family:tahoma;">'+value+'</span>';
												}
											},{
												header:"이름/닉네임",
												dataIndex:"name",
												width:120,
												renderer:function(value,p,record) {
													return value+"("+record.data.nickname+")";
												}
											},{
												header:"이메일",
												dataIndex:"email",
												flex:1,
												renderer:function(value) {
													return '<div style="font-family:tahoma;">'+value+'</div>';
												}
											}
										],
										columnLines:true,
										selModel:new Ext.selection.CheckboxModel(),
										store:Member<?php echo $group[$i]['group']; ?>,
										bbar:new Ext.PagingToolbar({
											store:Member<?php echo $group[$i]['group']; ?>,
											displayInfo:true
										})
									})
									<?php } ?>
								]
							})
						]
					}),
					new Ext.Panel({
						title:"메일작성",
						flex:1,
						layout:"fit",
						margin:"5 5 5 0",
						items:[
							new Ext.TabPanel({
								id:"WriteTab",
								tabPosition:"bottom",
								activeTab:0,
								border:false,
								items:[
									new Ext.form.FormPanel({
										id:"EmailForm",
										title:"메일작성",
										flex:1,
										border:false,
										autoScroll:true,
										fieldDefaults:{labelWidth:60,labelAlign:"right",anchor:"100%",allowBlank:false},
										bodyPadding:"10 10 5 10",
										tbar:[
											new Ext.Button({
												text:"메일발송",
												icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_email_edit.png",
												handler:function() {
													SendEmail();
												}
											})
										],
										items:[
											new Ext.form.FieldSet({
												title:"보내는 사람",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"이름",
														layout:"hbox",
														items:[
															new Ext.form.TextField({
																name:"name",
																width:100,
																value:"<?php echo $mEmail->GetConfig('name'); ?>"
															}),
															new Ext.form.TextField({
																fieldLabel:"메일주소",
																name:"email",
																flex:1,
																value:"<?php echo $mEmail->GetConfig('email'); ?>"
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"SMTP 사용",
												items:[
													new Ext.form.Checkbox({
														id:"is_smtp",
														hideLabel:true,
														name:"is_smtp",
														allowBlank:false,
														boxLabel:"모듈설정의 SMTP서버를 통해 메일을 발송합니다. 체크해제시 로컬서버를 통해 발송됩니다.",
														checked:true
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"메일작성",
												items:[
													new Ext.form.TextField({
														name:"subject",
														allowBlank:false,
														emptyText:"메일 제목"
													}),
													new Ext.form.TextArea({
														id:"EmailFormWysiwyg",
														name:"body",
														height:400,
														listeners:{render:{fn:function() {
															nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"EmailFormWysiwyg-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
														}}}
													}),
													new Ext.Panel({
														id:"EmailFormUploaderPanel",
														border:false,
														padding:"5 0 5 0",
														html:'<div id="EmailFormUploader-area"></div><div id="EmailFormUploader-image"></div><div id="EmailFormUploader-file"></div>',
														listeners:{render:{fn:function() {
															new AzUploader({
																id:"EmailFormUploader",
																autoRender:false,
																flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
																uploadURL:"<?php echo $_ENV['dir']; ?>/module/email/exec/FileUpload.do.php?type=HTML",
																buttonURL:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_file_button.gif",
																width:75,
																height:20,
																moduleDir:"<?php echo $_ENV['dir']; ?>/module/email",
																wysiwygElement:"EmailFormWysiwyg-inputEl",
																panelElement:"EmailFormUploaderPanel",
																formElement:"EmailForm",
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
															}).render("EmailFormUploader-area");
															Ext.getCmp("EmailFormUploaderPanel").doLayout();
														}}}
													})
												]
											})
										]
									}),
									new Ext.grid.GridPanel({
										id:"ReceiverList",
										title:"받는사람",
										border:false,
										tbar:[
											new Ext.form.TextField({
												id:"ReceiverKeyword",
												width:160,
												emptyText:"받는사람, 이메일주소"
											}),
											new Ext.Button({
												text:"검색",
												icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_magnifier.png",
												handler:function() {
													ReceiverStore.getProxy().setExtraParam("keyword",Ext.getCmp("ReceiverKeyword").getValue());
													ReceiverStore.loadPage(1);
												}
											}),
											'-',
											new Ext.Button({
												text:"직접추가",
												icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_email_add.png",
												handler:function() {
													new Ext.Window({
														id:"ReceiverAddWindow",
														title:"직접추가",
														width:400,
														modal:true,
														items:[
															new Ext.form.FormPanel({
																id:"ReceiverAddForm",
																border:false,
																fieldDefaults:{labelWidth:80,labelAlign:"right",anchor:"100%",allowBlank:false},
																bodyPadding:"5 5 0 5",
																items:[
																	new Ext.form.TextField({
																		fieldLabel:"받는사람",
																		name:"name"
																	}),
																	new Ext.form.TextField({
																		fieldLabel:"이메일주소",
																		name:"email"
																	})
																]
															})
														],
														buttons:[
															new Ext.Button({
																text:"추가",
																handler:function() {
																	Ext.getCmp("ReceiverAddForm").getForm().submit({
																		url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=send&do=receiver_add&mode=direct&key="+gKey,
																		submitEmptyText:false,
																		waitTitle:"잠시만 기다려주십시오.",
																		waitMsg:"받는사람을 추가하고 있습니다.",
																		success:function(form,action) {
																			Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
																				if (button == "yes") {
																					Ext.getCmp("ReceiverAddForm").getForm().reset();
																				} else {
																					Ext.getCmp("ReceiverAddWindow").close();
																				}
																				ReceiverStore.getProxy().setExtraParam("keyword","");
																				ReceiverStore.loadPage(1);
																			}});
																		},
																		failure:function(form,action) {
																			Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																		}
																	});
																}
															}),
															new Ext.Button({
																text:"취소",
																handler:function() {
																	Ext.getCmp("ReceiverAddWindow").close();
																}
															})
														]
													}).show();
												}
											}),
											new Ext.Button({
												text:"선택제외",
												icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_email_delete.png",
												handler:function() {
													var checked = Ext.getCmp("ReceiverList").getSelectionModel().getSelection();
													if (checked.length == 0) {
														Ext.Msg.show({title:"에러",msg:"받는사람에서 제외할 인원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														return false;
													}
													
													var emails = new Array();
													for (var i=0, loop=checked.length;i<loop;i++) {
														emails.push(checked[i].get("email"));
													}
													
													Ext.Msg.show({title:"확인",msg:"선택된 대상을 받는사람에서 제외하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
														if (button == "yes") {
															Ext.Msg.wait("받는사람을 제외하고 있습니다.","잠시만 기다려주십시오.");
															Ext.Ajax.request({
																url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php",
																success:function(response) {
																	var data = Ext.JSON.decode(response.responseText);
																	if (data.success == true) {
																		Ext.Msg.show({title:"안내",msg:"성공적으로 제외하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																			ReceiverStore.getProxy().setExtraParam("keyword","");
																			ReceiverStore.loadPage(1);
																		}});
																	} else {
																		Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
																	}
																},
																failure:function() {
																	Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
																},
																params:{"action":"send","do":"receiver_remove","email":emails.join(","),"key":gKey}
															});
														}
													}});
												}
											}),
											'->',
											new Ext.Button({
												text:"메일발송",
												icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_email_edit.png",
												handler:function() {
													SendEmail();
												}
											})
										],
										columns:[
											{
												header:"받는사람",
												dataIndex:"name",
												width:120
											},{
												header:"이메일",
												dataIndex:"email",
												flex:1,
												renderer:function(value) {
													return '<div style="font-family:tahoma;">'+value+'</div>';
												}
											}
										],
										columnLines:true,
										selModel:new Ext.selection.CheckboxModel(),
										store:ReceiverStore,
										bbar:new Ext.PagingToolbar({
											store:ReceiverStore,
											displayInfo:true
										})
									})
								]
							})
						]
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>
<iframe name="execFrame" style="display:none;"></iframe>