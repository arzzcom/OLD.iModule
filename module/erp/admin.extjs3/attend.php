<script type="text/javascript">
var isPhotoRender = false;
var isPhotoRenderSpace = new Array();

function ShowPhotoView() {
	if (isPhotoRender == true) return;
	isPhotoRender = true;

	Ext.getCmp("ListTab2").getLayoutTarget().dom.innerHTML = "";
	isPhotoRenderSpace = new Array();

	new Ext.Window({
		id:"LoadingPhotoWindow",
		width:400,
		title:"사진정보 로딩중...",
		layout:"fit",
		border:false,
		modal:true,
		draggable:false,
		closable:false,
		items:new Ext.ProgressBar({
			text:"화면구성 준비중",
			id:"LoadingPhotoProgress",
			cls:"left-align"
		})
	}).show();

	ShowPhotoViewInner(0);
}

function ShowPhotoViewInner(idx) {
	var total = Ext.getCmp("ListTab1").getStore().getCount();
	if (idx == total) {
		Ext.getCmp("LoadingPhotoWindow").close();
	} else {
		var object = Ext.getCmp("ListTab2").getLayoutTarget().dom;
		var data = Ext.getCmp("ListTab1").getStore().getAt(idx);

		if (Ext.getCmp("ListTab1").getStore().baseParams.wno == "" && isPhotoRenderSpace[data.get("wno")] !== true) {
			var title = document.createElement("div");
			title.id = "PhotoTitle-"+data.get("wno");
			title.setAttribute("wno",data.get("wno"));
			title.className = "x-photo-expand";
			title.innerHTML = "현장명: "+data.get("workspace")+" (<span id='PhotoNum-"+data.get("wno")+"'>0</span>명)";
			title.onclick = function(event) {
				var e = event ? event : window.event;
				var object = e.target ? e.target : e.srcElement;

				if (document.getElementById("PhotoArea-"+object.getAttribute("wno")).style.display == "none") {
					document.getElementById("PhotoArea-"+object.getAttribute("wno")).style.display = "";
					object.className = "x-photo-expand";
				} else {
					document.getElementById("PhotoArea-"+object.getAttribute("wno")).style.display = "none";
					object.className = "x-photo-collapse";
				}
			}
			object.appendChild(title);

			var area = document.createElement("div");
			area.id = "PhotoArea-"+data.get("wno");
			object.appendChild(area);
			isPhotoRenderSpace[data.get("wno")] = true;
		}

		var photo = document.createElement("div");
		var sHTML = "";
		sHTML+= '<div style="width:120px; border:1px solid #99BBE8; padding:3px; background:#D2E0F1;">';
		sHTML+= '<div style="font:0/0 arial;"><img src="'+data.get("photo")+'" style="width:120px; height:90px;" /></div>';
		sHTML+= '<div style="font-family:돋움; font-size:11px; text-align:center; margin-top:3px;"><span class="bold">'+data.get("name")+'</span> / 공수 <span class="bold">'+(data.get("working")/10).toFixed(1)+'</div>';
		sHTML+= '</div>';

		// 출근
		sHTML+= '<div style="width:120px; border:1px solid #CCCCCC; padding:3px; background:#F4F4F4; margin-top:5px;">';
		if (data.get("inphoto")) sHTML+= '<div style="font:0/0 arial;"><img src="'+data.get("inphoto")+'" style="width:120px; height:90px;" /></div>';
		else sHTML+= '<div style="font:0/0 arial;"><img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/nopic120.gif" style="width:120px; height:90px;" /></div>';
		sHTML+= '<div style="font-family:돋움; font-size:11px; margin-top:3px;">출근:';
		if (data.get("is_delay") == "TRUE") sHTML+= '<span style="color:#FF0000;">'+data.get("intime")+'</span>';
		else sHTML+= data.get("intime");
		if (data.get("write_intime")) {
			sHTML+= ' / ';
			if (data.get("is_delay") == "TRUE") sHTML+= '<span style="color:#FF0000;">';
			sHTML+= '<span style="font-weight:bold;">'+data.get("write_intime")+'</span>';
			if (data.get("is_delay") == "TRUE") sHTML+= '</span>';
		}
		if (data.get("is_write") == "TRUE") {
			sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet-text" onmouseover="Tip(true,\''+data.get("write_memo")+'\',event);" onmouseout="Tip(false)" />';
		}
		sHTML+= '</div>';
		sHTML+= '</div>';

		// 퇴근
		sHTML+= '<div style="width:120px; border:1px solid #CCCCCC; padding:3px; background:#F4F4F4; margin-top:5px;">';
		if (data.get("outphoto")) sHTML+= '<div style="font:0/0 arial;"><img src="'+data.get("outphoto")+'" style="width:120px; height:90px;" /></div>';
		else sHTML+= '<div style="font:0/0 arial;"><img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/nopic120.gif" style="width:120px; height:90px;" /></div>';
		sHTML+= '<div style="font-family:돋움; font-size:11px; margin-top:3px;">퇴근: ';
		if (data.get("is_early") == "TRUE") sHTML+= '<span style="color:#FF0000;">'+data.get("outtime")+'</span>';
		else sHTML+= data.get("outtime");
		if (data.get("write_outtime")) {
			sHTML+= ' / ';
			if (data.get("is_early") == "TRUE") sHTML+= '<span style="color:#FF0000;">';
			sHTML+= '<span style="font-weight:bold;">'+data.get("write_outtime")+'</span>';
			if (data.get("is_early") == "TRUE") sHTML+= '</span>';
		}
		if (data.get("is_write") == "TRUE") {
			sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet-text" onmouseover="Tip(true,\''+data.get("write_memo")+'\',event);" onmouseout="Tip(false)" />';
		}
		sHTML+= '</div>';
		sHTML+= '</div>';

		photo.className = "panel-attend-photo";
		photo.innerHTML = sHTML;

		if (Ext.getCmp("ListTab1").getStore().baseParams.wno == "") {
			document.getElementById("PhotoArea-"+data.get("wno")).appendChild(photo);
			document.getElementById("PhotoNum-"+data.get("wno")).innerHTML = parseInt(document.getElementById("PhotoNum-"+data.get("wno")).innerHTML)+1;
		} else {
			object.appendChild(photo);
		}

		idx++;
		Ext.getCmp("LoadingPhotoProgress").updateProgress((idx)/total,"사진데이터 정리중... "+(idx)+' of '+total+'...');
		setTimeout("ShowPhotoViewInner("+idx+")",50);
	}
}

function WorkerMonthlyCalendarFunction(data) {
	var calendar = "";
	calendar+= '<table cellpadding="0" cellspacing="0" style="background:#E5E5E5; table-layout:fixed; width:100%;">';
	calendar+= '<col width="14.28%" /><col width="14.28%" /><col width="14.28%" /><col width="14.28%" /><col width="14.28%" /><col width="14.28%" /><col width="14.28%" />';
	calendar+= '<tr class="x-grid3-header x-grid3-hd-row" style="height:25px;">';
	calendar+= '<td style="text-align:center; font-weight:bold; color:#960000;">일</td>';
	calendar+= '<td style="text-align:center; font-weight:bold;">월</td>';
	calendar+= '<td style="text-align:center; font-weight:bold;">화</td>';
	calendar+= '<td style="text-align:center; font-weight:bold;">수</td>';
	calendar+= '<td style="text-align:center; font-weight:bold;">목</td>';
	calendar+= '<td style="text-align:center; font-weight:bold;">금</td>';
	calendar+= '<td style="text-align:center; font-weight:bold; color:#0753BB;">토</td>';
	calendar+= '</tr>';

	var temp = data.baseParams.date.split("-");

	var day = 1;
	var startPoint = new Date(temp[1]+"/01/"+temp[0]).format("w");
	var endPoint = new Date(temp[1]+"/01/"+temp[0]).format("t");

	var isStart = false;
	for (var i=0;i<42;i++) {
		if (i == startPoint) isStart = true;
		if (i%7 == 0) calendar+= '<tr style="background:#FFFFFF;" class="x-grid3-hd-row">';
		calendar+= '<td style="vertical-align:top;">';

		if (isStart == true) {
			if (day <= endPoint) {
				var forThisDay = new Date(temp[1]+"/"+day+"/"+temp[0]);
				if (forThisDay.format("w") == 0) calendar+= '<div style="font-family:verdana; font-weight:bold; font-size:10px; color:#960000; width:15px; padding:5px 0px 0px 5px;">'+day+'</div>';
				else if (forThisDay.format("w") == 6) calendar+= '<div style="font-family:verdana; font-weight:bold; font-size:10px; color:#0753BB; width:15px; padding:5px 0px 0px 5px;">'+day+'</div>';
				else calendar+= '<div style="font-family:verdana; font-weight:bold; font-size:10px; width:15px; padding:5px 0px 0px 5px;">'+day+'</div>';
				calendar+= '<div id="MonthlyCalendar'+forThisDay.format("Y-m-d")+'"></div>';
			}
			day++;
		}

		calendar+= '</td>';

		if (i%7 == 6) calendar+= '</tr><tr class="height1"><td colspan="7" style="background:#D0D0D0; height:1px; overflow:hidden;"></td></tr>';
		if (i%7 == 6 && day > endPoint) break;
	}
	calendar+= '</table>';

	Ext.getCmp("MonthlyCalendarPanel").getLayoutTarget().dom.innerHTML = calendar;

	for (i=0, total=data.getCount();i<total;i++) {
		var day = i+1;
		var dayString = day < 10 ? "0"+day : day;
		var object = document.getElementById("MonthlyCalendar"+data.getAt(i).get("date"));

		var sHTML = '<div style="margin:5px; border:1px solid #DDDDDD; overflow:hidden; position:relative;">';

		if (data.getAt(i).get("inphoto")) {
			sHTML+= '<img src="'+data.getAt(i).get("inphoto")+'" style="width:100%;" />';
		} else {
			sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/nopic120.gif" style="width:100%;" />';
		}
		sHTML+= '</div>';
		sHTML+= '<div class="dotum f11 center">출근 : '+(data.getAt(i).get("intime") ? (data.getAt(i).get("intime")+(data.getAt(i).get("write_intime") ? ' / <span class="red bold" style="cursor:pointer;" onmouseover="Tip(true,\''+data.getAt(i).get("write_meno")+'\',event)" onmouseout="Tip(false)">'+data.getAt(i).get("write_intime")+'</span>' : '')) : '출근기록없음')+'</div>';

		sHTML+= '<div class="height5"></div>';

		sHTML+= '<div style="margin:5px; border:1px solid #DDDDDD; overflow:hidden; position:relative;">';
		if (data.getAt(i).get("outphoto")) {
			sHTML+= '<img src="'+data.getAt(i).get("outphoto")+'" style="width:100%;" />';
		} else {
			sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/nopic120.gif" style="width:100%;" />';
		}
		sHTML+= '</div>';
		sHTML+= '<div class="dotum f11 center">퇴근 : '+(data.getAt(i).get("outtime") ? (data.getAt(i).get("outtime")+(data.getAt(i).get("write_outtime") ? ' / <span class="red bold" style="cursor:pointer;" onmouseover="Tip(true,\''+data.getAt(i).get("write_meno")+'\',event)" onmouseout="Tip(false)">'+data.getAt(i).get("write_outtime")+'</span>' : '')) : '퇴근기록없음')+'</div>';

		sHTML+= '<div class="height5"></div>';

		object.innerHTML = sHTML;
	}
}

function WorkerMonthlyFunction(date,data) {
	var MonthlyStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"date","workspace","intime","outtime","write_intime","write_outtime","inphoto","outphoto","is_early","is_delay","is_support","oworkspace","is_write",{name:"month_attend",type:"int"},{name:"month_delay",type:"int"},{name:"month_early",type:"int"},{name:"working",type:"int"},"write_memo","etc"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"date":date,"workernum":data.get("workernum"),"action":"attend","get":"monthly"}
	});

	new Ext.Window({
		title:data.get("name")+"님의 근태기록보기",
		width:900,
		height:500,
		modal:true,
		resizable:false,
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
				text:"이전달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex == 0) {
						Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						MonthlyStore.baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
						Ext.getCmp("month").setValue(MonthlyStore.baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
						MonthlyStore.reload();
					}
				}
			}),
			' ',
			new Ext.form.ComboBox({
				id:"month",
				store:new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["date","display"]
					}),
					remoteSort:false,
					sortInfo:{field:"date",direction:"ASC"},
					baseParams:{"action":"attend","get":"monthly_list","workernum":data.get("workernum")}
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
						form.getStore().load();
						form.getStore().on("load",function() {
							var temp = date.split("-");
							var thisMonth = temp[0]+"-"+temp[1]+"-01";
							form.setValue(thisMonth);

							for (var i=0, loop=form.getStore().getCount();i<loop;i++) {
								if (form.getStore().getAt(i).get("date") == form.getValue()) {
									form.selectedIndex = i;
									break;
								}
							}
						});
					}},
					select:{fn:function(form) {
						MonthlyStore.baseParams.date = form.getValue();
						MonthlyStore.reload();
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
						MonthlyStore.baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
						Ext.getCmp("month").setValue(MonthlyStore.baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
						MonthlyStore.reload();
					}
				}
			}),
			'-',
			new Ext.Button({
				text:"엑셀파일로 변환",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
				handler:function() {
					ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=worker&get=monthly&workernum="+data.get("workernum")+"&date="+Ext.getCmp("month").getValue());
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"MonthlyPanel",
				border:false,
				tabPosition:"bottom",
				activeTab:0,
				items:[
					new Ext.grid.GridPanel({
						id:"MonthlyListPanel",
						title:"리스트보기",
						border:false,
						layout:"fit",
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"현장명",
								dataIndex:"workspace",
								width:120
							},{
								header:"출근시간",
								dataIndex:"intime",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_delay == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_intime) {
										sHTML+= ' / ';
										if (record.data.is_delay == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_intime+'</span>';
										if (record.data.is_delay == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.inphoto) {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.inphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"퇴근시간",
								dataIndex:"outtime",
								sortable:true,
								width:110,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_early == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_outtime) {
										sHTML+= ' / ';
										if (record.data.is_early == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_outtime+'</span>';
										if (record.data.is_early == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.outphoto) {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.outphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"금월근무일",
								dataIndex:"month_attend",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"금월지각일",
								dataIndex:"month_delay",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"금월조퇴일",
								dataIndex:"month_early",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"공수",
								dataIndex:"working",
								sortable:true,
								width:40,
								renderer:function(value) {
									return '<div style="text-align:right; font-family:arial;">'+(value/10).toFixed(1)+'</div>';
								}
							},{
								header:"검토사항",
								dataIndex:"etc",
								sortable:true,
								width:250
							}
						]),
						store:MonthlyStore
					}),
					new Ext.Panel({
						id:"MonthlyCalendarPanel",
						title:"달력보기",
						border:false,
						layout:"fit",
						autoScroll:true,
						html:""
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getId() == "MonthlyCalendarPanel") {
						WorkerMonthlyCalendarFunction(Ext.getCmp("MonthlyListPanel").getStore());
					}
				}}}
			})
		]
	}).show();

	MonthlyStore.load();
	MonthlyStore.on("load",function(store) {
		if (Ext.getCmp("MonthlyPanel").getActiveTab().getId() == "MonthlyCalendarPanel") {
			WorkerMonthlyCalendarFunction(store);
		}
	});
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	var AttendStore = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"workspace","wno","pno","name","grade","photo","workernum","jumin","intime","outtime","write_intime","write_outtime","inphoto","outphoto","is_early","is_delay","is_support","oworkspace","is_write",{name:"month_attend",type:"int"},{name:"month_delay",type:"int"},{name:"month_early",type:"int"},{name:"working",type:"int"},"write_memo","etc"]
		}),
		remoteSort:false,
		groupField:"workspace",
		sortInfo:{field:"workspace",direction:"ASC"},
		baseParams:{"wno":"","action":"attend","get":"attend","date":"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>"}
	});
	AttendStore.on("load",function(store) {
		isPhotoRender = false;
		if (Ext.getCmp("ListTab").getActiveTab().getId() == "ListTab2") ShowPhotoView();

		if (store.baseParams.wno != "") {
			store.clearGrouping();
		} else {
			store.groupBy("workspace");
		}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"근태관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						id:"PrevButton",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);
							AttendStore.baseParams.date = today;
							AttendStore.load();
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
							AttendStore.baseParams.date = today;
							AttendStore.load();
						}}}
					}),
					' ',
					new Ext.Button({
						id:"NextButton",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);
							AttendStore.baseParams.date = today;
							AttendStore.load();
						}
					}),
					'-',
					new Ext.form.ComboBox({
						id:"workspace",
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title"]
							}),
							remoteSort:false,
							sortInfo:{field:"title",direction:"ASC"},
							baseParams:{"action":"workspace","get":"list","category":"working"}
						}),
						displayField:"title",
						valueField:"idx",
						typeAhead:true,
						mode:"local",
						triggerAction:"all",
						width:160,
						editable:false,
						emptyText:"현장별 보기",
						listeners:{
							render:{fn:function(form) {
								form.getStore().load();
							}},
							select:{fn:function(form) {
								Ext.getCmp("ListTab1").getStore().baseParams.wno = form.getValue();
								Ext.getCmp("ListTab1").getStore().reload();
							}}
						}
					}),
					' ',
					new Ext.Button({
						id:"AllButton",
						text:"전체현장보기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
						handler:function() {
							Ext.getCmp("ListTab1").getStore().baseParams.wno = "";
							Ext.getCmp("workspace").setValue("");
							Ext.getCmp("ListTab1").getStore().reload();
						}
					}),
					new Ext.Button({
						id:"ExcelButton",
						text:"엑셀파일로 변환",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
						handler:function() {
							if (!Ext.getCmp("workspace").getValue()) {
								Ext.Msg.show({title:"에러",msg:"엑셀파일로 변환할 현장을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=worker&get=all&wno="+Ext.getCmp("workspace").getValue()+"&date="+Ext.getCmp("today").getValue().format("Y-m"));
							}
						}
					}),
					'-',
					new Ext.Button({
						id:"SaveButton",
						text:"변경사항 저장하기",
						disabled:true,
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
						handler:function() {
							var data = GetGridData(Ext.getCmp("ConditionPanel"));

							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("ConditionPanel").getStore().commitChanges();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"attend","do":"condition","data":data}
							});
						}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListTab1",
						title:"리스트보기",
						border:false,
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"현장명",
								dataIndex:"workspace",
								width:120
							},{
								header:"이름",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"직위",
								dataIndex:"grade",
								width:80,
								renderer:function(value) {
									var temp = value.split("||");
									var sHTML = temp[0];

									if (temp.length > 1 && temp[1]) {
										sHTML+= " ("+temp[1]+")";
									}

									return sHTML;
								}
							},{
								header:"직원번호",
								dataIndex:"workernum",
								sortable:true,
								width:110,
								renderer:function(value,p,record) {
									var sHTML = value;
									if (record.data.is_support == "TRUE") {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_support.png" class="grid-bullet-text" onmouseover="Tip(true,\'['+record.data.oworkspace+']에서 파견근무\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								sortable:true,
								width:110
							},{
								header:"출근시간",
								dataIndex:"intime",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_delay == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_intime) {
										sHTML+= ' / ';
										if (record.data.is_delay == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_intime+'</span>';
										if (record.data.is_delay == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.inphoto) {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.inphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"퇴근시간",
								dataIndex:"outtime",
								sortable:true,
								width:110,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_early == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_outtime) {
										sHTML+= ' / ';
										if (record.data.is_early == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_outtime+'</span>';
										if (record.data.is_early == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.outphoto) {
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.outphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"금월근무일",
								dataIndex:"month_attend",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"금월지각일",
								dataIndex:"month_delay",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"금월조퇴일",
								dataIndex:"month_early",
								sortable:true,
								width:70,
								renderer:GridNumberFormat
							},{
								header:"공수",
								dataIndex:"working",
								sortable:true,
								width:40,
								renderer:function(value) {
									return '<div style="text-align:right; font-family:arial;">'+(value/10).toFixed(1)+'</div>';
								}
							},{
								header:"검토사항",
								dataIndex:"etc",
								sortable:true,
								width:250
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:AttendStore,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							groupTextTpl:'{text} ({[values.rs.length]}명)'
						}),
						listeners:{
							rowcontextmenu:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
/*
								var menu = new Ext.menu.Menu();
								menu.add({
									text:"<b>"+data.get("name")+"</b>",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_user.png")
								});
								menu.add(new Ext.menu.Separator({}));
								menu.add({
									text:"근로자정보수정",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_user_edit.png"),
									handler:function(item) {
										WorkerFormFunction("modify",data.get("idx"));
									}
								});
								menu.add({
									text:"근로자카드인쇄",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_vcard.png"),
									handler:function(item) {
										WorkerCardFunction(data.get("idx"));
									}
								});
								e.stopEvent();
								menu.showAt(e.getXY());
*/
							}},
							rowdblclick:{fn:function(grid,idx,e) {
								WorkerMonthlyFunction(grid.getStore().baseParams.date,grid.getStore().getAt(idx));
							}}
						}
					}),
					new Ext.Panel({
						id:"ListTab2",
						autoScroll:true,
						title:"사진보기",
						html:""
					}),
					new Ext.grid.EditorGridPanel({
						id:"ConditionPanel",
						title:"출결설정",
						border:false,
						layout:"fit",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"현장명",
								dataIndex:"workspace",
								width:250
							},{
								header:"자동지각설정",
								dataIndex:"auto_delay_condition",
								width:350,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"자동조퇴설정",
								dataIndex:"auto_early_condition",
								width:350,
								editor:new Ext.form.TextField({selectOnFocus:true})
							}
						]),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"workspace","auto_delay_condition","auto_early_condition"]
							}),
							remoteSort:false,
							sortInfo:{field:"idx",direction:"ASC"},
							baseParams:{"action":"attend","get":"condition"}
						}),
						trackMouseOver:true,
						clicksToEdit:1,
						listeners:{render:{fn:function() {
							Ext.getCmp("ConditionPanel").getStore().load();
						}}}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getId() == "ListTab2") {
						ShowPhotoView();
					}

					if (tab.getId() == "ConditionPanel") {
						Ext.getCmp("PrevButton").disable();
						Ext.getCmp("today").disable();
						Ext.getCmp("NextButton").disable();
						Ext.getCmp("workspace").disable();
						Ext.getCmp("AllButton").disable();
						Ext.getCmp("ExcelButton").disable();
						Ext.getCmp("SaveButton").enable();
					} else {
						Ext.getCmp("PrevButton").enable();
						Ext.getCmp("today").enable();
						Ext.getCmp("NextButton").enable();
						Ext.getCmp("workspace").enable();
						Ext.getCmp("AllButton").enable();
						Ext.getCmp("ExcelButton").enable();
						Ext.getCmp("SaveButton").disable();
					}
				}}}
			})
		]
	});

	AttendStore.load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>