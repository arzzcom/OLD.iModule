<?php $mSMS = new ModuleSMS(); ?>
<script type="text/javascript">
var SMSInterval = null;

var CheckSMSStart = function() {
	SMSInterval = setInterval("CheckSMSLength()",100);
}

var CheckSMSLength = function() {
	var isOver = false;
	var str = Ext.getCmp("SMSForm").getForm().findField("content").getValue();
	var length = 0;
	for (var i=0, loop=str.length;i<loop;i++) {
		if (escape(str.charAt(i)).length > 4) {
			length+= 2;
		} else {
			length++;
		}
	}

	document.getElementById("SMSLength").innerHTML = length;
	document.getElementById("SMSCount").innerHTML = Math.ceil(length/80);
}

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
			fields:[{name:"idx",type:"int"},"user_id","name","nickname","phone","cellphone"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"member",get:"phone",keyword:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"SMS발송",
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
												header:"연락처",
												dataIndex:"phone",
												sortable:false,
												width:120,
												renderer:function(value) {
													var data = value.split("||");

													if (data.length == 2) {
														return '<div style="font-family:tahoma;">'+data[0]+' <span style="color:#C6C6C6;">['+data[1]+']</span></div>';
													} else {
														return '<div style="font-family:tahoma;">'+value+'</div>';
													}
												}
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
						id:"SMSForm",
						title:"발신내용",
						region:"center",
						margins:"5 5 5 0",
						labelWidth:100,
						labelAlign:"right",
						items:[
							new Ext.form.FieldSet({
								title:"발신기록 및 잔여건수",
								autoWidth:true,
								autoHeight:true,
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.TextField({
										fieldLabel:"총 발송내역",
										disabled:true,
										style:"text-align:right;",
										value:"<?php echo number_format($mSMS->GetTotalCount()); ?>"
									}),
									new Ext.form.TextField({
										fieldLabel:"30일간 발송내역",
										disabled:true,
										style:"text-align:right;",
										value:"<?php echo number_format($mSMS->GetTotalCount(30)); ?>"
									}),
									new Ext.form.TextField({
										fieldLabel:"잔여건수",
										disabled:true,
										style:"text-align:right;",
										value:"<?php echo number_format($mSMS->GetRemainCount()); ?>"
									})
								]
							}),
							new Ext.form.FieldSet({
								title:"회신정보",
								autoWidth:true,
								autoHeight:true,
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.Hidden({
										name:"to"
									}),
									new Ext.form.TextField({
										fieldLabel:"회신받을번호",
										name:"from",
										allowBlank:false,
										value:"<?php echo $mSMS->GetConfig('defaultnumber'); ?>"
									})
								]
							}),
							new Ext.form.FieldSet({
								title:"발신내용",
								autoWidth:true,
								autoHeight:true,
								style:"margin:10px; background:#FFFFFF;",
								items:[
									new Ext.form.CompositeField({
										hideLabel:true,
										width:300,
										items:[
											new Ext.form.TextArea({
												name:"content",
												value:"<?php echo $mSMS->GetConfig('headmsg'); ?>",
												width:130,
												height:100,
												allowBlank:false,
												preventScrollbars:true,
												style:"overflow-y:scroll;",
												listeners:{
													focus:{fn:function() {
														CheckSMSStart();
													}},
													blur:{fn:function() {
														clearTimeout(SMSInterval);
													}}
												}
											}),
											new Ext.Panel({
												border:false,
												html:'<div style="margin-top:40px; padding-left:8px;">글자수:<span id="SMSLength" class="red bold"><?php echo $mSMS->GetCheckLength($mSMS->GetConfig('headmsg')); ?></span>/80<br />발송건수:<span id="SMSCount" class="red bold">1</span>건</div>',
												buttons:[
													new Ext.Button({
														text:"전송하기",
														handler:function() {
															var toList = new Array();
															if (Ext.getCmp("SelectAll").pressed == false) {
																var memberList = Ext.getCmp("MemberList").selModel.getSelections();
																for (var i=0, loop=memberList.length;i<loop;i++) {
																	toList.push(memberList[i].get("cellphone"));
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
															if (!Ext.getCmp("SMSForm").getForm().findField("content").getValue()) {
																Ext.getCmp("SMSForm").getForm().findField("to").markInvalid("값을 입력해주세요.");
																isError = true;
															}

															if (!Ext.getCmp("SMSForm").getForm().findField("from").getValue()) {
																Ext.getCmp("SMSForm").getForm().findField("from").markInvalid("값을 입력해주세요.");
																isError = true;
															}

															if (isError == false) {
																Ext.getCmp("SMSForm").getForm().findField("to").setValue(toList.join("\n"));

																if (toList[0] == "ALL") {
																	var total = MemberStore.getTotalCount()+toList.length-1;
																} else {
																	var total = toList.length;
																}

																Ext.Msg.show({title:"안내",msg:total+"명에게 SMS를 전송하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
																	if (button == "ok") {
																		ShowProgress(0,total);
																		Ext.getCmp("SMSForm").getForm().getEl().dom.action = "<?php echo $_ENV['dir']; ?>/module/sms/exec/Admin.do.php?action=send";
																		Ext.getCmp("SMSForm").getForm().getEl().dom.target = "execFrame";
																		Ext.getCmp("SMSForm").getForm().getEl().dom.method = "post";
																		Ext.getCmp("SMSForm").getForm().getEl().dom.submit();
																	}
																}});
															}
														}
													})
												]
											})
										]
									})
								]
							})
						]
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