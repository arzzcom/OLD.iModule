var BankSimpleStore = new Ext.data.SimpleStore({
	fields:["bank"],
	data:[["우리은행"],["경남은행"],["광주은행"],["국민(주택)은행"],["기업은행"],["농협"],["대구은행"],["도이치은행"],["부산은행"],["산업은행"],["상호저축은행"],["새마을금고"],["수출입은행"],["수협중앙회"],["신용협동조합"],["신한(조흥)은행"],["외환은행"],["우체국"],["제주은행"],["하나(서울)은행"],["한국시티은행(한미)"],["ABN-AMRO"],["HSBC은행"],["SC제일은행"]]
});

var JobSimpleStore = new Ext.data.SimpleStore({
	fields:["job"],
	data:[["일반공"],["철거공"],["도장공"],["목공"],["철근공"],["설비공"],["미장공"]]
});

/************************************************************************************************
 * 자재관련
 ***********************************************************************************************/
// 품명DB검색실패
function GridItemNotFound(value,p,record) {
	if (!record.data.itemcode) {
		return '<div style="color:#FF0000;" onmouseover="Tip(true,\'품명DB에 없는 품목입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
	} else {
		return value;
	}
}

// 도급검색실패
function GridContractItemNotFound(value,p,record) {
	if (!record.data.code) {
		return '<div style="color:#FF0000;" onmouseover="Tip(true,\'도급내역에 없는 품목입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
	} else {
		return value;
	}
}

// 평균단가출력
function GridItemAvgCost(value,avgcost) {
	var tipHTML = '';
	var total = 0;
	if (avgcost) {
		var avgcost = avgcost.split(",");
		for (var i=0, loop=avgcost.length;i<loop;i++) {
			var temp = avgcost[i].split("@");
			total+= parseInt(temp[1]);
			tipHTML+= '<br /><b>'+temp[0]+':</b> '+GetNumberFormat(temp[1])+' ('+temp[2]+', '+temp[3]+')';
		}

		tipHTML = '<b>최근 '+avgcost.length+'개 현장의 평균단가:</b> '+GetNumberFormat(Math.floor(total/avgcost.length))+'<br />'+tipHTML;
	} else {
		tipHTML = '이전 단가내역이 없습니다.';
	}

	return '<div onmouseover="Tip(true,\''+tipHTML+'\',event)" onmouseout="Tip(false)" style="text-align:right;font-family:arial;">'+GetNumberFormat(value)+'</div>';
}

// 발주수량 출력
function GridItemOrderEA(value,p,record) {
	if (value.toString() == record.data.order_ea) {
		var order = value.split(",");
		return '<div style="font-family:arial;"><div style="float:left; width:40px; text-align:right; background:url('+ENV.dir+'/module/erp/images/common/icon_income_cost1.gif) no-repeat 0 50%;">'+order[1]+'</div><div style="float:left; width:45px; text-align:right; background:url('+ENV.dir+'/module/erp/images/common/icon_income_cost2.gif) no-repeat 3px 50%;">'+order[2]+'</div><div style="float:left; width:45px; text-align:right; background:url('+ENV.dir+'/module/erp/images/common/icon_income_cost3.gif) no-repeat 3px 50%;">'+order[3]+'</div></div>';
	} else {
		var order = record.data.order_ea.split(",");
		return '<div style="text-align:right; font-family:arial;" onmouseover="Tip(true,\'<b>재료발주수량: </b>'+GetNumberFormat(order[1])+'<br /><b>노무발주수량: </b>'+GetNumberFormat(order[2])+'<br /><b>경비발주수량: </b>'+GetNumberFormat(order[3])+'\',event)" onmouseout="Tip(false)">'+GetNumberFormat(value)+'</div>';
	}
}

// 품명자동완성폼
function GridEditorAutoMatchItem(grid,wno) {
	if (!wno) {
		wno = "0";
		url = "Admin.get.php";
	} else {
		url = "Workspace.get.php";
	}

	var editor = new Ext.form.TextField({
		selectOnFocus:true,
		enableKeyEvents:true,
		listeners:{
			focus:{fn:function(form) {
				autoMatch.ready = true;
				AutoMatchStart(form,new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/"+url}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["display","itemcode","code","workgroup","gno","worktype","tno","title","size","unit",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"sort",type:"int"}]
					}),
					remoteSort:false,
					sortInfo:{field:"sort",direction:"ASC"},
					baseParams:{"action":"item","get":"automatch","wno":wno}
				}),"display","value");
			}},
			blur:{fn:AutoMatchStop},
			keydown:{fn:AutoMatchKeyEvent}
		}
	});

	grid.getColumnModel().setEditor(grid.getColumnModel().findColumnIndex("title"),new Ext.grid.GridEditor(editor));
}

