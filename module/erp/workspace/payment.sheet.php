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

	var store = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:'/GetStore.do?ems=workspace&mode=payment&action=card&wno=2'}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:["group",{name:"seq",type:"int"},{name:"date",type:"date"},"pay_type","content","company_number","type",{name:"total",type:"int"},{name:"tax",type:"int"},{name:"price",type:"int"},"time","etc","file"]
		}),
		remoteSort:false,
		groupField:"group",
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{date:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"갑지",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"List",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/workspace/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("List").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("List").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("List").getStore().reload();
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

									Ext.getCmp("List").getStore().baseParams.date = Ext.getCmp("month").getValue();
									Ext.getCmp("List").getStore().load();
								});
							}},
							select:{fn:function(form) {
								Ext.getCmp("List").getStore().baseParams.date = form.getValue();
								Ext.getCmp("List").getStore().reload();
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
								Ext.getCmp("List").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("List").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("List").getStore().reload();
							}
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					{
						dataIndex:"group",
						hideable:false
					},{
						header:"분류",
						dataIndex:"category1",
						width:75,
						menuDisabled:true,
						summaryType:"dataIndex",
						summaryRenderer:function(value,p,record) {
							if (record.data.category1 == "노임") return "<div style='font-family:돋움;'>노임</div>";
							else if (record.data.category1 == "식대") return "<div style='font-family:돋움;'>식대</div>";
							else if (record.data.category1 == "전도금") return "<div style='font-family:돋움;'>전도금</div>";
							else if (record.data.category1 == "기타경비") return "<div style='font-family:돋움;'>기타경비</div>";
							else if (record.data.category1 == "경증빙무") return '<div style="padding-bottom:5px; font-family:돋움;">기타경비</div><div style="padding-top:3px; font-family:돋움;">투입금액</div>';
							else if (record.data.category1 == "전도금신청") return "<div style='font-family:돋움; font-size:11px;'>금월지급액</div>";
						}
					},{
						header:"세부항목",
						dataIndex:"category2",
						width:90,
						menuDisabled:true,
						renderer:function(value,p,record) {
							if (record.data.category1 == "기타경비" || record.data.category1 == "경증빙무") return '<span onmouseover="ListShowText(\''+value+'\',1,event)" onmouseout="ListShowText(0,0)">'+value+'</span>';
							else return value;
						},
						summaryType:"dataIndex",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "경증빙무") {
								return '<div style="padding-bottom:5px;">소계</div><div style="padding-top:3px;">합계</div>';
							} else {
								if (record.data.category1 == "전도금신청") return "<div style='font-family:돋움; font-size:11px;'>합계</div>";
								else return "<div style='font-family:돋움; font-size:11px;'>소계</div>";
							}
						}
					},{
						header:"(부가세포함)",
						dataIndex:"claimpay",
						width:78,
						menuDisabled:true,
						renderer:function(value,p,record) {
							record.data.claimpay = record.data.price + record.data.tax;
							return GridNumberFormat(record.data.claimpay);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount();i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("claimpay");
								}
								return GridNumberFormat(total);
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("claimpay");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#FF0000;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"공급가액",
						dataIndex:"price",
						width:75,
						renderer:function(value,p,record) {
							if (record.data.category1 == "전도금신청") return GridNumberFormat(0);
							else return GridNumberFormat(value);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("price");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"부가세",
						dataIndex:"tax",
						width:65,
						renderer:function(value,p,record) {
							if (record.data.category1 == "전도금신청") return GridNumberFormat(0);
							else return GridNumberFormat(value);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"합계",
						dataIndex:"total",
						width:80,
						renderer:function(value,p,record) {
							record.data.total = record.data.claimpay;
							if (record.data.category1 == "전도금신청") return GridNumberFormat(0);
							else return GridNumberFormat(record.data.total);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("total");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						},
						css:"font-weight:bold;"
					},{
						header:"갑근세",
						dataIndex:"tax1",
						width:60,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax1");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"고용보험료",
						dataIndex:"tax2",
						width:65,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax2");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"국민연금",
						dataIndex:"tax3",
						width:60,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax3");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"건강보험",
						dataIndex:"tax4",
						width:60,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax4");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"사업소득세",
						dataIndex:"tax5",
						width:65,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax5");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"공제계",
						dataIndex:"tax_total",
						width:65,
						renderer:function(value,p,record) {
							record.data.tax_total = record.data.tax1+record.data.tax2+record.data.tax3+record.data.tax4+record.data.tax5;
							return GridNumberFormat(record.data.tax_total);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("tax_total");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						}
					},{
						header:"기지급",
						dataIndex:"prepayment",
						width:70,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								return "";
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("prepayment");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						},
						css:"font-weight:bold; color:#D00000;"
					},{
						header:"추가공제",
						dataIndex:"tax6",
						width:65,
						renderer:GridNumberFormat,
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							return GridNumberFormat(value);
						}
					},{
						header:"지급액",
						dataIndex:"payment",
						width:85,
						renderer:function(value,p,record) {
							record.data.payment = record.data.total-record.data.tax_total-record.data.prepayment+record.data.tax6;
							return GridNumberFormat(record.data.payment);
						},
						summaryType:"sum",
						summaryRenderer: function(value,p,record){
							if (record.data.category1 == "전도금신청") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount();i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("payment");
								}
								return '<div style="color:#FF0000;">'+GridNumberFormat(total)+'</div>';
							} else if (record.data.category1 == "경증빙무") {
								var total = 0;
								for (var i=0, loop=Ext.getCmp("GridPanel").getStore().getCount()-1;i<loop;i++) {
									total+= Ext.getCmp("GridPanel").getStore().getAt(i).get("payment");
								}
								return '<div style="padding-bottom:5px;">'+GridNumberFormat(value)+'</div><div style="padding-top:3px; color:#071594;">'+GridNumberFormat(total)+'</div>';
							} else {
								return GridNumberFormat(value);
							}
						},
						css:"font-weight:bold;"
					},{
						header:"검토사항",
						dataIndex:"etc",
						width:300
					}
				]),
				store:store,
				autoScroll:true,
				trackMouseOver:true,
				clicksToEdit:1,
				plugins:[
					new Ext.ux.plugins.GroupHeaderGrid({
						rows:[[
							{},
							{header:"구분",colspan:2,align:"center"},
							{header:"현장청구분",colspan:1,align:"center"},
							{header:"본사조정분",colspan:11,align:"center"},
							{},
							{}
						]],
						hierarchicalColMenu:true
					}),
					new Ext.grid.GroupSummary()
				],
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:true,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:false
				})
			})
		]
	});

	store.on("load",function() {
		Ext.getCmp("List").getColumnModel().setEditor(4,new Ext.grid.GridEditor(
			new Ext.form.DateField({
				value:"2010-04-10",
				format:"Y-m-d"
			})
		));
	});

	store.load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>