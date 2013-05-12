<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var InputStore1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"sort","title",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"lastyear",type:"int"}<?php for ($i=1;$i<=12;$i++) { ?>,{name:"p<?php echo $i; ?>",type:"int"}<?php } ?>,{name:"summary",type:"int"},{name:"total",type:"int"},{name:"remainContract",type:"int"},{name:"remainExec",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{"action":"status","get":"list","category":"working","year":"<?php echo Request('iErpYear','cookie') ? Request('iErpYear','cookie') : date('Y'); ?>"}
	});

	var InputStore2 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"sort","title",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"lastyear",type:"int"}<?php for ($i=1;$i<=12;$i++) { ?>,{name:"p<?php echo $i; ?>",type:"int"}<?php } ?>,{name:"summary",type:"int"},{name:"total",type:"int"},{name:"remainContract",type:"int"},{name:"remainExec",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{"action":"status","get":"list","category":"complete","year":"<?php echo Request('iErpYear','cookie') ? Request('iErpYear','cookie') : date('Y'); ?>"}
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
			width:100,
			renderer:GridNumberFormat
		},{
			header:"하도급계약",
			dataIndex:"exec",
			width:100,
			renderer:GridNumberFormat
		},{
			header:"전년까지",
			dataIndex:"lastyear",
			width:100,
			renderer:GridNumberFormat
		}<?php for ($i=1;$i<=12;$i++) { ?>,{
			header:"<?php echo $i; ?>월",
			dataIndex:"p<?php echo $i; ?>",
			width:100,
			renderer:GridNumberFormat
		}<?php } ?>,{
			header:"소계",
			dataIndex:"summary",
			width:100,
			renderer:function(value,p,record) {
				record.data.summary = record.data.p1<?php for ($i=2;$i<=12;$i++) { ?>+record.data.p<?php echo $i; ?><?php } ?>;
				return GridNumberFormat(record.data.summary);
			}
		},{
			header:"합계",
			dataIndex:"total",
			width:100,
			renderer:function(value,p,record) {
				record.data.total = record.data.lastyear+record.data.summary;
				return GridNumberFormat(record.data.total);
			}
		},{
			header:"잔여계약금",
			dataIndex:"remainContract",
			width:100,
			renderer:function(value,p,record) {
				record.data.remainContract = record.data.contract-record.data.total;
				return GridNumberFormat(record.data.remainContract);
			}
		},{
			header:"잔여하도급계약금",
			dataIndex:"remainExec",
			width:100,
			renderer:function(value,p,record) {
				record.data.remainExec = record.data.exec-record.data.total;
				return GridNumberFormat(record.data.remainExec);
			}
		}
	]);

	function PaymentFunction(idx,date) {
		var date = date ? date : new Date().format("Y-m");
		var temp = date.split("-");
		temp[0] = parseInt(temp[0]);
		temp[1] = temp[1].indexOf("0") == 0 ? parseInt(temp[1].replace("0","")) : parseInt(temp[1]);
		var prevDate = temp[1] == 1 ? (temp[0]-1)+"년 12월" : temp[0]+"년 "+(temp[1]-1)+"월";
		var thisDate = temp[0]+"년 "+temp[1]+"월";

		new Ext.Window({
			id:"PaymentWindow",
			title:"공종별 도급기성대비표",
			width:980,
			height:550,
			modal:true,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"PaymentList",
					border:false,
					tbar:[
						new Ext.Button({
							text:"엑셀파일로 변환",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
							handler:function() {
								ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=status&wno="+idx+"&date="+date);
							}
						})
					],
					cm:new Ext.grid.ColumnModel([
						{
							header:"공종타입",
							dataIndex:"basegroup"
						},{
							header:"공종그룹",
							dataIndex:"workgroup",
							width:270
						},{
							header:"계약금액",
							dataIndex:"contract",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"하도급계약금액",
							dataIndex:"outsourcing",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:prevDate,
							dataIndex:"prev_monthly",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:thisDate,
							dataIndex:"monthly",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"누계기성",
							dataIndex:"total_monthly",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"기성고",
							dataIndex:"",
							width:60,
							renderer:function(value,p,record) {
								if (record.data.contract > 0 && record.data.total_monthly > 0) {
									return '<div style="font-family:arial; text-align:right;">'+(Math.floor(record.data.total_monthly/record.data.contract*100))+"%</div>";
								} else {
									return '<div style="font-family:arial; text-align:right;">0%</div>';
								}
							}
						},{
							header:"차액",
							dataIndex:"",
							width:100,
							renderer:function(value,p,record) {
								return GridNumberFormat(record.data.contract-record.data.total_monthly);
							}
						}
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["gno","tno","basegroup","workgroup","worktype",{name:"contract",type:"int"},{name:"outsourcing",type:"int"},{name:"prev_monthly",type:"int"},{name:"monthly",type:"int"},{name:"total_monthly",type:"int"},"sort"]
						}),
						remoteSort:true,
						sortInfo:{field:"sort",direction:"ASC"},
						groupField:"basegroup",
						baseParams:{"action":"status","get":"sheet","idx":idx,"date":date}
					}),
					plugins:[new Ext.ux.grid.ColumnHeaderGroup({
						rows:[[
							{},
							{},
							{},
							{},
							{header:"기성금액",colspan:3,align:"center"},
							{},
							{}
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
					listeners:{
						render:{fn:function() {
							Ext.getCmp("PaymentList").getStore().load();
						}},
						rowdblclick:{fn:function(grid,row) {
							PaymentGroupFunction(idx,date,grid.getStore().getAt(row).get("gno"),grid.getStore().getAt(row).get("tno"),grid.getStore().getAt(row).get("workgroup"));
						}}
					}
				})
			]
		}).show();
	}

	function PaymentGroupFunction(idx,date,gno,tno,title) {
		var temp = date.split("-");
		temp[0] = parseInt(temp[0]);
		temp[1] = temp[1].indexOf("0") == 0 ? parseInt(temp[1].replace("0","")) : parseInt(temp[1]);
		var prevDate = temp[1] == 1 ? (temp[0]-1)+"년 12월" : temp[0]+"년 "+(temp[1]-1)+"월";
		var thisDate = temp[0]+"년 "+temp[1]+"월";

		if (Ext.getCmp("PaymentTab-"+gno)) return;

		new Ext.Window({
			width:940,
			height:540,
			layout:"fit",
			title:title+" 도급기성대비표",
			items:[
				new Ext.TabPanel({
					id:"PaymentTab-"+gno,
					tabPosition:"bottom",
					border:false,
					activeTab:0,
					enableTabScroll:true,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["tno","title"]
						}),
						remoteSort:true,
						sortInfo:{field:"sort",direction:"ASC"},
						baseParams:{"action":"status","get":"tab","idx":idx,"gno":gno},
						listeners:{
							load:{fn:function(store) {
								for (var i=0, loop=store.getCount();i<loop;i++) {
									CreatePaymentGroupFunction(idx,date,gno,store.getAt(i).get("tno"),store.getAt(i).get("title"));
								}
							}}
						}
					}),
					items:[
						new Ext.grid.GridPanel({
							id:"Payment-Total",
							title:title,
							border:false,
							cm:new Ext.grid.ColumnModel([
								{
									header:"공종그룹",
									dataIndex:"workgroup",
								},{
									header:"공종명",
									dataIndex:"worktype",
									width:240
								},{
									header:"계약금액",
									dataIndex:"contract",
									width:100,
									renderer:GridNumberFormat,
									summaryType:"sum"
								},{
									header:"하도급계약금액",
									dataIndex:"outsourcing",
									width:100,
									renderer:GridNumberFormat,
									summaryType:"sum"
								},{
									header:prevDate,
									dataIndex:"prev_monthly",
									width:100,
									renderer:GridNumberFormat,
									summaryType:"sum"
								},{
									header:thisDate,
									dataIndex:"monthly",
									width:100,
									renderer:GridNumberFormat,
									summaryType:"sum"
								},{
									header:"누계기성",
									dataIndex:"total_monthly",
									width:100,
									renderer:GridNumberFormat,
									summaryType:"sum"
								},{
									header:"기성고",
									dataIndex:"",
									width:60,
									renderer:function(value,p,record) {
										if (record.data.contract > 0 && record.data.total_monthly > 0) {
											return '<div style="font-family:arial; text-align:right;">'+(Math.floor(record.data.total_monthly/record.data.contract*100))+"%</div>";
										} else {
											return '<div style="font-family:arial; text-align:right;">0%</div>';
										}
									}
								},{
									header:"차액",
									dataIndex:"",
									width:100,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.contract-record.data.total_monthly);
									}
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["workgroup","worktype",{name:"contract",type:"int"},{name:"outsourcing",type:"int"},{name:"prev_monthly",type:"int"},{name:"monthly",type:"int"},{name:"total_monthly",type:"int"},"sort"]
								}),
								remoteSort:true,
								sortInfo:{field:"sort",direction:"ASC"},
								groupField:"workgroup",
								baseParams:{"action":"status","get":"sheet","idx":idx,"gno":gno,"date":date}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"기성금액",colspan:3,align:"center"},
									{},
									{}
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
									Ext.getCmp("Payment-Total").getStore().load();
								}}
							}
						})
					]
				})
			],
			listeners:{
				show:{fn:function() {
					Ext.getCmp("PaymentTab-"+gno).store.load();
				}}
			}
		}).show();
	}

	function CreatePaymentGroupFunction(idx,date,gno,tno,title) {
		var temp = date.split("-");
		temp[0] = parseInt(temp[0]);
		temp[1] = temp[1].indexOf("0") == 0 ? parseInt(temp[1].replace("0","")) : parseInt(temp[1]);
		var prevDate = temp[1] == 1 ? (temp[0]-1)+"년 12월" : temp[0]+"년 "+(temp[1]-1)+"월";
		var thisDate = temp[0]+"년 "+temp[1]+"월";

		Ext.getCmp("PaymentTab-"+gno).add(
			new Ext.grid.GridPanel({
				id:"Payment-"+tno,
				title:title,
				cm:new Ext.grid.ColumnModel([
					{
						dataIndex:"group"
					},{
						header:"품명",
						dataIndex:"title",
						width:150
					},{
						header:"규격",
						dataIndex:"size",
						width:100
					},{
						header:"단위",
						dataIndex:"unit",
						width:55
					},{
						header:"수량",
						dataIndex:"contract_ea",
						width:40,
						renderer:GridNumberFormat
					},{
						header:"단가",
						dataIndex:"contract_cost",
						width:70,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"contract_price",
						width:90,
						renderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"outsourcing_ea",
						width:40,
						renderer:GridNumberFormat
					},{
						header:"단가",
						dataIndex:"outsourcing_cost",
						width:70,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"outsourcing_price",
						width:90,
						renderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"prev_monthly_ea",
						width:40,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"prev_monthly_price",
						width:90,
						renderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"monthly_ea",
						width:40,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"monthly_price",
						width:90,
						renderer:GridNumberFormat
					},{
						header:"수량",
						dataIndex:"total_monthly_ea",
						width:40,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"total_monthly_price",
						width:90,
						renderer:GridNumberFormat
					},{
						header:"기성고",
						dataIndex:"",
						width:60,
						renderer:function(value,p,record) {
							if (record.data.contract_price > 0 && record.data.total_monthly_price > 0) {
								return '<div style="font-family:arial; text-align:right;">'+(Math.floor(record.data.total_monthly_price/record.data.contract_price*100))+"%</div>";
							} else {
								return '<div style="font-family:arial; text-align:right;">0%</div>';
							}
						}
					},{
						header:"차액",
						dataIndex:"",
						width:100,
						renderer:function(value,p,record) {
							return GridNumberFormat(record.data.contract_price-record.data.total_monthly_price);
						}
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["group","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"},{name:"contract_price",type:"int"},{name:"outsourcing_ea",type:"float"},{name:"outsourcing_cost",type:"int"},{name:"outsourcing_price",type:"int"},{name:"prev_monthly_ea",type:"float"},{name:"prev_monthly_price",type:"int"},{name:"monthly_ea",type:"float"},{name:"monthly_price",type:"int"},{name:"total_monthly_ea",type:"float"},{name:"total_monthly_price",type:"int"}]
					}),
					remoteSort:true,
					sortInfo:{field:"title",direction:"ASC"},
					groupField:"group",
					baseParams:{"action":"status","get":"tabdata","idx":idx,"gno":gno,"tno":tno,"date":date}
				}),
				plugins:[new Ext.ux.grid.ColumnHeaderGroup({
					rows:[[
						{},
						{header:"품목",colspan:3,align:"center"},
						{header:"계약금액",colspan:3,align:"center"},
						{header:"하도급계약",colspan:3,align:"center"},
						{header:prevDate+" 기성",colspan:2,align:"center"},
						{header:date+" 기성",colspan:2,align:"center"},
						{header:"누계기성",colspan:2,align:"center"},
						{},
						{}
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
						Ext.getCmp("Payment-"+tno).getStore().load();
					}}
				}
			})
		);
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"투입현황",
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
				width:90,
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
						store:InputStore1,
						plugins:[new Ext.ux.grid.ColumnHeaderGroup({
							rows:[[
								{header:"현장정보",colspan:6,align:"center"},
								{header:"<span id='thisYear'></span>",colspan:12,align:"center"},
								{header:"계",colspan:4,align:"center"}
							]],
							hierarchicalColMenu:true
						})],
						listeners:{
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);
								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								menu.add({
									text:"공종별 도급기성대비표",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_chart_bar.png"),
									handler:function() {
										PaymentFunction(data.get("idx"));
									}
								});

								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab2",
						title:"완료현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:InputStore2,
						viewConfig:{forceFit:false},
						listeners:{
							rowdblclick:{fn:function(grid,row) {
								PaymentFunction(grid.getStore().getAt(row).get("idx"));
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

	InputStore1.on("load",function(store) {
		document.getElementById("thisYear").innerHTML = store.baseParams.year+"년";
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>