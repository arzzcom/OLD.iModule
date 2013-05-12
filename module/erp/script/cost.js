function CostLoad(wno,idx) {
	new Ext.Window({
		title:"기존 내역 불러오기",
		id:"CostLoadWindow",
		modal:true,
		width:400,
		height:120,
		layout:"fit",
		items:[
			new Ext.form.FormPanel({
				id:"CostLoadForm",
				border:false,
				style:"padding:10px; background:#FFFFFF;",
				labelAlign:"right",
				labelWidth:80,
				autoWidth:true,
				errorReader:new Ext.form.XmlErrorReader(),
				items:[
					new Ext.form.ComboBox({
						fieldLabel:"기존내역선택",
						width:280,
						hiddenName:"load",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title"]
							}),
							remoteSort:false,
							sortInfo:{field:"title",direction:"ASC"},
							baseParams:{"action":"cost","get":"alllist","wno":wno,"idx":idx},
						}),
						editable:false,
						allowBlank:false,
						mode:"local",
						displayField:"title",
						valueField:"idx",
						emptyText:"기존내역을 선택하여 주세요.",
						listeners:{render:{fn:function(form) {
							form.getStore().load();
						}}}
					})
				],
				listeners:{actioncomplete:{fn:function(form,action) {
					if (action.type == "submit") {
						Ext.Msg.show({title:"안내",msg:"기존내역을 성공적으로 불러왔습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO});
						Ext.getCmp("CostSheet").getStore().reload();
						Ext.getCmp("CostList").getStore().reload();
						Ext.getCmp("CostLoadWindow").close();
					}
				}}}
			})
		],
		buttons:[
			new Ext.Button({
				text:"확인",
				icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
				handler:function() {
					Ext.Msg.show({title:"안내",msg:"기존내역을 불러올 경우, 현재내역이 모두 초기화됩니다.<br />기존내역을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
						if (button == "ok") {
							Ext.getCmp("CostLoadForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=cost&do=load&idx="+idx,waitMsg:"기존내역을 로딩중입니다."});
						}
					}});
				}
			}),
			new Ext.Button({
				text:"취소",
				icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
				handler:function() {
					Ext.getCmp("CostLoadWindow").close();
				}
			})
		]
	}).show();
}

