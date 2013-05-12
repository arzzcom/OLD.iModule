<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var fieldType = {user_id:"회원아이디",password:"패스워드",name:"이름",nickname:"닉네임",nickcon:"닉이미지",jumin:"주민등록번호",companyno:"사업자등록번호",birthday:"생년월일",gender:"성별",telephone:"전화번호",cellphone:"휴대전화번호",homepage:"홈페이지",address:"주소",email:"이메일",photo:"회원사진",voter:"추천인"}

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"필드수정",
			handler:function() {
				if (record.data.is_default == "TRUE") {
					new Ext.Window({
						id:"DefaultFieldModifyWindow",
						title:"기본필드수정",
						width:500,
						modal:true,
						resizable:false,
						layout:"fit",
						items:[
							new Ext.form.FormPanel({
								id:"DefaultFieldModifyForm",
								border:false,
								bodyPadding:"10 10 5 10",
								fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%"},
								items:[
									new Ext.form.FieldSet({
										title:"필드 기본설정",
										items:[
											new Ext.form.TextField({
												name:"title",
												fieldLabel:"필드제목",
												allowBlank:false,
												value:record.data.title
											}),
											new Ext.form.TextField({
												name:"msg",
												fieldLabel:"안내메세지",
												emptyMsg:"사용자에게 해당입력란에 대한 설명을 입력합니다.",
												value:record.data.msg
											}),
											new Ext.form.Checkbox({
												name:"allowblank",
												fieldLabel:"필수항목",
												boxLabel:"필수항목으로 설정합니다."
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"필드 부가설정",
										hidden:record.data.type != "cellphone",
										items:[
											new Ext.form.Checkbox({
												name:"realphone",
												fieldLabel:"번호인증",
												boxLabel:"SMS을 통해 인증을 받게 합니다.(SMS모듈필요)"
											}),
											new Ext.form.Checkbox({
												name:"provider",
												fieldLabel:"통신사",
												boxLabel:"휴대전화번호 통신사를 입력받도록 합니다."
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"필드 부가설정 (추천인/추천받는인 포인트설정)",
										hidden:record.data.type != "voter",
										items:[
											new Ext.form.FieldContainer({
												fieldLabel:"추천인",
												layout:"hbox",
												items:[
													new Ext.form.NumberField({
														name:"vote",
														value:0,
														width:80
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;포인트 (추천하는사람에게 줄 추가 포인트)"
													})
												]
											}),
											new Ext.form.FieldContainer({
												fieldLabel:"추천받는인",
												layout:"hbox",
												items:[
													new Ext.form.NumberField({
														name:"voter",
														value:0,
														width:80
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;포인트 (추천을 받는사람에게 줄 포인트)"
													})
												]
											})
										]
									})
								]
							})
						],
						buttons:[
							new Ext.Button({
								text:"확인",
								handler:function() {
									Ext.getCmp("DefaultFieldModifyForm").getForm().submit({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=modify_default&idx="+record.data.idx,
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"필드를 수정하고 있습니다.",
										success:function(form,action) {
											Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
												Ext.getCmp("ListTab").getActiveTab().getStore().reload();
												Ext.getCmp("DefaultFieldModifyWindow").close();
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
									Ext.getCmp("DefaultFieldModifyWindow").close();
								}
							})
						],
						listeners:{show:{fn:function() {
							Ext.getCmp("DefaultFieldModifyForm").getForm().load({
								url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=signin&get=info&idx="+record.data.idx,
								submitEmptyText:false,
								waitTitle:"잠시만 기다려주십시오.",
								waitMsg:"데이터를 로딩중입니다.",
								success:function(form,action) {
									if (action.result.data.type.search(/^(user_id|name|nickname|password|email|jumin|companyno)$/) != -1) {
										form.findField("allowblank").setValue(true);
										form.findField("allowblank").disable();
									}
								},
								failure:function(form,action) {
									Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							});
						}}}
					}).show();
				} else {
					new Ext.Window({
						id:"ExtraFieldModifyWindow",
						title:"사용자정의필드수정",
						width:500,
						modal:true,
						resizable:false,
						layout:"fit",
						items:[
							new Ext.form.FormPanel({
								id:"ExtraFieldModifyForm",
								border:false,
								bodyPadding:"10 10 5 10",
								fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%"},
								items:[
									new Ext.form.FieldSet({
										title:"필드 기본설정",
										items:[
											new Ext.form.TextField({
												name:"name",
												fieldLabel:"필드명",
												allowBlank:false,
												emptyText:"$member['extra']['필드명'] 으로 정보를 가져옵니다."
											}),
											new Ext.form.TextField({
												name:"title",
												fieldLabel:"필드제목",
												allowBlank:false
											}),
											new Ext.form.TextField({
												name:"msg",
												fieldLabel:"안내메세지",
												emptyMsg:"사용자에게 해당입력란에 대한 설명을 입력합니다."
											}),
											new Ext.form.Checkbox({
												name:"allowblank",
												fieldLabel:"필수항목",
												boxLabel:"필수항목으로 설정합니다."
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"필드 부가설정",
										hidden:record.data.type != "input",
										items:[
											new Ext.form.TextField({
												fieldLabel:"유효성체크",
												name:"valid",
												emptyText:"정규식으로 유효성체크를 할 수 있습니다. (예:/^[a-z0-9]$/)"
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"필드 부가설정",
										hidden:record.data.type != "textarea",
										items:[
											new Ext.form.FieldContainer({
												fieldLabel:"필드높이",
												layout:"hbox",
												items:[
													new Ext.form.NumberField({
														name:"height",
														value:100,
														width:100,
														allowBlank:false
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;픽셀",
														flex:1
													})
												]
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"필드 부가설정",
										hidden:record.data.type.search(/^(checkbox|radio|select)$/) == -1,
										items:[
											new Ext.form.TextArea({
												name:"list",
												fieldLabel:"선택항목",
												height:120,
												emptyText:"항목을 한줄에 하나씩 입력하여 주십시오. (엔터:줄바꿈으로 항목구분)"
											})
										]
									})
								]
							})
						],
						buttons:[
							new Ext.Button({
								text:"확인",
								handler:function() {
									Ext.getCmp("ExtraFieldModifyForm").getForm().submit({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=modify_extra&idx="+record.data.idx,
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"필드를 수정하고 있습니다.",
										success:function(form,action) {
											Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
												Ext.getCmp("ListTab").getActiveTab().getStore().reload();
												Ext.getCmp("ExtraFieldModifyWindow").close();
											}});
										},
										failure:function(form,action) {
											if (action.result) {
												if (action.result.errors.message) {
													Ext.Msg.show({title:"에러",msg:action.result.errors.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													return;
												}
											}
											Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}
							}),
							new Ext.Button({
								text:"취소",
								handler:function() {
									Ext.getCmp("ExtraFieldModifyWindow").close();
								}
							})
						],
						listeners:{show:{fn:function() {
							Ext.getCmp("ExtraFieldModifyForm").getForm().load({
								url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=signin&get=info&idx="+record.data.idx,
								submitEmptyText:false,
								waitTitle:"잠시만 기다려주십시오.",
								waitMsg:"데이터를 로딩중입니다.",
								success:function(form,action) {

								},
								failure:function(form,action) {
									Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							});
						}}}
					}).show();
				}
			}
		});
		
		menu.add({
			text:"필드삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택된 필드를 정말 삭제하시겠습니까?<br />필드를 삭제하면 해당 회원정보도 모두 삭제됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 필드를 삭제중중입니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"signin","do":"delete","group":Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),"idx":record.data.idx}
						});
					}
				}});
			}
		});
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}
	
	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"회원목록",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_page_text.png",
						text:"회원약관",
						handler:function() {
							new Ext.Window({
								id:"AgreementWindow",
								title:Ext.getCmp("ListTab").getActiveTab().title+" 회원약관설정",
								width:700,
								height:500,
								modal:true,
								resizable:false,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"AgreementForm",
										bodyPadding:"10 10 0 10",
										border:false,
										items:[
											new Ext.form.FieldSet({
												title:"회원약관",
												items:[
													new Ext.form.TextArea({
														id:"Agreement",
														anchor:"100%",
														name:"value",
														height:290
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"동의메세지",
												items:[
													new Ext.form.TextField({
														name:"msg",
														anchor:"100%",
														allowBlank:false
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"수정하기",
										handler:function() {
											oEditors.getById["Agreement-inputEl"].exec("UPDATE_IR_FIELD",[]);
											Ext.getCmp("AgreementForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=agreement&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"회원약관을 수정하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("AgreementWindow").close();
													}});
												},
												failure:function(form,action) {
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"닫기",
										handler:function() {
											Ext.getCmp("AgreementWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
									Ext.getCmp("AgreementForm").getForm().load({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=signin&get=agreement&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"데이터를 로딩중입니다.",
										success:function(form,action) {
											nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"Agreement-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
										},
										failure:function(form,action) {
											Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}}}
							}).show();
						}
					}),
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_page_text.png",
						text:"개인정보보호정책",
						handler:function() {
							new Ext.Window({
								id:"PrivacyWindow",
								title:Ext.getCmp("ListTab").getActiveTab().title+" 개인정보보호정책설정",
								width:700,
								height:500,
								modal:true,
								resizable:false,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"PrivacyForm",
										bodyPadding:"10 10 5 10",
										border:false,
										items:[
											new Ext.form.FieldSet({
												title:"개인정보보호정책 사용여부",
												items:[
													new Ext.form.Checkbox({
														name:"disable",
														boxLabel:Ext.getCmp("ListTab").getActiveTab().title+"에서 개인정보보호정책을 사용하지 않습니다."
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"개인정보보호정책",
												items:[
													new Ext.form.TextField({
														id:"Privacy",
														anchor:"100%",
														name:"value",
														height:240
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"동의메세지",
												items:[
													new Ext.form.TextField({
														name:"msg",
														anchor:"100%",
														allowBlank:false
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"수정하기",
										handler:function() {
											oEditors.getById["Privacy-inputEl"].exec("UPDATE_IR_FIELD",[]);
											Ext.getCmp("PrivacyForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=privacy&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"개인정보보호정책을 수정하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("PrivacyWindow").close();
													}});
												},
												failure:function(form,action) {
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"닫기",
										handler:function() {
											Ext.getCmp("PrivacyWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
									Ext.getCmp("PrivacyForm").getForm().load({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=signin&get=privacy&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"데이터를 로딩중입니다.",
										success:function(form,action) {
											nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"Privacy-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
										},
										failure:function(form,action) {
											Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}}}
							}).show();
						}
					}),
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_page_text.png",
						text:"청소년보호정책",
						handler:function() {
							new Ext.Window({
								id:"YoungPolicyWindow",
								title:Ext.getCmp("ListTab").getActiveTab().title+" 청소년보호정책설정",
								width:700,
								height:500,
								modal:true,
								resizable:false,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"YoungPolicyForm",
										bodyPadding:"10 10 5 10",
										border:false,
										items:[
											new Ext.form.FieldSet({
												title:"개인정보보호정책 사용여부",
												items:[
													new Ext.form.Checkbox({
														name:"disable",
														boxLabel:Ext.getCmp("ListTab").getActiveTab().title+"에서 청소년보호정책을 사용하지 않습니다."
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"청소년보호정책",
												items:[
													new Ext.form.TextField({
														id:"YoungPolicy",
														anchor:"100%",
														name:"value",
														height:240
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"동의메세지",
												items:[
													new Ext.form.TextField({
														name:"msg",
														anchor:"100%",
														allowBlank:false
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"수정하기",
										handler:function() {
											oEditors.getById["YoungPolicy-inputEl"].exec("UPDATE_IR_FIELD",[]);
											Ext.getCmp("YoungPolicyForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=youngpolicy&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"청소년보호정책을 수정하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("YoungPolicyWindow").close();
													}});
												},
												failure:function(form,action) {
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"닫기",
										handler:function() {
											Ext.getCmp("YoungPolicyWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
									Ext.getCmp("YoungPolicyForm").getForm().load({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=signin&get=youngpolicy&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"데이터를 로딩중입니다.",
										success:function(form,action) {
											nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"YoungPolicy-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
										},
										failure:function(form,action) {
											Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}}}
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_form_add.png",
						text:"기본필드추가",
						handler:function() {
							var fields = new Array();
							var store = Ext.getCmp("ListTab").getActiveTab().getStore();
							for (type in fieldType) {
								if (store.find("name",type,0,false,false,false) == -1) {
									fields.push([type]);
								}
							}
							
							new Ext.Window({
								id:"DefaultFieldAddWindow",
								title:"기본필드추가",
								width:700,
								height:400,
								modal:true,
								resizable:false,
								layout:"fit",
								items:[
									new Ext.Panel({
										layout:{type:"hbox",align:"stretch"},
										border:false,
										items:[
											new Ext.grid.GridPanel({
												title:"필드종류선택",
												id:"DefaultFieldAddList",
												width:200,
												layout:"fit",
												margin:"5 5 5 5",
												columns:[{
													header:"필드종류",
													dataIndex:"type",
													flex:1,
													renderer:function(value) {
														return fieldType[value];
													}
												}],
												store:new Ext.data.ArrayStore({
													fields:["type"],
													data:fields
												}),
												hideHeaders:true,
												selMode:new Ext.selection.RowModel({mode:"SINGLE"}),
												listeners:{select:{fn:function(grid,record,index,e) {
													if (record.data.type.search(/^(user_id|name|nickname|password|email|jumin|companyno)$/) == -1) {
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("allowblank").setValue(false);
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("allowblank").enable();
													} else {
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("allowblank").setValue(true);
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("allowblank").disable();
													}
													
													if (record.data.type == "cellphone") {
														Ext.getCmp("DefaultFieldAddCellphone").show();
													} else {
														Ext.getCmp("DefaultFieldAddCellphone").hide();
													}
													
													if (record.data.type == "voter") {
														Ext.getCmp("DefaultFieldAddVoter").show();
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("msg").setValue("추천인 아이디를 입력하시면 추천하신분/추천받으시는분께 포인트를 선물해드립니다.")
													} else {
														Ext.getCmp("DefaultFieldAddVoter").hide();
														Ext.getCmp("DefaultFieldAddForm").getForm().findField("msg").setValue("");
													}
													
													Ext.getCmp("DefaultFieldAddForm").getForm().findField("title").setValue(fieldType[record.data.type]);
												}}}
											}),
											new Ext.form.FormPanel({
												id:"DefaultFieldAddForm",
												title:"필드설정",
												margin:"5 5 5 0",
												flex:1,
												bodyPadding:"10 10 5 10",
												fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%"},
												items:[
													new Ext.form.FieldSet({
														title:"필드 기본설정",
														items:[
															new Ext.form.TextField({
																name:"title",
																fieldLabel:"필드제목",
																allowBlank:false
															}),
															new Ext.form.TextField({
																name:"msg",
																fieldLabel:"안내메세지",
																emptyMsg:"사용자에게 해당입력란에 대한 설명을 입력합니다."
															}),
															new Ext.form.Checkbox({
																name:"allowblank",
																fieldLabel:"필수항목",
																boxLabel:"필수항목으로 설정합니다."
															})
														]
													}),
													new Ext.form.FieldSet({
														id:"DefaultFieldAddCellphone",
														title:"필드 부가설정",
														hidden:true,
														items:[
															new Ext.form.Checkbox({
																name:"realphone",
																fieldLabel:"번호인증",
																boxLabel:"SMS을 통해 인증을 받게 합니다.(SMS모듈필요)"
															}),
															new Ext.form.Checkbox({
																name:"provider",
																fieldLabel:"통신사",
																boxLabel:"휴대전화번호 통신사를 입력받도록 합니다."
															})
														]
													}),
													new Ext.form.FieldSet({
														id:"DefaultFieldAddVoter",
														title:"필드 부가설정 (추천인/추천받는인 포인트설정)",
														hidden:true,
														items:[
															new Ext.form.FieldContainer({
																fieldLabel:"추천인",
																layout:"hbox",
																items:[
																	new Ext.form.NumberField({
																		name:"vote",
																		value:500,
																		width:80
																	}),
																	new Ext.form.DisplayField({
																		value:"&nbsp;포인트 (추천하는사람에게 줄 추가 포인트)"
																	})
																]
															}),
															new Ext.form.FieldContainer({
																fieldLabel:"추천받는인",
																layout:"hbox",
																items:[
																	new Ext.form.NumberField({
																		name:"voter",
																		value:500,
																		width:80
																	}),
																	new Ext.form.DisplayField({
																		value:"&nbsp;포인트 (추천을 받는사람에게 줄 포인트)"
																	})
																]
															})
														]
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"필드추가",
										handler:function() {
											var checked = Ext.getCmp("DefaultFieldAddList").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"먼저 추가할 필드종류를 좌측에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												return;
											}
											
											Ext.getCmp("DefaultFieldAddForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=add_default&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop()+"&field="+checked[0].get("type"),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"기본필드를 추가하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListTab").getActiveTab().getStore().reload();
														Ext.getCmp("DefaultFieldAddWindow").close();
													}});
												},
												failure:function(form,action) {
													if (action.result) {
														if (action.result.errors.message) {
															Ext.Msg.show({title:"에러",msg:action.result.errors.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
															return;
														}
													}
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("DefaultFieldAddWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_form_add.png",
						text:"사용자정의필드추가",
						handler:function() {
							new Ext.Window({
								id:"ExtraFieldAddWindow",
								title:"사용자정의필드추가",
								width:700,
								height:400,
								modal:true,
								resizable:false,
								layout:"fit",
								items:[
									new Ext.Panel({
										layout:{type:"hbox",align:"stretch"},
										border:false,
										items:[
											new Ext.grid.GridPanel({
												title:"필드종류선택",
												id:"ExtraFieldAddList",
												width:200,
												layout:"fit",
												margin:"5 5 5 5",
												columns:[{
													header:"필드종류",
													dataIndex:"name",
													flex:1
												}],
												store:new Ext.data.ArrayStore({
													fields:["name","type"],
													data:[
														["한줄입력필드(input)","input"],["다중입력필드(textarea)","textarea"],["다중선택박스(checkbox)","checkbox"],["단일선택박스(radio)","radio"],["콤보박스(select)","select"],["날짜","date"],["전화번호","phone"],["주소","search_address"]
													]
												}),
												hideHeaders:true,
												selMode:new Ext.selection.RowModel({mode:"SINGLE"}),
												listeners:{select:{fn:function(grid,record,index,e) {
													if (record.data.type == "input") {
														Ext.getCmp("ExtraFieldAddInput").show();
													} else {
														Ext.getCmp("ExtraFieldAddInput").hide();
													}
													
													if (record.data.type == "textarea") {
														Ext.getCmp("ExtraFieldTextArea").show();
													} else {
														Ext.getCmp("ExtraFieldTextArea").hide();
													}
													
													if (record.data.type.search(/^(checkbox|radio|select)$/) > -1) {
														Ext.getCmp("ExtraFieldSelect").show();
													} else {
														Ext.getCmp("ExtraFieldSelect").hide();
													}
												}}}
											}),
											new Ext.form.FormPanel({
												id:"ExtraFieldAddForm",
												title:"필드설정",
												margin:"5 5 5 0",
												flex:1,
												bodyPadding:"10 10 5 10",
												fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%"},
												items:[
													new Ext.form.FieldSet({
														title:"필드 기본설정",
														items:[
															new Ext.form.TextField({
																name:"name",
																fieldLabel:"필드명",
																allowBlank:false,
																emptyText:"$member['extra']['필드명'] 으로 정보를 가져옵니다."
															}),
															new Ext.form.TextField({
																name:"title",
																fieldLabel:"필드제목",
																allowBlank:false
															}),
															new Ext.form.TextField({
																name:"msg",
																fieldLabel:"안내메세지",
																emptyMsg:"사용자에게 해당입력란에 대한 설명을 입력합니다."
															}),
															new Ext.form.Checkbox({
																name:"allowblank",
																fieldLabel:"필수항목",
																boxLabel:"필수항목으로 설정합니다."
															})
														]
													}),
													new Ext.form.FieldSet({
														id:"ExtraFieldAddInput",
														title:"필드 부가설정",
														hidden:true,
														items:[
															new Ext.form.TextField({
																fieldLabel:"유효성체크",
																name:"valid",
																emptyText:"정규식으로 유효성체크를 할 수 있습니다. (예:/^[a-z0-9]$/)"
															})
														]
													}),
													new Ext.form.FieldSet({
														id:"ExtraFieldTextArea",
														title:"필드 부가설정",
														hidden:true,
														items:[
															new Ext.form.FieldContainer({
																fieldLabel:"필드높이",
																layout:"hbox",
																items:[
																	new Ext.form.NumberField({
																		name:"height",
																		value:100,
																		width:100,
																		allowBlank:false
																	}),
																	new Ext.form.DisplayField({
																		value:"&nbsp;픽셀",
																		flex:1
																	})
																]
															})
														]
													}),
													new Ext.form.FieldSet({
														id:"ExtraFieldSelect",
														title:"필드 부가설정",
														hidden:true,
														items:[
															new Ext.form.TextArea({
																name:"list",
																fieldLabel:"선택항목",
																height:120,
																emptyText:"항목을 한줄에 하나씩 입력하여 주십시오. (엔터:줄바꿈으로 항목구분)"
															})
														]
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"필드추가",
										handler:function() {
											var checked = Ext.getCmp("ExtraFieldAddList").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"먼저 추가할 필드종류를 좌측에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												return;
											}
											Ext.getCmp("ExtraFieldAddForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=signin&do=add_extra&group="+Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop()+"&type="+checked[0].get("type"),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"사용자정의필드를 추가하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListTab").getActiveTab().getStore().reload();
														Ext.getCmp("ExtraFieldAddWindow").close();
													}});
												},
												failure:function(form,action) {
													if (action.result) {
														if (action.result.errors.message) {
															Ext.Msg.show({title:"에러",msg:action.result.errors.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
															return;
														}
													}
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("ExtraFieldAddWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_form_delete.png",
						text:"필드삭제",
						handler:function() {
							var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"삭제할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							for (var i=0, loop=checked.length;i<loop;i++) {
								if (checked[i].get("type").search(/^(user_id|password|email|name)$/) > -1) {
									Ext.Msg.show({title:"에러",msg:"회원아이디, 패스워드, 이메일, 이름 필드는 삭제할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									break;
								}
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"선택된 필드를 정말 삭제하시겠습니까?<br />필드를 삭제하면 해당 회원정보도 모두 삭제됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("선택한 필드를 삭제중중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListTab").getActiveTab().getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"signin","do":"delete","group":Ext.getCmp("ListTab").getActiveTab().getId().split("-").pop(),"idx":idxs.join(",")}
									});
								}
							}});
						}
					}),
					'-',
					{xtype:"tbtext",text:"순서변경"},
					new Ext.Button({
						text:"위로",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_arrow_up.png",
						handler:function() {
							var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}

							for (var i=0, loop=checked.length;i<loop;i++) {
								var sort = checked[i].get("sort");
								if (sort != 0) {
									Ext.getCmp("ListTab").getActiveTab().getStore().getAt(sort).set("sort",sort-1);
									Ext.getCmp("ListTab").getActiveTab().getStore().getAt(sort-1).set("sort",sort);
									Ext.getCmp("ListTab").getActiveTab().getStore().sort("sort","ASC");
								} else {
									return false;
								}
							}
							
							var update = Ext.getCmp("ListTab").getActiveTab().getStore().getUpdatedRecords();
							if (update.length > 0) {
								var data = new Array();
								for (var i=0, loop=update.length;i<loop;i++) {
									data.push(update[i].data);
								}
								data = Ext.JSON.encode(data);
								
								Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.getCmp("ListTab").getActiveTab().getStore().commitChanges();
											Ext.Msg.hide();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"signin","do":"sort","data":data}
								});
							}
						}
					}),
					new Ext.Button({
						text:"아래로",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_arrow_down.png",
						handler:function() {
							var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}

							for (var i=checked.length-1;i>=0;i--) {
								var sort = checked[i].get("sort");
								if (sort != Ext.getCmp("ListTab").getActiveTab().getStore().getCount()-1) {
									Ext.getCmp("ListTab").getActiveTab().getStore().getAt(sort).set("sort",sort+1);
									Ext.getCmp("ListTab").getActiveTab().getStore().getAt(sort+1).set("sort",sort);
									Ext.getCmp("ListTab").getActiveTab().getStore().sort("sort","ASC");
								} else {
									return false;
								}
							}
							
							var update = Ext.getCmp("ListTab").getActiveTab().getStore().getUpdatedRecords();
							if (update.length > 0) {
								var data = new Array();
								for (var i=0, loop=update.length;i<loop;i++) {
									data.push(update[i].data);
								}
								data = Ext.JSON.encode(data);
								
								Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.getCmp("ListTab").getActiveTab().getStore().commitChanges();
											Ext.Msg.hide();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"signin","do":"sort","data":data}
								});
							}
						}
					})
				],
				items:[
					<?php
					$group = $mDB->DBfetchs($_ENV['table']['group'],'*','','sort,asc');
					for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
					?>
					new Ext.grid.GridPanel({
						id:"SignIn-<?php echo $group[$i]['group']; ?>",
						title:"<?php echo $group[$i]['title']; ?>",
						border:false,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"필드타입",
								dataIndex:"is_default",
								width:80,
								renderer:function(value) {
									if (value == "TRUE") return '<span style="color:blue;">기본필드</span>';
									else return '<span style="color:#EF5600;">사용자정의</span>';
								}
							},{
								header:"필드명",
								dataIndex:"type",
								width:80
							},{
								header:"종류",
								dataIndex:"type",
								sortable:true,
								width:100,
								renderer:function(value) {
									return fieldType[value];
								}
							},{
								header:"필드제목",
								dataIndex:"title",
								width:80
							},{
								header:"필수",
								dataIndex:"allowblank",
								width:60,
								renderer:function(value) {
									if (value == "FALSE") return '<span style="color:red;">필수</span>';
								}
							},{
								header:"안내메세지",
								dataIndex:"msg",
								minWidth:100,
								flex:1
							}
						],
						columnLines:true,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"signin",get:"field",group:"<?php echo $group[$i]['group']; ?>"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:[{name:"idx",type:"int"},"is_default","type","name","title","msg","allowblank",{name:"sort",type:"int"}]
						}),
						listeners:{
							itemdblclick:{fn:function() {
								Ext.Msg.show({title:"안내",msg:"현재 설정화면은 더블클릭으로 실행되는 동작이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
							}},
							itemcontextmenu:ItemContextMenu
						}
					}),
					<?php } ?>
					new Ext.Panel({
						hidden:true
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>