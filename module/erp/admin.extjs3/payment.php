<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var PaymentStore1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"sort","title",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"lastyear",type:"int"}<?php for ($i=1;$i<=12;$i++) { ?>,{name:"p<?php echo $i; ?>",type:"int"}<?php } ?>,{name:"summary",type:"int"},{name:"total",type:"int"},{name:"remainContract",type:"int"},{name:"remainExec",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{"action":"monthly_payment","get":"list","category":"working","year":"<?php echo Request('iErpYear','cookie') ? Request('iErpYear','cookie') : date('Y'); ?>"}
	});

	var PaymentStore2 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"sort","title",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"lastyear",type:"int"}<?php for ($i=1;$i<=12;$i++) { ?>,{name:"p<?php echo $i; ?>",type:"int"}<?php } ?>,{name:"summary",type:"int"},{name:"total",type:"int"},{name:"remainContract",type:"int"},{name:"remainExec",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{"action":"monthly_payment","get":"list","category":"complete","year":"<?php echo Request('iErpYear','cookie') ? Request('iErpYear','cookie') : date('Y'); ?>"}
	});

	var WorkspaceListCm = new Ext.grid.ColumnModel([
		new Ext.grid.RowNumberer(),
		{
			dataIndex:"sort",
			hidden:true,
			hidable:false
		},{
			header:"현장명",
			dataIndex:"title",
			width:250
		},{
			header:"계약금액",
			dataIndex:"contract",
			width:80,
			renderer:GridNumberFormat
		},{
			header:"실행금액",
			dataIndex:"exec",
			width:80,
			renderer:GridNumberFormat
		},{
			header:"전년까지",
			dataIndex:"lastyear",
			width:80,
			renderer:GridNumberFormat
		}<?php for ($i=1;$i<=12;$i++) { ?>,{
			header:"<?php echo $i; ?>월",
			dataIndex:"p<?php echo $i; ?>",
			width:80,
			renderer:GridNumberFormat
		}<?php } ?>,{
			header:"소계",
			dataIndex:"summary",
			width:80,
			renderer:function(value,p,record) {
				record.data.summary = record.data.p1<?php for ($i=2;$i<=12;$i++) { ?>+record.data.p<?php echo $i; ?><?php } ?>;
				return GridNumberFormat(record.data.summary);
			}
		},{
			header:"합계",
			dataIndex:"total",
			width:80,
			renderer:function(value,p,record) {
				record.data.total = record.data.lastyear+record.data.summary;
				return GridNumberFormat(record.data.total);
			}
		},{
			header:"잔여계약금",
			dataIndex:"remainContract",
			width:80,
			renderer:function(value,p,record) {
				record.data.remainContract = record.data.contract-record.data.total;
				return GridNumberFormat(record.data.remainContract);
			}
		},{
			header:"잔여실행금",
			dataIndex:"remainExec",
			width:80,
			renderer:function(value,p,record) {
				record.data.remainExec = record.data.exec-record.data.total;
				return GridNumberFormat(record.data.remainExec);
			}
		}
	]);

	function PaymentFunction(idx,month) {
		new Ext.Window({
			id:"PaymentWindow",
			title:new Date(month+"-01").format("Y년 m월")+" 기성현황",
			width:950,
			height:550,
			modal:true,
			layout:"fit",
			tbar:[
				new Ext.Button({
					id:"LockWorkspace",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lock_open.png",
					text:"현장수정잠금",
					enableToggle:true,
					handler:function(button) {

					}
				}),
				'-',
				new Ext.Button({
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_relationship.png",
					text:"현장청구내역 불러오기",
					handler:function() {
						Ext.Msg.show({title:"안내",msg:"현장청구내역을 불러올경우, 지금까지의 본사조절내역이 초기화됩니다.<br />현장청구내역을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
							if (button == "ok") {
								Ext.getCmp("PaymentForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=monthly_payment&do=load&wno="+idx+"&date="+month,waitMsg:"현장청구내역을 로딩중입니다."});
							}
						}});
					}
				}),
				'-',
				new Ext.Button({
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
					text:"엑셀파일로 변환",
					handler:function() {

					}
				})
			],
			items:[
				new Ext.grid.GridPanel({
					id:"PaymentList",
					border:false,
					cm:new Ext.grid.ColumnModel([
						{
							dataIndex:"basegroup",
							hideable:false,
							summaryType:"data"
						},{
							header:"공종그룹",
							dataIndex:"workgroup",
							width:360,
							summaryType:"data",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
								} else {
									return "소계";
								}
							}
						},{
							header:"계약금액",
							dataIndex:"contract",
							width:110,
							renderer:GridNumberFormat,
							summaryType:"sum",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("PaymentList").getStore().sum("contract"))+'</div>';
								} else {
									return GridNumberFormat(value);
								}
							}
						},{
							header:"실행금액",
							dataIndex:"exec",
							width:110,
							renderer:GridNumberFormat,
							summaryType:"sum",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("PaymentList").getStore().sum("exec"))+'</div>';
								} else {
									return GridNumberFormat(value);
								}
							}
						},{
							header:"발주금액",
							dataIndex:"order",
							width:110,
							renderer:GridNumberFormat,
							summaryType:"sum",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("PaymentList").getStore().sum("order"))+'</div>';
								} else {
									return GridNumberFormat(value);
								}
							}
						},{
							header:"현장청구금액",
							dataIndex:"workspace",
							width:110,
							renderer:GridNumberFormat,
							summaryType:"sum",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("PaymentList").getStore().sum("workspace"))+'</div>';
								} else {
									return GridNumberFormat(value);
								}
							}
						},{
							header:"본사조절금액",
							dataIndex:"commander",
							width:110,
							renderer:GridNumberFormat,
							summaryType:"sum",
							summaryRenderer:function(value,p,record) {
								var lastRow = Ext.getCmp("PaymentList").getStore().getAt(Ext.getCmp("PaymentList").getStore().getCount()-1);
								if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
									return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("PaymentList").getStore().sum("commander"))+'</div>';
								} else {
									return GridNumberFormat(value);
								}
							}
						}
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["basegroup","gno","workgroup",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"order",type:"int"},{name:"workspace",type:"int"},{name:"commander",type:"int"},"sort"]
						}),
						remoteSort:true,
						sortInfo:{field:"sort",direction:"ASC"},
						groupField:"basegroup",
						baseParams:{"action":"monthly_payment","get":"group","wno":idx,"date":month}
					}),
					plugins:new Ext.ux.grid.GroupSummary(),
					view:new Ext.grid.GroupingView({
						enableGroupingMenu:false,
						hideGroupedColumn:true,
						showGroupName:false,
						enableNoGroups:false,
						headersDisabled:false
					}),
					listeners:{
						render:{fn:function() {
							Ext.getCmp("PaymentList").getStore().load();
						}},
						rowdblclick:{fn:function(grid,row) {
							PaymentGroupFunction(idx,month,grid.getStore().getAt(row).get("gno"));
						}}
					}
				}),
				new Ext.form.FormPanel({
					id:"PaymentForm",
					errorReader:new Ext.form.XmlErrorReader(),
					listeners:{actioncomplete:{fn:function(form,action) {
						Ext.getCmp("PaymentList").getStore().load();
					}}}
				})
			],
			listeners:{close:{fn:function() {
				Ext.getCmp("ListTab").getActiveTab().getStore().load();
			}}}
		}).show();
	}

	function PaymentGroupFunction(idx,month,gno) {
		new Ext.Window({
			id:"PaymentGroupWindow",
			title:"하위공종 및 품목관리하기",
			width:940,
			height:520,
			layout:"fit",
			modal:true,
			maximizable:true,
			tbar:[
				new Ext.Button({
					id:"ShowContractButton",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table.png",
					text:"계약내역보기",
					enableToggle:true,
					handler:function(button) {
						var temp = Ext.getCmp("PaymentGroupTab").getActiveTab().getId().split("-");
						var tno = temp[1];
						Ext.getCmp("PaymentGroupTab").store.getAt(Ext.getCmp("PaymentGroupTab").store.find("tab",tno,false,false)).set("contract",button.pressed == true ? "TRUE" : "FALSE");
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(7,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(8,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(9,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(10,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(11,!button.pressed);
					}
				}),
				' ',
				new Ext.Button({
					id:"ShowExecButton",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_lightning.png",
					text:"실행내역보기",
					enableToggle:true,
					handler:function(button) {
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(12,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(13,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(14,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(15,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(16,!button.pressed);
					}
				}),
				' ',
				new Ext.Button({
					id:"ShowOrderButton",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry.png",
					text:"발주내역보기",
					enableToggle:true,
					handler:function(button) {
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(17,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(18,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(19,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(20,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(21,!button.pressed);
					}
				}),
				' ',
				new Ext.Button({
					id:"ShowWorkspaceButton",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building.png",
					text:"현장청구내역보기",
					enableToggle:true,
					handler:function(button) {
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(22,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(23,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(24,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(25,!button.pressed);
						Ext.getCmp("PaymentGroupTab").getActiveTab().colModel.setHidden(26,!button.pressed);
					}
				}),
				'-',
				new Ext.Button({
					id:"SaveButton",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
					text:"변경사항저장하기",
					handler:function() {
						var data = GetGridData(Ext.getCmp("PaymentGroupTab").getActiveTab());
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								Ext.getCmp("PaymentGroupTab").getActiveTab().getStore().commitChanges();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"monthly_payment","do":"save","wno":idx,"date":month,"data":data}
						});
					}
				})
			],
			items:[
				new Ext.TabPanel({
					id:"PaymentGroupTab",
					tabPosition:"bottom",
					activeTab:0,
					enableTabScroll:true,
					border:false,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:'lists',
							totalProperty:'totalCount',
							fields:[{name:"tab",type:"int"},"title","contract","exec","order","workspace"]
						}),
						remoteSort:false,
						groupField:"group",
						sortInfo:{field:"tab", direction:"ASC"},
						baseParams:{"action":"monthly_payment","get":"grouplist","mode":"tab","wno":idx,"gno":gno}
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
							var tno = temp[1];
							if (tab.getId() != "LoadingTab" && tno != "0") {
								var store = Ext.getCmp("PaymentGroupTab").store.getAt(Ext.getCmp("PaymentGroupTab").store.find("tab",tno,0,false,false));
								Ext.getCmp("ShowContractButton").enable();
								Ext.getCmp("ShowExecButton").enable();
								Ext.getCmp("ShowOrderButton").enable();
								Ext.getCmp("ShowWorkspaceButton").enable();

								Ext.getCmp("ShowContractButton").toggle(store.get("contract") == "TRUE");
								Ext.getCmp("ShowExecButton").toggle(store.get("exec") == "TRUE");
								Ext.getCmp("ShowOrderButton").toggle(store.get("order") == "TRUE");
								Ext.getCmp("ShowWorkspaceButton").toggle(store.get("workspace") == "TRUE");
							} else {
								Ext.getCmp("ShowContractButton").toggle(false);
								Ext.getCmp("ShowExecButton").toggle(false);
								Ext.getCmp("ShowOrderButton").toggle(false);
								Ext.getCmp("ShowWorkspaceButton").toggle(false);

								Ext.getCmp("ShowContractButton").disable();
								Ext.getCmp("ShowExecButton").disable();
								Ext.getCmp("ShowOrderButton").disable();
								Ext.getCmp("ShowWorkspaceButton").disable();
							}
						}}
					}
				})
			],
			listeners:{
				render:{fn:function() {
					Ext.getCmp("PaymentGroupTab").store.on("load",function(store) {
						Ext.getCmp("PaymentGroupTab").removeAll();

						for (var i=0, loop=store.getCount();i<loop;i++) {
							if (store.getAt(i).get("tab") == "0") {
								Ext.getCmp("PaymentGroupWindow").setTitle(store.getAt(i).get("title")+" 하위공종 및 품목관리하기");

								Ext.getCmp("PaymentGroupTab").add(
									new Ext.grid.GridPanel({
										id:"PaymentGroupTab-0",
										title:store.getAt(i).get("title")+"집계",
										layout:"fit",
										cm:new Ext.grid.ColumnModel([
										{
											dataIndex:"group",
											hideable:false
										},{
											header:"공종명",
											dataIndex:"worktype",
											width:340,
											summaryType:"data",
											summaryRenderer:function(value,p,record) {
												return "소계";
											}
										},{
											header:"계약금액",
											dataIndex:"contract",
											width:110,
											renderer:GridNumberFormat,
											summaryType:"sum"
										},{
											header:"실행금액",
											dataIndex:"exec",
											width:110,
											renderer:GridNumberFormat,
											summaryType:"sum"
										},{
											header:"발주금액",
											dataIndex:"order",
											width:110,
											renderer:GridNumberFormat,
											summaryType:"sum"
										},{
											header:"현장청구금액",
											dataIndex:"workspace",
											width:110,
											renderer:GridNumberFormat,
											summaryType:"sum"
										},{
											header:"본사조절금액",
											dataIndex:"commander",
											width:110,
											renderer:GridNumberFormat,
											summaryType:"sum"
										}
									]),
										store:new Ext.data.GroupingStore({
											proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["idx","group","gno","worktype","tno",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"order",type:"int"},{name:"workspace",type:"int"},{name:"commander",type:"int"},{name:"sort",type:"int"}]
											}),
											remoteSort:false,
											groupField:"group",
											sortInfo:{field:"sort",direction:"ASC"},
											baseParams:{"action":"monthly_payment","get":"grouplist","mode":"group","wno":idx,"gno":gno,"date":month}
										}),
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
											render:{fn:function() {
												Ext.getCmp("PaymentGroupTab-0").getStore().load();
											}},
											rowdblclick:{fn:function(grid,row,e) {
												Ext.getCmp("PaymentGroupTab").activate("PaymentGroupTab-"+grid.getStore().getAt(row).get("tno"));
											}}
										}
									})
								).show();
							} else {
								CreatePaymentGroupFunction(idx,month,gno,store.getAt(i).get("tab"),store.getAt(i).get("title"));
							}
						}
					});
					Ext.getCmp("PaymentGroupTab").store.load();
				}},
				close:{fn:function() {
					Ext.getCmp("PaymentList").getStore().load();
				}}
			}
		}).show();
	}

	function CreatePaymentGroupFunction(idx,month,gno,tno,title) {
		Ext.getCmp("PaymentGroupTab").add(
			new Ext.grid.EditorGridPanel({
				id:"PaymentGroupTab-"+tno,
				title:title,
				cm:new Ext.grid.ColumnModel([
					{
						dataIndex:"group"
					},{
						dataIndex:"itemcode",
						hidden:true,
						hideable:false
					},{
						dataIndex:"code",
						hidden:true,
						hideable:false
					},{
						dataIndex:"subcode",
						hidden:true,
						hideable:false
					},{
						header:"품명",
						dataIndex:"title",
						width:180
					},{
						header:"규격",
						dataIndex:"size",
						width:100
					},{
						header:"단위",
						dataIndex:"unit",
						width:60
					},{
						header:"수량",
						dataIndex:"contract_ea",
						width:50,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"재료비",
						dataIndex:"contract_cost1",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"contract_cost2",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"contract_cost3",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"contract_price",
						width:75,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.contract_price = Math.floor((record.data.contract_cost1+record.data.contract_cost2+record.data.contract_cost3)*record.data.contract_ea);
							return GridNumberFormat(record.data.contract_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"exec_ea",
						width:50,
						summaryType:"sum",
						hidden:true,
						renderer:GridNumberFormat
					},{
						header:"재료비",
						dataIndex:"exec_cost1",
						width:65,
						summaryType:"sum",
						hidden:true,
						renderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"exec_cost2",
						width:65,
						summaryType:"sum",
						hidden:true,
						renderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"exec_cost3",
						width:65,
						summaryType:"sum",
						hidden:true,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"exec_price",
						width:75,
						summaryType:"sum",
						hidden:true,
						renderer:function(value,p,record) {
							record.data.exec_price = Math.floor((record.data.exec_cost1+record.data.exec_cost2+record.data.exec_cost3)*record.data.exec_ea);
							return GridNumberFormat(record.data.exec_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"order_ea",
						width:50,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"재료비",
						dataIndex:"order_cost1",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"order_cost2",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"order_cost3",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"order_price",
						width:75,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.order_price = Math.floor((record.data.order_cost1+record.data.order_cost2+record.data.order_cost3)*record.data.order_ea);
							return GridNumberFormat(record.data.order_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"workspace_ea",
						width:50,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"재료비",
						dataIndex:"workspace_cost1",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"노무비",
						dataIndex:"workspace_cost2",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"경비",
						dataIndex:"workspace_cost3",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"workspace_price",
						width:75,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.workspace_price = Math.floor((record.data.workspace_cost1+record.data.workspace_cost2+record.data.workspace_cost3)*record.data.workspace_ea);
							return GridNumberFormat(record.data.workspace_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"commander_ea",
						width:50,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"재료비",
						dataIndex:"commander_cost1",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"노무비",
						dataIndex:"commander_cost2",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"경비",
						dataIndex:"commander_cost3",
						width:65,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"금액",
						dataIndex:"commander_price",
						width:75,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.commander_price = Math.floor((record.data.commander_cost1+record.data.commander_cost2+record.data.commander_cost3)*record.data.commander_ea);
							return GridNumberFormat(record.data.commander_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"비고",
						dataIndex:"etc",
						width:100,
						editor:new Ext.form.TextField({selectOnFocus:true})
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["group","itemcode","code","subcode","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost1",type:"int"},{name:"contract_cost2",type:"int"},{name:"contract_cost3",type:"float"},{name:"contract_price",type:"int"},{name:"exec_ea",type:"float"},{name:"exec_cost1",type:"int"},{name:"exec_cost2",type:"int"},{name:"exec_cost3",type:"float"},{name:"exec_price",type:"int"},{name:"order_ea",type:"float"},{name:"order_cost1",type:"int"},{name:"order_cost2",type:"int"},{name:"order_cost3",type:"float"},{name:"order_price",type:"int"},{name:"workspace_ea",type:"float"},{name:"workspace_cost1",type:"int"},{name:"workspace_cost2",type:"int"},{name:"workspace_cost3",type:"float"},{name:"workspace_price",type:"int"},{name:"commander_ea",type:"float"},{name:"commander_cost1",type:"int"},{name:"commander_cost2",type:"int"},{name:"commander_cost3",type:"float"},{name:"commander_price",type:"int"},"etc"]
					}),
					remoteSort:true,
					sortInfo:{field:"title",direction:"ASC"},
					groupField:"group",
					baseParams:{"action":"monthly_payment","get":"grouplist","mode":"tabdata","wno":idx,"gno":gno,"tno":tno,"date":month}
				}),
				trackMouseOver:true,
				clicksToEdit:1,
				plugins:[new Ext.ux.plugins.GroupHeaderGrid({
					rows:[[
						{},
						{},
						{},
						{},
						{header:"품목",colspan:3,align:"center"},
						{header:"계약금액",colspan:5,align:"center"},
						{header:"실행금액",colspan:5,align:"center"},
						{header:"발주금액",colspan:5,align:"center"},
						{header:"현장청구금액",colspan:5,align:"center"},
						{header:"본사조절금액",colspan:6,align:"center"}
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
						Ext.getCmp("PaymentGroupTab-"+tno).getStore().load();
					}}
				}
			})
		);
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기성관리 및 현황",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
				text:"이전",
				handler:function() {
					if (Ext.getCmp("year").selectedIndex == 0) {
						Ext.Msg.show({title:"에러",msg:"이전 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.year = Ext.getCmp("ListTab2").getStore().baseParams.year = Ext.getCmp("year").getStore().getAt(Ext.getCmp("year").selectedIndex-1).get("date");
						Ext.getCmp("year").setValue(Ext.getCmp("ListTab1").getStore().baseParams.year);
						Ext.getCmp("year").selectedIndex = Ext.getCmp("year").selectedIndex - 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
					}
				}
			}),
			' ',
			new Ext.form.ComboBox({
				id:"year",
				store:new Ext.data.SimpleStore({
					fields:["date","display"],
					data:[<?php for ($i=1990;$i<=date('Y');$i++) { if ($i != 1990) echo ','; ?>["<?php echo $i; ?>","<?php echo $i; ?>년"]<?php } ?>]
				}),
				displayField:"display",
				valueField:"date",
				typeAhead:true,
				mode:"local",
				triggerAction:"all",
				width:80,
				editable:false,
				listeners:{
					render:{fn:function(form) {
						form.setValue("<?php echo Request('iErpYear','cookie') != null ? Request('iErpYear','cookie') : GetTime('Y'); ?>");
						for (var i=0, loop=form.getStore().getCount();i<loop;i++) {
							if (form.getStore().getAt(i).get("date") == form.getValue()) {
								form.selectedIndex = i;
								break;
							}
						}

						if (form.selectedIndex == -1) {
							form.selectedIndex = form.getStore().getCount()-1;
							form.setValue(form.getStore().getAt(form.getStore().getCount()-1).get("date"));
							SetCookie("iErpYear",form.getValue());
						}

						Ext.getCmp("ListTab1").getStore().baseParams.year = Ext.getCmp("year").getValue();
						Ext.getCmp("ListTab1").getStore().load({params:{start:0,limit:30}});

						Ext.getCmp("ListTab2").getStore().baseParams.year = Ext.getCmp("year").getValue();
						Ext.getCmp("ListTab2").getStore().load({params:{start:0,limit:30}});
					}},
					select:{fn:function(form) {
						Ext.getCmp("ListTab1").getStore().baseParams.year = form.getValue();
						Ext.getCmp("ListTab1").getStore().reload();

						Ext.getCmp("ListTab2").getStore().baseParams.year = form.getValue();
						Ext.getCmp("ListTab2").getStore().reload();
					}}
				}
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_right.png",
				iconAlign:"right",
				text:"다음",
				handler:function() {
					if (Ext.getCmp("year").selectedIndex+1 == Ext.getCmp("year").getStore().getCount()) {
						Ext.Msg.show({title:"에러",msg:"다음 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.year = Ext.getCmp("ListTab2").getStore().baseParams.year = Ext.getCmp("year").getStore().getAt(Ext.getCmp("year").selectedIndex+1).get("date");
						Ext.getCmp("year").setValue(Ext.getCmp("ListTab1").getStore().baseParams.year);
						Ext.getCmp("year").selectedIndex = Ext.getCmp("year").selectedIndex + 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
					}
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
						id:"ListTab1",
						title:"공사현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:PaymentStore1,
						plugins:[new Ext.ux.plugins.GroupHeaderGrid({
							rows:[[
								{header:"현장정보",colspan:6,align:"center"},
								{header:"<span id='thisYear'></span>",colspan:12,align:"center"},
								{header:"계",colspan:4,align:"center"}
							]],
							hierarchicalColMenu:true
						})],
						listeners:{
							celldblclick:{fn:function(grid,idx,col,e) {
								var month = grid.colModel.getDataIndex(col);
								if (month.indexOf("p") == 0) {
									month = month.replace("p","").length == 1 ? "0"+month.replace("p","") : month.replace("p","");
									month = Ext.getCmp("year").getValue()+"-"+month;
									PaymentFunction(grid.getStore().getAt(idx).get("idx"),month);
								}
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab2",
						title:"완료현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:PaymentStore2,
						viewConfig:{forceFit:false},
						listeners:{
							celldblclick:{fn:function(grid,idx,col,e) {
								var month = grid.colModel.getDataIndex(col);
								if (month.indexOf("p") == 0) {
									month = month.replace("p","").length == 1 ? "0"+month.replace("p","") : month.replace("p","");
									month = Ext.getCmp("year").getValue()+"-"+month;
									PaymentFunction(grid.getStore().getAt(idx).get("idx"),month);
								}
							}}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					Ext.getCmp(tab.getId()).getStore().load({params:{start:0,limit:30}});
				}}}
			})
		]
	});

	PaymentStore1.on("load",function(store) {
		document.getElementById("thisYear").innerHTML = store.baseParams.year+"년";
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>