// 품명자동완성
function GridAutoMatchItem(object,wno) {
	if (!wno) {
		wno = "0";
		url = "Admin.get.php";
	} else {
		url = "Workspace.get.php";
	}

	var isMatched = false;
	if (object.field == "title") {
		if (autoMatch.start == true) {
			if (autoMatch.position != -1) {
				isMatched = true;
				var data = autoMatch.store.getAt(autoMatch.position);

				object.grid.getStore().getAt(object.row).set("itemcode",data.get("itemcode"));
				object.grid.getStore().getAt(object.row).set("title",data.get("title"));
				object.grid.getStore().getAt(object.row).set("size",data.get("size"));
				object.grid.getStore().getAt(object.row).set("unit",data.get("unit"));
				try {
					object.grid.getStore().getAt(object.row).set("avgcost1",data.get("avgcost1"));
					object.grid.getStore().getAt(object.row).set("avgcost2",data.get("avgcost2"));
					object.grid.getStore().getAt(object.row).set("avgcost3",data.get("avgcost3"));
					if (wno != "0") object.grid.getStore().getAt(object.row).set("code",data.get("code"));
				} catch(e) {}

				if (object.record.data.is_new == "TRUE") {
					object.grid.getStore().getAt(object.row).set("cost1",data.get("cost1"));
					object.grid.getStore().getAt(object.row).set("cost2",data.get("cost2"));
					object.grid.getStore().getAt(object.row).set("cost3",data.get("cost3"));
					try {
						object.grid.getStore().getAt(object.row).set("worktype",data.get("worktype"));
						object.grid.getStore().getAt(object.row).set("workgroup",data.get("workgroup"));
						object.grid.getStore().getAt(object.row).set("gno",data.get("gno"));
						object.grid.getStore().getAt(object.row).set("tno",data.get("tno"));
					} catch(e) {}
				}
			}
		}
	}

	if (isMatched == false && (object.field == "gno" || object.field == "tno" || object.field == "title" || object.field == "size" || object.field == "unit")) {
		Ext.Ajax.request({
			url:ENV.dir+"/module/erp/exec/"+url,
			success:function(XML) {
				if (AjaxResult(XML,"itemcode")) {
					object.grid.getStore().getAt(object.row).set("itemcode",AjaxResult(XML,"itemcode"));
					try {
						object.grid.getStore().getAt(object.row).set("avgcost1",AjaxResult(XML,"avgcost1"));
						object.grid.getStore().getAt(object.row).set("avgcost2",AjaxResult(XML,"avgcost2"));
						object.grid.getStore().getAt(object.row).set("avgcost3",AjaxResult(XML,"avgcost3"));
						if (wno != "0") object.grid.getStore().getAt(object.row).set("code",AjaxResult(XML,"code"));
					} catch(e) {}
				} else {
					object.grid.getStore().getAt(object.row).set("itemcode","");
					try {
						object.grid.getStore().getAt(object.row).set("avgcost1","");
						object.grid.getStore().getAt(object.row).set("avgcost2","");
						object.grid.getStore().getAt(object.row).set("avgcost3","");
						if (wno != "0") object.grid.getStore().getAt(object.row).set("code","");
					} catch(e) {}
				}
			},
			failure:function() {
				object.grid.getStore().getAt(object.row).set("itemcode","");
				try {
					object.grid.getStore().getAt(object.row).set("avgcost1","");
					object.grid.getStore().getAt(object.row).set("avgcost2","");
					object.grid.getStore().getAt(object.row).set("avgcost3","");
					if (wno != "0") object.grid.getStore().getAt(object.row).set("code","");
				} catch(e) {}
			},
			headers:{},
			params:{"action":"item","get":"check","wno":wno,"gno":object.record.data.gno,"tno":object.record.data.tno,"title":object.record.data.title,"size":object.record.data.size,"unit":object.record.data.unit}
		});
	}

	object.grid.selModel.select(object.row,object.column);
}

