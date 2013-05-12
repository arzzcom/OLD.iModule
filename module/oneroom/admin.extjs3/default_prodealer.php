<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	var DealerStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","agent","name","item","email","cellphone"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"dealer",get:"list",agent:"0",status:"ACTIVE"}
	});
	
	var DefaultProDealerStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","agent","region","name","item","email","cellphone"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"prodealer",status:"default"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기본지역전문가관리",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:"hbox",
				border:false,
				layoutConfig:{align:"stretch"},
				items:[
					new Ext.grid.GridPanel({
						id:"DealerList",
						title:"담당자",
						margins:"5 5 5 5",
						tbar:[
							new Ext.form.ComboBox({
								id:"Agent",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"agent","get":"list"}
								}),
								width:90,
								editable:false,
								mode:"local",
								displayField:"title",
								valueField:"idx",
								emptyText:"중개업소",
								listeners:{
									render:{fn:function(form) {
										form.getStore().load();
									}},
									select:{fn:function(form,selected) {
										Ext.getCmp("ListPanel").getStore().baseParams.agent = form.getValue();
										Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:50}});
									}}
								}
							}),
							'-',
							new Ext.form.TextField({
								id:"Keyword",
								width:100,
								emptyText:"검색어 입력"
							}),
							' ',
							new Ext.Button({
								text:"검색",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
								handler:function() {
									if (!Ext.getCmp("Keyword").getValue()) {
										Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}
									Ext.getCmp("ListPanel").getStore().baseParams.keyword = Ext.getCmp("Keyword").getValue();
									Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:50}});
								}
							}),
							new Ext.Button({
								text:"검색취소",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier_zoom_out.png",
								handler:function() {
									Ext.getCmp("Agent").setValue("");
									Ext.getCmp("Keyword").setValue("");
									Ext.getCmp("ListPanel").getStore().baseParams.agent = "";
									Ext.getCmp("ListPanel").getStore().baseParams.keyword = "";
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:DealerStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"중개업소",
								dataIndex:"agent",
								sortable:false,
								width:100
							},{
								header:"담당자명",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"완료/전체매물",
								dataIndex:"item",
								sortable:false,
								width:80,
								renderer:function(value) {
									var temp = value.split(",");
									return '<div style="text-align:right;"><span class="blue bold">'+GetNumberFormat(temp[0])+'</span> / '+GetNumberFormat(temp[1])+'</div>';
								}
							},{
								header:"이메일",
								dataIndex:"email",
								sortable:false,
								width:150
							},{
								header:"핸드폰번호",
								dataIndex:"cellphone",
								sortable:false,
								width:120
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:DealerStore,
						flex:1
					}),
					new Ext.grid.GridPanel({
						id:"ProDealerList",
						title:"기본지역전문가",
						margins:"5 5 5 0",
						tbar:[
							new Ext.Button({
								text:"지역전문가추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_right.png",
								handler:function() {
									var checked = Ext.getCmp("DealerList").selModel.getSelections();
									if (checked == 0) {
										Ext.Msg.show({title:"에러",msg:"추가할 담당자를 좌측목록에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										return;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									var idx = idxs.join(",");
									
									new Ext.Window({
										id:"AddProDealerWindow",
										title:"지역전문가추가",
										width:500,
										modal:true,
										resizable:false,
										layout:"fit",
										autoHeight:true,
										items:[
											new Ext.form.FormPanel({
												id:"AddProDealerForm",
												labelAlign:"right",
												labelWidth:85,
												border:false,
												autoHeight:true,
												style:"padding:10px; background:#FFFFFF;",
												errorReader:new Ext.form.XmlErrorReader(),
												items:[
													new Ext.form.Hidden({
														name:"idx",
														value:idx
													}),
													new Ext.form.CompositeField({
														labelAlign:"right",
														fieldLabel:"담당지역",
														width:500,
														items:[
															new Ext.form.ComboBox({
																hiddenName:"region1",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																allowBlank:false,
																store:new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:"lists",
																		totalProperty:"totalCount",
																		fields:["idx","title","sort"]
																	}),
																	remoteSort:false,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"region"}
																}),
																width:100,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"1차지역",
																listeners:{
																	render:{fn:function(form) {
																		form.getStore().load();
																	}},
																	select:{fn:function(form,selected) {
																		if (form.getValue() == "0") {
																			Ext.getCmp("AddProDealerForm").getForm().findField("region2").disable();
																			Ext.getCmp("AddProDealerForm").getForm().findField("region3").disable();
																		} else {
																			Ext.getCmp("AddProDealerForm").getForm().findField("region2").enable();
																			Ext.getCmp("AddProDealerForm").getForm().findField("region2").store.baseParams.parent = form.getValue();
																			Ext.getCmp("AddProDealerForm").getForm().findField("region2").store.load();
																		}
																	}}
																}
															}),
															new Ext.form.ComboBox({
																hiddenName:"region2",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																disabled:true,
																store:new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:"lists",
																		totalProperty:"totalCount",
																		fields:["idx","title","sort"]
																	}),
																	remoteSort:false,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"region","prent":"-1","is_none":"true"},
																	listeners:{load:{fn:function() {
																		Ext.getCmp("AddProDealerForm").getForm().findField("region2").setValue("");
																		Ext.getCmp("AddProDealerForm").getForm().findField("region2").clearInvalid();
																	}}}
																}),
																width:100,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"2차지역",
																listeners:{
																	select:{fn:function(form,selected) {
																		if (form.getValue() == "0") {
																			Ext.getCmp("AddProDealerForm").getForm().findField("region3").disable();
																		} else {
																			Ext.getCmp("AddProDealerForm").getForm().findField("region3").enable();
																			Ext.getCmp("AddProDealerForm").getForm().findField("region3").store.baseParams.parent = form.getValue();
																			Ext.getCmp("AddProDealerForm").getForm().findField("region3").store.load();
																		}
																	}}
																}
															}),
															new Ext.form.ComboBox({
																hiddenName:"region3",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																disabled:true,
																store:new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:"lists",
																		totalProperty:"totalCount",
																		fields:["idx","title","sort"]
																	}),
																	remoteSort:false,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"region","prent":"-1","is_none":"true"},
																	listeners:{load:{fn:function() {
																		Ext.getCmp("AddProDealerForm").getForm().findField("region3").setValue("");
																		Ext.getCmp("AddProDealerForm").getForm().findField("region3").clearInvalid();
																	}}}
																}),
																width:100,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"3차지역"
															})
														]
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.QUESTION});
														Ext.getCmp("ProDealerList").getStore().reload();
														Ext.getCmp("AddProDealerWindow").close();
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"등록하기",
												icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("AddProDealerForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=default_prodealer&do=add",waitMsg:"데이터를 저장중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("AddProDealerWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"지역전문가삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
								handler:function() {
									
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:DefaultProDealerStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"중개업소",
								dataIndex:"agent",
								sortable:false,
								width:100
							},{
								header:"담당자명",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"담당지역",
								dataIndex:"region",
								sortable:false,
								width:120
							},{
								header:"완료/전체매물",
								dataIndex:"item",
								sortable:false,
								width:80,
								renderer:function(value) {
									var temp = value.split(",");
									return '<div style="text-align:right;"><span class="blue bold">'+GetNumberFormat(temp[0])+'</span> / '+GetNumberFormat(temp[1])+'</div>';
								}
							},{
								header:"이메일",
								dataIndex:"email",
								sortable:false,
								width:150
							},{
								header:"핸드폰번호",
								dataIndex:"cellphone",
								sortable:false,
								width:120
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:DefaultProDealerStore,
						flex:1
					})
				]
			})
		],
		listeners:{render:{fn:function() {
			Ext.getCmp("DealerList").getStore().load({params:{start:0,limit:50}});
			Ext.getCmp("ProDealerList").getStore().load({params:{start:0,limit:50}});
		}}}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>