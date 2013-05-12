<script type="text/javascript">
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
		baseParams:{"action":"month","wno":"<?php echo $this->wno; ?>"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"노임",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("WorkerList").getStore().baseParams.date = Ext.getCmp("DayworkerList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("WorkerList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("WorkerList").getStore().reload();
								Ext.getCmp("DayworkerList").getStore().reload();
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
								Ext.getCmp("WorkerList").getStore().baseParams.date = form.getValue();
								Ext.getCmp("WorkerList").getStore().reload();

								Ext.getCmp("DayworkerList").getStore().baseParams.date = form.getValue();
								Ext.getCmp("DayworkerList").getStore().reload();
							}}
						}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
								Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("WorkerList").getStore().baseParams.date = Ext.getCmp("DayworkerList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("WorkerList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("WorkerList").getStore().reload();
								Ext.getCmp("DayworkerList").getStore().reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						id:"AttendLogButton",
						text:"출력일자보기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_calendar.png",
						enableToggle:true,
						handler:function(button) {
							for (var i=0;i<31;i++) {
								var hidden = !button.pressed;
								if (hidden == false && i >= new Date(Ext.getCmp("month").getValue()+"-01").format("t")) hidden = true;
								Ext.getCmp("WorkerList").getColumnModel().setHidden(9+i,hidden);
								Ext.getCmp("DayworkerList").getColumnModel().setHidden(8+i,hidden);
							}
						}
					}),
					' ',
					new Ext.Button({
						text:"계좌정보보기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_money.png",
						enableToggle:true,
						handler:function(button) {
							Ext.getCmp("WorkerList").getColumnModel().setHidden(7,!button.pressed);
							Ext.getCmp("WorkerList").getColumnModel().setHidden(8,!button.pressed);
							Ext.getCmp("WorkerList").getColumnModel().setHidden(9,!button.pressed);

							Ext.getCmp("DayworkerList").getColumnModel().setHidden(4,!button.pressed);
							Ext.getCmp("DayworkerList").getColumnModel().setHidden(5,!button.pressed);
							Ext.getCmp("DayworkerList").getColumnModel().setHidden(6,!button.pressed);
						}
					}),
					'-',
					new Ext.Button({
						text:"근태기록재조정",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_calendar_link.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"노무비내역이 저장된 이후 변경된 근태기록을 반영합니다.<br />근태기록을 재조정하게 되면 급여형태에 따라 금액이 변경될 수 있습니다.<br />근태기록을 재조정하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
								if (button == "ok") {
									var mode = Ext.getCmp("ListTab").getActiveTab().getId() == "WorkerList" ? "member" : "dayworker";
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"근태기록을 성공적으로 조절하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
											Ext.getCmp("ListTab").getActiveTab().getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"payment","do":"worker","wno":"<?php echo $this->wno; ?>","mode":mode,"submode":"attend","date":Ext.getCmp("month").getValue()}
									});
								}
							}});
						}
					}),
					'-',
					new Ext.Button({
						text:"변경사항 저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						handler:function() {
							for (var i=0, loop=Ext.getCmp("ListTab").getActiveTab().getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("ListTab").getActiveTab().getStore().getAt(i);
								if (!data.get("account_name") || !data.get("account_bank") || !data.get("account_number")) {
									Ext.Msg.show({title:"에러",msg:"계좌정보가 빠진 근로자가 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								if (data.get("attend_day") != data.get("calc_attend_day")) {
									Ext.Msg.show({title:"에러",msg:"실제 근태기록과 집계된 총일수가 다른 근로자가 있습니다.<br />먼저 근태기록재조정을 하여주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
							}
							var mode = Ext.getCmp("ListTab").getActiveTab().getId() == "WorkerList" ? "member" : "dayworker";
							var data = GetGridData(Ext.getCmp("ListTab").getActiveTab());

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"노무비내역이 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"payment","do":"worker","wno":"<?php echo $this->wno; ?>","mode":mode,"submode":"modify","data":data,"date":Ext.getCmp("month").getValue()}
							});
						}
					})
				],
				items:[
					new Ext.grid.EditorGridPanel({
						id:"WorkerList",
						title:"직원",
						layout:"fit",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"group",
								hideable:false,
								renderer:function(value,p,record) {
									if (record.data.is_save == "false") record.markDirty();
									return value;
								}
							},{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"형태",
								dataIndex:"pay_type",
								width:60,
								sortable:true,
								menuDisabled:true,
								sortable:true,
								renderer:function(value) {
									if (value == "MONTH") return "월급제";
									else if (value == "DAY") return "단가제";
								},
								editor:new Ext.form.ComboBox({
									store:new Ext.data.SimpleStore({
										fields:["pay_type","display"],
										data:[["MONTH","월급제"],["DAY","단가제"]]
									}),
									displayField:"display",
									valueField:"pay_type",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									editable:false
								})
							},{
								header:"이름",
								dataIndex:"name",
								width:50,
								sortable:true,
								menuDisabled:true
							},{
								header:"직원번호",
								dataIndex:"workernum",
								width:100,
								sortable:true
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								width:140,
								sortable:true
							},{
								header:"예금주",
								dataIndex:"account_name",
								width:60,
								sortable:false,
								hidden:true,
								hideable:false,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"은행",
								dataIndex:"account_bank",
								width:80,
								sortable:false,
								hidden:true,
								hideable:false,
								editor:new Ext.form.ComboBox({
									store:BankSimpleStore,
									displayField:"bank",
									valueField:"bank",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									editable:false
								})
							},{
								header:"계좌번호",
								dataIndex:"account_number",
								width:140,
								sortable:false,
								hidden:true,
								hideable:false,
								editor:new Ext.form.TextField({selectOnFocus:true})
							}<?php for ($i=1;$i<=31;$i++) { ?>,{
								header:"<?php echo $i; ?>",
								dataIndex:"day<?php echo $i; ?>",
								width:30,
								sortable:false,
								align:"center",
								menuDisabled:true,
								hidden:true,
								hideable:false,
								renderer:function(value,p,record) {
									if (value == 0) return "";
									else if (value == -1) { value = 0; return '<span style="color:#CCCCCC">삭감</span>'; }
									else if (value > 10) return '<span class="bold" style="font-family:arial; color:#CC0000;">'+(value/10).toFixed(1)+'</span>';
									else return '<span style="font-family:arial;">'+(value/10).toFixed(1)+'</span>';
								},
								summaryType:"sum",
								summaryRenderer:function(value) {
									return '<span style="font-family:arial;">'+(value/10).toFixed(1)+'</span>';
								}
							}<?php } ?>,{
								header:"총일수",
								dataIndex:"attend_day",
								width:47,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_attend_day != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+(value/10).toFixed(1)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+(value/10).toFixed(1)+'</div>';
									}
								},
								summaryRenderer:function(value) {
									return '<div style="font-family:arial; text-align:right;">'+(value/10).toFixed(1)+'</div>';
								}
							},{
								header:"야근",
								dataIndex:"overwork_day",
								width:36,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"지각",
								dataIndex:"delay_day",
								width:38,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"조퇴",
								dataIndex:"early_day",
								width:40,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"결근",
								dataIndex:"notattend_day",
								width:40,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (value < 0) record.data.notattend_day = 0;
									return GridNumberFormat(record.data.notattend_day);
								}
							},{
								header:"전월단가",
								dataIndex:"prev_pay",
								width:70,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"금월단가",
								dataIndex:"pay",
								width:70,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"총급여",
								dataIndex:"payment",
								width:75,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.pay_type == "MONTH") {
										record.data.payment = record.data.pay;
									} else {
										record.data.payment = Math.floor(record.data.pay*record.data.attend_day/10);
									}
									return GridNumberFormat(record.data.payment);
								}
							},{
								header:"소득세",
								dataIndex:"tax1",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax1 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"주민세",
								dataIndex:"tax2",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax2 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"고용보험",
								dataIndex:"tax3",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax3 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"국민연금",
								dataIndex:"tax4",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax4 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"건강보험",
								dataIndex:"tax5",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax5 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"차감합계",
								dataIndex:"tax_total",
								width:60,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.tax_total = record.data.tax1+record.data.tax2+record.data.tax3+record.data.tax4+record.data.tax5;
									return GridNumberFormat(record.data.tax_total);
								}
							},{
								header:"금액보정",
								dataIndex:"revision",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"지급액",
								dataIndex:"send_payment",
								width:70,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.send_payment = record.data.payment-record.data.tax_total+record.data.revision;
									return GridNumberFormat(record.data.send_payment);
								}
							},{
								header:"검토사항",
								dataIndex:"comment",
								width:150,
								menuDisabled:true,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"비고",
								dataIndex:"etc",
								width:350,
								menuDisabled:true
							}
						]),
						trackMouseOver:true,
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["is_save","idx","group","pay_type","name","workernum","jumin"<?php for ($i=1;$i<=31;$i++) { ?>,{name:"day<?php echo $i; ?>",type:"int"}<?php } ?>,"account_name","account_bank","account_number",{name:"attend_day",type:"int"},{name:"calc_attend_day",type:"int"},{name:"overwork_day",type:"int"},{name:"delay_day",type:"int"},{name:"early_day",type:"int"},{name:"notattend_day",type:"int"},{name:"prev_pay",type:"int"},{name:"pay",type:"int"},{name:"payment",type:"int"},{name:"tax1",type:"int"},{name:"tax2",type:"int"},{name:"tax3",type:"int"},{name:"tax4",type:"int"},{name:"tax5",type:"int"},{name:"calc_tax1",type:"int"},{name:"calc_tax2",type:"int"},{name:"calc_tax3",type:"int"},{name:"calc_tax4",type:"int"},{name:"calc_tax5",type:"int"},{name:"tax_total",type:"int"},{name:"revision",type:"int"},{name:"send_payment",type:"int"},"comment"]
							}),
							remoteSort:false,
							sortInfo:{field:"name",direction:"ASC"},
							groupField:"group",
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"payment","get":"worker","mode":"member","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
						}),
						plugins:new Ext.ux.grid.GroupSummary(),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							forceFit:false,
							showGroupHeader:false
						})
					}),
					new Ext.grid.EditorGridPanel({
						id:"DayworkerList",
						title:"직영노무자",
						layout:"fit",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"group",
								hideable:false,
								renderer:function(value,p,record) {
									if (record.data.is_save == "false") record.markDirty();
									return value;
								}
							},{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"이름",
								dataIndex:"name",
								width:80,
								sortable:true
							},{
								header:"주민(사업자)번호",
								dataIndex:"jumin",
								width:140,
								sortable:true
							},{
								header:"예금주",
								dataIndex:"account_name",
								width:60,
								sortable:false,
								hidden:true,
								hideable:false,
								menuDisabled:true
							},{
								header:"은행",
								dataIndex:"account_bank",
								width:80,
								sortable:false,
								hidden:true,
								hideable:false,
								menuDisabled:true
							},{
								header:"계좌번호",
								dataIndex:"account_number",
								width:140,
								sortable:false,
								hidden:true,
								hideable:false,
								menuDisabled:true
							}<?php for ($i=1;$i<=31;$i++) { ?>,{
								header:"<?php echo $i; ?>",
								dataIndex:"day<?php echo $i; ?>",
								width:40,
								sortable:false,
								align:"center",
								hidden:true,
								hideable:false,
								menuDisabled:true,
								renderer:function(value,p,record) {
									if (value == 0) return "";
									else return '<div style="font-family:arial; text-align:center;" onmouseover="Tip(true,\''+GetNumberFormat(value)+'\',event)" onmouseout="Tip(false)">'+(value/10000).toFixed(1)+'</div>';
								},
								summaryType:"sum",
								summaryRenderer:function(value) {
									return '<div style="font-family:arial; text-align:center;" onmouseover="Tip(true,\''+GetNumberFormat(value)+'\',event)" onmouseout="Tip(false)">'+(value/10000).toFixed(1)+'</div>';
								}
							}<?php } ?>,{
								header:"총일수",
								dataIndex:"attend_day",
								width:47,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"야근",
								dataIndex:"overwork_day",
								width:36,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"총급여",
								dataIndex:"payment",
								width:75,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"소득세",
								dataIndex:"tax1",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax1 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"주민세",
								dataIndex:"tax2",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax2 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"고용보험",
								dataIndex:"tax3",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax3 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"국민연금",
								dataIndex:"tax4",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax4 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"건강보험",
								dataIndex:"tax5",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.calc_tax5 != value) {
										return '<div style="font-family:arial; text-align:right; color:#FF5600;">'+GetNumberFormat(value)+'</div>';
									} else {
										return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+'</div>';
									}
								},
								summaryRenderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"차감합계",
								dataIndex:"tax_total",
								width:60,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.tax_total = record.data.tax1+record.data.tax2+record.data.tax3+record.data.tax4+record.data.tax5;
									return GridNumberFormat(record.data.tax_total);
								}
							},{
								header:"금액보정",
								dataIndex:"revision",
								width:55,
								sortable:false,
								menuDisabled:true,
								summaryType:"sum",
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"지급액",
								dataIndex:"send_payment",
								width:70,
								menuDisabled:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.send_payment = record.data.payment-record.data.tax_total+record.data.revision;
									return GridNumberFormat(record.data.send_payment);
								}
							},{
								header:"검토사항",
								dataIndex:"comment",
								width:150,
								menuDisabled:true,
								editor:new Ext.form.TextField({selectOnFocus:true})
							}
						]),
						trackMouseOver:true,
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["is_save","idx","group","pay_type","name","workernum","jumin"<?php for ($i=1;$i<=31;$i++) { ?>,{name:"day<?php echo $i; ?>",type:"int"}<?php } ?>,"account_name","account_bank","account_number",{name:"attend_day",type:"int"},{name:"calc_attend_day",type:"int"},{name:"overwork_day",type:"int"},{name:"delay_day",type:"int"},{name:"early_day",type:"int"},{name:"notattend_day",type:"int"},{name:"prev_pay",type:"int"},{name:"pay",type:"int"},{name:"payment",type:"int"},{name:"tax1",type:"int"},{name:"tax2",type:"int"},{name:"tax3",type:"int"},{name:"tax4",type:"int"},{name:"tax5",type:"int"},{name:"calc_tax1",type:"int"},{name:"calc_tax2",type:"int"},{name:"calc_tax3",type:"int"},{name:"calc_tax4",type:"int"},{name:"calc_tax5",type:"int"},{name:"tax_total",type:"int"},{name:"payment_setup",type:"int"},{name:"revision",type:"int"},{name:"send_payment",type:"int"},"comment"]
							}),
							remoteSort:false,
							sortInfo:{field:"name",direction:"ASC"},
							groupField:"group",
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"payment","get":"worker","mode":"dayworker","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
						}),
						plugins:new Ext.ux.grid.GroupSummary(),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							forceFit:false,
							showGroupHeader:false
						})
					})
				]
			})
		]
	});

	Ext.getCmp("WorkerList").getStore().load();
	Ext.getCmp("DayworkerList").getStore().load();

	Ext.getCmp("WorkerList").getStore().on("load",function() {
		SetCookie("iErpMonth",Ext.getCmp("WorkerList").getStore().baseParams.date);

		for (var i=0;i<31;i++) {
			var hidden = !Ext.getCmp("AttendLogButton").pressed;
			if (hidden == false && i >= new Date(Ext.getCmp("month").getValue()+"-01").format("t")) hidden = true;
			Ext.getCmp("WorkerList").getColumnModel().setHidden(9+i,hidden);
			Ext.getCmp("DayworkerList").getColumnModel().setHidden(7+i,hidden);
		}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>