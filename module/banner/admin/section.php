<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"section",get:"list"}
		},
		remoteSort:true,
		sorters:[{property:"code",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["code","title","type","point","filetype","width","height","allow_user","auto_active"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'('+record.data.code+')</b>');
		
		menu.add({
			text:"배너영역수정",
			handler:function() {
				SectionFormFunction(record.data.code);
			}
		});

		menu.add('-');
		
		menu.add({
			text:"배너영역삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"배너영역을 삭제하면 해당 배너영역의 모든 배너가 삭제됩니다.<br />배너영역을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("배너영역을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
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
							params:{"action":"section","do":"delete","code":record.data.code}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function SectionFormFunction(code) {
		if (code) {
			var title = "배너영역수정";
		} else {
			var title = "배너영역추가";
		}
		
		new Ext.Window({
			id:"SectionWindow",
			title:title,
			width:650,
			modal:true,
			minWidth:650,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"SectionForm",
					bodyPadding:"10 10 5 10",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.TextField({
									fieldLabel:"배너영역코드명",
									name:"code",
									disabled:(code ? true : false),
									emptyText:"영문 및 숫자로만 구성된 코드명"
								}),
								new Ext.form.TextField({
									fieldLabel:"배너영역명",
									name:"title"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"광고유형 및 광고비설정",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"광고유형",
									name:"type",
									store:new Ext.data.ArrayStore({
										fields:["value","display"],
										data:[["CPC","CPC방식 (유효클릭당 정해진 배너비만큼 차감되어 배너선입금이 모두 소진되면 노출중단)"],["CPM","CPM방식 (30일동안 정해진 배너비로 고정적으로 노출 (클릭수와는 무관, 정액제)"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									value:"CPC"
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"광고비",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"point",
											width:80,
											value:10
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (CPC방식 : 유호클릭당 비용, CPM방식 : 30일비용)",
											flex:1
										})
									]
								}),
								new Ext.Panel({
									border:false,
									bodyPadding:"0 0 10 0",
									html:'<div class="boxDefault">배너유형 및 배너비설정은 설정이전에 등록된 배너에 대해서는 적용되지 않습니다.</div>'
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"배너파일설정",
							items:[
								new Ext.form.CheckboxGroup({
									fieldLabel:"배너허용종류",
									columns:3,
									items:[
										new Ext.form.Checkbox({
											boxLabel:"이미지",
											name:"IMG"
										}),
										new Ext.form.Checkbox({
											boxLabel:"플래시",
											name:"SWF"
										}),
										new Ext.form.Checkbox({
											boxLabel:"텍스트",
											name:"TEXT"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"배너크기",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"width",
											minValue:10,
											value:200,
											width:80
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;픽셀(가로) X&nbsp;"
										}),
										new Ext.form.NumberField({
											name:"height",
											minValue:10,
											value:200,
											width:80
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;픽셀(세로)"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"배너등록설정",
							items:[
								new Ext.form.Checkbox({
									fieldLabel:"일반회원등록여부",
									name:"allow_user",
									boxLabel:"일반회원도 포인트를 이용하여 등록가능합니다."
								}),
								new Ext.form.Checkbox({
									fieldLabel:"자동활성화",
									name:"auto_active",
									boxLabel:"유저가 등록한 배너에 대해 자동으로 승인하여 노출합니다. (체크해제시 관리자 승인필요)"
								}),
								new Ext.Panel({
									border:false,
									bodyPadding:"0 0 10 0",
									html:'<div class="boxDefault">배너계약을 통해 배너를 집행하고자 한다면 체크해제한 뒤 배너관리에서 계약건입력후 배너노출이 가능합니다.</div>'
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"최대등록갯수",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"limit",
											width:80,
											value:10
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;개 (최대 활성화 가능한 배너갯수, 초과시 배너등록불가)"
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
						Ext.getCmp("SectionForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php?action=section&do="+(code ? "modify&code="+code : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(code ? "배너영역을 수정하고 있습니다." : "배너영역을 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(code ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("SectionWindow").close();
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
						Ext.getCmp("SectionWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (code) {
					Ext.getCmp("SectionForm").getForm().load({
						url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php?action=section&get=data&code="+(code ? code : ""),
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"데이터를 로딩중입니다.",
						success:function(form,action) {
							
						},
						failure:function(form,action) {
							Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					});
				}
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"배너영역관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"배너영역추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_layout_add.png",
				handler:function() {
					SectionFormFunction();
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
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 배너영역을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 배너영역 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"배너영역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var codes = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								codes.push(checked[i].get("code"));
							}
							
							Ext.Msg.show({title:"확인",msg:"배너영역을 삭제하면 해당 배너영역의 모든 배너가 삭제됩니다.<br />배너영역을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("배너영역을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
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
										params:{"action":"section","do":"delete","code":codes.join(",")}
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
						header:"영역코드",
						dataIndex:"code",
						sortable:true,
						width:150,
						renderer:function(value) { return '<span style="font-family:verdana; font-weight:bold; font-size:11px;">'+value+'</span>'; }
					},{
						header:"영역이름",
						dataIndex:"title",
						sortable:true,
						minWidth:150,
						flex:1
					},{
						header:"형식",
						dataIndex:"type",
						sortable:true,
						width:60
					},{
						header:"포인트",
						dataIndex:"point",
						sortable:true,
						width:100,
						renderer:function(value) {
							return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
						}
					},{
						header:"파일형식",
						dataIndex:"filetype",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"가로크기",
						dataIndex:"width",
						sortable:true,
						width:80,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+'px</div>';
						}
					},{
						header:"세로크기",
						dataIndex:"height",
						sortable:true,
						width:80,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+'px</div>';
						}
					},{
						header:"옵션",
						sortable:false,
						width:180,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (record.data.allow_user == "TRUE") sHTML+= '<span style="color:blue;">일반회원등록가능</span>';
							else sHTML+= '<span style="color:red;">일반회원등록불가</span>';
							sHTML+= " / ";
							if (record.data.auto_active == "TRUE") sHTML+= '<span style="color:blue;">배너자동승인</span>';
							else sHTML+= '<span style="color:red;">관리자승인필요</span>';
							
							return sHTML;
						}
					},{
						header:"활성/최대배너수",
						dataIndex:"adnum",
						sortable:false,
						width:120,
						renderer:function(value) {
							'<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+'EA</div>'
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
						
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>