function CostView(type,wno,idx,title) {
	if (type == "ESTIMATE") {
		var typeText = "견적내역서";
	} else if (type == "EXEC") {
		var typeText = "실행내역서";
	} else if (type == "CONTRACT") {
		var typeText = "계약내역서";
	}

	new Ext.Window({
		id:"CostWindow",
		title:title,
		modal:true,
		layout:"fit",
		width:950,
		height:540,
		items:[
			new Ext.TabPanel({
				id:"CostTab",
				border:false,
				tabPosition:"bottom",
				activeTab:0,
				items:[
					new Ext.grid.EditorGridPanel({
						id:"CostSheet",
						title:typeText,
						border:false,
						tbar:[
							new Ext.Button({
								text:"공종그룹관리하기",
								icon:ENV.dir+"/module/erp/images/common/icon_package.png",
								handler:function() {
									WorkGroupSetup(wno);
								}
							}),
							new Ext.Button({
								text:"기존 내역 불러오기",
								icon:ENV.dir+"/module/erp/images/common/icon_paste_plain.png",
								hidden:Ext.getCmp("CostListTab") ? false : true,
								handler:function() {
									CostLoad(wno,idx);
								}
							}),
							new Ext.Button({
								text:"현장에 적용하기",
								icon:ENV.dir+"/module/erp/images/common/icon_page_copy.png",
								hidden:Ext.getCmp("CostListTab") ? false : true,
								handler:function() {
									Ext.Msg.show({title:"안내",msg:"현장에 적용한 뒤, 변경사항은 자동으로 현장에 반영됩니다.<br />현재의 "+typeText+"를 현장에 반영하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Ajax.request({
												url:ENV.dir+"/module/erp/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 적용하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 적용하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"cost","do":"apply","idx":idx,"type":type}
											});
										}
									}});
								}
							}),
							'-',
							new Ext.Button({
								text:"변경사항저장하기",
								icon:ENV.dir+"/module/erp/images/common/icon_disk.png",
								handler:function() {
									var data = GetGridData(Ext.getCmp("CostSheet"));
									Ext.Ajax.request({
										url:ENV.dir+"/module/erp/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("CostSheet").getStore().commitChanges();
											Ext.getCmp("CostUnit").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"cost","do":"sheet","idx":idx,"data":data}
									});
								}
							}),
							'-',
							new Ext.Button({
								text:"새로고침",
								icon:ENV.dir+"/module/erp/images/common/icon_refresh.png",
								handler:function() {
									Ext.getCmp("CostSheet").getStore().reload();
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								sortable:false,
								hidden:true,
								hideable:false,
								width:100
							},{
								dataIndex:"is_write",
								hidden:true,
								hideable:false
							},{
								header:"분류",
								dataIndex:"group",
								sortable:false
							},{
								header:"분류",
								dataIndex:"type",
								sortable:false,
								width:100,
								summaryType:"data",
								summaryRenderer:function(value,p,record) {
									if (value == "절삭") {
										return "공급가액";
									} else if (value == "부가가치세") {
										return "총공사비";
									} else if (value == "경비") {
										return '<div>경비</div><div class="x-grid3-summary-double">계</div>';
									} else {
										return value;
									}
								}
							},{
								header:"세부항목",
								dataIndex:"category",
								sortable:true,
								width:150,
								summaryType:"data",
								summaryRenderer:function(value,p,record) {
									if (record.data["type"] != "절삭" && record.data["type"] != "부가가치세") return "소계"
								}
							},{
								header:"금액",
								dataIndex:"price",
								sortable:false,
								width:150,
								renderer:function(value,p,record) {
									if (record.data.category == "간접노무비") {
										record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostSheet").getStore().getAt(3).get("price")*(record.data.percent/100));
									} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
										record.data.origin_price = record.data.price = Math.floor((Ext.getCmp("CostSheet").getStore().getAt(3).get("price")+Ext.getCmp("CostSheet").getStore().getAt(4).get("price"))*(record.data.percent/100));
									} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
										record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostSheet").getStore().getAt(3).get("price")*(record.data.percent/100));
									} else if (record.data.category == "노인장기요양보험료") {
										record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostSheet").getStore().getAt(8).get("price")*(record.data.percent/100));
									} else if (record.data.category == "산업안전보건관리비") {
										record.data.origin_price = record.data.price = Math.floor((Ext.getCmp("CostSheet").getStore().getAt(0).get("price")+Ext.getCmp("CostSheet").getStore().getAt(1).get("price")+Ext.getCmp("CostSheet").getStore().getAt(2).get("price")+Ext.getCmp("CostSheet").getStore().getAt(3).get("price"))*(record.data.percent/100));
									} else if (record.data.type == "일반관리비" || record.data.type == "이윤") {
										var total = 0;
										for (var i=0;i<=11;i++) {
											total+= Ext.getCmp("CostSheet").getStore().getAt(i).get("price");
										}
										record.data.origin_price = record.data.price = Math.floor(total*(record.data.percent/100));
									} else if (record.data.type == "부가가치세") {
										var total = 0;
										for (var i=0;i<=14;i++) {
											total+= Ext.getCmp("CostSheet").getStore().getAt(i).get("price");
										}
										record.data.origin_price = record.data.price = Math.floor(total*(record.data.percent/100));
									}

									if (record.data.nego) {
										if (record.data.nego.indexOf("%") > -1) {
											var nego = parseInt(record.data.nego.replace("%",""));
											record.data.price = Math.floor(record.data.origin_price + (record.data.origin_price*nego/100));
										} else {
											var nego = parseInt(record.data.nego);
											record.data.price = record.data.origin_price + nego;
										}
									} else {
										record.data.price = record.data.origin_price;
									}

									return GridNumberFormat(record.data.price);
								},
								summaryType:"sum",
								editor:new Ext.form.NumberField({selectOnFocus:true}),
								summaryRenderer:function(value,p,record,data) {
									if (record.data["type"] == "경비") {
										var sHTML = '<div>'+GridNumberFormat(value)+'</div>';

										var total = 0;
										for (var i=0;i<=11;i++) {
											total+= Ext.getCmp("CostSheet").getStore().getAt(i).get("price");
										}
										sHTML+= '<div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';

										return sHTML;
									} else if (record.data["type"] == "절삭") {
										var total = 0;
										for (var i=0;i<=14;i++) {
											total+= Ext.getCmp("CostSheet").getStore().getAt(i).get("price");
										}
										return GridNumberFormat(total);
									} else if (record.data["type"] == "부가가치세") {
										var total = 0;
										for (var i=0;i<=15;i++) {
											total+= Ext.getCmp("CostSheet").getStore().getAt(i).get("price");
										}
										return GridNumberFormat(total);
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								dataIndex:"origin_price",
								hidden:true,
								hideable:false
							},{
								header:"구성비",
								dataIndex:"percent",
								sortable:false,
								width:180,
								renderer:function(value,p,record) {
									if (record.data.category == "간접노무비") {
										return "직접노무비 * "+value+"%";
									} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
										return "노무비 * "+value+"%";
									} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
										return "직접노무비 * "+value+"%";
									} else if (record.data.category == "노인장기요양보험료") {
										return "국민건강보험료 * "+value+"%";
									} else if (record.data.category == "산업안전보건관리비") {
										return "[재료비+직접노무비] * "+value+"%";
									} else if (record.data.type == "일반관리비" || record.data.type == "이윤") {
										return "순공사비 * "+value+"%";
									} else if (record.data.type == "부가가치세") {
										return "공급가액 * "+value+"%";
									}
								},
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"비고",
								dataIndex:"etc",
								sortable:false,
								width:300,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								dataIndex:"nego",
								hidden:true,
								hideable:false
							}
						]),
						plugins:new Ext.ux.grid.GroupSummary(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"is_write","group","type","category",{name:"price",type:"int"},{name:"origin_price",type:"int"},{name:"percent",type:"float"},"etc","nego"]
							}),
							groupField:"group",
							remoteSort:false,
							sortInfo:{field:"idx",direction:"ASC"},
							baseParams:{"action":"cost","get":"sheet","idx":idx}
						}),
						clicksToEdit:1,
						trackMouseOver:true,
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false,
							showGroupHeader:false
						}),
						listeners:{
							beforeedit:{fn:function(object) {
								if (object.field == "price" && object.record.data.is_write == "FALSE") return false;
								else if (object.field == "percent" && object.record.data.percent == "-1") return false;
								else return true;
							}},
							afteredit:{fn:function(object) {
								if (object.field == "price") {
									if (!object.value) {
										object.grid.getStore().getAt(object.row).set("price",0);
										object.grid.getStore().getAt(object.row).set("origin_price",0);
									} else {
										object.grid.getStore().getAt(object.row).set("origin_price",object.value);
									}
									Ext.getCmp("CostSheet").getStore().sort("idx","ASC");
								} else if (object.field == "percent") {
									if (!object.value || object.value < 0) object.grid.getStore().getAt(object.row).set("percent","0");
								}
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);

								var title = data.get("type")+(data.get("category") ? " > "+data.get("category") : "");
								var menu = new Ext.menu.Menu();
								menu.add({
									text:"<b>"+title+"</b> 증감액설정",
									icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_coins.png")
								});
								menu.add(new Ext.menu.Separator({}));
								menu.add(
									new Ext.form.TextField({
										width:200,
										emptyText:"% 또는 숫자로 입력하세요.",
										getListParent:function() {
											return this.el.up('.x-menu');
										},
										iconCls:'no-icon',
										enableKeyEvents:true,
										listeners:{
											keydown:{fn:function(form,e) {
												if (e.keyCode == 13) {
													data.set("nego",form.getValue());
													grid.getStore().sort("idx","ASC");
													menu.hide();
												}
											}},
											render:{fn:function(form) {
												form.setValue(data.get("nego"));
											}}
										}
									})
								);

								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"CostList",
						title:"공종별"+typeText,
						border:false,
						tbar:[
							new Ext.Button({
								text:"공종그룹관리하기",
								icon:ENV.dir+"/module/erp/images/common/icon_package.png",
								handler:function() {
									WorkGroupSetup(wno);
								}
							}),
							'-',
							new Ext.form.ComboBox({
								id:"CostAddGroupList",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","workgroup","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"workspace","get":"workgroup","wno":wno,"is_base":"true"},
								}),
								width:150,
								editable:false,
								mode:"local",
								displayField:"workgroup",
								valueField:"idx",
								listeners:{render:{fn:function() {
									Ext.getCmp("CostAddGroupList").getStore().load();
									Ext.getCmp("CostAddGroupList").getStore().on("load",function() {
										if (Ext.getCmp("CostAddGroupList").getStore().getCount() == 0) {
											Ext.getCmp("CostAddGroupList").setValue("공종그룹이 없습니다.");
											Ext.getCmp("CostAddGroupList").disable();
										} else {
											Ext.getCmp("CostAddGroupList").setValue(Ext.getCmp("CostAddGroupList").getStore().getAt(0).get("idx"));
											Ext.getCmp("CostAddGroupList").enable();
										}
									});
								}}}
							}),
							' ',
							new Ext.Button({
								text:"하위공종 및 품목관리하기",
								icon:ENV.dir+"/module/erp/images/common/icon_brick_link.png",
								handler:function() {
									if (!Ext.getCmp("CostAddGroupList").getValue()) {
										Ext.Msg.show({title:"에러",msg:"공종그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}

									CostSubView(idx,Ext.getCmp("CostAddGroupList").getValue());
								}
							}),
							'-',
							new Ext.Button({
								text:"새로고침",
								icon:ENV.dir+"/module/erp/images/common/icon_refresh.png",
								handler:function() {
									Ext.getCmp("CostList").getStore().reload();
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							{
								dataIndex:"basegroup",
								hidden:true,
								summaryType:"data",
								hideable:false
							},{
								header:"공종그룹",
								dataIndex:"workgroup",
								width:200,
								sortable:true,
								summaryType:"data",
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
									} else {
										return "소계";
									}
								}
							},{
								header:"단가",
								dataIndex:"cost1",
								width:80,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("cost1");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"금액",
								dataIndex:"price1",
								width:95,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("price1");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"단가",
								dataIndex:"cost2",
								width:80,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("cost2");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"금액",
								dataIndex:"price2",
								width:95,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("price2");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"단가",
								dataIndex:"cost3",
								width:80,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("cost3");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"금액",
								dataIndex:"price3",
								width:95,
								sortable:false,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("price3");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"단가",
								dataIndex:"total_cost",
								width:80,
								sortable:false,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
									return GridNumberFormat(record.data.total_cost);
								},
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("total_cost");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"금액",
								dataIndex:"total_price",
								width:100,
								sortable:false,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.total_price = record.data.price1+record.data.price2+record.data.price3;
									return GridNumberFormat(record.data.total_price);
								},
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostList").getStore().getAt(Ext.getCmp("CostList").getStore().getCount()-1);
									if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
										var total = 0;
										for (var i=0, loop=Ext.getCmp("CostList").getStore().getCount();i<loop;i++) {
											if (Ext.getCmp("CostList").getStore().getAt(i).get("group") != "0") total+= Ext.getCmp("CostList").getStore().getAt(i).get("total_price");
										}
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							}
						]),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["basegroup","gno","workgroup",{name:"cost1",type:"int"},{name:"price1",type:"int"},{name:"cost2",type:"int"},{name:"price2",type:"int"},{name:"cost3",type:"int"},{name:"price3",type:"int"},{name:"sort",type:"int"}]
							}),
							remoteSort:false,
							groupField:"basegroup",
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"cost","get":"group","idx":idx}
						}),
						plugins:[new Ext.ux.grid.ColumnHeaderGroup({
							rows:[[
								{},
								{},
								{header:"재료비",colspan:2,align:"center"},
								{header:"노무비",colspan:2,align:"center"},
								{header:"경비",colspan:2,align:"center"},
								{header:"합계",colspan:2,align:"center"}
							]],
							hierarchicalColMenu:true
						}),new Ext.ux.grid.GroupSummary()],
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{rowdblclick:function(grid,row,event) {
							CostSubView(idx,grid.getStore().getAt(row).get("gno"));
						}}
					}),
					new Ext.grid.EditorGridPanel({
						id:"CostUnit",
						title:"평당환산금액",
						border:false,
						tbar:[
							new Ext.Button({
								text:"공종그룹관리하기",
								icon:ENV.dir+"/module/erp/images/common/icon_package.png",
								handler:function() {
									WorkGroupSetup(wno);
								}
							}),
							'-',
							new Ext.Button({
								text:"변경사항저장하기",
								icon:ENV.dir+"/module/erp/images/common/icon_disk.png",
								handler:function() {
									var data = GetGridData(Ext.getCmp("CostUnit"));
									Ext.Ajax.request({
										url:ENV.dir+"/module/erp/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("CostUnit").getStore().commitChanges();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"cost","do":"unit","idx":idx,"data":data}
									});
								}
							}),
							'-',
							new Ext.Button({
								text:"새로고침",
								icon:ENV.dir+"/module/erp/images/common/icon_refresh.png",
								handler:function() {
									Ext.getCmp("CostUnit").getStore().reload();
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"정렬",
								dataIndex:"sort",
								width:60,
								sortable:false,
								menuDisabled:true,
								renderer:function(value,p,record) {
									var temp = record.data.workgroup.split(" ");
									var group = temp[0];
									var sort = group;
									if (value < 10) sort+= "0";
									sort+= value;

									return sort;
								},
								summaryType:"data",
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostUnit").getStore().getAt(Ext.getCmp("CostUnit").getStore().getCount()-1);
									if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
										return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
									} else {
										return "소계";
									}
								}
							},{
								dataIndex:"workgroup",
								hidden:true,
								hideable:false,
								summaryType:"data"
							},{
								header:"공종",
								dataIndex:"worktype",
								width:180,
								summaryType:"data"
							},{
								header:"공사금액",
								dataIndex:"price",
								width:120,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostUnit").getStore().getAt(Ext.getCmp("CostUnit").getStore().getCount()-1);
									if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("CostUnit").getStore().sum("price"))+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"평당환산금액",
								dataIndex:"unit_price",
								width:120,
								summaryType:"sum",
								renderer:GridNumberFormat,
								summaryRenderer:function(value,p,record) {
									var lastRow = Ext.getCmp("CostUnit").getStore().getAt(Ext.getCmp("CostUnit").getStore().getCount()-1);
									if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
										return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("CostUnit").getStore().sum("unit_price"))+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								}
							},{
								header:"비고",
								dataIndex:"etc",
								width:400,
								editor:new Ext.form.TextField({selectOnFocus:true})
							}
						]),
						plugins:new Ext.ux.grid.GroupSummary(),
						clicksToEdit:1,
						trackMouseOver:true,
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","workgroup","worktype",{name:"price",type:"int"},{name:"unit_price",type:"int"},{name:"sort",type:"int"},"etc"]
							}),
							groupField:"workgroup",
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"cost","get":"unit","idx":idx}
						}),
						clicksToEdit:1,
						trackMouseOver:true,
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						})
					})
				]
			})
		],
		listeners:{
			show:{fn:function() {
				Ext.getCmp("CostSheet").getStore().load();
				Ext.getCmp("CostList").getStore().load();
				Ext.getCmp("CostUnit").getStore().load();
			}},
			close:{fn:function() {
				if (Ext.getCmp("CostListTab")) Ext.getCmp("CostListTab").getActiveTab().getStore().reload();
			}}
		}
	}).show();
}

