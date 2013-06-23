<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"list"}
		},
		remoteSort:true,
		sorters:[{property:"pid",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["pid","title","width","skin","use_ment",{name:"postnum",type:"int"},"last_date"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'('+record.data.pid+')</b>');
		
		menu.add({
			text:"설문조사설정",
			handler:function() {
				PollFormFunction(record.data.pid);
			}
		});

		var width = record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width);
		
		menu.add({
			text:"설문조사바로가기",
			handler:function() {
				new Ext.Window({
					title:record.data.title,
					width:width,
					height:500,
					layout:"fit",
					maximizable:true,
					html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/poll/poll.php?pid='+record.data.pid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
				}).show();
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"설문조사삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"설문조사를 삭제하면 해당 설문조사의 모든 글과 자료가 삭제됩니다.<br />설문조사를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("설문조사를 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().loadPage(1);
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"poll","do":"delete","pid":record.data.pid}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function PollFormFunction(pid) {
		if (pid) {
			var title = "설문조사설정";
		} else {
			var title = "설문조사생성";
			var pid = "";
		}

		new Ext.Window({
			id:"PollWindow",
			title:title,
			width:600,
			height:500,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"PollForm",
					bodyPadding:"10 10 5 10",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.TextField({
									fieldLabel:"설문조사ID",
									name:"pid",
									disabled:(pid ? true : false)
								}),
								new Ext.form.TextField({
									fieldLabel:"설문조사명",
									name:"title",
									width:200
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"디자인정보",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"스킨선택",
									name:"skin",
									typeAhead:true,
									lazyRender:false,
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{action:"skin"}
										},
										remoteSort:false,
										sorters:[{property:"skin",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["skin"]
									}),
									editable:false,
									mode:"local",
									displayField:"skin",
									valueField:"skin",
									triggerAction:"all",
									emptyText:"스킨을 선택하세요."
								}),
								new Ext.form.TextField({
									fieldLabel:"가로크기",
									name:"width",
									emptyText:"%단위 또는 px단위로 입력하세요."
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"옵션",
							items:[
								new Ext.form.Checkbox({
									boxLabel:"댓글기능을 활성화 합니다.",
									name:"use_ment"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"목록페이지 설정",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"목록갯수",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"listnum",
											value:30,
											width:70
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;개/페이지"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"페이지링크수",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"pagenum",
											value:10,
											width:70
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;(페이지를 이동할 수 있는 페이지번호 갯수)"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"포인트설정",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"설문작성포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"post_point",
											value:50,
											width:100
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"댓글작성포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"ment_point",
											value:10,
											width:100
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"투표포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"vote_point",
											value:100,
											width:100
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"권한설정",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"목록보기",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_list_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_list",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"설문글보기",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_view_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_view",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"설문글작성",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_post_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_post",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"댓글작성",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_ment_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_ment",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"수정",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_modify_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_modify",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"삭제",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_delete_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_delete",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"투표권한",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_vote_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_vote",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"결과보기",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_result_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											style:{marginRight:"5px"},
											listeners:{select:{fn:function(form) {
												Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_result",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.Panel({
									border:false,
									autoScroll:true,
									bodyPadding:"0 0 5 105",
									html:'<div class="boxDefault">수정, 삭제권한의 경우 권한설정에 관계없이 설문글/댓글 작성자는 기본적으로 권한을 가지게 됩니다.<br />또한 최고관리자는 권한설정과 관계없이 모든 권한을 가지게 됩니다.</div>'
								})
							]
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"권한설정도움말",
					handler:function() {
						PollPermissionHelp();
					}
				}),
				'->',
				new Ext.Button({
					text:"확인",
					handler:function() {
						Ext.getCmp("PollForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php?action=poll&do="+(pid ? "modify&pid="+pid : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(pid ? "설문조사를 수정하고 있습니다." : "설문조사를 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(pid ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("PollWindow").close();
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
						Ext.getCmp("PollWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("PollForm").getForm().load({
					url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php?action=poll&pid="+(pid ? pid : ""),
					waitTitle:"잠시만 기다려주십시오.",
					waitMsg:"데이터를 로딩중입니다.",
					success:function(form,action) {
						form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
						form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
						form.findField("permission_post").fireEvent("blur",form.findField("permission_post"));
						form.findField("permission_ment").fireEvent("blur",form.findField("permission_ment"));
						form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
						form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
						form.findField("permission_vote").fireEvent("blur",form.findField("permission_vote"));
						form.findField("permission_result").fireEvent("blur",form.findField("permission_result"));
					},
					failure:function(form,action) {
						Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
					}
				});
			}}}
		}).show();
	}

	function PollPermissionHelp() {
		if (Ext.getCmp("PermissionHelpWindow")) return;

		new Ext.Window({
			id:"PermissionHelpWindow",
			title:"권한설정도움말",
			width:500,
			height:300,
			layout:"fit",
			resizeable:false,
			items:[
				new Ext.Panel({
					border:false,
					autoScroll:true,
					style:{lineHeight:"1.6"},
					html:'<div style="padding:5px;"><div class="boxDefault">권한설정은 산술적 수식으로 표현됩니다. 아래의 변수값들을 이용하여, 연산식으로 입력하시면 됩니다.</div><br /><b>{$member.user_id} :</b> 회원아이디<br /><b>{$member.level} :</b> 회원레벨<br /><b>{$member.type} :</b> 회원종류(ADMINISTRATOR, MODERATOR, MEMBER)<br /><br /><b>입력예</b><br />1. 회원레벨 5 초과인 사람만 허용<br />{$member.level} > 5<br /><br />2. 회원레벨이 5 이상이고, 10 이하인 사람만 허용<br />{$member.level} >= 5 && {$member.level} <= 10<br /><br />3. 회원종류가 MEMBER이고, 회원레벨이 5이상이거나 또는 회원레벨이 10이상인 경우<br />({$member.type} == "MEMBER" && {$member.level} >= 5) || ({$member.level} >= 10)<br /><br /><div class="boxDefault">위의 예제와 같이 괄호와, AND(&&)연산자, OR(||)연산자를 이용하여 정교한 권한을 설정할 수 있습니다.</div>'
				})
			],
			buttons:[
				new Ext.Button({
					text:"닫기",
					handler:function() {
						Ext.getCmp("PermissionHelpWindow").close();
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"설문조사관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"설문조사추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_poll_add.png",
				handler:function() {
					PollFormFunction();
				}
			}),
			'-',
			new Ext.form.TextField({
				id:"Keyword",
				width:180,
				emptyText:"검색어를 입력하세요."
			}),
			new Ext.Button({
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 설문조사를&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"설문조사일괄설정",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"설문조사를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							if (checked.length == 1) return PollFormFunction(checked[0].get("pid"));
							
							var pids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								pids.push(checked[i].get("pid"));
							}
							
							var isModifyFunction = function(form) {
								var temp = form.getName().split("_");
								temp.shift();
								var field = temp.join("_");
								Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
							}
							
							new Ext.Window({
								id:"PollWindow",
								title:"설문조사설정",
								width:600,
								height:500,
								minWidth:600,
								minHeight:400,
								modal:true,
								maximizable:true,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"PollForm",
										bodyPadding:"10 10 5 10",
										border:false,
										autoScroll:true,
										fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
										items:[
											new Ext.form.FieldSet({
												title:"디자인정보",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"스킨선택",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"skin",
																typeAhead:true,
																lazyRender:false,
																flex:1,
																disabled:true,
																store:new Ext.data.JsonStore({
																	proxy:{
																		type:"ajax",
																		simpleSortMode:true,
																		url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
																		reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																		extraParams:{action:"skin"}
																	},
																	remoteSort:false,
																	sorters:[{property:"skin",direction:"ASC"}],
																	autoLoad:true,
																	pageSize:50,
																	fields:["skin"]
																}),
																editable:false,
																mode:"local",
																displayField:"skin",
																valueField:"skin",
																triggerAction:"all",
																emptyText:"스킨을 선택하세요."
															}),
															new Ext.form.Checkbox({
																name:"is_skin",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"가로크기",
														layout:"hbox",
														items:[
															new Ext.form.TextField({
																name:"width",
																disabled:true,
																flex:1,
																emptyText:"%단위 또는 px단위로 입력하세요."
															}),
															new Ext.form.Checkbox({
																name:"is_width",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"옵션",
												items:[
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"댓글기능을 활성화 합니다.",
																disabled:true,
																flex:1,
																name:"use_ment"
															}),
															new Ext.form.Checkbox({
																name:"is_use_ment",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"목록페이지 설정",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"목록갯수",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"listnum",
																value:30,
																width:70,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;개/페이지",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_listnum",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"페이지링크수",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"pagenum",
																value:10,
																width:70,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;(페이지를 이동할 수 있는 페이지번호 갯수)",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_pagenum",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"포인트설정",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"설문작성포인트",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"post_point",
																value:50,
																width:100,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;포인트",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_post_point",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"댓글작성포인트",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"ment_point",
																value:10,
																width:100,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;포인트",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_ment_point",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"투표포인트",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"vote_point",
																value:100,
																width:100,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;포인트",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_vote_point",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"권한설정",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"목록보기",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_list_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																disabled:true,
																editable:false,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_list",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_list",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"설문항목보기",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_view_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_view",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_view",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"설문항목작성",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_post_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_post",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_post",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"댓글작성",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_ment_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_ment",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_ment",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"수정",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_modify_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_modify",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_modify",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"삭제",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_delete_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_delete",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_delete",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"투표권한",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_vote_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_vote",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_vote",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"결과보기",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_result_select",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																store:new Ext.data.ArrayStore({
																	fields:["display","value"],
																	data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
																}),
																width:150,
																editable:false,
																disabled:true,
																mode:"local",
																displayField:"display",
																valueField:"value",
																style:{marginRight:"5px"},
																listeners:{select:{fn:function(form) {
																	Ext.getCmp("PollForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_result",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("PollForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_result",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("PollForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("PollForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.Panel({
														border:false,
														autoScroll:true,
														bodyPadding:"0 0 5 105",
														html:'<div class="boxDefault">수정, 삭제권한의 경우 권한설정에 관계없이 설문글/댓글 작성자는 기본적으로 권한을 가지게 됩니다.<br />또한 최고관리자는 권한설정과 관계없이 모든 권한을 가지게 됩니다.</div>'
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"권한설정도움말",
										handler:function() {
											PollPermissionHelp();
										}
									}),
									'->',
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.getCmp("PollForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php?action=poll&do=modify_all&pid="+pids.join(","),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"선택설문조사를 일괄수정하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListPanel").getStore().loadPage(1);
														Ext.getCmp("PollWindow").close();
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
											Ext.getCmp("PollWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
									Ext.getCmp("PollForm").getForm().load({
										url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php?action=poll_all&pid="+pids.join(","),
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"데이터를 로딩중입니다.",
										success:function(form,action) {
											form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
											form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
											form.findField("permission_post").fireEvent("blur",form.findField("permission_post"));
											form.findField("permission_ment").fireEvent("blur",form.findField("permission_ment"));
											form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
											form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
											form.findField("permission_vote").fireEvent("blur",form.findField("permission_vote"));
											form.findField("permission_result").fireEvent("blur",form.findField("permission_result"));
										},
										failure:function(form,action) {
											Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}}}
							}).show();
						}
					},'-',{
						text:"선택 설문조사 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"설문조사를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var pids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								pids.push(checked[i].get("pid"));
							}
							
							Ext.Msg.show({title:"확인",msg:"설문조사를 삭제하면 해당 설문조사의 모든 글과 자료가 삭제됩니다.<br />설문조사를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("설문조사를 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"poll","do":"delete","pid":pids.join(",")}
									});
								}
							}});
						}
					}]
				})
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"설문조사ID",
						dataIndex:"pid",
						sortable:true,
						width:150,
						renderer:function(value) { return '<span style="font-family:verdana; font-weight:bold; font-size:11px;">'+value+'</span>'; }
					},{
						header:"설문조사명",
						dataIndex:"title",
						sortable:true,
						minWidth:150,
						flex:1
					},{
						header:"스킨명",
						dataIndex:"skin",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"너비",
						dataIndex:"width",
						sortable:true,
						width:60,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+value+'</div>';
						}
					},{
						header:"댓글여부",
						dataIndex:"use_ment",
						sortable:false,
						width:60,
						renderer:function(value) {
							if (value == "TRUE") return '<span style="color:blue;">사용함</span>';
							else return '<span style="color:red;">미사용</span>';
						}
					},{
						header:"설문글수",
						dataIndex:"postnum",
						sortable:false,
						width:80,
						renderer:GridNumberFormat
					},{
						header:"최종등록일",
						dataIndex:"last_date",
						sortable:false,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					}
				],
				store:store,
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						new Ext.Window({
							title:record.data.title,
							width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
							height:500,
							layout:"fit",
							maximizable:true,
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/poll/poll.php?pid='+record.data.pid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
						}).show();
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>