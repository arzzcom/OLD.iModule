<script type="text/javascript">
var  test = 1000;
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"monthly","wno":"<?php echo $this->wno; ?>"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"현금",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"PaymentList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("PaymentList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("PaymentList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("PaymentList").getStore().reload();
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
								Ext.getCmp("PaymentList").getStore().baseParams.date = form.getValue();
								Ext.getCmp("PaymentList").getStore().reload();
							}}
						}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_control_right.png",
						iconAlign:"right",
						text:"다음달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
								Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("PaymentList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("PaymentList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("PaymentList").getStore().reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_table_row_insert.png",
						text:"추가",
						handler:function() {
							GridInsertRow(Ext.getCmp("PaymentList"));
						}
					}),
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_table_row_delete.png",
						text:"삭제",
						handler:function() {
							GridDeleteRow(Ext.getCmp("PaymentList"));
						}
					}),
					'-',
					new Ext.Button({
						text:"변경사항 저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/workspace/icon_report_disk.png",
						handler:function() {
							var data = GetGridData(Ext.getCmp("PaymentList"));

							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"현금내역이 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("PaymentList").getStore().commitChanges();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"payment","do":"money","wno":"<?php echo $this->wno; ?>","date":Ext.getCmp("month").getValue(),"data":data}
							});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"group",
						hideable:false
					},{
						header:"날짜",
						dataIndex:"date",
						width:80,
						sortable:true,
						renderer:GridDateFormat
					},{
						header:"지출형태",
						dataIndex:"pay_type",
						width:80,
						sortable:true,
						editor:new Ext.form.ComboBox({
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							minListWidth:400,
							listClass:"x-combo-list-small",
							store:new Ext.data.SimpleStore({
								fields:["pay_type","view_value"],
								data:[["복리후생비","<b>복리후생비</b>: 식대,음료,생수,회식,직원경조사비"],["통신비","<b>통신비</b>: 우편물발송료,TV수신료,전화요금,인터넷사용료"],["수도광열비","<b>수도광열비</b>: 가스,수도,전기요금,난방용가스,난방유류"],["도서인쇄비","<b>도서인쇄비</b>: 도서구입,복사비,도면재본비,신문구독"],["소모품비","<b>소모품비</b>: 사무용품,복사용지,전산소모품(토너,잉크),화장지,종이컵,잡자재"],["안전관리비","<b>안전관리비</b>: 병원비,약품구매,폐기물처리비"],["중기임차료","<b>중기임차료</b>: 지게차,굴삭기,장비사용료"],["임차료","<b>임차료</b>: 숙소임차료"],["운반비","<b>운반비</b>: 택배,퀵서비스"],["접대비","<b>접대비</b>: 거래처 경조사비, 거래처 화환"],["여비교통비","<b>여비교통비</b>: 외근교통비,출장경비"],["수수료","<b>수수료</b>: 부동산중개수수료,송금수수료,보증서발급,증명서발급,주차료"],["교육훈련비","<b>교육훈련비</b>: 교육참가비"],["차량유지비","<b>차량유지비</b>: 작업차유류대, 작업차수리"],["잡비","<b>잡비</b>: 오물,분뇨수거비"]]
							}),
							editable:false,
							mode:"local",
							displayField:"view_value",
							valueField:"pay_type"
						})
					},{
						header:"지출내용",
						dataIndex:"content",
						width:200,
						sortable:true,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"사업자번호",
						dataIndex:"company_number",
						width:140,
						sortable:true,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"유형",
						dataIndex:"type",
						width:80,
						sortable:true,
						editor:new Ext.form.ComboBox({
							typeAhead: true,
							triggerAction:"all",
							lazyRender:true,
							listClass:"x-combo-list-small",
							store:new Ext.data.SimpleStore({
								fields:["type"],
								data:[["일반"],["간이"],["면세"],["폐업"],["기관"]]
							}),
							editable:false,
							mode:"local",
							displayField:"type",
							valueField:"type"
						})
					},{
						id:"GridSum",
						header:"합계",
						dataIndex:"total",
						width:80,
						sortable:true,
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						renderer:GridNumberFormat,
						summaryType:"sum"
					},{
						header:"세액",
						dataIndex:"tax",
						width:80,
						sortable:true,
						editor:new Ext.form.NumberField({selectOnFocus:true}),
						renderer:GridNumberFormat,
						summaryType:"sum"
					},{
						header:"공급액",
						dataIndex:"price",
						width:80,
						sortable:true,
						renderer:function(value,p,record) {
							record.data.price = Math.floor(record.data.total-record.data.tax);
							return GridNumberFormat(record.data.price);
						},
						summaryType:"sum"
					},{
						header:"비고",
						dataIndex:"etc",
						width:200,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["group","date","pay_type","content","company_number","type",{name:"total",type:"int"},{name:"tax",type:"int"},{name:"price",type:"int"},"time","etc","file"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"date",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"payment","get":"money","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
				}),
				autoScroll:true,
				trackMouseOver:true,
				clicksToEdit:1,
				plugins:new Ext.grid.GroupSummary(),
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

	Ext.getCmp("PaymentList").getStore().on("load",function() {
		var thisMonth = new Date(Ext.getCmp("month").getValue()+"-01");
		Ext.getCmp("PaymentList").getColumnModel().setEditor(2,new Ext.grid.GridEditor(
			new Ext.form.DateField({
				minValue:thisMonth.format("Y-m-d"),
				maxValue:new Date(thisMonth.format("Y-m")+"-"+thisMonth.format("t")).format("Y-m-d"),
				value:new Date().format("Y-m-d"),
				format:"Y-m-d"
			})
		));
		SetCookie("iErpMonth",Ext.getCmp("PaymentList").getStore().baseParams.date);
	});

	Ext.getCmp("PaymentList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>