function CostSubView(idx,gno,tno) {
	new Ext.Window({
		id:"CostSubWindow",
		title:"하위공종 및 품목관리하기",
		width:940,
		height:520,
		layout:"fit",
		modal:true,
		maximizable:true,
		tbar:[
			new Ext.form.TextField({
				id:"CostSubKeyword",
				width:200,
				emptyText:"검색어를 입력하세요.",
				enableKeyEvents:true,
				listeners:{keydown:{fn:function(form,e) {
					if (e.keyCode == 13) {
						Ext.getCmp("CostSubTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("CostSubKeyword").getValue();
						Ext.getCmp("CostSubTab").getActiveTab().getStore().load({params:{start:0,limit:30}});
					}
				}}}
			}),
			' ',
			new Ext.Button({
				id:"CostSubSearchButton",
				text:"검색",
				icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
				handler:function() {
					Ext.getCmp("CostSubTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("CostSubKeyword").getValue();
					Ext.getCmp("CostSubTab").getActiveTab().getStore().load({params:{start:0,limit:30}});
				}
			}),
			'-',
			new Ext.Button({
				text:"품목추가하기",
				icon:ENV.dir+"/module/erp/images/common/icon_brick_add.png",
				handler:function() {
					CostAddItem(idx,gno);
				}
			}),
			new Ext.Button({
				id:"CostSubDeleteButton",
				text:"품목삭제하기",
				icon:ENV.dir+"/module/erp/images/common/icon_brick_delete.png",
				handler:function() {
					var checked = Ext.getCmp("CostSubTab").getActiveTab().selModel.getSelections();

					if (Ext.getCmp("CostSubTab").getActiveTab().getStore().getModifiedRecords().length != 0) {
						Ext.Msg.show({title:"안내",msg:"변경된 사항이 있습니다. 변경사항을 먼저 저장하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						return false;
					}

					if (checked.length == 0) {
						Ext.Msg.show({title:"에러",msg:"삭제할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
						return false;
					}

					Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
						if (button == "ok") {
							var inos = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								inos[i] = checked[i].get("idx");
							}
							var ino = inos.join(",");

							Ext.Ajax.request({
								url:ENV.dir+"/module/erp/exec/Admin.do.php",
								success: function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("CostSubTab").getActiveTab().getStore().reload();
									Ext.getCmp("CostSubTab-0").getStore().reload();
								},
								failure: function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"cost","do":"del_item","ino":ino}
							});
						}
					}});
				}
			}),
			'-',
			new Ext.Button({
				id:"CostSubSaveButton",
				text:"변경사항저장하기",
				icon:ENV.dir+"/module/erp/images/common/icon_disk.png",
				handler:function() {
					if (Ext.getCmp("CostSubTab").getActiveTab().getStore().getModifiedRecords().length == 0) {
						Ext.Msg.show({title:"안내",msg:"변경된 사항이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						return false;
					}

					var data = GetGridData(Ext.getCmp("CostSubTab").getActiveTab());

					Ext.Ajax.request({
						url:ENV.dir+"/module/erp/exec/Admin.do.php",
						success: function() {
							Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("CostSubTab").getActiveTab().getStore().reload();
							Ext.getCmp("CostSubTab-0").getStore().reload();
						},
						failure: function() {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						},
						headers:{},
						params:{"action":"cost","do":"mod_item","data":data,"idx":idx}
					});
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"CostSubTab",
				tabPosition:"bottom",
				activeTab:0,
				enableTabScroll:true,
				border:false,
				store:new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:'lists',
						totalProperty:'totalCount',
						fields:[{name:"tab",type:"int"},"title"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"tab", direction:"ASC"},
					baseParams:{"action":"cost","get":"grouplist","mode":"tab","idx":idx,"gno":gno}
				}),
				items:[
					new Ext.Panel({
						id:"LoadingTab",
						title:"로딩중...",
						html:'<div style="width:500px; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">하위공종 및 품목을 로딩중입니다.</div>'
					})
				],
				listeners:{
					tabchange:{fn:function(tabs,tab) {
						if (!tab) return;
						var temp = tab.getId().split("-");
						if (temp.length == 2 && temp[1]) {
							tno = temp[1];
						}

						if (tno != "0") {
							Ext.getCmp("CostSubKeyword").enable();
							Ext.getCmp("CostSubSearchButton").enable();
							Ext.getCmp("CostSubDeleteButton").enable();
							Ext.getCmp("CostSubSaveButton").enable();
						} else {
							Ext.getCmp("CostSubKeyword").disable();
							Ext.getCmp("CostSubSearchButton").disable();
							Ext.getCmp("CostSubDeleteButton").disable();
							Ext.getCmp("CostSubSaveButton").disable();
						}
						if (tab.getId() != "LoadingTab") Ext.getCmp("CostSubKeyword").setValue(Ext.getCmp(tab.getId()).getStore().baseParams.keyword);
					}}
				}
			})
		],
		listeners:{
			render:{fn:function() {
				Ext.getCmp("CostSubTab").store.on("load",function(store) {
					Ext.getCmp("CostSubTab").removeAll();

					for (var i=0, loop=store.getCount();i<loop;i++) {
						if (store.getAt(i).get("tab") == "0") {
							Ext.getCmp("CostSubWindow").setTitle(store.getAt(i).get("title")+" 하위공종 및 품목관리하기");

							Ext.getCmp("CostSubTab").add(
								new Ext.grid.GridPanel({
									id:"CostSubTab-0",
									title:store.getAt(i).get("title")+"집계",
									layout:"fit",
									cm:new Ext.grid.ColumnModel([
										{
											dataIndex:"group",
											hideable:false
										},{
											dataIndex:"workgroup",
											hidden:true,
											summaryType:"data",
											hideable:false
										},{
											header:"공종명",
											dataIndex:"worktype",
											width:195,
											sortable:true,
											summaryType:"data",
											rowIndex:0,
											summaryRenderer:function(value,p,record) {
												return "소계";
											}
										},{
											header:"단가",
											dataIndex:"cost1",
											width:80,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"금액",
											dataIndex:"price1",
											width:95,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"단가",
											dataIndex:"cost2",
											width:80,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"금액",
											dataIndex:"price2",
											width:95,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"단가",
											dataIndex:"cost3",
											width:80,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"금액",
											dataIndex:"price3",
											width:95,
											sortable:false,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"단가",
											dataIndex:"total_cost",
											width:80,
											sortable:false,
											summaryType:"sum",
											renderer:function(value,p,record) {
												record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
												return GridNumberFormat(record.data.total_cost);
											},
											summaryRenderer:GridNumberFormat
										},{
											header:"금액",
											dataIndex:"total_price",
											width:100,
											sortable:false,
											summaryType:"sum",
											renderer:function(value,p,record) {
												record.data.total_price = record.data.price1+record.data.price2+record.data.price3;
												return GridNumberFormat(record.data.total_price);
											},
											summaryRenderer:GridNumberFormat
										}
									]),
									store:new Ext.data.GroupingStore({
										proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["idx","group","gno","worktype","tno",{name:"cost1",type:"int"},{name:"price1",type:"int"},{name:"cost2",type:"int"},{name:"price2",type:"int"},{name:"cost3",type:"int"},{name:"price3",type:"int"},{name:"sort",type:"int"}]
										}),
										remoteSort:false,
										groupField:"group",
										sortInfo:{field:"sort",direction:"ASC"},
										baseParams:{"action":"cost","get":"grouplist","mode":"group","idx":idx,"gno":gno}
									}),
									plugins:[new Ext.ux.plugins.GroupHeaderGrid({
										rows:[[
											{},
											{},
											{},
											{header:"재료비",colspan:2,align:"center"},
											{header:"노무비",colspan:2,align:"center"},
											{header:"경비",colspan:2,align:"center"},
											{header:"합계",colspan:2,align:"center"}
										]],
										hierarchicalColMenu:true
									}),new Ext.ux.grid.GroupSummary()],
									view:new Ext.grid.GroupingView({
										enableGroupingMenu:false,
										hideGroupedColumn:true,
										showGroupName:false,
										enableNoGroups:false,
										headersDisabled:false,
										showGroupHeader:false
									}),
									listeners:{
										render:{fn:function() {
											Ext.getCmp("CostSubTab-0").getStore().load();
										}},
										rowdblclick:{fn:function(grid,row,e) {
											if (grid.getStore().getAt(row).get("tno") != "0") Ext.getCmp("CostSubTab").activate("CostSubTab-"+grid.getStore().getAt(row).get("tno"));
										}}
									}
								})
							);
						} else {
							CostSubViewCreateTabPanel(idx,store.getAt(i).get("tab"),store.getAt(i).get("title"));
						}
					}
				});
				Ext.getCmp("CostSubTab").store.load();
			}},
			add:{fn:function(tabs,tab) {
				if (tno && tab.getId() == "CostSubTab-"+tno) tabs.activate(tab.getId());
				else if (!tno && tab.getId() == "CostSubTab-0") tabs.activate("CostSubTab-0");
			}},
			close:{fn:function() {
				Ext.getCmp("CostSheet").getStore().reload();
				Ext.getCmp("CostList").getStore().reload();
			}}
		}
	}).show();
}

