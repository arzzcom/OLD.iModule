<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var CheckStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["check","weather"]
		}),
		remoteSort:false,
		sortInfo:{field:"check",direction:"ASC"},
		baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"daily","mode":"check","date":""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"일일상황일지",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"DailyList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
						}
					}),
					' ',
					new Ext.form.DateField({
						id:"today",
						format:"Y-m-d",
						width:90,
						value:"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>",
						listeners:{select:{fn:function(form,date) {
							var today = new Date(date).format("Y-m-d");

							SetCookie("iErpDate",today);

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
						}}}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
						}
					}),
					'-',
					new Ext.Button({
						id:"SUNNYButton",
						text:"맑음",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_weather_sun.png",
						handler:function() {
							Ext.getCmp("SUNNYButton").toggle(true);
							Ext.getCmp("CLOUDYButton").toggle(false);
							Ext.getCmp("RAINYButton").toggle(false);
							Ext.getCmp("SNOWLYButton").toggle(false);
						}
					}),
					new Ext.Button({
						id:"CLOUDYButton",
						text:"흐림",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_weather_clouds.png",
						handler:function() {
							Ext.getCmp("SUNNYButton").toggle(false);
							Ext.getCmp("CLOUDYButton").toggle(true);
							Ext.getCmp("RAINYButton").toggle(false);
							Ext.getCmp("SNOWLYButton").toggle(false);
						}
					}),
					new Ext.Button({
						id:"RAINYButton",
						text:"비",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_weather_rain.png",
						handler:function() {
							Ext.getCmp("SUNNYButton").toggle(false);
							Ext.getCmp("CLOUDYButton").toggle(false);
							Ext.getCmp("RAINYButton").toggle(true);
							Ext.getCmp("SNOWLYButton").toggle(false);
						}
					}),
					new Ext.Button({
						id:"SNOWLYButton",
						text:"눈",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_weather_snow.png",
						handler:function() {
							Ext.getCmp("SUNNYButton").toggle(false);
							Ext.getCmp("CLOUDYButton").toggle(false);
							Ext.getCmp("RAINYButton").toggle(false);
							Ext.getCmp("SNOWLYButton").toggle(true);
						}
					}),
					'-',
					new Ext.Button({
						text:"변경사항 저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						handler:function() {
							for (var i=0, loop=Ext.getCmp("DailyList").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("DailyList").getStore().getAt(i);
								if (!data.get("gno") || !data.get("tno") || !data.get("title")) {
									Ext.Msg.show({title:"에러",msg:"필수항목중 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
							}
							var data = GetGridData(Ext.getCmp("DailyList"));

							var weather = "SUNNY";
							if (Ext.getCmp("CLOUDYButton").pressed == true) weather = "CLOUDY";
							if (Ext.getCmp("RAINYButton").pressed == true) weather = "RAINY";
							if (Ext.getCmp("SNOWLYButton").pressed == true) weather = "SNOWLY";

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"현장일일상황일지가 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("DailyList").getStore().reload();
									if (new Date(Ext.getCmp("today").getValue()).format("Y-m-d") == new Date().format("Y-m-d")) {
										if (document.getElementById("WorkReportAlert")) {
											document.getElementById("WorkReportAlert").style.display = "none";
										}
									}
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"work","do":"daily","wno":"<?php echo $this->wno; ?>","mode":"modify","data":data,"date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"weather":weather}
							});
						}
					}),
					'-',
					new Ext.Button({
						text:"엑셀파일로 변환",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_page_white_excel.png",
						handler:function() {
							if (CheckStore.getAt(0).get("check") == "false") {
								Ext.Msg.show({title:"안내",msg:"저장된 일일상황일지와 현재의 일일상황일지 내역이 일치하지 않습니다.<br />먼저 변경사항을 저장하신 뒤 엑셀파일로 변환하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}
							ExcelConvert("<?php echo $this->moduleDir; ?>/exec/GetExcel.do.php?action=workspace&get=daily&wno=<?php echo $this->wno; ?>&date="+new Date(Ext.getCmp("today").getValue()).format("Y-m-d"));
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"group",
						hideable:false
					},{
						dataIndex:"row",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							return record.data.row = row;
						}
					},{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"공종그룹",
						dataIndex:"gno",
						width:80,
						renderer:function(value,p,record,row,col,store) {
							return GridWorkgroup(value,p,record,Ext.getCmp("DailyList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"공종명",
						dataIndex:"tno",
						width:100,
						renderer:function(value,p,record,row,col,store) {
							return GridWorktype(value,p,record,Ext.getCmp("DailyList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"품명",
						dataIndex:"title",
						width:180,
						renderer:function(value,p,record) {
							if (record.data.type == "itemorder" || record.data.type == "item" || record.data.type == "expense" || record.data.type == "equipment") {
								return GridContractItemNotFound(value,p,record);
							} else {
								return value;
							}
						}
					},{
						header:"규격",
						dataIndex:"size",
						width:100,
						renderer:function(value,p,record) {
							if (record.data.type == "itemorder" || record.data.type == "item" || record.data.type == "expense" || record.data.type == "equipment") {
								return GridContractItemNotFound(value,p,record);
							} else {
								return value;
							}
						},
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"적요",
						dataIndex:"content",
						width:200
					},{
						header:"비목",
						dataIndex:"type",
						width:40,
						renderer:function(value) {
							if (value == "member" || value == "dayworker") return "노무";
							else if (value == "outsourcing") return "외주";
							else if (value == "itemorder" || value == "item") return "자재";
							else if (value == "expense") return "경비";
							else if (value == "equipment") return "장비";
						}
					},{
						header:"지불처",
						dataIndex:"cooperation",
						width:100
					},{
						header:"수량",
						dataIndex:"ea",
						width:60,
						sortable:true,
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"단위",
						dataIndex:"unit",
						width:40,
						renderer:function(value,p,record) {
							if (record.data.type == "itemorder" || record.data.type == "item" || record.data.type == "expense" || record.data.type == "equipment") {
								return GridContractItemNotFound(value,p,record);
							} else {
								return value;
							}
						},
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"단가",
						dataIndex:"cost",
						width:80,
						sortable:true,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"금액",
						dataIndex:"price",
						width:90,
						sortable:true,
						renderer:function(value,p,record) {
							record.data.price = Math.floor(record.data.ea*record.data.cost);
							return GridNumberFormat(record.data.price);
						},
						summaryType:"sum",
						summaryRenderer:GridNumberFormat
					},{
						header:"지불여부",
						dataIndex:"payment",
						width:80,
						sortable:true,
						renderer:function(value) {
							if (value == "TRUE") return "지불";
							else return "미불";
						},
						editor:new Ext.form.ComboBox({
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							listClass:"x-combo-list-small",
							store:new Ext.data.SimpleStore({
								fields:["payment","display"],
								data:[["TRUE","지불"],["FALSE","미불"]]
							}),
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"payment"
						})
					},
					new Ext.ux.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.ux.grid.CheckboxSelectionModel(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","is_new","group","date","gno","tno","workgroup","worktype","content","type","code","subcode","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost",type:"int"},{name:"price",type:"int"},"payment","cooperation","avgcost"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"date",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"daily","mode":"list","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
				}),
				trackMouseOver:true,
				plugins:new Ext.grid.GroupSummary(),
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
						GridEditorAutoMatchItem(Ext.getCmp("DailyList"),<?php echo $this->wno; ?>);
						GridEditorWorkgroupType(Ext.getCmp("DailyList"),<?php echo $this->wno; ?>);
					}},
					beforeedit:{fn:function(object) {
						if (object.record.data.type == "ITEMORDER") return false;
						GridEditorBeforeWorkgroupType(object);
					}},
					afteredit:{fn:function(object) {
						GridAutoMatchItem(object,<?php echo $this->wno; ?>);
						GridEditorAfterWorkgroupType(object);

						if (object.field == "ea" || object.field == "cost") {
							if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
						}
					}}
				}
			})
		]
	});

	Ext.getCmp("DailyList").getStore().load();
	Ext.getCmp("DailyList").getStore().on("load",function(store) {
		CheckStore.baseParams.date = store.baseParams.date;
		CheckStore.load();
	});
	CheckStore.on("load",function(store) {
		Ext.getCmp(store.getAt(0).get("weather")+"Button").toggle(true);
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>