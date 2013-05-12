<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"list"}
		},
		remoteSort:true,
		sorters:[{property:"bid",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["bid","title","width","skin","option",{name:"postnum",type:"int"},"last_date"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'('+record.data.bid+')</b>');
		
		menu.add({
			text:"게시판설정",
			handler:function() {
				BoardFormFunction(record.data.bid);
			}
		});

		var option = record.data.option.split(",");
		var width = record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width);
		
		if (option[0] == "TRUE") {
			menu.add({
				text:"카테고리설정",
				handler:function() {
					BoardCategoryFunction(record.data.bid);
				}
			});
		}
		
		menu.add({
			text:"게시판바로가기",
			handler:function() {
				new Ext.Window({
					title:record.data.title,
					width:width,
					height:500,
					layout:"fit",
					maximizable:true,
					html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
				}).show();
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"게시판삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"게시판을 삭제하면 해당 게시판의 모든 글과 자료가 삭제됩니다.<br />게시판을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("게시판을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
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
							params:{"action":"board","do":"delete","bid":record.data.bid}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function BoardFormFunction(bid) {
		if (bid) {
			var title = "게시판설정";
		} else {
			var title = "게시판생성";
			var bid = "";
		}

		new Ext.Window({
			id:"BoardWindow",
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
					id:"BoardForm",
					bodyPadding:"10 10 5 10",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.TextField({
									fieldLabel:"게시판ID",
									name:"bid",
									disabled:(bid ? true : false)
								}),
								new Ext.form.TextField({
									fieldLabel:"게시판명",
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
											url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
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
								}),
								new Ext.form.Checkbox({
									boxLabel:"트랙백기능을 활성화 합니다.",
									name:"use_trackback"
								}),
								new Ext.form.Checkbox({
									boxLabel:"파일업로드 기능을 활성화 합니다. (멀티업로드)",
									name:"use_uploader"
								}),
								new Ext.form.Checkbox({
									boxLabel:"카테고리기능을 활성화 합니다.",
									name:"use_category",
									listeners:{change:{fn:function(form) {
										if (form.checked == true) {
											Ext.getCmp("BoardForm").getForm().findField("use_category_option").enable();
										} else {
											Ext.getCmp("BoardForm").getForm().findField("use_category_option").disable();
										}
									}}}
								}),
								new Ext.form.Checkbox({
									boxLabel:"카테고리를 반드시 선택하도록 합니다.",
									name:"use_category_option",
									disabled:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"작성자에게 포인트를 지급할 수 있는 유료글작성기능을 활성화 합니다.",
									name:"use_charge"
								}),
								new Ext.form.Checkbox({
									boxLabel:"답변자에게 포인트를 지급할 수 있는 지식인기능을 활성화 합니다.",
									name:"use_select"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"RSS기능을 사용합니다. (글보기 권한과 관계없이 비밀글을 제외한 RSS가 제공됩니다)",
							checkboxToggle:true,
							checkboxName:"use_rss",
							collapsed:true,
							bodyPadding:"5 0 0 0",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"글 갯수",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"rss_limit",
											value:10,
											width:100,
											disabled:true
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;개 (RSS로 제공해줄 게시물 수)"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"글 내용 제한",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"rss_post_limit",
											value:100,
											width:100,
											disabled:true
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;자 (내용 글자수 제한. 0은 제한없음)"
										})
									]
								}),
								new Ext.form.TextField({
									fieldLabel:"게시판주소",
									name:"rss_link",
									disabled:true
								}),
								new Ext.form.TextField({
									fieldLabel:"RSS소개",
									name:"rss_description",
									disabled:true
								}),
								new Ext.form.ComboBox({
									fieldLabel:"RSS언어",
									name:"rss_language",
									typeAhead:true,
									lazyRender:false,
									store:new Ext.data.ArrayStore({
										fields:["value","display"],
										data:[["ko","한국어"],["en","영어"],["jp","일본어"],["cn","중국어"],["fr","프랑스어"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									emptyText:"RSS언어를 선택하세요.",
									disabled:true
								})
							],
							listeners:{
								collapse:{fn:function(form) {
									Ext.getCmp("BoardForm").getForm().findField("rss_limit").disable();
									Ext.getCmp("BoardForm").getForm().findField("rss_post_limit").disable();
									Ext.getCmp("BoardForm").getForm().findField("rss_link").disable();
									Ext.getCmp("BoardForm").getForm().findField("rss_description").disable();
									Ext.getCmp("BoardForm").getForm().findField("rss_language").disable();
								}},
								expand:{fn:function(form) {
									Ext.getCmp("BoardForm").getForm().findField("rss_limit").enable();
									Ext.getCmp("BoardForm").getForm().findField("rss_post_limit").enable();
									Ext.getCmp("BoardForm").getForm().findField("rss_link").enable();
									Ext.getCmp("BoardForm").getForm().findField("rss_description").enable();
									Ext.getCmp("BoardForm").getForm().findField("rss_language").enable();
								}}
							}
						}),
						new Ext.form.FieldSet({
							title:"목록페이지 설정",
							items:[
								new Ext.form.CheckboxGroup({
									fieldLabel:"목록항목",
									columns:3,
									width:400,
									items:[
										new Ext.form.Checkbox({
											boxLabel:"순번",
											name:"list_loopnum"
										}),
										new Ext.form.Checkbox({
											boxLabel:"작성자",
											name:"list_name"
										}),
										new Ext.form.Checkbox({
											boxLabel:"작성일",
											name:"list_reg_date"
										}),
										new Ext.form.Checkbox({
											boxLabel:"조회",
											name:"list_hit"
										}),
										new Ext.form.Checkbox({
											boxLabel:"추천",
											name:"list_vote"
										}),
										new Ext.form.Checkbox({
											boxLabel:"평점",
											name:"list_avgvote"
										})
									]
								}),
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
								}),
								new Ext.form.ComboBox({
									fieldLabel:"공지사항설정",
									name:"view_notice",
									width:300,
									typeAhead:true,
									lazyRender:false,
									store:new Ext.data.ArrayStore({
										fields:["value","display"],
										data:[["ALL,INCLUDE","전체페이지에 출력 및 목록수 갯수에 포함"],["ALL,EXCLUDE","전체페이지에 출력 및 목록수 갯수에 미포함"],["FIRST,INCLUDE","첫페이지에 출력 및 목록수 갯수에 포함"],["FIRST,EXCLUDE","첫페이지에 출력 및 목록수 갯수에 미포함"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"글보기페이지 설정",
							items:[
								new Ext.form.Checkbox({
									boxLabel:"글보기 페이지에 전체목록을 함께 보여줍니다.",
									name:"view_alllist"
								}),
								new Ext.form.Checkbox({
									boxLabel:"글보기 페이지에 이전글 다음글 정보를 보여줍니다.",
									name:"view_prevnext"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"포인트설정",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"글작성포인트",
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
									fieldLabel:"답변채택포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"select_point",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_list",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"게시물읽기",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_view",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"게시물작성",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_post",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_ment",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_modify",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_delete",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"답변채택",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_select_select",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_select",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"비밀글읽기",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_secret_select",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_secret",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"공지사항작성",
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"permission_notice_select",
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
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_notice",
											flex:1,
											allowBlank:true,
											listeners:{blur:{fn:function(form) {
												if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
												} else {
													Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
												}
											}}}
										})
									]
								}),
								new Ext.Panel({
									border:false,
									autoScroll:true,
									bodyPadding:"0 0 5 105",
									html:'<div class="boxDefault">수정, 삭제, 비밀글권한의 경우 권한설정에 관계없이 글 작성자는 기본적으로 권한을 가지게 됩니다.<br />또한 최고관리자는 권한설정과 관계없이 모든 권한을 가지게 됩니다.</div>'
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
						BoardPermissionHelp();
					}
				}),
				'->',
				new Ext.Button({
					text:"확인",
					handler:function() {
						Ext.getCmp("BoardForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=board&do="+(bid ? "modify&bid="+bid : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(bid ? "게시판을 수정하고 있습니다." : "게시판설정을 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(bid ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("BoardWindow").close();
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
						Ext.getCmp("BoardWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("BoardForm").getForm().load({
					url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php?action=board&bid="+(bid ? bid : ""),
					waitTitle:"잠시만 기다려주십시오.",
					waitMsg:"데이터를 로딩중입니다.",
					success:function(form,action) {
						form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
						form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
						form.findField("permission_post").fireEvent("blur",form.findField("permission_post"));
						form.findField("permission_ment").fireEvent("blur",form.findField("permission_ment"));
						form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
						form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
						form.findField("permission_select").fireEvent("blur",form.findField("permission_select"));
						form.findField("permission_secret").fireEvent("blur",form.findField("permission_secret"));
						form.findField("permission_notice").fireEvent("blur",form.findField("permission_notice"));
					},
					failure:function(form,action) {
						Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
					}
				});
			}}}
		}).show();
	}

	function BoardPermissionHelp() {
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
					html:'<div style="padding:5px;"><div class="boxDefault">권한설정은 해당 카테고리에 게시물을 작성할 수 있는 권한을 설정하는 것입니다. 이 권한설정은 산술적 수식으로 표현됩니다. 아래의 변수값들을 이용하여, 연산식으로 입력하시면 됩니다.</div><br /><b>{$member.user_id} :</b> 회원아이디<br /><b>{$member.level} :</b> 회원레벨<br /><b>{$member.type} :</b> 회원종류(ADMINISTRATOR, MODERATOR, MEMBER)<br /><br /><b>입력예</b><br />1. 회원레벨 5 초과인 사람만 허용<br />{$member.level} > 5<br /><br />2. 회원레벨이 5 이상이고, 10 이하인 사람만 허용<br />{$member.level} >= 5 && {$member.level} <= 10<br /><br />3. 회원종류가 MEMBER이고, 회원레벨이 5이상이거나 또는 회원레벨이 10이상인 경우<br />({$member.type} == "MEMBER" && {$member.level} >= 5) || ({$member.level} >= 10)<br /><br /><div class="boxDefault">위의 예제와 같이 괄호와, AND(&&)연산자, OR(||)연산자를 이용하여 정교한 권한을 설정할 수 있습니다.</div>'
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

	function BoardCategoryFunction(bid) {
		new Ext.Window({
			id:"CategoryWindow",
			title:"카테고리설정",
			width:600,
			height:400,
			modal:true,
			maximizable:false,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"CategoryList",
					border:false,
					tbar:[
						new Ext.Button({
							text:"카테고리추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tab_add.png",
							handler:function() {
								new Ext.Window({
									id:"CategoryAddWindow",
									title:"카테고리추가",
									width:500,
									modal:true,
									resizable:false,
									layout:"fit",
									items:[
										new Ext.form.FormPanel({
											id:"CategoryAddForm",
											border:false,
											bodyPadding:"10 10 5 10",
											fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
											items:[
												new Ext.form.TextField({
													fieldLabel:"카테고리명",
													name:"category"
												}),
												new Ext.form.TextField({
													fieldLabel:"권한설정",
													name:"permission",
													allowBlank:true
												})
											]
										})
									],
									buttons:[
										new Ext.Button({
											text:"권한설정도움말",
											handler:function() {
												BoardPermissionHelp();
											}
										}),
										'->',
										new Ext.Button({
											text:"확인",
											handler:function() {
												Ext.getCmp("CategoryAddForm").getForm().submit({
													url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=category&do=add&bid="+bid,
													submitEmptyText:false,
													waitTitle:"잠시만 기다려주십시오.",
													waitMsg:"카테고리를 추가중입니다.",
													success:function(form,action) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 카테고리를 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
															Ext.getCmp("CategoryList").getStore().add({"idx":action.result.idx,"category":action.result.category,"permission":action.result.permission,"sort":action.result.sort});
															if (button == "yes") {
																Ext.getCmp("CategoryAddForm").getForm().reset();
															} else {
																Ext.getCmp("CategoryAddWindow").close();
															}
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
												Ext.getCmp("CategoryAddWindow").close();
											}
										})
									]
								}).show();
							}
						}),
						new Ext.Button({
							text:"카테고리삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tab_delete.png",
							handler:function() {
								var checked = Ext.getCmp("CategoryList").getSelectionModel().getSelection();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return false;
								}

								var idxs = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									idxs.push(checked[i].get("idx"));
								}

								new Ext.Window({
									id:"CategoryDeleteWindow",
									title:"카테고리삭제",
									width:500,
									modal:true,
									resizable:false,
									layout:"fit",
									items:[
										new Ext.form.FormPanel({
											id:"CategoryDeleteForm",
											bodyPadding:"10 10 5 10",
											fieldDefaults:{labelAlign:"right",labelWidth:70,anchor:"100%",allowBlank:false},
											border:false,
											items:[
												new Ext.form.ComboBox({
													fieldLabel:"게시물처리",
													name:"post",
													typeAhead:true,
													triggerAction:"all",
													lazyRender:true,
													store:new Ext.data.ArrayStore({
														fields:["type","text"],
														data:[
															["move","삭제할 카테고리에 속한 게시물을 다른 카테고리로 이동합니다."],
															["reset","삭제할 카테고리에 속한 게시물의 카테고리 정보를 초기화합니다."],
															["delete","삭제할 카테고리에 속한 게시물을 함께 삭제합니다."]
														]
													}),
													editable:false,
													mode:"local",
													displayField:"text",
													valueField:"type",
													value:"delete",
													listeners:{change:{fn:function(form) {
														if (form.getValue() == "move") {
															Ext.getCmp("CategoryDeleteForm").getForm().findField("move").enable();
														} else {
															Ext.getCmp("CategoryDeleteForm").getForm().findField("move").disable();
														}
													}}}
												}),
												new Ext.form.ComboBox({
													fieldLabel:"게시물이동",
													name:"move",
													typeAhead:true,
													triggerAction:"all",
													lazyRender:true,
													disabled:true,
													store:new Ext.data.JsonStore({
														proxy:{
															type:"ajax",
															simpleSortMode:true,
															url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
															reader:{type:"json",root:"lists",totalProperty:"totalCount"},
															extraParams:{action:"category",bid:bid}
														},
														remoteSort:false,
														sorters:[{property:"sort",direction:"ASC"}],
														autoLoad:true,
														pageSize:50,
														fields:["idx","category"]
													}),
													editable:false,
													mode:"local",
													displayField:"category",
													valueField:"idx",
													emptyText:"삭제될 카테고리에 속한 게시물을 옮길 카테고리를 선택하여 주십시오."
												})
											]
										})
									],
									buttons:[
										new Ext.Button({
											text:"확인",
											handler:function() {
												Ext.Msg.show({title:"안내",msg:"선택한 카테고리를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "yes") {
														Ext.getCmp("CategoryDeleteForm").getForm().submit({
															url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=category&do=delete&bid="+bid+"&idx="+idxs.join(","),
															submitEmptyText:false,
															waitTitle:"잠시만 기다려주십시오.",
															waitMsg:"카테고리를 삭제중입니다.",
															success:function(form,action) {
																Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
																	var checked = Ext.getCmp("CategoryList").getSelectionModel().getSelection();
																	for (var i=0, loop=checked.length;i<loop;i++) {
																		Ext.getCmp("CategoryList").getStore().remove(checked[i]);
																	}
																	
																	var list = Ext.getCmp("CategoryList").getStore();
																	for (var i=0, loop=list.getCount();i<loop;i++) {
																		Ext.getCmp("CategoryList").getStore().getAt(i).set("sort",i);
																	}
																	Ext.getCmp("CategoryDeleteWindow").close();
																}});
															},
															failure:function(form,action) {
																if (action.result) {
																	Ext.Msg.show({title:"에러",msg:action.result.errors.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																	return;
																}
																Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
															}
														});
													}
												}});
											}
										}),
										new Ext.Button({
											text:"취소",
											handler:function() {
												Ext.getCmp("CategoryDeleteWindow").close();
											}
										})
									]
								}).show();
							}
						}),
						'-',
						{xtype:"tbtext",text:"순서변경"},
						new Ext.Button({
							text:"위로 이동",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_up.png",
							handler:function() {
								var checked = Ext.getCmp("CategoryList").getSelectionModel().getSelection();

								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}

								var selecter = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									selecter.push(checked[i].get("sort")-1);
								}
								for (var i=0, loop=checked.length;i<loop;i++) {
									var sort = checked[i].get("sort");
									if (sort != 0) {
										Ext.getCmp("CategoryList").getStore().getAt(sort).set("sort",sort-1);
										Ext.getCmp("CategoryList").getStore().getAt(sort-1).set("sort",sort);
										Ext.getCmp("CategoryList").getStore().sort("sort","ASC");
									} else {
										return false;
									}
								}
								
								for (var i=0, loop=selecter.length;i<loop;i++) {
									Ext.getCmp("CategoryList").getSelectionModel().select(selecter[i],i!=0);
								}

								var update = Ext.getCmp("CategoryList").getStore().getUpdatedRecords();
								if (update.length > 0) {
									var data = new Array();
									for (var i=0, loop=update.length;i<loop;i++) {
										data.push(update[i].data);
									}
									data = Ext.JSON.encode(data);
									
									Ext.Msg.wait("순서를 변경하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.hide();
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"category","do":"sort","data":data}
									});
								}
							}
						}),
						new Ext.Button({
							text:"아래로 이동",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_down.png",
							handler:function() {
								var checked = Ext.getCmp("CategoryList").getSelectionModel().getSelection();

								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}
								
								var selecter = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									selecter.push(checked[i].get("sort")+1);
								}
								for (var i=checked.length-1;i>=0;i--) {
									var sort = checked[i].get("sort");
									if (sort != Ext.getCmp("CategoryList").getStore().getCount()-1) {
										Ext.getCmp("CategoryList").getStore().getAt(sort).set("sort",sort+1);
										Ext.getCmp("CategoryList").getStore().getAt(sort+1).set("sort",sort);
										Ext.getCmp("CategoryList").getStore().sort("sort","ASC");
									} else {
										return false;
									}
								}
								
								for (var i=0, loop=selecter.length;i<loop;i++) {
									Ext.getCmp("CategoryList").getSelectionModel().select(selecter[i],i!=0);
								}

								var update = Ext.getCmp("CategoryList").getStore().getUpdatedRecords();
								if (update.length > 0) {
									var data = new Array();
									for (var i=0, loop=update.length;i<loop;i++) {
										data.push(update[i].data);
									}
									data = Ext.JSON.encode(data);
									
									Ext.Msg.wait("순서를 변경하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.hide();
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"category","do":"sort","data":data}
									});
								}
							}
						})
					],
					columns:[
						new Ext.grid.RowNumberer(),
						{
							header:"카테고리명",
							dataIndex:"category",
							sortable:true,
							width:200,
							editor:new Ext.form.TextField({selectOnFocus:true,allowBlank:false})
						},{
							header:"작성권한",
							dataIndex:"permission",
							sortable:true,
							flex:1,
							editor:new Ext.form.TextField({selectOnFocus:true})
						}
					],
					selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
					plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
					columnLines:true,
					store:new Ext.data.JsonStore({
						proxy:{
							type:"ajax",
							simpleSortMode:true,
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
							reader:{type:"json",root:"lists",totalProperty:"totalCount"},
							extraParams:{action:"category",bid:bid}
						},
						remoteSort:false,
						sorters:[{property:"sort",direction:"ASC"}],
						autoLoad:true,
						pageSize:50,
						fields:["idx","category","permission",{name:"sort",type:"int"}]
					})
				})
			],
			buttons:[
				new Ext.Button({
					text:"권한설정도움말",
					handler:function() {
						BoardPermissionHelp();
					}
				}),
				'->',
				new Ext.Button({
					text:"변경사항저장",
					handler:function() {
						var data = new Array();
						var update = Ext.getCmp("CategoryList").getStore().getUpdatedRecords();
						for (var i=0, loop=update.length;i<loop;i++) {
							data.push(update[i].data);
						}
						data = Ext.JSON.encode(data);

						Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("CategoryList").getStore().commitChanges();
										Ext.getCmp("CategoryWindow").close();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {	
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"category","do":"modify","data":data}
						});
					}
				}),
				new Ext.Button({
					text:"취소",
					handler:function() {
						Ext.getCmp("CategoryWindow").close();
					}
				})
			],
			listeners:{beforeclose:{fn:function() {
				var update = Ext.getCmp("CategoryList").getStore().getUpdatedRecords();
				var isModify = false;
		
				if (update.length > 0) {
					for (var i=0, loop=update.length;i<loop;i++) {
						for (field in update[i].modified) {
							if (field != "sort") {
								isModify = true;
							}
							if (isModify == true) break;
						}
					}
				}
				
				if (isModify == true) {
					Ext.Msg.show({title:"안내",msg:"저장되지 않은 변경사항이 있습니다.<br />변경사항을 저장하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
						if (button == "yes") {
							var data = new Array();
							for (var i=0, loop=update.length;i<loop;i++) {
								data.push(update[i].data);
							}
							data = Ext.JSON.encode(data);
							
							Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
								success:function(response) {
									var data = Ext.JSON.decode(response.responseText);
									if (data.success == true) {
										Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
											Ext.getCmp("CategoryList").getStore().commitChanges();
											Ext.getCmp("CategoryWindow").close();
										}});
									} else {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									}
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								},
								params:{"action":"category","do":"modify","data":data}
							});
						} else {
							Ext.getCmp("CategoryList").getStore().commitChanges();
							Ext.getCmp("CategoryWindow").close();
						}
					}});
					return false;
				}
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"게시판관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"게시판추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_add.png",
				handler:function() {
					BoardFormFunction();
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
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 게시판을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"게시판일괄설정",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"게시판을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							if (checked.length == 1) return BoardFormFunction(checked[i].get("bid"));
							
							var bids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								bids.push(checked[i].get("bid"));
							}
							
							var isModifyFunction = function(form) {
								var temp = form.getName().split("_");
								temp.shift();
								var field = temp.join("_");
								Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
							}
							
							new Ext.Window({
								id:"BoardWindow",
								title:"게시판설정",
								width:600,
								height:500,
								minWidth:600,
								minHeight:400,
								modal:true,
								maximizable:true,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"BoardForm",
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
																		url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
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
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"트랙백기능을 활성화 합니다.",
																disabled:true,
																flex:1,
																name:"use_trackback"
															}),
															new Ext.form.Checkbox({
																name:"is_use_trackback",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"파일업로드 기능을 활성화 합니다. (멀티업로드)",
																disabled:true,
																flex:1,
																name:"use_uploader"
															}),
															new Ext.form.Checkbox({
																name:"is_use_uploader",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"카테고리기능을 활성화 합니다.",
																disabled:true,
																flex:1,
																name:"use_category",
																listeners:{change:{fn:function(form) {
																	if (form.checked == true) {
																		Ext.getCmp("BoardForm").getForm().findField("is_use_category_option").enable();
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField("is_use_category_option").disable();
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_use_category",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"카테고리를 반드시 선택하도록 합니다.",
																disabled:true,
																flex:1,
																name:"use_category_option",
																disabled:true
															}),
															new Ext.form.Checkbox({
																name:"is_use_category_option",
																boxLabel:"일괄수정",
																disabled:true,
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"작성자에게 포인트를 지급할 수 있는 유료글작성기능을 활성화 합니다.",
																disabled:true,
																flex:1,
																name:"use_charge"
															}),
															new Ext.form.Checkbox({
																name:"is_use_charge",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"답변자에게 포인트를 지급할 수 있는 지식인기능을 활성화 합니다.",
																disabled:true,
																flex:1,
																name:"use_select"
															}),
															new Ext.form.Checkbox({
																name:"is_use_select",
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
														fieldLabel:"목록항목",
														layout:"hbox",
														items:[
															new Ext.form.CheckboxGroup({
																allowBlank:true,
																columns:3,
																flex:1,
																items:[
																	new Ext.form.Checkbox({
																		boxLabel:"순번",
																		name:"list_loopnum",
																		disabled:true
																	}),
																	new Ext.form.Checkbox({
																		boxLabel:"작성자",
																		name:"list_name",
																		disabled:true
																	}),
																	new Ext.form.Checkbox({
																		boxLabel:"작성일",
																		name:"list_reg_date",
																		disabled:true
																	}),
																	new Ext.form.Checkbox({
																		boxLabel:"조회",
																		name:"list_hit",
																		disabled:true
																	}),
																	new Ext.form.Checkbox({
																		boxLabel:"추천",
																		name:"list_vote",
																		disabled:true
																	}),
																	new Ext.form.Checkbox({
																		boxLabel:"평점",
																		name:"list_avgvote",
																		disabled:true
																	})
																]
															}),
															new Ext.form.Checkbox({
																name:"is_view_list",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	Ext.getCmp("BoardForm").getForm().findField("list_loopnum").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField("list_name").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField("list_reg_date").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField("list_hit").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField("list_vote").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField("list_avgvote").setDisabled(!form.checked);
																}}}
															})
														]
													}),
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
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"공지사항설정",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"view_notice",
																typeAhead:true,
																lazyRender:false,
																store:new Ext.data.ArrayStore({
																	fields:["value","display"],
																	data:[["ALL,INCLUDE","전체페이지에 출력 및 목록수 갯수에 포함"],["ALL,EXCLUDE","전체페이지에 출력 및 목록수 갯수에 미포함"],["FIRST,INCLUDE","첫페이지에 출력 및 목록수 갯수에 포함"],["FIRST,EXCLUDE","첫페이지에 출력 및 목록수 갯수에 미포함"]]
																}),
																flex:1,
																disabled:true,
																editable:false,
																mode:"local",
																displayField:"display",
																valueField:"value",
																triggerAction:"all"
															}),
															new Ext.form.Checkbox({
																name:"is_view_notice",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"글보기페이지 설정",
												items:[
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"글보기 페이지에 전체목록을 함께 보여줍니다.",
																name:"view_alllist",
																flex:1,
																disabled:true
															}),
															new Ext.form.Checkbox({
																name:"is_view_alllist",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:isModifyFunction}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Checkbox({
																boxLabel:"글보기 페이지에 이전글 다음글 정보를 보여줍니다.",
																name:"view_prevnext",
																flex:1,
																disabled:true
															}),
															new Ext.form.Checkbox({
																name:"is_view_prevnext",
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
														fieldLabel:"글작성포인트",
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
														fieldLabel:"답변채택포인트",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"select_point",
																value:100,
																width:100,
																disabled:true
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;포인트",
																flex:1
															}),
															new Ext.form.Checkbox({
																name:"is_select_point",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_list",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"게시물읽기",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_view",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"게시물작성",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_post",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_ment",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_modify",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_delete",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
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
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"답변채택",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_select_select",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_select",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_select",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"비밀글읽기",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_secret_select",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_secret",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_secret",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.form.FieldContainer({
														fieldLabel:"공지사항작성",
														layout:"hbox",
														items:[
															new Ext.form.ComboBox({
																name:"permission_notice_select",
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
																	Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
																}}}
															}),
															new Ext.form.TextField({
																name:"permission_notice",
																flex:1,
																allowBlank:true,
																disabled:true,
																listeners:{blur:{fn:function(form) {
																	if (Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue("");
																	} else {
																		Ext.getCmp("BoardForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
																	}
																}}}
															}),
															new Ext.form.Checkbox({
																name:"is_permission_notice",
																boxLabel:"일괄수정",
																style:{marginLeft:"5px"},
																listeners:{change:{fn:function(form) {
																	var temp = form.getName().split("_");
																	temp.shift();
																	var field = temp.join("_");
																	Ext.getCmp("BoardForm").getForm().findField(field+"_select").setDisabled(!form.checked);
																	Ext.getCmp("BoardForm").getForm().findField(field).setDisabled(!form.checked);
																}}}
															})
														]
													}),
													new Ext.Panel({
														border:false,
														autoScroll:true,
														bodyPadding:"0 0 5 105",
														html:'<div class="boxDefault">수정, 삭제, 비밀글권한의 경우 권한설정에 관계없이 글 작성자는 기본적으로 권한을 가지게 됩니다.<br />또한 최고관리자는 권한설정과 관계없이 모든 권한을 가지게 됩니다.</div>'
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
											BoardPermissionHelp();
										}
									}),
									'->',
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.getCmp("BoardForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=board&do=modify_all&bid="+bids.join(","),
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"선택게시판을 일괄수정하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListPanel").getStore().loadPage(1);
														Ext.getCmp("BoardWindow").close();
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
											Ext.getCmp("BoardWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
									Ext.getCmp("BoardForm").getForm().load({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php?action=board_all&bid="+bids.join(","),
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"데이터를 로딩중입니다.",
										success:function(form,action) {
											form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
											form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
											form.findField("permission_post").fireEvent("blur",form.findField("permission_post"));
											form.findField("permission_ment").fireEvent("blur",form.findField("permission_ment"));
											form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
											form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
											form.findField("permission_select").fireEvent("blur",form.findField("permission_select"));
											form.findField("permission_secret").fireEvent("blur",form.findField("permission_secret"));
											form.findField("permission_notice").fireEvent("blur",form.findField("permission_notice"));
										},
										failure:function(form,action) {
											Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}}}
							}).show();
						}
					},'-',{
						text:"선택 게시판 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"게시판을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var bids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								bids.push(checked[i].get("bid"));
							}
							
							Ext.Msg.show({title:"확인",msg:"게시판을 삭제하면 해당 게시판의 모든 글과 자료가 삭제됩니다.<br />게시판을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("게시판을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
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
										params:{"action":"board","do":"delete","bid":bids.join(",")}
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
						header:"게시판ID",
						dataIndex:"bid",
						sortable:true,
						width:150,
						renderer:function(value) { return '<span style="font-family:verdana; font-weight:bold; font-size:11px;">'+value+'</span>'; }
					},{
						header:"게시판명",
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
						header:"옵션",
						dataIndex:"option",
						sortable:false,
						width:270,
						renderer:function(value) {
							var sHTML = '<div style="font:0/0 arial;">';
							var option = value.split(",");

							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_category_'+option[0].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_uploader_'+option[1].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_ment_'+option[2].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_trackback_'+option[3].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_charge_'+option[4].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_select_'+option[5].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_rss_'+option[6].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '</div>';

							return sHTML;
						}
					},{
						header:"게시물수",
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
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
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