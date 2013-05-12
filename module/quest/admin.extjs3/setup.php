<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["bid","title","width","group","skin","option",{name:"postnum",type:"int"},"last_date"]
		}),
		remoteSort:true,
		sortInfo:{field:"bid",dir:"asc"},
		baseParams:{action:"list",keyword:""}
	});

	function BoardFormFunction(bid) {
		if (bid) {
			var title = "게시판설정";
		} else {
			var title = "게시판생성";
			var bid = "";
		}

		var SkinStore = new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:["skin"]
			}),
			baseParams:{action:"skin"}
		});
		SkinStore.load();

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
			style:"background:#FFFFFF;",
			items:[
				new Ext.form.FormPanel({
					id:"BoardForm",
					labelAlign:"right",
					labelWidth:85,
					border:false,
					autoWidth:true,
					autoScroll:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["title","skin","width","list_loopnum","list_title","list_name","list_reg_date","list_hit","list_vote","list_avgvote","listnum","pagenum","view_notice","alllist","prevnext","use_ment","use_trackback","use_uploader","use_category","use_category_option","use_charge","use_select","use_rss","rss_limit","rss_post_limit","rss_link","rss_description","rss_language","permission_list","permission_view","permission_post","permission_ment","permission_modify","permission_delete","permission_select","permission_secret","permission_notice"]
					),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.Hidden({
									name:"do"
								}),
								new Ext.form.TextField({
									fieldLabel:"게시판 ID",
									name:"bid",
									width:100,
									readOnly:(bid ? true : false),
									allowBlank:true
								}),
								new Ext.form.TextField({
									fieldLabel:"타이틀",
									name:"title",
									width:200,
									allowBlank:true
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"디자인정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"스킨선택",
									name:"skin",
									width:150,
									typeAhead:true,
									lazyRender:false,
									listClass:"x-combo-list-small",
									store:SkinStore,
									editable:false,
									mode:"local",
									displayField:"skin",
									valueField:"skin",
									triggerAction:"all",
									emptyText:"스킨을 선택하세요.",
									allowBlank:false
								}),
								new Ext.form.TextField({
									fieldLabel:"가로크기",
									name:"width",
									width:200,
									allowBlank:true,
									emptyText:"%단위 또는 px단위로 입력하세요.",
									value:"100%"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"옵션",
							autoWidth:true,
							autoHeight:true,
							defaults:{hideLabel:true,msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.Checkbox({
									boxLabel:"댓글기능을 활성화 합니다.",
									name:"use_ment",
									checked:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"트랙백기능을 활성화 합니다.",
									name:"use_trackback",
									checked:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"파일업로드 기능을 활성화 합니다. (멀티업로드)",
									name:"use_uploader",
									checked:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"카테고리기능을 활성화 합니다.",
									name:"use_category",
									listeners:{check:{fn:function(form) {
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
									disabled:true,
									checked:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"작성자에게 포인트를 지급할 수 있는 유료글작성기능을 활성화 합니다.",
									name:"use_charge"
								}),
								new Ext.form.Checkbox({
									boxLabel:"답변자에게 포인트를 지급할 수 있는 지식인기능을 활성화 합니다.",
									name:"use_select"
								}),
								new Ext.form.Checkbox({
									boxLabel:"RSS기능을 사용합니다. (글보기 권한과 관계없이 RSS가 제공됩니다)",
									name:"use_rss",
									listeners:{check:{fn:function(form) {
										if (form.checked == true) {
											Ext.getCmp("BoardForm").getForm().findField("rss_limit").enable();
											Ext.getCmp("BoardForm").getForm().findField("rss_post_limit").enable();
											Ext.getCmp("BoardForm").getForm().findField("rss_link").enable();
											Ext.getCmp("BoardForm").getForm().findField("rss_description").enable();
											Ext.getCmp("BoardForm").getForm().findField("rss_language").enable();
										} else {
											Ext.getCmp("BoardForm").getForm().findField("rss_limit").disable();
											Ext.getCmp("BoardForm").getForm().findField("rss_post_limit").disable();
											Ext.getCmp("BoardForm").getForm().findField("rss_link").disable();
											Ext.getCmp("BoardForm").getForm().findField("rss_description").disable();
											Ext.getCmp("BoardForm").getForm().findField("rss_language").disable();
										}
									}}}
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"목록페이지 설정",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.CheckboxGroup({
									fieldLabel:"목록항목",
									columns:3,
									width:400,
									items:[
										new Ext.form.Checkbox({
											boxLabel:"순번",
											name:"list_loopnum",
											value:"TRUE",
											checked:true,
											style:"margin-top:4px;"
										}),
										new Ext.form.Checkbox({
											boxLabel:"작성자",
											name:"list_name",
											checked:true,
											style:"margin-top:4px;"
										}),
										new Ext.form.Checkbox({
											boxLabel:"작성일",
											name:"list_reg_date",
											checked:true,
											style:"margin-top:4px;"
										}),
										new Ext.form.Checkbox({
											boxLabel:"조회",
											name:"list_hit",
											checked:true
										}),
										new Ext.form.Checkbox({
											boxLabel:"추천",
											name:"list_vote",
											checked:true
										}),
										new Ext.form.Checkbox({
											boxLabel:"평점",
											name:"list_avgvote"
										})
									],
									listeners:{afterRender:{fn:function() {
										Ext.form.CheckboxGroup.superclass.afterRender.apply(this, arguments);
										var form = this.findParentByType('form').getForm();
										form.add.apply(form,this.items.items);
									}}}
								}),
								new Ext.form.NumberField({
									fieldLabel:"목록수",
									name:"listnum",
									width:100,
									value:30
								}),
								new Ext.form.NumberField({
									fieldLabel:"페이지수",
									name:"pagenum",
									width:100,
									value:10
								}),
								new Ext.form.ComboBox({
									fieldLabel:"공지사항설정",
									hiddenName:"view_notice",
									width:300,
									typeAhead:true,
									lazyRender:false,
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["value","display"],
										data:[["ALL,INCLUDE","전체페이지에 출력 및 목록수 갯수에 포함"],["ALL,EXCLUDE","전체페이지에 출력 및 목록수 갯수에 미포함"],["FIRST,INCLUDE","첫페이지에 출력 및 목록수 갯수에 포함"],["FIRST,EXCLUDE","첫페이지에 출력 및 목록수 갯수에 미포함"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									allowBlank:false
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"글보기페이지 설정",
							autoWidth:true,
							autoHeight:true,
							defaults:{hideLabel:true,msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.Checkbox({
									boxLabel:"글보기 페이지에 전체목록을 함께 보여줍니다.",
									name:"alllist",
									checked:true
								}),
								new Ext.form.Checkbox({
									boxLabel:"글보기 페이지에 이전글 다음글 정보를 보여줍니다.",
									name:"prevnext",
									checked:true
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"포인트설정",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.TextField({
									fieldLabel:"글작성포인트",
									name:"post_point",
									width:100,
									allowBlank:false,
									value:30
								}),
								new Ext.form.TextField({
									fieldLabel:"댓글작성포인트",
									name:"ment_point",
									width:100,
									allowBlank:false,
									value:10
								}),
								new Ext.form.TextField({
									fieldLabel:"답변채택포인트",
									name:"select_point",
									width:100,
									allowBlank:false,
									value:50
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"RSS옵션",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.NumberField({
									fieldLabel:"글 갯수",
									name:"rss_limit",
									allow_blank:false,
									width:100,
									textAlign:"right",
									disabled:true
								}),
								new Ext.form.NumberField({
									fieldLabel:"글 내용 제한",
									name:"rss_post_limit",
									allow_blank:false,
									width:100,
									textAlign:"right",
									disabled:true
								}),
								new Ext.Panel({
									border:false,
									style:"padding-left:90px; margin-bottom:3px;",
									html:'<div class="boxDefault">글의 내용을 일부만 공개할 경우 공개할 글자수를 지정하세요.<br />"0"으로 지정하면 전체내용을 공개합니다.</div>'
								}),
								new Ext.form.TextField({
									fieldLabel:"게시판주소",
									name:"rss_link",
									allow_blank:false,
									width:400,
									disabled:true
								}),
								new Ext.Panel({
									border:false,
									style:"padding-left:90px; margin-bottom:3px;",
									html:'<div class="boxDefault">해당 게시판의 주소를 입력하세요. {$HTTP_HOST}변수를 사용하여, 현재 도메인을 지정할 수 있습니다.</div>'
								}),
								new Ext.form.TextField({
									fieldLabel:"RSS소개",
									name:"rss_description",
									width:400,
									disabled:true
								}),
								new Ext.form.ComboBox({
									fieldLabel:"RSS언어",
									hiddenName:"rss_language",
									width:150,
									typeAhead:true,
									lazyRender:false,
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["value","display"],
										data:[["ko","한국어"],["en","영어"],["jp","일본어"],["cn","중국어"],["fr","프랑스어"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									emptyText:"RSS언어를 선택하세요.",
									allowBlank:false,
									disabled:true
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"권한설정",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"목록보기",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_list_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"true",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_list",
											width:240,
											allowBlank:true,
											value:"true",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"게시물읽기",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_view_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"true",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_view",
											width:240,
											allowBlank:true,
											value:"true",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"게시물작성",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_post_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"true",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_post",
											width:240,
											allowBlank:true,
											value:"true",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"댓글작성",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_ment_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"true",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_ment",
											width:240,
											allowBlank:true,
											value:"true",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"수정",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_modify_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"{$member.type} == 'ADMINISTRATOR'",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_modify",
											width:240,
											allowBlank:true,
											value:"{$member.type} == 'ADMINISTRATOR'",
											value:"{$member.type} == 'ADMINISTRATOR'",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"삭제",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_delete_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"{$member.type} == 'ADMINISTRATOR'",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_delete",
											width:240,
											allowBlank:true,
											value:"{$member.type} == 'ADMINISTRATOR'",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"답변채택",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_select_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"{$member.type} == 'ADMINISTRATOR'",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_select",
											width:240,
											allowBlank:true,
											value:"{$member.type} == 'ADMINISTRATOR'",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"비밀글읽기",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_secret_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"{$member.type} == 'ADMINISTRATOR'",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_secret",
											width:240,
											allowBlank:true,
											value:"{$member.type} == 'ADMINISTRATOR'",
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
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"공지사항작성",
									width:400,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"permission_notice_select",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["display","value"],
												data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
											}),
											width:150,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											value:"{$member.type} == 'ADMINISTRATOR'",
											listeners:{select:{fn:function(form) {
												Ext.getCmp("BoardForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
											}}}
										}),
										new Ext.form.TextField({
											name:"permission_notice",
											width:240,
											allowBlank:true,
											value:"{$member.type} == 'ADMINISTRATOR'",
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
									style:"padding-left:90px; margin-bottom:3px;",
									html:'<div class="boxDefault">수정, 삭제, 비밀글권한의 경우 권한설정에 관계없이 글 작성자는 기본적으로 권한을 가지게 됩니다.<br />또한 최고관리자는 권한설정과 관계없이 모든 권한을 가지게 됩니다.</div>'
								})
							]
						}),
					],
					listeners:{
						render:{fn:function() {
							if (bid) {
								Ext.getCmp("BoardForm").getForm().findField("bid").setValue(bid);
								Ext.getCmp("BoardForm").getForm().findField("do").setValue("modify");
								Ext.getCmp("BoardForm").load({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php?action=board&bid="+bid,waitMsg:"정보를 로딩중입니다."});
							} else {
								Ext.getCmp("BoardForm").getForm().findField("do").setValue("add");
							}
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "load") {
								form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
								form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
								form.findField("permission_post").fireEvent("blur",form.findField("permission_post"));
								form.findField("permission_ment").fireEvent("blur",form.findField("permission_ment"));
								form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
								form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
								form.findField("permission_select").fireEvent("blur",form.findField("permission_select"));
								form.findField("permission_secret").fireEvent("blur",form.findField("permission_secret"));
								form.findField("permission_notice").fireEvent("blur",form.findField("permission_notice"));
							}

							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(){ Ext.getCmp("ListPanel").getStore().reload(); Ext.getCmp("BoardWindow").close(); }});
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"권한설정도움말",
					icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_help.png",
					handler:function() {
						BoardPermissionHelp();
					}
				}),
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
					handler:function() {
						Ext.getCmp("BoardForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=board",waitMsg:"데이터를 전송중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("BoardWindow").close();
					}
				})
			]
		}).show();
	}

	function BoardPermissionHelp() {
		if (Ext.getCmp("CategoryPermissionHelp")) return;

		new Ext.Window({
			id:"CategoryPermissionHelp",
			title:"카테고리권한설정 도움말",
			width:500,
			height:400,
			layout:"fit",
			resizeable:false,
			items:[
				new Ext.Panel({
					border:false,
					autoScroll:true,
					style:"background:#FFFFFF;font-family:돋움; font-size:11px; line-height:1.6;",
					html:'<div style="padding:5px;"><div class="boxDefault">권한설정은 해당 카테고리에 게시물을 작성할 수 있는 권한을 설정하는 것입니다. 이 권한설정은 산술적 수식으로 표현됩니다. 아래의 변수값들을 이용하여, 연산식으로 입력하시면 됩니다.</div><br /><b>{$member.user_id} :</b> 회원아이디<br /><b>{$member.level} :</b> 회원레벨<br /><b>{$member.type} :</b> 회원종류(ADMINISTRATOR, MODERATOR, MEMBER)<br /><br /><b>입력예</b><br />1. 회원레벨 5 초과인 사람만 허용<br />{$member.level} > 5<br /><br />2. 회원레벨이 5 이상이고, 10 이하인 사람만 허용<br />{$member.level} >= 5 && {$member.level} <= 10<br /><br />3. 회원종류가 MEMBER이고, 회원레벨이 5이상이거나 또는 회원레벨이 10이상인 경우<br />({$member.type} == "MEMBER" && {$member.level} >= 5) || ({$member.level} >= 10)<br /><br /><div class="boxDefault">위의 예제와 같이 괄호와, AND(&&)연산자, OR(||)연산자를 이용하여 정교한 권한을 설정할 수 있습니다.</div>'
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
			style:"background:#FFFFFF;",
			items:[
				new Ext.grid.EditorGridPanel({
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
									height:140,
									modal:true,
									resizable:false,
									layout:"fit",
									items:[
										new Ext.form.FormPanel({
											id:"CategoryAddForm",
											labelAlign:"right",
											labelWidth:85,
											border:false,
											errorReader:new Ext.form.XmlErrorReader(),
											style:"background:#FFFFFF; padding:10px;",
											items:[
												new Ext.form.TextField({
													fieldLabel:"카테고리명",
													name:"title",
													width:200,
													allowBlank:false
												}),
												new Ext.form.TextField({
													fieldLabel:"권한설정",
													name:"permission",
													width:350,
													allowBlank:true
												})
											],
											listeners:{actioncomplete:{fn:function(form,action) {
												if (action.type == "submit") {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 카테고리를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
														Ext.getCmp("CategoryList").getStore().reload();
														if (button == "ok") {
															Ext.getCmp("CategoryAddForm").getForm().findField("title").setValue();
															Ext.getCmp("CategoryAddForm").getForm().findField("title").clearInvalid();
															Ext.getCmp("CategoryAddForm").getForm().findField("permission").setValue();
															Ext.getCmp("CategoryAddForm").getForm().findField("title").focus();
														} else {
															Ext.getCmp("CategoryAddWindow").close();
														}
													}});

												}
											}}}
										})
									],
									buttons:[
										new Ext.Button({
											text:"권한설정도움말",
											icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_help.png",
											handler:function() {
												BoardPermissionHelp();
											}
										}),
										new Ext.Button({
											text:"확인",
											icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
											handler:function() {
												Ext.getCmp("CategoryAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=category&do=add&bid="+bid,waitMsg:"데이터를 전송중입니다."});
											}
										}),
										new Ext.Button({
											text:"취소",
											icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_cross.png",
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
								var checked = Ext.getCmp("CategoryList").selModel.getSelections();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								var idxs = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									idxs.push(checked[i].get("category"));
								}
								var idx = idxs.join(",");

								new Ext.Window({
									id:"CategoryDeleteWindow",
									title:"카테고리삭제",
									width:500,
									height:110,
									modal:true,
									resizable:false,
									layout:"fit",
									items:[
										new Ext.form.FormPanel({
											id:"CategoryDeleteForm",
											labelAlign:"right",
											labelWidth:85,
											border:false,
											errorReader:new Ext.form.XmlErrorReader(),
											style:"background:#FFFFFF; padding:10px;",
											items:[
												new Ext.form.Hidden({
													name:"idx",
													value:idx
												}),
												new Ext.form.ComboBox({
													fieldLabel:"게시물처리",
													hiddenName:"post",
													typeAhead:true,
													triggerAction:"all",
													lazyRender:true,
													store:new Ext.data.SimpleStore({
														fields:["type","text"],
														data:[
															["none","삭제할 카테고리에 속한 게시물에 대한 아무처리를 하지 않습니다."],
															["reset","삭제할 카테고리에 속한 게시물의 카테고리 정보를 초기화합니다."],
															["delete","삭제할 카테고리에 속한 게시물을 함께 삭제합니다."]
														]
													}),
													width:350,
													editable:false,
													mode:"local",
													displayField:"text",
													valueField:"type",
													value:"none"
												})
											],
											listeners:{actioncomplete:{fn:function(form,action) {
												if (action.type == "submit") {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(){
														Ext.getCmp("CategoryList").getStore().reload();
														Ext.getCmp("CategoryDeleteWindow").close();
													}});
												}
											}}}
										})
									],
									buttons:[
										new Ext.Button({
											text:"확인",
											icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
											handler:function() {
												Ext.Msg.show({title:"안내",msg:"정말 선택한 카테고리를 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
													if (button == "ok") {
														Ext.getCmp("CategoryDeleteForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=category&do=delete&bid="+bid,waitMsg:"데이터를 전송중입니다."});
													}
												}});
											}
										}),
										new Ext.Button({
											text:"취소",
											icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_cross.png",
											handler:function() {
												Ext.getCmp("CategoryDeleteWindow").close();
											}
										})
									]
								}).show();
							}
						}),
						'-',
						new Ext.Button({
							text:"위로 이동",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_up.png",
							handler:function() {
								var checked = Ext.getCmp("CategoryList").selModel.getSelections();

								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
									return false;
								}

								var selecter = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									var sort = checked[i].get("order");
									if (sort != 0) {
										Ext.getCmp("CategoryList").getStore().getAt(sort).set("order",sort-1);
										Ext.getCmp("CategoryList").getStore().getAt(sort-1).set("order",sort);

										selecter.push(sort-1);
										Ext.getCmp("CategoryList").getStore().sort("order","ASC");
									} else {
										return false;
									}
								}

								for (var i=0, loop=selecter.length;i<loop;i++) {
									Ext.getCmp("CategoryList").selModel.selectRow(selecter[i]);
								}
							}
						}),
						new Ext.Button({
							text:"아래로 이동",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_down.png",
							handler:function() {
								var checked = Ext.getCmp("CategoryList").selModel.getSelections();

								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
									return false;
								}

								var selecter = new Array();
								for (var i=checked.length-1;i>=0;i--) {
									var sort = checked[i].get("order");
									if (sort != Ext.getCmp("CategoryList").getStore().getCount()-1) {
										Ext.getCmp("CategoryList").getStore().getAt(sort).set("order",sort+1);
										Ext.getCmp("CategoryList").getStore().getAt(sort+1).set("order",sort);

										selecter.push(sort+1);
										Ext.getCmp("CategoryList").getStore().sort("order","ASC");
									} else {
										return false;
									}
								}

								for (var i=0, loop=selecter.length;i<loop;i++) {
									Ext.getCmp("CategoryList").selModel.selectRow(selecter[i]);
								}
							}
						})
					],
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							dataIndex:"category",
							hidden:true,
							hideable:false
						},{
							dataIndex:"order",
							sortable:true,
							hidden:true,
							hideable:false,
							width:20,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"카테고리명",
							dataIndex:"title",
							sortable:true,
							width:200,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"작성권한",
							dataIndex:"permission",
							sortable:true,
							width:320,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},
						new Ext.ux.grid.CheckboxSelectionModel()
					]),
					sm:new Ext.ux.grid.CheckboxSelectionModel(),
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["category","title","permission",{name:"order",type:"int"}]
						}),
						remoteSort:false,
						sortInfo:{field:"order",dir:"asc"},
						baseParams:{action:"category",bid:bid}
					})
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
					handler:function() {
						Ext.Msg.wait("처리중입니다.","Please Wait...");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
							success:function() {
								Ext.Msg.hide();
								Ext.getCmp("CategoryWindow").close();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"category","do":"modify","data":GetGridData(Ext.getCmp("CategoryList"))}
						});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("CategoryWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("CategoryList").getStore().load();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"게시판관리",
		layout:"fit",
		tbar:[
			new Ext.form.TextField({
				id:"Keyword",
				width:180,
				emptyText:"검색어를 입력하세요."
			}),
			' ',
			new Ext.Button({
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
				handler:function() {
					if (!Ext.getCmp("Keyword").getValue()) {
						Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
						return false;
					}
					store.baseParams.keyword = Ext.getCmp("Keyword").getValue();
					store.load({params:{start:0,limit:30}});
				}
			}),
			new Ext.Button({
				text:"검색취소",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magifier_out.png",
				handler:function() {
					Ext.getCmp("Keyword").setValue("");
					store.baseParams.keyword = "";
					store.load({params:{start:0,limit:30}});
				}
			}),
			'-',
			new Ext.Button({
				text:"게시판추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_add.png",
				handler:function() {
					BoardFormFunction();
				}
			}),
			new Ext.Button({
				text:"게시판삭제",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_delete.png",
				handler:function() {
					var checked = Ext.getCmp("ListPanel").selModel.getSelections();
					if (checked.length == 0) {
						Ext.Msg.show({title:"에러",msg:"삭제할 게시판을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
						return false;
					}

					Ext.Msg.show({title:"안내",msg:"선택한 게시판을 정말 삭제하시겠습니까?<br />게시판에 등록된 모든 게시물도 함께 삭제됩니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							var bids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								bids[i] = checked[i].get("bid");
							}
							var bid = bids.join(",");

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListPanel").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"board","do":"delete","bid":bid}
							});
						}
					}});
				}
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						header:"게시판ID",
						dataIndex:"bid",
						sortable:true,
						width:110,
						renderer:function(value) { return '<span style="font-family:verdana; font-weight:bold; font-size:11px;">'+value+'</span>'; }
					},{
						header:"그룹",
						dataIndex:"group",
						sortable:true,
						width:100
					},{
						header:"게시판명",
						dataIndex:"title",
						sortable:true,
						width:150
					},{
						header:"스킨명",
						dataIndex:"skin",
						sortable:true,
						width:150,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"옵션",
						dataIndex:"option",
						sortable:false,
						width:170,
						renderer:function(value) {
							var sHTML = '<div style="font:0/0 arial;">';
							var option = value.split(",");

							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_category_'+option[0].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_uploader_'+option[1].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_ment_'+option[2].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_trackback_'+option[3].toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '</div>';

							return sHTML;
						}
					},{
						header:"게시물수",
						dataIndex:"postnum",
						sortable:false,
						width:60,
						renderer:GridNumberFormat
					},{
						header:"최종등록일",
						dataIndex:"last_date",
						sortable:false,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+value+'</div>';
						}
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				store:store,
				sm:new Ext.grid.CheckboxSelectionModel(),
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:store,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx,e) {
						var data = grid.getStore().getAt(idx);
						if (data.get("width").indexOf("%") != -1) {
							var width = 800;
						} else {
							var width = parseInt(data.get("width"))+10;
						}

						new Ext.Window({
							title:data.get("title"),
							width:width,
							height:500,
							layout:"fit",
							maximizable:true,
							style:"background:#FFFFFF;",
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
						}).show();
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);
						var menu = new Ext.menu.Menu();
						var data = grid.getStore().getAt(idx);
						var option = data.get("option").split(",");

						menu.add('<b class="menu-title">'+data.get("title")+'</b>');
						menu.add({
							text:"게시판설정",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_gear.png",
							handler:function(item) {
								BoardFormFunction(data.get("bid"));
							}
						});

						if (option[0] == "TRUE") {
							menu.add({
								text:"카테고리설정",
								icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_category.png",
								handler:function(item) {
									BoardCategoryFunction(data.get("bid"));
								}
							});
						}

						menu.add({
							text:"게시판바로가기",
							icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_go.png",
							handler:function(item) {
								new Ext.Window({
									title:data.get("title"),
									width:width,
									height:500,
									layout:"fit",
									maximizable:true,
									style:"background:#FFFFFF;",
									html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
								}).show();
							}
						});
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	store.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>