// 품목추가하기
function CostAddItem(idx,gno) {
	var ItemStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"itemcode","workgroup","bgno","worktype","btno","title","size","unit",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"item","get":"list","bgno":"","btno":"","keyword":""}
	});
	ItemStore.load({params:{start:0,limit:30}});

	var WorktypeStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","worktype","sort"]
		}),
		remoteSort:false,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{"action":"workspace","get":"worktype","gno":gno,"is_all":"false"},
	});
	WorktypeStore.load();

	new Ext.Window({
		id:"CostAddItemAddWindow",
		title:"품목추가하기",
		width:900,
		height:500,
		modal:true,
		maximizable:true,
		layout:"fit",
		items:[
			new Ext.Panel({
				border:false,
				layout:"border",
				items:[
					new Ext.grid.GridPanel({
						id:"CostAddItemAddItemList",
						title:"품명DB검색",
						region:"north",
						height:200,
						layout:"fit",
						margins:"5 5 0 5",
						split:true,
						collapsible:true,
						tbar:[
							new Ext.form.ComboBox({
								id:"CostAddItemWorkgroup",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","workgroup","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"base","get":"workgroup","is_all":"true"}
								}),
								width:80,
								editable:false,
								mode:"local",
								displayField:"workgroup",
								valueField:"idx",
								listeners:{
									render:{fn:function() {
										Ext.getCmp("CostAddItemWorkgroup").getStore().load();
										Ext.getCmp("CostAddItemWorkgroup").getStore().on("load",function(store) {
											Ext.getCmp("CostAddItemWorkgroup").setValue(store.getAt(0).get("idx"));
										});
									}},
									select:{fn:function(form) {
										Ext.getCmp("CostAddItemWorktype").getStore().baseParams.bgno = form.getValue();
										Ext.getCmp("CostAddItemWorktype").getStore().load();
									}}
								}
							}),
							' ',
							new Ext.form.ComboBox({
								id:"CostAddItemWorktype",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","worktype","value","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"base","get":"worktype","bgno":"0","is_all":"true"}
								}),
								width:120,
								editable:false,
								mode:"local",
								displayField:"worktype",
								valueField:"idx",
								listeners:{
									render:{fn:function() {
										Ext.getCmp("CostAddItemWorktype").getStore().load();
										Ext.getCmp("CostAddItemWorktype").getStore().on("load",function(store) {
											Ext.getCmp("CostAddItemWorktype").setValue(store.getAt(0).get("idx"));
										});
									}}
								}
							}),
							' ',
							new Ext.form.TextField({
								id:"CostAddItemKeyword",
								width:150,
								emptyText:"검색어를 입력하세요.",
								enableKeyEvents:true,
								listeners:{keydown:{fn:function(form,e) {
									if (e.keyCode == 13) {
										ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
										ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
										ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
										ItemStore.load({params:{start:0,limit:30}});
									}
								}}}
							}),
							' ',
							new Ext.Button({
								text:"검색",
								icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
								handler:function() {
									ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
									ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
									ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
									ItemStore.load({params:{start:0,limit:30}});
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"공종명",
								dataIndex:"worktype",
								width:120,
								sortable:false,
								renderer:function(value,p,record) {
									return "["+record.data.workgroup+"]"+record.data.worktype;
								}
							},{
								header:"품명",
								dataIndex:"title",
								width:300,
								sortable:false
							},{
								header:"규격",
								dataIndex:"size",
								width:120
							},{
								header:"단위",
								dataIndex:"unit",
								width:40,
								sortable:false,
								renderer:function(value) {
									return '<div style="text-align:center;">'+value+'</div>';
								}
							},{
								header:"재료비",
								dataIndex:"cost1",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost1);
								}
							},{
								header:"노무비",
								dataIndex:"cost2",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost2);
								}
							},{
								header:"경비",
								dataIndex:"cost3",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost3);
								}
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:ItemStore,
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:ItemStore,
							displayInfo:true,
							displayMsg:"{0} - {1} of {2}",
							emptyMsg:"데이터없음"
						})
					}),
					new Ext.grid.EditorGridPanel({
						id:"CostAddItemAddInsertList",
						title:"데이터입력",
						region:"center",
						layout:"fit",
						margins:"0 5 5 5",
						tbar:[
							new Ext.Button({
								text:"선택항목추가하기",
								icon:ENV.dir+"/module/erp/images/common/icon_arrow_down.png",
								handler:function() {
									var checked = Ext.getCmp("CostAddItemAddItemList").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}

									var record = Ext.data.Record.create([{name:"itemno",type:"int"},"is_new",{name:"worktype",type:"string"},{name:"title",type:"string"},{name:"size",type:"string"},{name:"unit",type:"string"},"avgcost1","avgcost2","avgcost3"]);

									Ext.getCmp("CostAddItemAddInsertList").stopEditing();
									for (var i=0, loop=checked.length;i<loop;i++) {
										var data = checked[i];
										var insert = new Array();
										insert["is_new"] = "FALSE";
										insert["itemcode"] = data.get("itemcode");
										insert["worktype"] = data.get("worktype");
										insert["title"] = data.get("title");
										insert["size"] = data.get("size");
										insert["unit"] = data.get("unit");
										insert["ea"] = data.get("ea");
										insert["cost1"] = data.get("cost1");
										insert["cost2"] = data.get("cost2");
										insert["cost3"] = data.get("cost3");
										insert["avgcost1"] = data.get("avgcost1");
										insert["avgcost2"] = data.get("avgcost2");
										insert["avgcost3"] = data.get("avgcost3");

										GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
									}
									Ext.getCmp("CostAddItemAddInsertList").startEditing(0,0);
								}
							}),
							'-',
							new Ext.Button({
								text:"엑셀파일에서 불러오기",
								icon:ENV.dir+"/module/erp/images/common/icon_page_white_excel.png",
								handler:function() {
									new Ext.Window({
										id:"CostAddExcelWindow",
										title:"엑셀파일에서 불러오기",
										width:400,
										height:120,
										layout:"fit",
										modal:true,
										resizable:false,
										items:[
											new Ext.form.FormPanel({
												id:"CostAddExcelForm",
												border:false,
												style:"padding:10px; background:#FFFFFF;",
												labelAlign:"right",
												labelWidth:65,
												autoWidth:true,
												fileUpload:true,
												errorReader:new Ext.form.XmlErrorReader(),
												items:[
													new Ext.ux.form.FileUploadField({
														fieldLabel:"엑셀파일",
														name:"file",
														width:270,
														buttonText:"",
														buttonCfg:{iconCls:"upload-file"},
														emptyText:"규격에 맞는 엑셀파일을 선택하여 주세요.",
														listeners:{
															focus:{fn:function(form) {
																if (form.getValue()) {
																	Ext.Msg.show({title:"초기화선택",msg:"엑셀파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																		if (button == "ok") {
																			form.reset();
																		}
																	}});
																}
															}},
															invalid:{fn:function(form,text) {
																if (form.getValue()) {
																	form.reset();
																	form.markInvalid(text);
																}
															}}
														}
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														var code;
														Ext.each(action.result.errors,function(item,index,allItems) { code = item.id; });

														var store = new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:'lists',
																totalProperty:'totalCount',
																fields:["itemcode","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"sort",type:"int"}]
															}),
															remoteSort:true,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"cost","get":"excel","code":code}
														});
														store.load();

														store.on("load",function(store) {
															Ext.getCmp("CostAddItemAddInsertList").stopEditing();
															for (var i=0, loop=store.getCount();i<loop;i++) {
																var data = store.getAt(i);
																var insert = new Array();
																insert["is_new"] = "FALSE";
																insert["itemcode"] = data.get("itemcode");
																insert["worktype"] = data.get("worktype");
																insert["title"] = data.get("title");
																insert["size"] = data.get("size");
																insert["unit"] = data.get("unit");
																insert["ea"] = data.get("ea");
																insert["cost1"] = data.get("cost1");
																insert["cost2"] = data.get("cost2");
																insert["cost3"] = data.get("cost3");
																insert["avgcost1"] = data.get("avgcost1");
																insert["avgcost2"] = data.get("avgcost2");
																insert["avgcost3"] = data.get("avgcost3");

																GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
															}
															Ext.getCmp("CostAddItemAddInsertList").startEditing(0,0);
															Ext.getCmp("CostAddExcelWindow").close();
														});
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
												handler:function() {
													Ext.getCmp("CostAddExcelForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=cost&do=add_item&mode=excel&idx="+idx,waitMsg:"품목을 추가중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
												handler:function() {
													Ext.getCmp("CostAddExcelWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							'-',
							new Ext.Button({
								text:"새항목추가하기",
								icon:ENV.dir+"/module/erp/images/common/icon_table_row_insert.png",
								handler:function() {
									var insert = new Array();
									insert["is_new"] = "TRUE";
									GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),value);
								}
							}),
							new Ext.Button({
								text:"선택항목삭제하기",
								icon:ENV.dir+"/module/erp/images/common/icon_table_row_delete.png",
								handler:function() {
									GridDeleteRow(Ext.getCmp("CostAddItemAddInsertList"));
								}
							}),
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								dataIndex:"itemcode",
								hidden:true,
								hideable:false
							},{
								header:"공종명",
								dataIndex:"worktype",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									if (WorktypeStore.find("worktype",value,0,false,false) == -1) {
										return '<div style="color:#FF0000;" onmouseover="Tip(true,\'현재 공종그룹에 없는 하위 공종명입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
									} else {
										return value;
									}
								},
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"품명",
								dataIndex:"title",
								width:150,
								sortable:false,
								renderer:GridItemNotFound
							},{
								header:"규격",
								dataIndex:"size",
								width:100,
								sortable:false,
								renderer:GridItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"단위",
								dataIndex:"unit",
								width:40,
								sortable:false,
								renderer:GridItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"수량",
								dataIndex:"ea",
								width:40,
								sortable:false,
								renderer:function(value,p,record) {
									if (!value) record.data.ea = 0;
									return GridNumberFormat(value);
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"단가",
								dataIndex:"cost1",
								width:60,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost1);
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금액",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridNumberFormat(record.data.ea*record.data.cost1);
								}
							},{
								header:"단가",
								dataIndex:"cost2",
								width:60,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost1);
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금액",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridNumberFormat(record.data.ea*record.data.cost2);
								}
							},{
								header:"단가",
								dataIndex:"cost3",
								width:60,
								sortable:false,
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost1);
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금액",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridNumberFormat(record.data.ea*record.data.cost3);
								}
							},{
								header:"단가",
								dataIndex:"total_cost",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
									return GridNumberFormat(record.data.total_cost);
								}
							},{
								header:"금액",
								dataIndex:"total_price",
								width:80,
								sortable:false,
								renderer:function(value,p,record) {
									return GridNumberFormat(record.data.ea*record.data.total_cost);
								}
							}
						]),
						clicksToEdit:1,
						trackMouseOver:true,
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.SimpleStore({
							fields:["is_new","itemcode","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"total_cost",type:"int"},{name:"total_price",type:"int"}]
						}),
						plugins:[new Ext.ux.plugins.GroupHeaderGrid({
							rows:[[
								{},
								{},
								{},
								{},
								{},
								{},
								{},
								{header:"재료비",colspan:2,align:"center"},
								{header:"노무비",colspan:2,align:"center"},
								{header:"경비",colspan:2,align:"center"},
								{header:"합계",colspan:2,align:"center"}
							]],
							hierarchicalColMenu:true
						})],
						listeners:{
							render:{fn:function() {
								GridEditorAutoMatchItem(Ext.getCmp("CostAddItemAddInsertList"));
							}},
							afteredit:{fn:function(object) {
								GridAutoMatchItem(object);

								if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
									if (!object.value || parseInt(object.value) <= 0) object.grid.getStore().getAt(object.row).set(object.field,0);
								}
							}}
						}
					})
				]
			}),
			new Ext.form.FormPanel({
				id:"CostAddItemAddForm",
				errorReader:new Ext.form.XmlErrorReader(),
				items:[
					new Ext.form.Hidden({
						name:"data"
					})
				],
				listeners:{actioncomplete:{fn:function(form,action) {
					if (action.type == "submit") {
						var except;
						Ext.each(action.result.errors,function(item,index,allItems) { except = item.id; });

						if (except == "0") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						} else {
							Ext.Msg.show({title:"안내",msg:"기존의 품목과 중복되는 "+except+"개의 항목을 제외하고 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						}
						Ext.getCmp("CostSubTab").store.reload();
						Ext.getCmp("CostAddItemAddWindow").close();
					}
				}}}
			})
		],
		buttons:[
			new Ext.Button({
				text:"확인",
				icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
				handler:function() {
					var isNotMatch = false;
					for (var i=0, loop=Ext.getCmp("CostAddItemAddInsertList").getStore().getCount();i<loop;i++) {
						var data = Ext.getCmp("CostAddItemAddInsertList").getStore().getAt(i);
						if (!data.get("worktype") || !data.get("title")) {
							Ext.Msg.show({title:"에러",msg:"공종명 또는 품명이 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						}
						if (!data.get("itemcode")) isNotMatch = true;
					}
					Ext.getCmp("CostAddItemAddForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("CostAddItemAddInsertList")));

					if (isNotMatch == true) {
						Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목이 있습니다.<br />품명DB에서 검색되지 않은 항목을 그대로 저장하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
							if (button == "ok") {
								Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목을 품명DB에 신규로 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
									if (button == "ok") {
										var is_insert = "true";
									} else {
										var is_insert = "false";
									}

									Ext.getCmp("CostAddItemAddForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=cost&do=add_item&mode=insert&gno="+gno+"&idx="+idx+"&is_insert="+is_insert,waitMsg:"데이터를 전송중입니다."});
								}});
							}
						}});
					} else {
						Ext.getCmp("CostAddItemAddForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=cost&do=add_item&mode=insert&gno="+gno+"&idx="+idx,waitMsg:"데이터를 전송중입니다."});
					}
				}
			}),
			new Ext.Button({
				text:"취소",
				icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
				handler:function() {
					Ext.getCmp("CostAddItemAddWindow").close();
				}
			})
		]
	}).show();
}

