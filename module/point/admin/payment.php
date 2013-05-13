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
		
		menu.add('<b class="menu-title">'+record.data.value+'</b>');
		
		menu.add({
			text:"결제방법수정",
			handler:function() {
				PaymentFormFunction(record.data.idx);
			}
		});
		
		menu.add({
			text:"결제방식삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"결제방식을 삭제하면 해당 결제방식으로 결제된 모든 내역이 삭제됩니다.<br />결제방식을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("결제방식을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"payment","do":"delete","idx":record.data.idx}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}
	
	function PaymentFormFunction(idx) {
		new Ext.Window({
			id:"PaymentWindow",
			title:(idx ? "결제방식수정" : "결제방식추가"),
			width:400,
			layout:"fit",
			resizable:false,
			maximizable:true,
			items:[
				new Ext.form.FormPanel({
					id:"PaymentForm",
					border:false,
					fieldDefaults:{labelWidth:85,labelAlign:"right",anchor:"100%",allowBlank:false},
					bodyPadding:"5 5 0 5",
					items:[
						new Ext.form.FieldSet({
							title:"결제방식",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"결제방식",
									name:"type",
									typeAhead:true,
									lazyRender:false,
									store:new Ext.data.ArrayStore({
										fields:["value","display"],
										data:[["BANKING","무통장입금"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									listeners:{change:{fn:function(form) {
										if (form.getValue() == "BANKING") {
											Ext.getCmp("PaymentFormBANKING").show();
											Ext.getCmp("PaymentFormBANKING").enable();
										}
									}}}
								})
							]
						}),
						new Ext.form.FieldSet({
							id:"PaymentFormBANKING",
							disabled:true,
							hidden:true,
							title:"무통장입금 상세설정",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"은행명",
									name:"banking1",
									typeAhead:true,
									lazyRender:false,
									store:new Ext.data.ArrayStore({
										fields:["value"],
										data:[["기업은행"],["국민은행"],["우리은행"],["신한은행"],["하나은행"],["농협"],["단위농협"],["SC은행"],["외환은행"],["한국씨티은행"],["우체국"],["경남은행"],["광주은행"],["대구은행"],["도이치"],["부산은행"],["산림조합"],["산업은행"],["상호저축은행"],["새마을금고"],["수협"],["신협중앙회"],["전북은행"],["제주은행"],["BOA"],["HSBC"],["JP모간"],["교보증권"],["대신증권"],["대우증권"],["동부증권"],["동양증권"],["메리츠증권"],["미래에셋"],["부국증권"],["삼성증권"],["솔로몬투자증권"],["신영증권"],["신한금융투자"],["우리투자증권"],["유진투자증권"],["이트레이드증권"],["키움증권"],["하나대투"],["하이투자"],["한국투자"],["한화증권"],["현대증권"],["HMC증권"],["LIG투자증권"],["NH증권"],["SK증권"],["비엔피파리바은행"]]
									}),
									editable:false,
									mode:"local",
									displayField:"value",
									valueField:"value",
									triggerAction:"all"
								}),
								new Ext.form.TextField({
									fieldLabel:"계좌번호",
									name:"banking2",
									emptyText:"'-'를 포함하여 입력하여 주십시오."
								}),
								new Ext.form.TextField({
									fieldLabel:"예금자명",
									name:"banking3"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"결제방식 세부설정",
							items:[
								new Ext.form.Checkbox({
									fieldLabel:"활성화",
									name:"is_use",
									boxLabel:"결제방법을 활성화합니다.",
									checked:true
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"최소결제포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"min_point",
											width:100,
											value:0
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (0 : 제한없음)"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"최대결제포인트",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"max_point",
											width:100,
											value:0
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (0 : 제한없음)"
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
						Ext.getCmp("PaymentForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php?action=payment&do="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(idx ? "결제방식을 수정하고 있습니다." : "결제방식을 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().reload();
									Ext.getCmp("PaymentWindow").close();
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
						Ext.getCmp("PaymentWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					if (idx) {
						Ext.getCmp("PaymentForm").getForm().load({
							url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.get.php?action=payment&get=info&idx="+idx,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"데이터를 로딩중입니다.",
							success:function(form,action) {
							},
							failure:function(form,action) {
								Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							}
						});
					} else {
						Ext.getCmp("PaymentForm").getForm().findField("type").setValue("BANKING");
					}
				}},
				resize:{fn:function(window) {
					window.center();
				}}
			}
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
				text:"결제방식추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_add.png",
				handler:function() {
					PaymentFormFunction();
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 결제방식을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 결제방식 활성화",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"결제방식을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"결제방식을 활성화하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("결제방식을 활성화하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"payment","do":"activemode","value":"TRUE","idx":idxs.join(",")}
									});
								}
							}});
						}
					},{
						text:"선택 결제방식 비활성화",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"결제방식을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"결제방식을 비활성화하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("결제방식을 비활성화하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"payment","do":"activemode","value":"TRUE","idx":idxs.join(",")}
									});
								}
							}});
						}
					},'-',{
						text:"선택 결제방식 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"결제방식을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"결제방식을 삭제하면 해당 결제방식으로 결제된 모든 내역이 삭제됩니다.<br />결제방식을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("결제방식을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"payment","do":"delete","idx":idxs.join(",")}
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
						header:"상태",
						dataIndex:"is_use",
						sortable:true,
						width:80,
						renderer:function(value) { 
							if (value == "TRUE") return '<span style="color:blue;">활성화중</span>';
							else return '<span style="color:red;">비활성화</span>';
						}
					},{
						header:"결제방식",
						dataIndex:"type",
						sortable:true,
						width:100,
						renderer:function(value) { 
							var type = {BANKING:"무통장입금"};
							return type[value];
						}
					},{
						header:"결제정보",
						dataIndex:"value",
						sortable:true,
						minWidth:150,
						flex:1
					},{
						header:"최소결제",
						dataIndex:"min_point",
						sortable:true,
						width:120,
						renderer:function(value) {
							if (value == "0") return "제한없음";
							else return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+' points</div>';
						}
					},{
						header:"최대결제",
						dataIndex:"max_point",
						sortable:true,
						width:120,
						renderer:function(value) {
							if (value == "0") return "제한없음";
							else return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+' points</div>';
						}
					}
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"payment",get:"list"}
					},
					remoteSort:false,
					sorters:[{property:"idx",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					groupField:"is_use",
					groupDir:"DESC",
					fields:["idx","type","value","min_point","max_point","is_use"]
				}),
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				features:[{
					ftype:'grouping',
					groupHeaderTpl:'<tpl if="name == \'TRUE\'">상태 : 활성화중<tpl else>상태 : 비활성화</tpl>',
					hideGroupedHeader:false,
					enableGroupingMenu:false
				}],
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