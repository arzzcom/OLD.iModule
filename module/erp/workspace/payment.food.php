<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"monthly","wno":"<?php echo $this->wno; ?>"}
	});

	var TabStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:["tab","title"]
		}),
		remoteSort:false,
		groupField:"group",
		sortInfo:{field:"tab", direction:"ASC"},
		baseParams:{"action":"payment","get":"food","mode":"tab","wno":"<?php echo $this->wno; ?>","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});
	TabStore.load();

	TabStore.on("load",function() {
		Ext.getCmp("ListTab").removeAll();
		SetCookie("iErpMonth",TabStore.baseParams.date);

		if (TabStore.getCount() == 0) {
			var tempNewIDX = "N"+new Date().format("U");
			CreateTabPanel(tempNewIDX,"새 시트");
		} else {
			for (var i=0, loop=TabStore.getCount();i<loop;i++) {
				CreateTabPanel(TabStore.getAt(i).get("tab"),TabStore.getAt(i).get("title"));
			}
		}
	});

	function CreateTabPanel(TabIDX,TabTitle) {
		Ext.getCmp("ListTab").add({
			id:"Tab-"+TabIDX,
			title:TabTitle,
			layout:"border",
			items:[
				new Ext.grid.EditorGridPanel({
					id:"Tab-"+TabIDX+"-List",
					title:"식대현황",
					region:"center",
					autoScroll:true,
					split:true,
					margins:"5 0 5 5",
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							dataIndex:"idx",
							hidden:true,
							hideable:false
						},{
							dataIndex:"group",
							hideable:false
						},{
							header:"조식",
							dataIndex:"breakfast",
							width:50,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"중식",
							dataIndex:"lunch",
							width:50,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"석식",
							dataIndex:"dinner",
							width:50,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"수량계",
							dataIndex:"food_total",
							width:60,
							sortable:false,
							menuDisabled:true,
							renderer:function(value,p,record) {
								record.data.food_total = record.data.breakfast+record.data.lunch+record.data.dinner;
								return GridNumberFormat(record.data.food_total);
							},
							summaryType:"sum",
							css:"font-weight:bold; background:#F2F2F2;"
						},{
							header:"간식",
							dataIndex:"snack",
							width:50,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"추가금액",
							dataIndex:"add",
							width:70,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"추가금액내용",
							dataIndex:"addtext",
							width:100,
							sortable:false,
							menuDisabled:true,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"총계",
							dataIndex:"total",
							width:80,
							sortable:false,
							menuDisabled:true,
							renderer:function(value,p,record) {
								if (record.data.food_total > 0) {
									var food_price = Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_food_price").getValue() ? parseInt(Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_food_price").getValue().replace(/\,/g,'')) : 0;
									var snack_price = Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_snack_price").getValue() ? parseInt(Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_snack_price").getValue().replace(/\,/g,'')) : 0;
								} else {
									var food_price = 0;
									var snack_price = 0;
								}

								record.data.total = record.data.food_total * food_price + record.data.snack * snack_price + record.data.add;

								return GridNumberFormat(record.data.total);
							},
							summaryType:"sum",
							summaryRenderer:function(value) {
								Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_price").setValue(GetNumberFormat(value));
								var tax = Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_tax").getValue() ? parseInt(Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_tax").getValue().replace(/\,/g,'')) : 0;
								Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_total").setValue(GetNumberFormat(value+tax));
								return GridNumberFormat(value);
							},
							css:"font-weight:bold; background:#F2F2F2;"
						},{
							header:"출력인원",
							dataIndex:"attend",
							width:60,
							sortable:false,
							menuDisabled:true,
							tooltip:"해당날짜의 출력인원",
							renderer:GridNumberFormat,
							summaryType:"sum"
						}
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
						reader:new Ext.data.JsonReader({
							root:'lists',
							totalProperty:'totalCount',
							fields:[{name:"idx",type:"int"},"group",{name:"breakfast",type:"int"},{name:"lunch",type:"int"},{name:"dinner",type:"int"},{name:"food_total",type:"int"},{name:"snack",type:"int"},{name:"add",type:"int"},"addtext",{name:"total",type:"int"},{name:"attend",type:"int"}]
						}),
						remoteSort:false,
						groupField:"group",
						sortInfo:{field:"idx", direction:"ASC"},
						baseParams:{"action":"payment","get":"food","mode":"list","tab":TabIDX,"wno":<?php echo $this->wno; ?>,"date":TabStore.baseParams.date}
					}),
					clicksToEdit:1,
					trackMouseOver:true,
					plugins:new Ext.grid.GroupSummary(),
					view:new Ext.grid.GroupingView({
						enableGroupingMenu:false,
						hideGroupedColumn:true,
						showGroupName:false,
						enableNoGroups:false,
						headersDisabled:false,
						showGroupHeader:false
					})
				}),
				new Ext.Panel({
					width:340,
					region:"east",
					split:true,
					margins:"5 5 5 0",
					autoScroll:true,
					title:"업체정보",
					items:[
						new Ext.form.FormPanel({
							id:"Tab-"+TabIDX+"-Form",
							border:false,
							style:"padding:5px; background:#FFFFFF;",
							labelAlign:"right",
							autoWidth:true,
							fileUpload:true,
							errorReader:new Ext.form.XmlErrorReader(),
							items:[
								new Ext.form.FieldSet({
									title:"식당정보",
									style:"margin:5px 8px 0px 8px;",
									labelWidth:70,
									layout:"form",
									border:true,
									autoHeight:true,
									items:[
										new Ext.form.Hidden({
											name:"list"
										}),
										new Ext.form.Hidden({
											name:"tab"
										}),
										new Ext.form.TextField({
											fieldLabel:"업체명",
											name:"rest_title",
											width:200,
											allowBlank:false
										}),
										new Ext.form.TextField({
											fieldLabel:"사업자번호",
											name:"rest_number",
											width:200,
											emptyText:"'-' 제외하고 입력하세요.",
											allowBlank:false,
											validator:CheckCompanyNumber,
											listeners:{
												focus:{fn:FocusNumberOnly},
												blur:{fn:BlurCompanyNumberFormat}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"연락처",
											name:"rest_telephone",
											width:200,
											emptyText:"'-' 제외하고 입력하세요.",
											allowBlank:false,
											listeners:{
												blur:{fn:BlurTelephoneFormat},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"주소",
											name:"rest_address",
											width:200
										}),
										new Ext.form.TextField({
											fieldLabel:"대표명",
											name:"rest_owner",
											width:100
										}),
										new Ext.form.TextField({
											fieldLabel:"예금주",
											name:"rest_account_name",
											width:150
										}),
										new Ext.form.ComboBox({
											fieldLabel:"은행명",
											hiddenName:"rest_account_bank",
											store:BankSimpleStore,
											displayField:"bank",
											valueField:"bank",
											typeAhead:true,
											mode:"local",
											triggerAction:"all",
											emptyText:"은행을 선택하세요.",
											width:150,
											editable:false
										}),
										new Ext.form.TextField({
											fieldLabel:"계좌번호",
											name:"rest_account_number",
											width:200
										})
									]
								}),
								new Ext.form.FieldSet({
									title:"결제정보",
									style:"margin:5px 8px 0px 8px;",
									labelWidth:70,
									layout:"form",
									border:true,
									autoHeight:true,
									items:[
										new Ext.form.TextField({
											fieldLabel:"식대단가",
											name:"rest_food_price",
											width:200,
											style:"text-align:right;",
											enableKeyEvents:true,
											allowBlank:false,
											emptyText:"세액을 제외한 단가를 입력하세요.   ",
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:function(form) {
													Ext.getCmp("Tab-"+TabIDX+"-List").getStore().sort("idx","ASC");
													BlurNumberFormat(form);
												}},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"간식단가",
											name:"rest_snack_price",
											width:200,
											style:"text-align:right;",
											enableKeyEvents:true,
											allowBlank:false,
											emptyText:"세액을 제외한 단가를 입력하세요.   ",
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:function(form) {
													Ext.getCmp("Tab-"+TabIDX+"-List").getStore().sort("idx","ASC");
													BlurNumberFormat(form);
												}},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"공급가액",
											name:"rest_price",
											width:200,
											style:"text-align:right; background:#FFFF01;",
											readOnly:true
										}),
										new Ext.form.TextField({
											fieldLabel:"세액",
											name:"rest_tax",
											width:200,
											style:"text-align:right;",
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:function(form) {
													Ext.getCmp("Tab-"+TabIDX+"-List").getStore().sort("idx","ASC");
													BlurNumberFormat(form);
												}},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"합계",
											name:"rest_total",
											width:200,
											style:"text-align:right; background:#FFFF01;",
											readOnly:true
										})
									]
								})
							],
							listeners:{actioncomplete:{fn:function(form,action) {
								if (action.type == "submit") {
									if (TabIDX.substr(0,1) == "N") {
										var newTabIDX;
										var newTabTitle = Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("rest_title").getValue();
										Ext.each(action.result.errors,function(item,index,allItems) { newTabIDX = item.id; });
										Ext.getCmp("ListTab").remove("Tab-"+TabIDX);
										CreateTabPanel(newTabIDX,newTabTitle);
									}
								}
							}}}
						})
					]
				})
			],
			bbar:[
				new Ext.Button({
					text:"시트저장",
					icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_table_save.png",
					handler:function() {
						Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("list").setValue(GetGridData(Ext.getCmp("Tab-"+TabIDX+"-List")));
						Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().findField("tab").setValue(TabIDX);
						Ext.getCmp("Tab-"+TabIDX+"-Form").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=payment&do=food&wno=<?php echo $this->wno; ?>&date="+Ext.getCmp("month").getValue(),waitMsg:"데이터를 저장중입니다."});
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"식대",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/workspace/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								TabStore.baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(TabStore.baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								TabStore.reload();
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
									}
								});
							}},
							select:{fn:function(form) {
								TabStore.baseParams.date = form.getValue();
								TabStore.reload();
							}}
						}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/workspace/icon_control_right.png",
						iconAlign:"right",
						text:"다음달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
								Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								TabStore.baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(TabStore.baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								TabStore.reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"새 시트추가",
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_table_add.png",
						handler:function() {
							var tempNewIDX = "N"+new Date().format("U");
							CreateTabPanel(tempNewIDX,"새 시트");
						}
					})
				],
				items:[
					new Ext.Panel({
						id:"LoadingTab",
						title:"로딩중...",
						html:'<div style="width:500px; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">식대정보를 로딩중입니다.</div>'
					})
				],
				listeners:{add:{fn:function(tabs,tab) {
					if (tab.getId() != "LoadingTab") {
						Ext.getCmp(tab.getId()+"-List").getStore().load();
					}
				}}}
			})
		]
	});

};
Ext.extend(ContentArea, Ext.Panel,{});
</script>