function CostSubViewCreateTabPanel(idx,tno,title) {
	Ext.getCmp("CostSubTab").add(
		new Ext.grid.EditorGridPanel({
			id:"CostSubTab-"+tno,
			title:title,
			layout:"fit",
			cm:new Ext.grid.ColumnModel([
				new Ext.grid.CheckboxSelectionModel(),
				{
					dataIndex:"idx",
					hidden:true,
					hideable:false
				},{
					dataIndex:"group",
					hidden:true,
					hideable:false
				},{
					header:"품명",
					dataIndex:"title",
					width:145,
					sortable:false,
					summaryType:"count",
					summaryRenderer:function(value) {
						return '<span style="font-family:tahoma; font-size:10px;">Total '+GetNumberFormat(value)+' Item'+(value > 1 ? 's' : '')+'</span>';
					}
				},{
					header:"규격",
					dataIndex:"size",
					width:80,
					sortable:false
				},{
					header:"단위",
					dataIndex:"unit",
					width:40,
					sortable:false
				},{
					header:"수량",
					dataIndex:"ea",
					width:40,
					sortable:false,
					renderer:GridNumberFormat,
					editor:new Ext.form.NumberField({selectOnFocus:true})
				},{
					header:"단가",
					dataIndex:"cost1",
					width:60,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						return GridItemAvgCost(value,record.data.avgcost1);
					},
					summaryRenderer:GridNumberFormat,
					editor:new Ext.form.NumberField({selectOnFocus:true})
				},{
					header:"금액",
					dataIndex:"price1",
					width:80,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.price1 = record.data.ea*record.data.cost1;
						return GridNumberFormat(record.data.price1);
					},
					summaryRenderer:GridNumberFormat
				},{
					header:"단가",
					dataIndex:"cost2",
					width:60,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						return GridItemAvgCost(value,record.data.avgcost1);
					},
					summaryRenderer:GridNumberFormat,
					editor:new Ext.form.NumberField({selectOnFocus:true})
				},{
					header:"금액",
					dataIndex:"price2",
					width:80,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.price1 = record.data.ea*record.data.cost1;
						return GridNumberFormat(record.data.price1);
					},
					summaryRenderer:GridNumberFormat
				},{
					header:"단가",
					dataIndex:"cost3",
					width:60,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						return GridItemAvgCost(value,record.data.avgcost1);
					},
					summaryRenderer:GridNumberFormat,
					editor:new Ext.form.NumberField({selectOnFocus:true})
				},{
					header:"금액",
					dataIndex:"price3",
					width:80,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.price1 = record.data.ea*record.data.cost1;
						return GridNumberFormat(record.data.price1);
					},
					summaryRenderer:GridNumberFormat
				},{
					header:"단가",
					dataIndex:"total_cost",
					width:80,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
						return GridNumberFormat(record.data.total_cost);
					},
					summaryRenderer:GridNumberFormat
				},{
					header:"금액",
					dataIndex:"total_price",
					width:80,
					sortable:false,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.total_price = record.data.ea*record.data.total_cost;
						return GridNumberFormat(record.data.total_price);
					},
					summaryRenderer:GridNumberFormat
				}
			]),
			clicksToEdit:1,
			trackMouseOver:true,
			sm:new Ext.grid.CheckboxSelectionModel(),
			store:new Ext.data.GroupingStore({
				proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
				reader:new Ext.data.JsonReader({
					root:'lists',
					totalProperty:'totalCount',
					fields:[{name:"idx",type:"int"},"group","itemcode","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
				}),
				remoteSort:true,
				sortInfo:{field:"idx",direction:"ASC"},
				groupField:"group",
				baseParams:{"action":"cost","get":"grouplist","mode":"tabdata","keyword":"","idx":idx,"tno":tno}
			}),
			plugins:[new Ext.ux.plugins.GroupHeaderGrid({
				rows:[[
					{},
					{},
					{},
					{},
					{},
					{},
					{},
					{header:"재료비",colspan:2,align:"center"},
					{header:"노무비",colspan:2,align:"center"},
					{header:"경비",colspan:2,align:"center"},
					{header:"합계",colspan:2,align:"center"}
				]],
				hierarchicalColMenu:true
			}),new Ext.ux.grid.GroupSummary()],
			view:new Ext.grid.GroupingView({
				enableGroupingMenu:false,
				hideGroupedColumn:true,
				showGroupName:false,
				enableNoGroups:false,
				headersDisabled:false,
				showGroupHeader:false
			}),
			listeners:{
				render:{fn:function() {
					Ext.getCmp("CostSubTab-"+tno).getStore().load();
				}},
				afteredit:{fn:function(object) {
					if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
						if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
					}
				}}
			}
		})
	);
}