// 금액자동완성폼
var GridAutoMatchPriceObject = {};
function GridAutoMatchPrice(object) {
	GridAutoMatchPriceObject = object;
}

function GridEditorAutoMatchPrice(object) {
	var avgcost = null;
	if (object.field == "cost1") var avgcost = object.record.data.avgcost1;
	else if (object.field == "cost2") var avgcost = object.record.data.avgcost2;
	else if (object.field == "cost3") var avgcost = object.record.data.avgcost3;

	if (avgcost != null) {
		var position = 0;
		var editor = new Ext.form.TextField({
			selectOnFocus:true,
			enableKeyEvents:true,
			listeners:{
				focus:{fn:function(form) {
					position = -1;
					var list = document.getElementById("AutoMatchLayer");
					list.style.top = (form.getPosition()[1]+20)+"px";
					list.style.left = (form.getPosition()[0])+"px";

					list.innerHTML = "";
					list.style.display = "";
					avgcost = avgcost.split(",");
					var total = 0;
					for (var i=0, loop=avgcost.length;i<loop;i++) {
						var temp = avgcost[i].split("@");
						total+= parseInt(temp[1]);

						var cost = document.createElement("div");
						cost.setAttribute("cost",temp[1]);
						cost.innerHTML = '<b>'+temp[0]+':</b> '+GetNumberFormat(temp[1])+' ('+temp[2]+', '+temp[3]+')';
						list.appendChild(cost);
					}

					var cost = document.createElement("div");
					cost.setAttribute("cost",Math.floor(total/avgcost.length));
					cost.innerHTML = '<b>최근 '+avgcost.length+'개 현장의 평균단가:</b> '+GetNumberFormat(Math.floor(total/avgcost.length));
					list.insertBefore(cost,list.getElementsByTagName("div")[0]);
					list.style.display = "";
				}},
				blur:{fn:function() {
					document.getElementById("AutoMatchLayer").innerHTML = "";
					document.getElementById("AutoMatchLayer").style.display = "none";
				}},
				keydown:{fn:function(form,e) {
					var list = document.getElementById("AutoMatchLayer");
					if (list.getElementsByTagName("div").length == 0) return;

					if (e.keyCode == 40) {
						if (position != -1) list.getElementsByTagName("div")[position].className = "";
						position++;
						if (position >= list.getElementsByTagName("div").length) position = 0;
						list.getElementsByTagName("div")[position].className = "select";
					} else if (e.keyCode == 38) {
						if (position == -1) position = list.getElementsByTagName("div").length;
						else list.getElementsByTagName("div")[position].className = "";
						position--;
						if (position < 0) position = list.getElementsByTagName("div").length-1;
						list.getElementsByTagName("div")[position].className = "select";
					}

					if (e.keyCode == 38 || e.keyCode == 40) {
						form.setValue(list.getElementsByTagName("div")[position].getAttribute("cost"));
					}
				}}
			}
		});

		object.grid.getColumnModel().setEditor(object.grid.getColumnModel().findColumnIndex(object.field),new Ext.grid.GridEditor(editor));
	}
}

