<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"monthly"}
	});

	var WorkspaceOrderStore = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},"workspace","title",{name:"item",type:"int"},"date","is_confirm","is_estimate","is_order","is_contract","is_complete","order","contract","estimate"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		groupField:"date",
		baseParams:{"action":"order","get":"workspace","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});

	var OrderStore = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},{name:"repto",type:"int"},"type","workspace","title","company",{name:"item",type:"int"},"date","is_order","is_complete","is_contract","contract"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		groupField:"date",
		baseParams:{"action":"order","get":"company","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});

	var ContractStore = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},{name:"ono",type:"int"},{name:"repto",type:"int"},"workspace","company","title",{name:"item",type:"int"},"date",{name:"price",type:"int"},"is_complete"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		groupField:"date",
		baseParams:{"action":"order","get":"contract","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});

	WorkspaceOrderStore.on("load",function(store) {
		SetCookie("iErpMonth",store.baseParams.date);
	});

	function WorkspaceFunction(idx,wno,title) {
		new Ext.Window({
			id:"WorkspaceWindow",
			title:(title ? title+" " : "")+"발주요청서 보기",
			width:950,
			height:550,
			modal:true,
			maximizable:true,
			layout:"border",
			items:[
				new Ext.grid.GridPanel({
					title:"품목보기",
					split:true,
					region:"center",
					id:"WorkspaceListPanel",
					margins:"5 5 0 5",
					border:true,
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							header:"그룹",
							dataIndex:"workgroup",
							width:80
						},{
							header:"공종명",
							dataIndex:"worktype",
							width:120
						},{
							header:"품명",
							dataIndex:"title",
							width:250,
							renderer:GridContractItemNotFound
						},{
							header:"규격",
							dataIndex:"size",
							width:100,
							renderer:GridContractItemNotFound
						},{
							header:"단위",
							dataIndex:"unit",
							width:60,
							renderer:GridContractItemNotFound
						},{
							header:"계약",
							dataIndex:"contract_ea",
							width:50,
							renderer:GridNumberFormat
						},{
							header:"발주",
							dataIndex:"order_ea",
							width:180,
							renderer:GridItemOrderEA
						},{
							header:"수량",
							dataIndex:"ea",
							width:50,
							renderer:GridNumberFormat
						}
					]),
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["code","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},{name:"ea",type:"float"},"order_ea",{name:"sort",type:"int"}]
						}),
						remoteSort:false,
						sortInfo:{field:"sort",direction:"ASC"},
						baseParams:{"action":"order","get":"workspace","mode":"data","idx":idx}
					}),
					sm:new Ext.grid.CheckboxSelectionModel()
				}),
				new Ext.form.FormPanel({
					id:"WorkspaceForm",
					title:"비고",
					region:"south",
					split:true,
					margins:"0 5 5 5",
					height:90,
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["etc"]
					),
					items:[
						new Ext.form.TextArea({
							name:"etc",
							hideLabel:true,
							style:"margin:5px;"
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("WorkspaceForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=workspace&mode=etc&idx="+idx,waitMsg:"정보를 로딩중입니다."});
						}},
						resize:{fn:function() {
							Ext.getCmp("WorkspaceForm").getForm().findField("etc").setWidth(Ext.getCmp("WorkspaceForm").getInnerWidth()-12);
							Ext.getCmp("WorkspaceForm").getForm().findField("etc").setHeight(Ext.getCmp("WorkspaceForm").getInnerHeight()-12);
						}},
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
					handler:function() {
						ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=workspace&get=order&idx="+data.get("idx"));
					}
				}),
				new Ext.Button({
					text:"본사품위서 작성",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry_add.png",
					hidden:(title ? false : true),
					handler:function() {
						OrderWrite(idx,wno);
					}
				}),
				new Ext.Button({
					text:"닫기",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("WorkspaceForm").getForm().findField("etc").setWidth(Ext.getCmp("WorkspaceForm").getInnerWidth()-12);
				Ext.getCmp("WorkspaceForm").getForm().findField("etc").setHeight(Ext.getCmp("WorkspaceForm").getInnerHeight()-12);
				Ext.getCmp("WorkspaceListPanel").getStore().load();
			}}}
		}).show();
	}

	function CompanyAddFunction(wno) {
		var AddressStore = new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Extjs.get.php?action=address"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:["zipcode","address","value"]
			})
		});

		AddressStore.on("load",function(store) {
			if (store.getCount() == 0) {
				Ext.Msg.show({title:"에러",msg:"주소를 찾을수 없습니다. 다시 검색하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").setValue(""); Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
			} else {
				Ext.getCmp("AddOutsourcingForm").getForm().findField("select_address").enable();
			}
		},AddressStore);

		new Ext.Window({
			title:"외주업체등록",
			id:"AddOutsourcingWindow",
			width:600,
			height:400,
			layout:"fit",
			modal:true,
			items:[
				new Ext.form.FormPanel({
					id:"AddOutsourcingForm",
					style:"background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:100,
					autoScroll:true,
					border:false,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							msgTarget:"side",
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									name:"title",
									fieldLabel:"업체명",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"company_number",
									fieldLabel:"사업자등록번호",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									allowBlank:false,
									validator:CheckCompanyNumber,
									listeners:{
										focus:{fn:FocusNumberOnly},
										blur:{fn:BlurCompanyNumberFormat}
									}
								}),
								new Ext.form.TextField({
									name:"type",
									fieldLabel:"업태/업종",
									width:200
								}),
								new Ext.form.TextField({
									name:"master",
									fieldLabel:"대표자",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"telephone",
									fieldLabel:"대표번호",
									width:200,
									allowBlank:false,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"주소",
							layout:"table",
							layoutConfig:{columns:2},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								{
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"우편번호검색",
											name:"search_zipcode",
											style:"padding-top:2px;",
											width:320,
											emptyText:"읍.면.동을 입력하세요.",
											enableKeyEvents:true,
											listeners:{keydown:{fn:function(form,e) {
												if (e.keyCode == 13) {
													if (!Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()) {
														Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
														return false;
													}
													AddressStore.load({params:{keyword:Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()}});
													e.stopEvent();
												}
											}}}
										})
									]
								},{
									border:false,
									items:[
										new Ext.Button({
											text:"우편번호검색",
											style:"margin-bottom:4px;",
											handler:function(p1,p2,p3) {
												if (!Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()) {
													Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
													return false;
												}
												AddressStore.load({params:{keyword:Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()}});
											}
										})
									]
								},{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.ComboBox({
											fieldLabel:"기본주소선택",
											name:"select_address",
											disabled:true,
											width:400,
											typeAhead:true,
											lazyRender:false,
											listClass:"x-combo-list-small",
											store:AddressStore,
											editable:false,
											mode:"local",
											displayField:"address",
											valueField:"value",
											emptyText:"기본주소를 선택하세요.",
											listeners:{
												select:{fn:function(object,store,idx) {
													Ext.getCmp("AddOutsourcingForm").getForm().findField("zipcode").setValue(store.get("zipcode"));
													Ext.getCmp("AddOutsourcingForm").getForm().findField("address1").setValue(store.get("value"));
													Ext.getCmp("AddOutsourcingForm").getForm().findField("address2").focus(false,100);
												}}
											}
										})
									]
								},{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"우편번호",
											name:"zipcode",
											width:100,
											allowBlank:true,
											readOnly:true
										})
									]
								},{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"기본주소",
											name:"address1",
											width:400,
											allowBlank:true,
											readOnly:true
										})
									]
								},{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"상세주소",
											name:"address2",
											width:400,
											allowBlank:true
										})
									]
								}
							]
						})
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("CompanyList").getStore().load();
							Ext.getCmp("AddOutsourcingWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						Ext.getCmp("AddOutsourcingForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.do.php?action=attend&do=add_outsourcing&wno="+wno,waitMsg:"외주업체를 추가중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("AddOutsourcingWindow").close();
					}
				})
			]
		}).show();
	}

	function OrderSelectWorkgroupType(grid,idx,e,wno) {
		GridContextmenuSelect(grid,idx);
		var data = grid.getStore().getAt(idx);
		var title = data.get("title") ? data.get("title") : "품명없음";
		var menu = new Ext.menu.Menu();
		menu.add('<b class="menu-title">'+title+'</b>');

		var record = Ext.data.Record.create([{name:"value",type:"int"},{name:"display",type:"string"}]);
		var checked = new Array();
		for (var i=0, loop=Ext.getCmp("ContractPanel").getStore().getCount();i<loop;i++) {
			if (!checked[Ext.getCmp("ContractPanel").getStore().getAt(i).get("gno")+","+Ext.getCmp("ContractPanel").getStore().getAt(i).get("tno")]) {
				checked[Ext.getCmp("ContractPanel").getStore().getAt(i).get("gno")+","+Ext.getCmp("ContractPanel").getStore().getAt(i).get("tno")] = true;
				menu.add({
					text:Ext.getCmp("ContractPanel").getStore().getAt(i).get("workgroup")+" > "+Ext.getCmp("ContractPanel").getStore().getAt(i).get("worktype"),
					checked:(data.get("gno") == Ext.getCmp("ContractPanel").getStore().getAt(i).get("gno") && data.get("tno") == Ext.getCmp("ContractPanel").getStore().getAt(i).get("tno")),
					gno:Ext.getCmp("ContractPanel").getStore().getAt(i).get("gno"),
					tno:Ext.getCmp("ContractPanel").getStore().getAt(i).get("tno"),
					group:"workgroup",
					handler:function(menu) {
						data.set("gno",menu.gno);
						data.set("tno",menu.tno);

						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php",
							success:function(XML) {
								if (AjaxResult(XML,"itemcode")) {
									data.set("code",AjaxResult(XML,"code"));
								} else {
									data.set("code","");
								}
							},
							failure:function() {
								data.set("code","");
							},
							headers:{},
							params:{"action":"item","get":"check","wno":wno,"gno":menu.gno,"tno":menu.tno,"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit")}
						});
					}
				});
			}
		}
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function OrderFunction(type,wno,idx,widx,title) {
		var ContractStore = new Ext.data.GroupingStore({
			proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:[{name:"sort",type:"int"},"group","itemcode","code","gno","tno","workgroup","worktype","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"exec_cost1",type:"int"},{name:"exec_cost2",type:"int"},{name:"exec_cost3",type:"int"},{name:"cost",type:"int"},{name:"exec_cost",type:"int"},{name:"price",type:"int"},{name:"exec_price",type:"int"},"avgcost1","avgcost2","avgcost3"]
			}),
			remoteSort:false,
			groupField:"group",
			sortInfo:{field:"sort",direction:"ASC"},
			baseParams:{"action":"order","get":"company","mode":"contract","idx":idx,"widx":widx}
		});
		ContractStore.load();

		var ContractPanel = new Ext.grid.GridPanel({
			id:"ContractPanel",
			title:"도급내역",
			region:"west",
			width:300,
			split:true,
			layout:"fit",
			margins:"0 0 0 5",
			collapsible:true,
			tbar:[
				new Ext.Button({
					id:"OrderWorkspaceCost1",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"재료비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("CompanyOrderPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("CompanyOrderPanel").store.getAt(tno).set("cost1",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(10,!pressed);

						if (pressed == false) {
							for (var i=0, loop=tab.getStore().getCount();i<loop;i++) {
								tab.getStore().getAt(i).set("cost1",0);
							}
							tab.getStore().commitChanges();
						}

						var store = Ext.getCmp("ContractPanel").getStore();
						for (var i=0, loop=store.getCount();i<loop;i++) {
							var cost = 0;
							var exec_cost = 0;
							if (Ext.getCmp("OrderWorkspaceCost1").pressed == true) {
								cost+= store.getAt(i).get("cost1");
								exec_cost+= store.getAt(i).get("exec_cost1");
							}
							if (Ext.getCmp("OrderWorkspaceCost2").pressed == true) {
								cost+= store.getAt(i).get("cost2");
								exec_cost+= store.getAt(i).get("exec_cost2");
							}
							if (Ext.getCmp("OrderWorkspaceCost3").pressed == true) {
								cost+= store.getAt(i).get("cost3");
								exec_cost+= store.getAt(i).get("exec_cost3");
							}

							store.getAt(i).set("cost",cost);
							store.getAt(i).set("exec_cost",exec_cost);
						}
						store.commitChanges();
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderWorkspaceCost2",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"노무비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("CompanyOrderPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("CompanyOrderPanel").store.getAt(tno).set("cost2",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(11,!pressed);

						if (pressed == false) {
							for (var i=0, loop=tab.getStore().getCount();i<loop;i++) {
								tab.getStore().getAt(i).set("cost2",0);
							}
							tab.getStore().commitChanges();
						}

						var store = Ext.getCmp("ContractPanel").getStore();
						for (var i=0, loop=store.getCount();i<loop;i++) {
							var cost = 0;
							var exec_cost = 0;
							if (Ext.getCmp("OrderWorkspaceCost1").pressed == true) {
								cost+= store.getAt(i).get("cost1");
								exec_cost+= store.getAt(i).get("exec_cost1");
							}
							if (Ext.getCmp("OrderWorkspaceCost2").pressed == true) {
								cost+= store.getAt(i).get("cost2");
								exec_cost+= store.getAt(i).get("exec_cost2");
							}
							if (Ext.getCmp("OrderWorkspaceCost3").pressed == true) {
								cost+= store.getAt(i).get("cost3");
								exec_cost+= store.getAt(i).get("exec_cost3");
							}

							store.getAt(i).set("cost",cost);
							store.getAt(i).set("exec_cost",exec_cost);
						}
						store.commitChanges();
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderWorkspaceCost3",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"경비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("CompanyOrderPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("CompanyOrderPanel").store.getAt(tno).set("cost3",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(12,!pressed);

						if (pressed == false) {
							for (var i=0, loop=tab.getStore().getCount();i<loop;i++) {
								tab.getStore().getAt(i).set("cost3",0);
							}
							tab.getStore().commitChanges();
						}

						var store = Ext.getCmp("ContractPanel").getStore();
						for (var i=0, loop=store.getCount();i<loop;i++) {
							var cost = 0;
							var exec_cost = 0;
							if (Ext.getCmp("OrderWorkspaceCost1").pressed == true) {
								cost+= store.getAt(i).get("cost1");
								exec_cost+= store.getAt(i).get("exec_cost1");
							}
							if (Ext.getCmp("OrderWorkspaceCost2").pressed == true) {
								cost+= store.getAt(i).get("cost2");
								exec_cost+= store.getAt(i).get("exec_cost2");
							}
							if (Ext.getCmp("OrderWorkspaceCost3").pressed == true) {
								cost+= store.getAt(i).get("cost3");
								exec_cost+= store.getAt(i).get("exec_cost3");
							}

							store.getAt(i).set("cost",cost);
							store.getAt(i).set("exec_cost",exec_cost);
						}
						store.commitChanges();
					}}}
				})
			],
			cm:new Ext.grid.ColumnModel([
				{
					dataIndex:"sort",
					hidden:true,
					hideable:false
				},{
					dataIndex:"group",
					hidden:true,
					hideable:false
				},{
					dataIndex:"gno",
					hidden:true,
					hideable:false
				},{
					dataIndex:"tno",
					hidden:true,
					hideable:false
				},{
					header:"품명",
					dataIndex:"title",
					width:180,
					summaryType:"data",
					renderer:GridContractItemNotFound,
					summaryRenderer:function(value) {
						return '도급:<span id="ContractPercent">0.00%</span>, 실행:<span id="ExecPercent">0.00%</span>';
					}
				},{
					header:"규격",
					dataIndex:"size",
					width:80,
					renderer:GridContractItemNotFound
				},{
					header:"단위",
					dataIndex:"unit",
					width:40,
					hidden:true,
					renderer:GridContractItemNotFound
				},{
					header:"수량",
					dataIndex:"ea",
					width:40,
					sortable:false,
					renderer:GridItemOrderEA
				},{
					dataIndex:"order_ea",
					hidden:true,
					hideable:false
				},{
					header:"단가",
					dataIndex:"cost",
					width:80,
					summaryType:"sum",
					renderer:GridNumberFormat
				},{
					header:"금액",
					dataIndex:"price",
					width:90,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.price = record.data.cost * record.data.ea;
						return GridNumberFormat(record.data.price);
					},
					summaryRenderer:GridNumberFormat
				}
			]),
			store:ContractStore,
			plugins:new Ext.ux.grid.GroupSummary(),
			view:new Ext.grid.GroupingView({
				enableGroupingMenu:false,
				hideGroupedColumn:true,
				showGroupName:false,
				enableNoGroups:false,
				headersDisabled:false,
				showGroupHeader:false
			})
		});

		var CompanyTabPanel = new Ext.Panel({
			title:"업체별 품위서",
			region:"center",
			layout:"fit",
			items:[
				new Ext.TabPanel({
					id:"CompanyOrderPanel",
					tabPosition:"bottom",
					activeTab:0,
					enableTabScroll:true,
					border:false,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:'lists',
							totalProperty:'totalCount',
							fields:["cno","wno","title","cost1","cost2","cost3"]
						}),
						remoteSort:false,
						sortInfo:{field:"title", direction:"ASC"},
						baseParams:{"action":"order","get":"company","mode":"tab","idx":idx},
						listeners:{
							add:{fn:function(store,data,tno) {
								OrderCreateTabPanel(type,idx,tno);
							}},
							load:{fn:function(store) {
								if (store.getCount() == 0) return;
								for (var i=0, loop=store.getCount();i<loop;i++) {
									OrderCreateTabPanel(type,idx,i);
								}
							}},
							remove:{fn:function(store) {
								if (store.getCount() == 0) {
									Ext.getCmp("CompanyOrderPanel").add(
										new Ext.Panel({
											id:"LoadingTab",
											title:"외주업체선택",
											html:'<div style="width:80%; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">업체를 선택하세요.</div>'
										})
									);
								}
							}}
						}
					}),
					tbar:[
						new Ext.form.ComboBox({
							id:"CompanyList",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","title"]
								}),
								remoteSort:false,
								sortInfo:{field:"title",direction:"ASC"},
								baseParams:{"action":"order","get":"company","mode":"outsourcing","wno":wno}
							}),
							width:180,
							editable:false,
							mode:"local",
							displayField:"title",
							valueField:"idx",
							emptyText:"외주업체선택",
							listeners:{render:{fn:function(form) {
								form.getStore().load();
							}}}
						}),
						' ',
						new Ext.Button({
							text:"작성",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
							handler:function() {
								if (!Ext.getCmp("CompanyList").getValue()) {
									Ext.Msg.show({title:"에러",msg:"업체를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
								var store = Ext.getCmp("CompanyOrderPanel").store;
								var record = Ext.data.Record.create(["cno","wno","title","cost1","cost2","cost3"]);

								if (type == "SERIES") {
									for (var i=0, loop=store.getCount();i<loop;i++) {
										if (store.getAt(i).get("cno") != Ext.getCmp("CompanyList").getValue()) {
											Ext.Msg.show({title:"에러",msg:"회차별 품의서는 동일업체만 선택가능합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}
									}
									store.add(new record({"cno":Ext.getCmp("CompanyList").getValue(),"wno":wno,"title":(store.getCount()+1)+"회("+Ext.getCmp("CompanyList").getRawValue()+")","cost1":"FALSE","cost2":"FALSE","cost3":"FALSE"}));
								} else {
									store.add(new record({"cno":Ext.getCmp("CompanyList").getValue(),"wno":wno,"title":Ext.getCmp("CompanyList").getRawValue(),"cost1":"FALSE","cost2":"FALSE","cost3":"FALSE"}));
								}
								Ext.getCmp("CompanyList").setValue("");
							}
						}),
						new Ext.Button({
							text:"외주업체등록",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_link.png",
							handler:function() {
								CompanyAddFunction(wno);
							}
						}),
						new Ext.Toolbar.Separator({hidden:(type == "EQUAL" ? true : false)}),
						new Ext.Button({
							id:"OrderCompanyLoad",
							disabled:true,
							text:"도급내역불러오기",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_right.png",
							hidden:(type == "EQUAL" ? true : false),
							handler:function() {
								Ext.Msg.show({title:"확인",msg:"품목을 불러오면, 해당 업체의 품목목록이 초기화됩니다.<br />좌측의 품목을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
									if (button == "ok") {
										Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().removeAll();
										for (var i=0, loop=Ext.getCmp("ContractPanel").getStore().getCount();i<loop;i++) {
											var data = Ext.getCmp("ContractPanel").getStore().getAt(i);
											var insert = new Array();
											insert["is_new"] = "FALSE";
											insert["group"] = " ";
											insert["code"] = data.get("code");
											insert["gno"] = data.get("gno");
											insert["tno"] = data.get("tno");
											insert["title"] = data.get("title");
											insert["size"] = data.get("size");
											insert["unit"] = data.get("unit");
											insert["ea"] = data.get("ea");
											insert["order_ea"] = data.get("order_ea");
											insert["avgcost1"] = data.get("avgcost1");
											insert["avgcost2"] = data.get("avgcost2");
											insert["avgcost3"] = data.get("avgcost3");
											insert["sort"] = data.get("sort");

											GridInsertRow(Ext.getCmp("CompanyOrderPanel").getActiveTab(),insert);
										}
									}
								}});
							}
						}),
						'-',
						new Ext.Button({
							id:"OrderCompanyAdd",
							disabled:true,
							text:"추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
							hidden:(type == "EQUAL" ? true : false),
							handler:function() {
								var insert = new Array();
								insert["is_new"] = "TRUE";
								insert["sort"] = 10000;
								insert["order_ea"] = "0,0,0,0";
								insert["group"] = " ";
								GridInsertRow(Ext.getCmp("CompanyOrderPanel").getActiveTab(),insert);

								for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().getCount();i<loop;i++) {
									Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().getAt(i).set("sort",i);
								}
							}
						}),
						new Ext.Button({
							id:"OrderCompanyDelete",
							disabled:true,
							text:"삭제",
							hidden:(type == "EQUAL" ? true : false),
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
							handler:function() {
								var checked = Ext.getCmp("CompanyOrderPanel").getActiveTab().selModel.getSelections();

								if (checked.length == 0) {
									Ext.Msg.show({title:"삭제오류",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								} else {
									for (var i=0, loop=checked.length;i<loop;i++) {
										Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().remove(checked[i]);
									}
									for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().getCount();i<loop;i++) {
										Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().getAt(i).set("sort",i);
									}
								}
							}
						})
					],
					items:[
						new Ext.Panel({
							id:"LoadingTab",
							title:"업체선택",
							html:'<div style="width:80%; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">업체를 선택하세요.</div>'
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("CompanyOrderPanel").store.load();
						}},
						tabchange:{fn:function(tabs,tab) {
							if (tab.getId() == "LoadingTab") {
								Ext.getCmp("OrderWorkspaceCost1").disable();
								Ext.getCmp("OrderWorkspaceCost2").disable();
								Ext.getCmp("OrderWorkspaceCost3").disable();
								Ext.getCmp("OrderCompanyLoad").disable();
								Ext.getCmp("OrderCompanyAdd").disable();
								Ext.getCmp("OrderCompanyDelete").disable();
							} else {
								Ext.getCmp("OrderWorkspaceCost1").enable();
								Ext.getCmp("OrderWorkspaceCost2").enable();
								Ext.getCmp("OrderWorkspaceCost3").enable();
								Ext.getCmp("OrderCompanyLoad").enable();
								Ext.getCmp("OrderCompanyAdd").enable();
								Ext.getCmp("OrderCompanyDelete").enable();

								for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
									if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
										var tno = i;
										break;
									}
								}

								var tabData = Ext.getCmp("CompanyOrderPanel").store.getAt(tno);

								if (tabData.get("cost1") == "TRUE") Ext.getCmp("OrderWorkspaceCost1").toggle(true);
								else Ext.getCmp("OrderWorkspaceCost1").toggle(false);

								if (tabData.get("cost2") == "TRUE") Ext.getCmp("OrderWorkspaceCost2").toggle(true);
								else Ext.getCmp("OrderWorkspaceCost2").toggle(false);

								if (tabData.get("cost3") == "TRUE") Ext.getCmp("OrderWorkspaceCost3").toggle(true);
								else Ext.getCmp("OrderWorkspaceCost3").toggle(false);
							}
						}}
					}
				})
			]
		});

		var EtcPanel = new Ext.Panel({
			region:"south",
			height:80,
			title:"비고",
			split:true,
			margins:"0 5 0 5",
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"OrderForm",
					border:false,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["etc"]
					),
					items:[
						new Ext.form.Hidden({
							name:"wno",
							value:wno
						}),
						new Ext.form.Hidden({
							name:"title",
							value:title
						}),
						new Ext.form.Hidden({
							name:"repto",
							value:widx
						}),
						new Ext.form.Hidden({
							name:"idx",
							value:idx
						}),
						new Ext.form.Hidden({
							name:"item"
						}),
						new Ext.form.Hidden({
							name:"order"
						}),
						new Ext.form.Hidden({
							name:"type",
							value:type
						}),
						new Ext.form.TextArea({
							name:"etc",
							hideLabel:true,
							style:"margin:5px;",
							width:944,
							height:43
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("OrderForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=company&mode=etc&idx="+idx,waitMsg:"정보를 로딩중입니다."});
						}},
						resize:{fn:function() {
							Ext.getCmp("OrderForm").getForm().findField("etc").setWidth(Ext.getCmp("OrderForm").getInnerWidth()-10);
							Ext.getCmp("OrderForm").getForm().findField("etc").setHeight(Ext.getCmp("OrderForm").getInnerHeight()-10);
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								var idx;
								Ext.each(action.result.errors,function(item,index,allItems) { idx = item.id; });
								Ext.getCmp("ListTab1").getStore().reload();
								Ext.getCmp("ListTab3").getStore().reload();
								Ext.getCmp("ListTab").activate("ListTab3");

								Ext.getCmp("OrderWindow").close();
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(button) {
									OrderFunction(type,wno,idx,widx,title);
								}});
							}
						}}
					}
				})
			]
		});

		if (type == "EQUAL") {
			var CenterPanel = new Ext.Panel({
				region:"center",
				layout:"border",
				split:true,
				margins:"0 5 0 0",
				border:false,
				items:[
					new Ext.grid.EditorGridPanel({
						id:"OrderPanel",
						title:"품위내역",
						border:true,
						layout:"fit",
						split:true,
						region:"west",
						width:300,
						tbar:[
							new Ext.Button({
								text:"도급내역불러오기",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_right.png",
								handler:function() {
									Ext.Msg.show({title:"확인",msg:"품목을 불러오면, 해당 업체의 품목목록이 초기화됩니다.<br />좌측의 품목을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											var store = Ext.getCmp("OrderPanel").getStore().removeAll();
											for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
												if (Ext.getCmp("CompanyOrderPanel").get(i)) {
													Ext.getCmp("CompanyOrderPanel").get(i).getStore().removeAll();
												}
											}

											Ext.getCmp("CompanyOrderPanel").getActiveTab().getStore().removeAll();
											for (var i=0, loop=Ext.getCmp("ContractPanel").getStore().getCount();i<loop;i++) {
												var data = Ext.getCmp("ContractPanel").getStore().getAt(i);
												var insert = new Array();
												insert["is_new"] = "FALSE";
												insert["group"] = " ";
												insert["code"] = data.get("code");
												insert["gno"] = data.get("gno");
												insert["tno"] = data.get("tno");
												insert["title"] = data.get("title");
												insert["size"] = data.get("size");
												insert["unit"] = data.get("unit");
												insert["ea"] = data.get("ea");
												insert["order_ea"] = data.get("order_ea");
												insert["avgcost1"] = data.get("avgcost1");
												insert["avgcost2"] = data.get("avgcost2");
												insert["avgcost3"] = data.get("avgcost3");
												insert["sort"] = data.get("sort");

												GridInsertRow(Ext.getCmp("OrderPanel"),insert);

												for (var j=0, loopj=Ext.getCmp("CompanyOrderPanel").store.getCount();j<loopj;j++) {
													if (Ext.getCmp("CompanyOrderPanel").get(j)) {
														GridInsertRow(Ext.getCmp("CompanyOrderPanel").get(j),insert);
													}
												}
											}
										}
									}});
								}
							}),
							'-',
							new Ext.Button({
								text:"추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
								handler:function() {
									var insert = new Array();
									insert["sort"] = 10000;
									insert["is_new"] = "TRUE";
									insert["order_ea"] = "0,0,0,0";
									insert["group"] = " ";
									GridInsertRow(Ext.getCmp("OrderPanel"),insert);
									for (var i=0, loop=Ext.getCmp("OrderPanel").getStore().getCount();i<loop;i++) {
										Ext.getCmp("OrderPanel").getStore().getAt(i).set("sort",i);
									}

									for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
										if (Ext.getCmp("CompanyOrderPanel").get(i)) {
											GridInsertRow(Ext.getCmp("CompanyOrderPanel").get(i),insert);

											for (var j=0, loopj=Ext.getCmp("CompanyOrderPanel").get(i).getStore().getCount();j<loopj;j++) {
												Ext.getCmp("CompanyOrderPanel").get(i).getStore().getAt(j).set("sort",j);
											}
										}
									}
								}
							}),
							new Ext.Button({
								text:"삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
								handler:function() {
									var checked = Ext.getCmp("OrderPanel").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"삭제오류",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									} else {
										var delRow = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											Ext.getCmp("OrderPanel").getStore().remove(checked[i]);
											delRow.push(checked[i].get("sort"));
										}
										for (var i=0, loop=Ext.getCmp("OrderPanel").getStore().getCount();i<loop;i++) {
											Ext.getCmp("OrderPanel").getStore().getAt(i).set("sort",i);
										}

										for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
											if (Ext.getCmp("CompanyOrderPanel").get(i)) {
												for (var j=0, loopj=delRow.length;j<loopj;j++) {
													Ext.getCmp("CompanyOrderPanel").get(i).getStore().removeAt(Ext.getCmp("CompanyOrderPanel").get(i).getStore().find("sort",delRow[j],false,false));
												}

												for (var j=0, loopj=Ext.getCmp("CompanyOrderPanel").get(i).getStore().getCount();j<loopj;j++) {
													Ext.getCmp("CompanyOrderPanel").get(i).getStore().getAt(j).set("sort",j);
												}
											}
										}
									}
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								dataIndex:"group",
								hidden:true,
								hideable:false
							},{
								dataIndex:"gno",
								hidden:true,
								hideable:false
							},{
								dataIndex:"tno",
								hidden:true,
								hideable:false
							},{
								dataIndex:"sort",
								hidden:true,
								hideable:false
							},{
								header:"품명",
								dataIndex:"title",
								width:130,
								renderer:GridContractItemNotFound
							},{
								header:"규격",
								dataIndex:"size",
								width:80,
								renderer:GridContractItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"단위",
								dataIndex:"unit",
								width:40,
								hidden:true,
								renderer:GridContractItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"수량",
								dataIndex:"ea",
								width:40,
								sortable:false,
								renderer:GridItemOrderEA,
								editor:new Ext.form.TextField({selectOnFocus:true})
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["is_new","group","code","gno","tno","title","size","unit",{name:"ea",type:"float"},{name:"sort",type:"int"},"order_ea","avgcost1","avgcost2","avgcost3"]
							}),
							remoteSort:false,
							groupField:"group",
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"order","get":"company","mode":"tabdata","idx":idx,"tno":"0"},
							listeners:{
								update:{fn:function(store) {
									for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
										if (Ext.getCmp("CompanyOrderPanel").get(i)) {
											Ext.getCmp("CompanyOrderPanel").get(i).getStore().sort("sort","ASC");
										}
									}

									Ext.getCmp("OrderPanel").getView().getRowClass = function(record,index) {
										if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
										return "";
									}
								}},
								add:{fn:function(store) {
									Ext.getCmp("OrderPanel").getView().getRowClass = function(record,index) {
										if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
										return "";
									}
								}}
							}
						}),
						clicksToEdit:1,
						trackMouseOver:true,
						listeners:{
							render:{fn:function(object) {
								GridEditorAutoMatchItem(Ext.getCmp("OrderPanel"),wno);
								if (idx != 0) Ext.getCmp("OrderPanel").getStore().load();
								object.getView().scroller.on("scroll",function(object,scroller) {
									var tab = Ext.getCmp("CompanyOrderPanel").getActiveTab();
									if (tab.getId() != "LoadingTab") {
										tab.getView().scroller.dom.scrollTop = scroller.scrollTop;
									}
								});
							}},
							afteredit:{fn:function(object) {
								GridAutoMatchItem(object,wno);
								if (object.field == "ea" && !object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								OrderSelectWorkgroupType(grid,idx,e,wno);
							}}
						}
					}),
					CompanyTabPanel
				]
			});
		} else {
			var CenterPanel = CompanyTabPanel;
		}

		new Ext.Window({
			id:"OrderWindow",
			title:"본사품위서"+(idx == 0 ? "작성" : "보기"),
			modal:true,
			width:980,
			height:550,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.Panel({
					border:false,
					layout:"border",
					style:"padding:5px 0px 5px 0px;",
					items:[
						ContractPanel,
						CenterPanel,
						EtcPanel
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"발주계약서작성",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					hidden:(idx != 0 ? false : true),
					handler:function() {
						ContractFunction(0,idx);
					}
				}),
				new Ext.Button({
					text:"현장발주요청서",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					hidden:(widx != 0 ? false : true),
					handler:function() {
						WorkspaceFunction(widx,wno);
					}
				}),
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
					hidden:(idx != 0 ? false : true),
					handler:function() {
						new Ext.Window({
							id:"ExcelWindow",
							title:"엑셀파일로 변환",
							width:500,
							height:300,
							modal:true,
							layout:"fit",
							items:[
								new Ext.grid.GridPanel({
									border:false,
									id:"ExcelList",
									cm:new Ext.grid.ColumnModel([
										new Ext.grid.CheckboxSelectionModel(),
										{
											header:"업체명/회차",
											dataIndex:"title",
											width:310
										},{
											header:"금액",
											dataIndex:"price",
											width:120,
											renderer:GridNumberFormat
										}
									]),
									sm:new Ext.grid.CheckboxSelectionModel(),
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:[{name:"tno",type:"int"},"title",{name:"price",type:"int"}]
										}),
										remoteSort:false,
										sortInfo:{field:"tno",direction:"ASC"},
										baseParams:{"action":"order","get":"company","mode":"excel","idx":idx}
									}),
									listeners:{
										beforeselect:{fn:function(grid,idx) {
											alert(grid.getStore().getAt(idx).get("tno"));
											return false;
										}}
									}
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
									handler:function() {
										var checked = Ext.getCmp("ExcelList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"엑셀파일로 변환할 업체명/회차를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										} else if (checked.length >= 4) {
											Ext.Msg.show({title:"에러",msg:"동시에 4개까지만 출력할 수 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var tnos = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											tnos[i] = checked[i].get("tno");
										}
										var tno = tnos.join(",");
										Ext.getCmp("ExcelWindow").close();
										ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=order&idx="+idx+"&tno="+tno);
									}
								}),
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("ExcelWindow").close();
									}
								})
							],
							listeners:{
								show:{fn:function() {
									Ext.getCmp("ExcelList").getStore().load();
								}}
							}
						}).show();
					}
				}),
				new Ext.Button({
					text:(idx == 0 ? "확인" : "수정"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (Ext.getCmp("CompanyOrderPanel").store.getCount() == 0) {
							Ext.Msg.show({title:"에러",msg:"품위내역을 작성하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							return false;
						}

						var datas = new Array();
						var tabs = Ext.getCmp("CompanyOrderPanel").store;

						for (var i=0, loop=tabs.getCount();i<loop;i++) {
							var checked = Ext.getCmp("CompanyOrderPanel").get(i).getStore();
							for (var j=0, loopj=checked.getCount();j<loopj;j++) {
								if (!checked.getAt(j).get("gno") || !checked.getAt(j).get("tno")) {
									Ext.Msg.show({title:"에러",msg:"품위내역에 공종그룹과 공종명이 빠진 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
								if (!checked.getAt(j).get("title")) {
									Ext.Msg.show({title:"에러",msg:"품위내역에 품명이 빠진 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
							}
							datas[i] = tabs.getAt(i).get("cno")+"\t"+tabs.getAt(i).get("cost1")+"\t"+tabs.getAt(i).get("cost2")+"\t"+tabs.getAt(i).get("cost3")+"\t"+GetGridData(Ext.getCmp("CompanyOrderPanel").get(i));
						}

						Ext.getCmp("OrderForm").getForm().findField("item").setValue(GetGridData(Ext.getCmp("ContractPanel")));
						Ext.getCmp("OrderForm").getForm().findField("order").setValue(datas.join("\n"));

						if (idx == 0) {
							Ext.getCmp("OrderForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=company&mode=add",waitMsg:"데이터를 저장중입니다."});
						} else {
							Ext.getCmp("OrderForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=company&mode=modify&idx="+idx,waitMsg:"데이터를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					if (Ext.getCmp("WorkspaceWindow")) Ext.getCmp("WorkspaceWindow").close();
				}}
			}
		}).show();
	}

	function OrderCreateTabPanel(type,idx,tno) {
		if (Ext.getCmp("LoadingTab")) Ext.getCmp("CompanyOrderPanel").remove(Ext.getCmp("LoadingTab"));

		if (type == "EQUAL") {
			var CreatePanel = new Ext.grid.EditorGridPanel({
				title:Ext.getCmp("CompanyOrderPanel").store.getAt(tno).get("title"),
				layout:"fit",
				closable:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.CheckboxSelectionModel({hidden:true}),
					{
						dataIndex:"group",
						hidden:true,
						hideable:false
					},{
						dataIndex:"sort",
						hidden:true,
						hideable:false
					},{
						dataIndex:"gno",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.gno = Ext.getCmp("OrderPanel").getStore().getAt(row).get("gno");
							return record.data.gno;
						}
					},{
						dataIndex:"tno",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.tno = Ext.getCmp("OrderPanel").getStore().getAt(row).get("tno");
							return record.data.tno;
						}
					},{
						dataIndex:"title",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.title = Ext.getCmp("OrderPanel").getStore().getAt(row).get("title");
							return record.data.title;
						}
					},{
						dataIndex:"size",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.size = Ext.getCmp("OrderPanel").getStore().getAt(row).get("size");
							return record.data.size;
						}
					},{
						dataIndex:"unit",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.unit = Ext.getCmp("OrderPanel").getStore().getAt(row).get("unit");
							return record.data.unit;
						}
					},{
						dataIndex:"ea",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							record.data.ea = Ext.getCmp("OrderPanel").getStore().getAt(row).get("ea");
							return record.data.ea;
						}
					},{
						header:"재료비",
						dataIndex:"cost1",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost1);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"cost2",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost2);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"cost3",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost3);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"합계",
						dataIndex:"price",
						width:80,
						renderer:function(value,p,record) {
							record.data.price = (record.data.cost1 + record.data.cost2 + record.data.cost3) * record.data.ea;
							return GridNumberFormat(record.data.price);
						},
						summaryType:"sum",
						summaryRenderer:function(value) {
							if (Ext.getCmp("ContractPanel").getStore().sum("price") > 0) document.getElementById("ContractPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("price")*100).toFixed(2)+"%";
							if (Ext.getCmp("ContractPanel").getStore().sum("exec_price") > 0)  document.getElementById("ExecPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("exec_price")*100).toFixed(2)+"%";
							return GridNumberFormat(value);
						}
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:[{name:"sort",type:"int"},"group","gno","tno","itemno","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"}]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"sort",direction:"ASC"},
					baseParams:{"action":"order","get":"company","mode":"tabdata","idx":idx,"tno":tno},
					listeners:{load:{fn:function(store) {
						if (store.getCount() == 0) {
							for (var i=0, loop=Ext.getCmp("OrderPanel").getStore().getCount();i<loop;i++) {
								GridInsertRow(Ext.getCmp("CompanyOrderPanel").get(tno));
							}
							for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").get(tno).getStore().getCount();i<loop;i++) {
								Ext.getCmp("CompanyOrderPanel").get(tno).getStore().getAt(i).set("group"," ");
								Ext.getCmp("CompanyOrderPanel").get(tno).getStore().getAt(i).set("sort",i);
							}
						}
					}}}
				}),
				clicksToEdit:1,
				trackMouseOver:true,
				plugins:new Ext.ux.grid.GroupSummary(),
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:true,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:false
				}),
				listeners:{
					render:{fn:function(object) {
						object.getView().scroller.on("scroll",function(object,scroller) {
							Ext.getCmp("OrderPanel").getView().scroller.dom.scrollTop = scroller.scrollTop;
						});

						Ext.getCmp("CompanyOrderPanel").get(tno).getStore().load();
					}},
					afteredit:{fn:function(object) {
						if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
							if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
						}
					}},
					close:{fn:function(tab) {
						for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
								Ext.getCmp("CompanyOrderPanel").store.removeAt(i);
								break;
							}
						}
					}}
				}
			});
		} else {
			var CreatePanel = new Ext.grid.EditorGridPanel({
				title:Ext.getCmp("CompanyOrderPanel").store.getAt(tno).get("title"),
				layout:"fit",
				closable:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.CheckboxSelectionModel(),
					{
						dataIndex:"group",
						hidden:true,
						hideable:false
					},{
						dataIndex:"sort",
						hidden:true,
						hideable:false
					},{
						dataIndex:"gno",
						hidden:true,
						hideable:false
					},{
						dataIndex:"tno",
						hidden:true,
						hideable:false
					},{
						header:"품명",
						dataIndex:"title",
						width:130,
						sortable:false,
						renderer:GridContractItemNotFound
					},{
						header:"규격",
						dataIndex:"size",
						width:80,
						renderer:GridContractItemNotFound,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"단위",
						dataIndex:"unit",
						width:40,
						renderer:GridContractItemNotFound,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"수량",
						dataIndex:"ea",
						width:40,
						renderer:GridItemOrderEA,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"재료비",
						dataIndex:"cost1",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost1);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"cost2",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost2);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"cost3",
						hidden:true,
						hideable:false,
						width:80,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost3);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"합계",
						dataIndex:"price",
						width:80,
						renderer:function(value,p,record) {
							record.data.price = (record.data.cost1 + record.data.cost2 + record.data.cost3) * record.data.ea;
							return GridNumberFormat(record.data.price);
						},
						summaryType:"sum",
						summaryRenderer:function(value) {
							if (Ext.getCmp("ContractPanel").getStore().sum("price") > 0) document.getElementById("ContractPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("price")*100).toFixed(2)+"%";
							if (Ext.getCmp("ContractPanel").getStore().sum("exec_price") > 0)  document.getElementById("ExecPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("exec_price")*100).toFixed(2)+"%";
							return GridNumberFormat(value);
						}
					}
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["is_new",{name:"sort",type:"int"},"group","gno","tno","code","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"sort",direction:"ASC"},
					baseParams:{"action":"order","get":"company","mode":"tabdata","idx":idx,"tno":tno},
					listeners:{
						update:{fn:function(store) {
							Ext.getCmp("CompanyOrderPanel").getActiveTab().getView().getRowClass = function(record,index) {
								if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
								return "";
							}
						}},
						add:{fn:function(store) {
							Ext.getCmp("CompanyOrderPanel").getActiveTab().getView().getRowClass = function(record,index) {
								if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
								return "";
							}
						}}
					}
				}),
				clicksToEdit:1,
				trackMouseOver:true,
				plugins:new Ext.ux.grid.GroupSummary(),
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:true,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:false
				}),
				listeners:{
					render:{fn:function(object) {
						GridEditorAutoMatchItem(Ext.getCmp("CompanyOrderPanel").get(tno),Ext.getCmp("CompanyOrderPanel").store.getAt(tno).get("wno"));
						Ext.getCmp("CompanyOrderPanel").get(tno).getStore().load();
					}},
					afteredit:{fn:function(object) {
						GridAutoMatchItem(object,Ext.getCmp("CompanyOrderPanel").store.getAt(tno).get("wno"));

						if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
							if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
						}
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						OrderSelectWorkgroupType(grid,idx,e,Ext.getCmp("CompanyOrderPanel").store.getAt(tno).get("wno"));
					}},
					close:{fn:function(tab) {
						for (var i=0, loop=Ext.getCmp("CompanyOrderPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("CompanyOrderPanel").get(i).getId()) {
								Ext.getCmp("CompanyOrderPanel").store.removeAt(i);
								break;
							}
						}
					}}
				}
			});
		}
		Ext.getCmp("CompanyOrderPanel").add(
			CreatePanel
		).show();
	}

	function OrderWrite(idx,wno) {
		new Ext.Window({
			id:"OrderWriteWindow",
			title:"본사품위서작성",
			modal:true,
			width:400,
			height:140,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					border:false,
					style:"padding:10px; background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:80,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.TextField({
							fieldLabel:"품위서명",
							width:280,
							id:"OrderWriteTitle",
							value:new Date().format("Y년 m월 d일")+" 품위서"
						}),
						new Ext.form.ComboBox({
							fieldLabel:"품위서선택",
							width:280,
							id:"OrderWriteType",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["value","display"],
								data:[["EACH","개별항목 품위서"],["EQUAL","동일항목 품위서"],["SERIES","회차별 품위서"]]
							}),
							editable:false,
							allowBlank:false,
							mode:"local",
							displayField:"display",
							valueField:"value",
							value:"EACH",
							emptyText:"품위서종류를 선택하여 주십시오."
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (!Ext.getCmp("OrderWriteType").getValue()) {
							Ext.Msg.show({title:"에러",msg:"품위서종류를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						} else if (!Ext.getCmp("OrderWriteTitle").getValue()) {
							Ext.Msg.show({title:"에러",msg:"품위서명를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						} else {
							OrderFunction(Ext.getCmp("OrderWriteType").getValue(),wno,0,idx,Ext.getCmp("OrderWriteTitle").getValue());
						}
						Ext.getCmp("OrderWriteWindow").close();
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWriteWindow").close();
					}
				})
			]
		}).show();
	}

	function ContractFunction(idx,cidx) {
		new Ext.Window({
			id:"ContractWindow",
			layout:"fit",
			width:850,
			height:500,
			modal:true,
			title:(idx != 0 ? "발주계약서보기" : "발주계약서작성"),
			items:[
				new Ext.Panel({
					layout:"border",
					border:false,
					items:[
						new Ext.grid.EditorGridPanel({
							id:"ContractItemList",
							title:"계약품목",
							margins:"5 5 0 5",
							layout:"fit",
							region:"center",
							tbar:[
								new Ext.form.TextField({
									id:"ContractTitle",
									width:200,
									emptyText:"발주계약서명을 입력하여 주십시오."
								}),
								'-',
								new Ext.form.ComboBox({
									id:"ContractCompanyList",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									disabled:(idx == 0 ? false : true),
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["tno","cno","title"]
										}),
										remoteSort:false,
										sortInfo:{field:"tno",direction:"ASC"},
										baseParams:{"action":"order","get":"company","mode":"tab","idx":cidx}
									}),
									width:200,
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"cno",
									emptyText:"품위업체선택",
									listeners:{
										render:{fn:function() {
											Ext.getCmp("ContractCompanyList").getStore().load();
										}},
										select:{fn:function(form,record) {
											Ext.getCmp("ContractForm").getForm().findField("cno").setValue(form.getValue());
											Ext.getCmp("ContractItemList").getStore().baseParams.tno = record.data.tno;
											Ext.getCmp("ContractItemList").getStore().load();
										}}
									}
								}),
								'-',
								new Ext.Button({
									text:"금액자동수정",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_calculator.png",
									handler:function() {
										new Ext.Window({
											id:"ContractCalculatorWindow",
											title:"금액자동수정",
											width:300,
											height:120,
											layout:"fit",
											modal:true,
											resizable:false,
											items:[
												new Ext.form.FormPanel({
													id:"ContractCalculatorForm",
													border:false,
													style:"padding:10px; background:#FFFFFF;",
													labelAlign:"right",
													labelWidth:95,
													autoWidth:true,
													errorReader:new Ext.form.XmlErrorReader(),
													items:[
														new Ext.ux.form.SpinnerField({
															fieldLabel:"발주요청서 대비",
															width:150,
															minValue:1,
															maxValue:200,
															name:"percent",
															value:100
														})
													]
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
													handler:function() {
														Ext.Msg.show({title:"확인",msg:"발주요청서 금액대비 자동으로 계약금액을 계산하시겠습니까?",buttons:Ext.Msg.OK,icon:Ext.MessageBox.QUESTION,fn:function(button) {
															if (button == "ok") {
																for (var i=0, loop=Ext.getCmp("ContractItemList").getStore().getCount();i<loop;i++) {
																	Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost1",Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost1")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100);
																	Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost2",Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost2")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100);
																	Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost3",Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost3")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100);
																}

																Ext.getCmp("ContractCalculatorWindow").close();
															}
														}});
													}
												}),
												new Ext.Button({
													text:"취소",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
													handler:function() {
														Ext.getCmp("ContractCalculatorWindow").close();
													}
												})
											]
										}).show();
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									dataIndex:"group",
									hidden:true,
									hideable:false
								},{
									dataIndex:"gno",
									hidden:true,
									hideable:false
								},{
									dataIndex:"tno",
									hidden:true,
									hideable:false
								},{
									header:"품명",
									dataIndex:"title",
									width:250,
									renderer:GridContractItemNotFound
								},{
									header:"규격",
									dataIndex:"size",
									width:100,
									renderer:GridContractItemNotFound
								},{
									header:"단위",
									dataIndex:"unit",
									width:40,
									hidden:false,
									renderer:GridContractItemNotFound
								},{
									header:"수량",
									dataIndex:"ea",
									width:40,
									sortable:false,
									renderer:GridItemOrderEA
								},{
									header:"자재비",
									dataIndex:"cost1",
									width:80,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"노무비",
									dataIndex:"cost2",
									width:90,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"경비",
									dataIndex:"cost3",
									width:90,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"금액",
									dataIndex:"price",
									width:100,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.price = (record.data.cost1 + record.data.cost2 + record.data.cost3) * record.data.ea;
										return GridNumberFormat(record.data.price);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","group","gno","tno","code","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"price",type:"int"},{name:"sort",type:"int"},"avgcost1","avgcost2","avgcost3"]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"order","get":"contract","mode":"data","idx":idx,"cidx":cidx,"tno":""}
							}),
							clicksToEdit:1,
							trackMouseOver:true,
							plugins:new Ext.ux.grid.GroupSummary(),
							view:new Ext.grid.GroupingView({
								enableGroupingMenu:false,
								hideGroupedColumn:true,
								showGroupName:false,
								enableNoGroups:false,
								headersDisabled:false,
								showGroupHeader:false
							}),
							listeners:{
								afteredit:{fn:function(object) {
									if ((object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") && !object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
								}}
							}
						}),
						new Ext.Panel({
							region:"south",
							height:80,
							title:"비고",
							split:true,
							margins:"0 5 5 5",
							layout:"fit",
							items:[
								new Ext.form.FormPanel({
									id:"ContractForm",
									border:false,
									errorReader:new Ext.form.XmlErrorReader(),
									reader:new Ext.data.XmlReader(
										{record:"form",success:"@success",errormsg:"@errormsg"},
										["cno","title","etc"]
									),
									items:[
										new Ext.form.Hidden({
											name:"cno"
										}),
										new Ext.form.Hidden({
											name:"cidx",
											value:cidx
										}),
										new Ext.form.Hidden({
											name:"title"
										}),
										new Ext.form.Hidden({
											name:"data"
										}),
										new Ext.form.TextArea({
											name:"etc",
											hideLabel:true,
											style:"margin:5px;",
											width:944,
											height:43
										})
									],
									listeners:{
										render:{fn:function() {
											Ext.getCmp("ContractForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=contract&mode=form&idx="+idx,waitMsg:"정보를 로딩중입니다."});
										}},
										resize:{fn:function() {
											Ext.getCmp("ContractForm").getForm().findField("etc").setWidth(Ext.getCmp("ContractForm").getInnerWidth()-10);
											Ext.getCmp("ContractForm").getForm().findField("etc").setHeight(Ext.getCmp("ContractForm").getInnerHeight()-10);
										}},
										actioncomplete:{fn:function(form,action) {
											if (action.type == "load") {
												Ext.getCmp("ContractTitle").setValue(form.findField("title").getValue());
											}
											if (action.type == "submit") {
												var idx;
												Ext.each(action.result.errors,function(item,index,allItems) { idx = item.id; });
												Ext.getCmp("ListTab1").getStore().reload();
												Ext.getCmp("ListTab3").getStore().reload();
												Ext.getCmp("ListTab4").getStore().reload();
												Ext.getCmp("ListTab").activate("ListTab4");

												Ext.getCmp("ContractWindow").close();
												Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(button) {
													ContractFunction(idx,cidx);
												}});
											}
										}}
									}
								})
							]
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:(idx == 0 ? "확인" : "수정"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (Ext.getCmp("ContractItemList").getStore().getCount() == 0) {
							Ext.Msg.show({title:"에러",msg:"계약품목이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						}
						Ext.getCmp("ContractForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("ContractItemList")));
						if (!Ext.getCmp("ContractTitle").getValue()) Ext.getCmp("ContractTitle").setValue(new Date().format("Y년 m월 d일")+" 발주계약서");
						Ext.getCmp("ContractForm").getForm().findField("title").setValue(Ext.getCmp("ContractTitle").getValue());

						if (idx == 0) {
							Ext.getCmp("ContractForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=contract&mode=add",waitMsg:"데이터를 저장중입니다."});
						} else {
							Ext.getCmp("ContractForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=contract&mode=modify&idx="+idx,waitMsg:"데이터를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("ContractWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx != 0) Ext.getCmp("ContractItemList").getStore().load();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"자재발주관리",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
				text:"이전달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex == 0) {
						Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("ListTab4").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab3").getStore().reload();
						Ext.getCmp("ListTab4").getStore().reload();
					}
				}
			}),
			' ',
			new Ext.form.ComboBox({
				id:"month",
				store:MonthListStore,
				displayField:"display",
				valueField:"date",
				typeAhead:true,
				mode:"local",
				triggerAction:"all",
				width:90,
				editable:false,
				listeners:{
					render:{fn:function(form) {
						form.getStore().load();
						form.getStore().on("load",function() {
							form.setValue("<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>");
							for (var i=0, loop=form.getStore().getCount();i<loop;i++) {
								if (form.getStore().getAt(i).get("date") == form.getValue()) {
									form.selectedIndex = i;
									break;
								}
							}

							if (form.selectedIndex == -1) {
								form.selectedIndex = form.getStore().getCount()-1;
								form.setValue(form.getStore().getAt(form.getStore().getCount()-1).get("date"));
								SetCookie("iErpMonth",form.getValue());
							}

							Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab1").getStore().load({params:{start:0,limit:30}});

							Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab3").getStore().load({params:{start:0,limit:30}});

							Ext.getCmp("ListTab4").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab4").getStore().load({params:{start:0,limit:30}});
						});
					}},
					select:{fn:function(form) {
						Ext.getCmp("ListTab1").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab1").getStore().reload();

						Ext.getCmp("ListTab3").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab3").getStore().reload();

						Ext.getCmp("ListTab4").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab4").getStore().reload();
					}}
				}
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_right.png",
				iconAlign:"right",
				text:"다음달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
						Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("ListTab4").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab3").getStore().reload();
						Ext.getCmp("ListTab4").getStore().reload();
					}
				}
			}),
			'-',
			new Ext.Button({
				id:"GroupByDate",
				text:"일자별",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png",
				enableToggle:true,
				pressed:true,
				handler:function(button) {
					if (button.pressed == true) {
						button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						Ext.getCmp("ListTab1").getStore().groupBy("date");
						Ext.getCmp("ListTab3").getStore().groupBy("date");
						Ext.getCmp("ListTab4").getStore().groupBy("date");
						Ext.getCmp("GroupByWorkspace").setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						Ext.getCmp("GroupByWorkspace").toggle(false);
					}
				}
			}),
			' ',
			new Ext.Button({
				id:"GroupByWorkspace",
				text:"현장별",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
				enableToggle:true,
				handler:function(button) {
					if (button.pressed == true) {
						button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						Ext.getCmp("ListTab1").getStore().groupBy("workspace");
						Ext.getCmp("ListTab3").getStore().groupBy("workspace");
						Ext.getCmp("ListTab4").getStore().groupBy("workspace");
						Ext.getCmp("GroupByDate").setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						Ext.getCmp("GroupByDate").toggle(false);
					}
				}
			}),
			'-',
			new Ext.Button({
				text:"본사품위서작성",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry_add.png",
				handler:function() {
					OrderFunction("new");
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.grid.GridPanel({
						title:"현장발주요청서",
						id:"ListTab1",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"요청현장명",
								dataIndex:"workspace",
								sortable:false,
								width:150
							},{
								header:"발주요청서명",
								dataIndex:"title",
								sortable:false,
								width:450
							},{
								header:"품목수",
								dataIndex:"item",
								width:80,
								sortable:false,
								renderer:GridNumberFormat
							},{
								header:"상태",
								width:150,
								sortable:true,
								renderer:function(value,p,record) {
									var sHTML = '<div style="text-align:center; font:0/0 arial;">';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_confirm_';
									sHTML+= record.data.is_confirm == "TRUE" ? "on" : "off";
									sHTML+= '.gif" style="margin-right:1px;" />';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_estimate_';
									sHTML+= record.data.is_estimate == "TRUE" ? "on" : "off";
									sHTML+= '.gif" style="margin-right:1px;" />';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_order_';
									sHTML+= record.data.is_order == "TRUE" ? "on" : "off";
									sHTML+= '.gif" style="margin-right:1px;" />';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_contract_';
									sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
									sHTML+= '.gif" style="margin-right:1px;" />';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_stored_';
									sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
									sHTML+= '.gif" />';
									sHTML+= '</div>';

									return sHTML;
								}
							},{
								header:"요청일",
								dataIndex:"date",
								sortable:true,
								width:110
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:WorkspaceOrderStore,
						loadMask:{msg:"데이터를 로딩중입니다."},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:WorkspaceOrderStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);

								WorkspaceFunction(data.get("idx"),data.get("wno"),data.get("title"));
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								if (data.get("is_confirm") == "FALSE") {
									menu.add({
										text:"본사확인처리",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png"),
										handler:function(item) {
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("ListTab1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"order","do":"workspace","mode":"confirm","idx":data.get("idx")}
											});
										}
									});
								} else {
									menu.add({
										text:"본사확인취소",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png"),
										handler:function(item) {
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("ListTab1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"order","do":"workspace","mode":"confirm","idx":data.get("idx")}
											});
										}
									});
								}
								var orderMenu = new Ext.menu.Menu();
								orderMenu.add('<b class="menu-sub-title">본사품위서</b>');
								orderMenu.add({
									text:"본사품위서 작성",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
									handler:function(item) {
										OrderWrite(data.get("idx"),data.get("wno"));
									}
								});

								if (data.get("order")) {
									orderMenu.add('-');
									var temp = data.get("order").split("##");
									for (var i=0, loop=temp.length;i<loop;i++) {
										var info = temp[i].split("||");
										orderMenu.add({
											text:info[2],
											handler:function() {
												OrderFunction(info[1],data.get("wno"),info[0],data.get("idx"));
											}
										});
									}
								}
								menu.add({
									text:"본사품위서",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
									menu:orderMenu
								});

								if (data.get("contract")) {
									var contractMenu = new Ext.menu.Menu();
									contractMenu.add('<b class="menu-sub-title">발주계약서</b>');
									var temp = data.get("contract").split("##");
									for (var i=0, loop=temp.length;i<loop;i++) {
										var info = temp[i].split("||");
										contractMenu.add({
											text:info[2],
											handler:function() {
												ContractFunction(info[0],info[1]);
											}
										});
									}
									menu.add({
										text:"발주계약서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry_add.png"),
										menu:contractMenu
									});
								} else {
									menu.add({
										text:"발주계약서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry_add.png"),
										handler:function() {
											Ext.Msg.show({title:"안내",msg:"작성된 발주계약서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										}
									});
								}

								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						title:"본사품위서",
						id:"ListTab3",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hidable:false
							},{
								header:"현장명",
								dataIndex:"workspace",
								sortable:false,
								width:150
							},{
								header:"품위서명",
								dataIndex:"title",
								sortable:false,
								width:300
							},{
								header:"품위업체",
								dataIndex:"company",
								sortable:false,
								width:150
							},{
								header:"품목수",
								dataIndex:"item",
								width:80,
								sortable:false,
								renderer:GridNumberFormat
							},{
								header:"상태",
								width:70,
								sortable:true,
								renderer:function(value,p,record) {
									var sHTML = '<div style="text-align:center; font:0/0 arial;">';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_contract_';
									sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
									sHTML+= '.gif" style="margin-right:1px;" />';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_stored_';
									sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
									sHTML+= '.gif" />';
									sHTML+= '</div>';

									return sHTML;
								}
							},{
								header:"품위일",
								dataIndex:"date",
								sortable:true,
								width:110
							}
						]),
						store:OrderStore,
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:OrderStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,row,event) {
								OrderFunction(grid.getStore().getAt(row).get("type"),grid.getStore().getAt(row).get("wno"),grid.getStore().getAt(row).get("idx"),grid.getStore().getAt(row).get("repto"));
							}},
							rowcontextmenu:{fn:function(grid,idx,event) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								if (data.get("repto") > 0) {
									menu.add({
										text:"현장발주요청서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
										handler:function(item) {
											WorkspaceFunction(data.get("idx"),data.get("wno"));
										}
									});
								}

								var contractMenu = new Ext.menu.Menu();
								contractMenu.add('<b class="menu-sub-title">발주계약서</b>');
								contractMenu.add({
									text:"발주계약서작성",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
									handler:function(item) {
										ContractFunction(0,data.get("idx"));
									}
								});

								if (data.get("contract")) {
									contractMenu.add('-');
									var temp = data.get("contract").split("##");
									for (var i=0, loop=temp.length;i<loop;i++) {
										var info = temp[i].split("||");
										contractMenu.add({
											text:info[1],
											handler:function() {
												ContractFunction(info[0],data.get("idx"));
											}
										});
									}
								}

								menu.add({
									text:"발주계약서",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png"),
									menu:contractMenu
								});

								event.stopEvent();
								menu.showAt(event.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						title:"발주계약서",
						id:"ListTab4",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"현장명",
								dataIndex:"workspace",
								sortable:false,
								width:150
							},{
								header:"업체명",
								dataIndex:"company",
								sortable:false,
								width:150
							},{
								header:"발주계약서명",
								dataIndex:"title",
								sortable:false,
								width:350
							},{
								header:"품목수",
								dataIndex:"item",
								width:60,
								sortable:false,
								renderer:GridNumberFormat
							},{
								header:"금액",
								dataIndex:"price",
								width:100,
								sortable:true,
								renderer:GridNumberFormat
							},{
								header:"상태",
								width:50,
								sortable:true,
								renderer:function(value,p,record) {
									var sHTML = '<div style="text-align:center; font:0/0 arial;">';
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_stored_';
									sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
									sHTML+= '.gif" />';
									sHTML+= '</div>';

									return sHTML;
								}
							},{
								header:"작성일",
								dataIndex:"date",
								sortable:true,
								width:110
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:ContractStore,
						loadMask:{msg:"데이터를 로딩중입니다."},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:ContractStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);

								ContractFunction(data.get("idx"),data.get("ono"));
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								if (data.get("repto") > 0) {
									menu.add({
										text:"현장발주요청서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
										handler:function(item) {
											WorkspaceFunction(data.get("idx"),data.get("wno"));
										}
									});
								}

								if (data.get("ono") > 0) {
									menu.add({
										text:"본사품위서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
										handler:function(item) {
											OrderFunction(data.get("ono"),data.get("repto"));
										}
									});
								}
								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>