function WorkGroupSetup(wno) {
	new Ext.Window({
		title:"공종그룹관리하기",
		modal:true,
		width:650,
		height:400,
		layout:"fit",
		items:[
			new Ext.Panel({
				border:false,
				layout:"border",
				items:[
					new Ext.grid.GridPanel({
						id:"WorkgroupSetupList1",
						title:"공종그룹",
						region:"west",
						width:285,
						split:true,
						layout:"fit",
						margins:"5 0 5 5",
						tbar:[
							new Ext.Button({
								text:"공종그룹추가",
								icon:ENV.dir+"/module/erp/images/common/icon_package_add.png",
								handler:function() {
									new Ext.Window({
										id:"WorkgroupSetupAddGroupWindow",
										title:"공종그룹추가하기",
										width:300,
										height:170,
										layout:"fit",
										modal:true,
										resizable:false,
										items:[
											new Ext.form.FormPanel({
												id:"WorkgroupSetupAddGroupForm",
												border:false,
												style:"padding:10px; background:#FFFFFF;",
												labelAlign:"right",
												labelWidth:65,
												autoWidth:true,
												errorReader:new Ext.form.XmlErrorReader(),
												items:[
													new Ext.form.TextField({
														fieldLabel:"공종그룹명",
														width:180,
														name:"workgroup",
														allowBlank:false
													}),
													new Ext.form.ComboBox({
														fieldLabel:"공종타입",
														width:180,
														hiddenName:"bgno",
														typeAhead:true,
														lazyRender:false,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","workgroup","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"base","get":"workgroup"}
														}),
														editable:false,
														mode:"local",
														displayField:"workgroup",
														valueField:"idx",
														emptyText:"공종타입을 선택하세요.",
														listeners:{render:{fn:function(form) {
															form.getStore().load();
														}}}
													}),
													new Ext.ux.form.SpinnerField({
														fieldLabel:"정렬순서",
														width:180,
														minValue:1,
														maxValue:99,
														name:"sort",
														value:1
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("WorkgroupSetupList1").getStore().reload();
														Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
														Ext.getCmp("WorkgroupSetupList2").getStore().reload();
														Ext.getCmp("WorkgroupSetupAddGroupWindow").close();
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
												handler:function() {
													Ext.getCmp("WorkgroupSetupAddGroupForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=workspace&do=workgroup&mode=add&wno="+wno,waitMsg:"공종그룹을 추가중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
												handler:function() {
													Ext.getCmp("WorkgroupSetupAddGroupWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"공종그룹삭제",
								icon:ENV.dir+"/module/erp/images/common/icon_package_delete.png",
								handler:function() {
									var checked = Ext.getCmp("WorkgroupSetupList1").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 공종그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}

									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									var idx = idxs.join(",");

									Ext.Msg.show({title:"안내",msg:"공종그룹을 삭제하면, 하위 공종그룹의 모든 데이터가 삭제됩니다.<br />공종그룹을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Ajax.request({
												url:ENV.dir+"/module/erp/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("WorkgroupSetupList1").getStore().reload();
													Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
													Ext.getCmp("WorkgroupSetupList2").getStore().reload();
													Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"workspace","do":"workgroup","mode":"delete","wno":wno,"idx":idx}
											});
										}
									}});
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								dataIndex:"bgno",
								hideable:false,
								renderer:function(value,p,record) {
									return GetFixNumberLength(record.data.bgno,2)+"."+record.data.basegroup;
								}
							},{
								header:"정렬",
								dataIndex:"sort",
								width:50,
								sortable:false,
								renderer:function(value,p,record) {
									return GetFixNumberLength(record.data.bgno,2)+GetFixNumberLength(value,2);
								}
							},{
								header:"공종그룹명",
								dataIndex:"workgroup",
								width:190,
								sortable:false,
								renderer:function(value,p,record) {
									if (record.data.is_construction == "TRUE") return '<img src="'+ENV.dir+'/module/erp/images/common/icon_construction.gif" style="vertical-align:middle; margin:-1px 5px -1px 0px;" />'+value;
									else return value;
								}
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","bgno","basegroup","workgroup","sort"]
							}),
							remoteSort:false,
							groupField:"bgno",
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"workspace","get":"workgroup","wno":wno},
						}),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							render:{fn:function() {
								Ext.getCmp("WorkgroupSetupList1").getStore().load();
							}},
							rowclick:{fn:function(grid,row,e) {
								if (grid.selModel.getSelections().length == 1) {
									Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = grid.selModel.getSelections()[0].get("idx");
								} else {
									Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = "";
								}
								Ext.getCmp("WorkgroupSetupList2").getStore().reload();
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);

								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("workgroup")+'</b>');
								menu.add({
									text:"공종그룹수정",
									icon:ENV.dir+"/module/erp/images/common/icon_package_edit.png",
									handler:function(item) {
										new Ext.Window({
											id:"WorkgroupSetupModifyGroupWindow",
											title:"공종그룹수정",
											width:300,
											height:170,
											layout:"fit",
											modal:true,
											resizable:false,
											items:[
												new Ext.form.FormPanel({
													id:"WorkgroupSetupModifyGroupForm",
													border:false,
													style:"padding:10px; background:#FFFFFF;",
													labelAlign:"right",
													labelWidth:65,
													autoWidth:true,
													errorReader:new Ext.form.XmlErrorReader(),
													items:[
														new Ext.form.TextField({
															fieldLabel:"공종그룹명",
															width:180,
															name:"workgroup",
															allowBlank:false,
															value:data.get("workgroup")
														}),
														new Ext.form.ComboBox({
															fieldLabel:"공종타입",
															width:180,
															hiddenName:"bgno",
															typeAhead:true,
															lazyRender:false,
															store:new Ext.data.Store({
																proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
																reader:new Ext.data.JsonReader({
																	root:"lists",
																	totalProperty:"totalCount",
																	fields:["idx","workgroup","sort"]
																}),
																remoteSort:false,
																sortInfo:{field:"sort",direction:"ASC"},
																baseParams:{"action":"base","get":"workgroup"}
															}),
															editable:false,
															mode:"local",
															displayField:"workgroup",
															valueField:"idx",
															emptyText:"공종타입을 선택하세요.",
															value:data.get("bgno"),
															listeners:{render:{fn:function(form) {
																form.getStore().load();
															}}}
														}),
														new Ext.ux.form.SpinnerField({
															fieldLabel:"정렬순서",
															width:180,
															minValue:1,
															maxValue:99,
															name:"sort",
															value:data.get("sort")
														})
													],
													listeners:{actioncomplete:{fn:function(form,action) {
														if (action.type == "submit") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("WorkgroupSetupList1").getStore().reload();
															Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
															Ext.getCmp("WorkgroupSetupList2").getStore().reload();
															Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
														}
													}}}
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
													handler:function() {
														Ext.getCmp("WorkgroupSetupModifyGroupForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=workspace&do=workgroup&mode=modify&wno="+wno+"&idx="+data.get("idx"),waitMsg:"공종그룹을 수정중입니다."});
													}
												}),
												new Ext.Button({
													text:"취소",
													icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
													handler:function() {
														Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
													}
												})
											]
										}).show();
									}
								});
								menu.add({
									text:"공종그룹삭제",
									icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_package_delete.png"),
									handler:function(item) {
										Ext.Msg.show({title:"안내",msg:"공종그룹을 삭제하면, 하위 공종그룹의 모든 데이터가 삭제됩니다.<br />공종그룹을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Ajax.request({
													url:ENV.dir+"/module/erp/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("WorkgroupSetupList1").getStore().reload();
														Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
														Ext.getCmp("WorkgroupSetupList2").getStore().reload();
														Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"workspace","do":"workgroup","mode":"delete","wno":wno,"idx":grid.getStore().getAt(row).get("idx")}
												});
											}
										}});
									}
								});

								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"WorkgroupSetupList2",
						title:"하위공종",
						region:"center",
						split:true,
						layout:"fit",
						margins:"5 5 5 0",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"정렬",
								dataIndex:"sort",
								width:60,
								sortable:false
							},{
								header:"공종그룹명",
								dataIndex:"worktype",
								width:225,
								sortable:false
							}
						]),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","workgroup","sort"]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"workspace","get":"worktype","workgroup":"","wno":wno,"is_all":"false"},
						}),
						listeners:{
							render:{fn:function() {
								Ext.getCmp("WorkgroupSetupList2").getStore().load();
							}}
						}
					})
				]
			})
		],
		listeners:{close:{fn:function() {
			if (Ext.getCmp("CostList")) Ext.getCmp("CostList").getStore().reload();
			if (Ext.getCmp("CostAddGroupList")) Ext.getCmp("CostAddGroupList").getStore().reload();
		}}}
	}).show();
}