// 도급내역 검색창
function GridContractSearchList(type,wno,region,height) {
	if (!region) region = "north";
	if (!height) height = 180;
	if (type == "admin") {
		var url = "Admin.get.php";
	} else {
		var url = "Workspace.get.php";
	}

	var ItemStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/"+url}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"itemcode","code","workgroup","worktype","gno","tno","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"order_ea","avgcost1","avgcost2","avgcost3"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"ASC"},
		baseParams:{"action":"workspace","get":"contract","keyword":"","wno":wno,"gno":"","tno":""}
	});

	var toolbar = [
		new Ext.form.ComboBox({
			id:"ContractSearchWorkgroup",
			typeAhead:true,
			triggerAction:"all",
			lazyRender:true,
			store:new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/"+url}),
				reader:new Ext.data.JsonReader({
					root:"lists",
					totalProperty:"totalCount",
					fields:["idx","workgroup","sort"]
				}),
				remoteSort:false,
				sortInfo:{field:"sort",direction:"ASC"},
				baseParams:{"action":"workspace","get":"workgroup","wno":wno,"is_all":"true"},
			}),
			width:100,
			editable:false,
			mode:"local",
			displayField:"workgroup",
			valueField:"idx",
			listeners:{
				render:{fn:function(form) {
					form.getStore().load();
					form.getStore().on("load",function(store) {
						Ext.getCmp("ContractSearchWorkgroup").setValue(store.getAt(0).get("idx"));
					});
				}},
				select:{fn:function(form) {
					Ext.getCmp("ContractSearchWorktype").getStore().baseParams.gno = form.getValue();
					Ext.getCmp("ContractSearchWorktype").getStore().load();
				}}
			}
		}),
		' ',
		new Ext.form.ComboBox({
			id:"ContractSearchWorktype",
			typeAhead:true,
			triggerAction:"all",
			lazyRender:true,
			store:new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/"+url}),
				reader:new Ext.data.JsonReader({
					root:"lists",
					totalProperty:"totalCount",
					fields:["idx","worktype","sort"]
				}),
				remoteSort:false,
				sortInfo:{field:"sort",direction:"ASC"},
				baseParams:{"action":"workspace","get":"worktype","wno":wno,"gno":"","is_all":"true"},
			}),
			width:100,
			editable:false,
			mode:"local",
			displayField:"worktype",
			valueField:"idx",
			emptyText:"공종선택",
			listeners:{
				render:{fn:function(form) {
					form.getStore().load();
					form.getStore().on("load",function(store) {
						Ext.getCmp("ContractSearchWorktype").setValue(store.getAt(0).get("idx"));
					});
				}}
			}
		}),
		' ',
		new Ext.form.TextField({
			id:"ContractSearchKeyword",
			width:150,
			emptyText:"검색어를 입력하세요"
		}),
		' ',
		new Ext.Button({
			id:"ContractSearchSearchButton",
			text:"검색",
			icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
			handler:function() {
				Ext.getCmp("ContractSearchList").getStore().baseParams.keyword = Ext.getCmp("ContractSearchKeyword").getValue();
				Ext.getCmp("ContractSearchList").getStore().baseParams.gno = Ext.getCmp("ContractSearchWorkgroup").getValue();
				Ext.getCmp("ContractSearchList").getStore().baseParams.tno = Ext.getCmp("ContractSearchWorktype").getValue();
				Ext.getCmp("ContractSearchList").getStore().load({params:{start:0,limit:30}});
			}
		})
	];

	var cm = new Ext.grid.ColumnModel([
		new Ext.grid.CheckboxSelectionModel(),
		{
			header:"공종그룹",
			dataIndex:"workgroup",
			width:80
		},{
			header:"공종명",
			dataIndex:"worktype",
			width:120
		},{
			header:"품명",
			dataIndex:"title",
			width:240
		},{
			header:"규격",
			dataIndex:"size",
			width:100
		},{
			header:"단위",
			dataIndex:"unit",
			width:40
		},{
			header:"수량",
			dataIndex:"ea",
			width:60,
			sortable:false,
			renderer:GridItemOrderEA
		},{
			header:"재료단가",
			dataIndex:"cost1",
			width:80,
			sortable:false,
			renderer:function(value,p,record) {
				return GridItemAvgCost(value,record.data.avgcost1);
			}
		},{
			header:"노무단가",
			dataIndex:"cost2",
			width:80,
			sortable:false,
			renderer:function(value,p,record) {
				return GridItemAvgCost(value,record.data.avgcost2);
			}
		},{
			header:"경비단가",
			dataIndex:"cost3",
			width:80,
			sortable:false,
			renderer:function(value,p,record) {
				return GridItemAvgCost(value,record.data.avgcost3);
			}
		}
	]);

	var bottombar = new Ext.PagingToolbar({
		pageSize:30,
		store:ItemStore,
		displayInfo:true,
		displayMsg:'{0} - {1} of {2}',
		emptyMsg:"데이터없음"
	});

	if (region == "fit") {
		var panel = new Ext.grid.GridPanel({
			id:"ContractSearchList",
			border:false,
			layout:"fit",
			tbar:toolbar,
			cm:cm,
			sm:new Ext.grid.CheckboxSelectionModel(),
			store:ItemStore,
			bbar:bottombar,
			listeners:{
				render:{fn:function() {
					Ext.getCmp("ContractSearchList").getStore().load({params:{start:0,limit:30}});
				}}
			}
		});
	} else {
		var panel = new Ext.grid.GridPanel({
			id:"ContractSearchList",
			title:"도급내역검색",
			margins:"5 5 0 5",
			region:region,
			height:height,
			split:true,
			collapsible:true,
			tbar:toolbar,
			cm:cm,
			sm:new Ext.grid.CheckboxSelectionModel(),
			store:ItemStore,
			bbar:bottombar,
			listeners:{
				render:{fn:function() {
					Ext.getCmp("ContractSearchList").getStore().load({params:{start:0,limit:30}});
				}}
			}
		});
	}

	return panel;
}

/************************************************************************************************
 * 공종그룹 / 공종명 편집관련
 ***********************************************************************************************/
// 공종그룹 렌더러
function GridWorkgroup(value,p,record,editor) {
	if (record.data.workgroup) {
		return record.data.workgroup;
	} else if (value == "0") {
		return "";
	} else {
		if (value) {
			if (editor.rendered == false) {
				editor.store.load();
				for (var i=0, loop=editor.store.getCount();i<loop;i++) {
					if (editor.store.getAt(i).get("idx") == value) {
						return editor.store.getAt(i).get("workgroup");
					}
				}
			} else {
				for (var i=0, loop=editor.store.getCount();i<loop;i++) {
					if (editor.store.getAt(i).get("idx") == value) {
						return editor.store.getAt(i).get("workgroup");
					}
				}
			}

			return value;
		}
	}
}

// 공종명 렌더러
function GridWorktype(value,p,record,editor) {
	if (record.data.worktype) {
		return record.data.worktype;
	} else if (value == "0") {
		return "";
	} else {
		if (value) {
			if (editor.rendered == false) {
				editor.store.load();
				for (var i=0, loop=editor.store.getCount();i<loop;i++) {
					if (editor.store.getAt(i).get("idx") == value) {
						return editor.store.getAt(i).get("worktype");
					}
				}
			} else {
				for (var i=0, loop=editor.store.getCount();i<loop;i++) {
					if (editor.store.getAt(i).get("idx") == value) {
						return editor.store.getAt(i).get("worktype");
					}
				}
			}

			return value;
		}
	}
}

function GridEditorWorkgroupType(grid,wno) {
	var workgroup = new Ext.form.ComboBox({
		typeAhead:true,
		triggerAction:"all",
		lazyRender:true,
		listClass:"x-combo-list-small",
		store:new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Workspace.get.php"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:["idx","workgroup","sort"]
			}),
			remoteSort:false,
			sortInfo:{field:"sort",direction:"ASC"},
			baseParams:{"action":"workspace","get":"workgroup","wno":wno,"is_all":"false","is_default":"true"},
		}),
		editable:false,
		mode:"local",
		displayField:"workgroup",
		valueField:"idx",
		listeners:{
			render:{fn:function(form) {
				form.getStore().load();
			}}
		}
	});

	var worktype = new Ext.form.ComboBox({
		typeAhead:true,
		triggerAction:"all",
		lazyRender:true,
		listClass:"x-combo-list-small",
		store:new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Workspace.get.php"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:["idx","worktype","sort"]
			}),
			remoteSort:false,
			sortInfo:{field:"sort",direction:"ASC"},
			baseParams:{"action":"workspace","get":"worktype","wno":wno,"gno":"","is_all":"false"},
		}),
		editable:false,
		mode:"local",
		displayField:"worktype",
		valueField:"idx"
	});

	grid.getColumnModel().setEditor(grid.getColumnModel().findColumnIndex("gno"),new Ext.grid.GridEditor(workgroup));
	grid.getColumnModel().setEditor(grid.getColumnModel().findColumnIndex("tno"),new Ext.grid.GridEditor(worktype));
}

function GridEditorBeforeWorkgroupType(object) {
	if (object.field == "tno") {
		object.grid.getColumnModel().getCellEditor(object.column,object.row).field.store.baseParams.gno = object.record.data.gno;
		object.grid.getColumnModel().getCellEditor(object.column,object.row).field.store.load();
	}
}

function GridEditorAfterWorkgroupType(object) {
	if (object.field == "gno") object.grid.getStore().getAt(object.row).set("workgroup","");
	if (object.field == "tno") object.grid.getStore().getAt(object.row).set("worktype","");
}