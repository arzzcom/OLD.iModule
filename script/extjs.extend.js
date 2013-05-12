document.writeln('<div id="TipLayer" style="display:none;"></div>');
document.writeln('<div id="AutoMatchLayer" style="display:none;"></div>');
document.writeln('<iframe name="ExcelFrame" style="display:none;"></iframe>');

// Cookie
function SetCookie(name,value,expire,path) {
	path = !path ? "/" : path;
	var todaydate = new Date();
	unixtime = todaydate.getTime();

	if (value == null) {
		extime = unixtime-3600;
		todaydate.setTime(extime);
		expiretime = " expires=" + todaydate.toUTCString() +";";
	} else {
		extime = unixtime+(expire*1000);
		todaydate.setTime(extime);
		if (expire) expiretime = " expires=" + todaydate.toUTCString() +";";
		else expiretime = "";
	}

	document.cookie = name + "=" + escape(value) + "; path="+path+";"+expiretime;
}

function GetCookie(name) {
	var cookies = document.cookie.split(";");
	var values = "";

	for (var i=0, total=cookies.length;i<total;i++) {
		if (cookies[i].indexOf(name+"=")!=-1) {
			var temp = cookies[i].split("=");
			values = temp[1];
			break;
		}
	}

	return values;
}

// Getter
function ExcelConvert(path) {
	new Ext.Window({
		id:"ExcelConvertWindow",
		title:"엑셀파일로 변환",
		modal:true,
		closable:false,
		width:600,
		resizable:false,
		items:[
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"변환 대기중",
				id:"ExcelConvertProgressBar",
				cls:"left-align",
				border:false
			})
		]
	}).show();

	ExcelFrame.location.href = path;
}

function ExcelConvertProgress(percent,text) {
	if (Ext.getCmp("ExcelConvertWindow")) {
		Ext.getCmp("ExcelConvertProgressBar").updateProgress(percent/100,text);
	}

	if (percent == 100) setTimeout("ExcelConvertEnd()",3000);
}

function ExcelConvertEnd() {
	if (Ext.getCmp("ExcelConvertWindow")) {
		Ext.getCmp("ExcelConvertWindow").close();
	}
}

function ExcelError(msg) {
	Ext.Msg.show({title:"엑셀변환에러",msg:msg,buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:ExcelConvertEnd});
}

function GetGridData(grid) {
	var rowData = new Array();
	for (var r=0, totalRow=grid.getStore().getCount();r<totalRow;r++) {
		var colData = new Array();

		for (var c=0, totalCol=grid.getColumnModel().getColumnCount();c<totalCol;c++) {
			if (grid.getColumnModel().getDataIndex(c)) {
				var field = grid.getColumnModel().getDataIndex(c);

				if (grid.getStore().getAt(r).get(field) !== undefined) {
					var value = grid.getStore().getAt(r).get(field).toString();
				} else {
					var value = "";
				}

				colData.push('"'+field+'":"'+value.replace(/\"/g,'&quot;')+'"');
			}
		}
		rowData[r] = '{'+colData.join(",")+'}';
	}
	var data = rowData.join(",");
	var paramReg = new RegExp("\\+","gi");
	data = data.replace(paramReg,"#*plus*#");
	return encodeURIComponent(data);
}

function GetFileSize(size) {
	size = parseInt(size);
	if(size < 1024) {
		return size + "B";
	} else if(size < 1048576) {
		return (size/1024).toFixed(2) + "KiB";
	} else if (size < 1073741824) {
		return (size/1048576).toFixed(2) + "MiB";
	} else {
		return (size/1073741824).toFixed(2) + "GiB";
	}
}

function GetFileIcon(filename) {
	var fileExt = filename.split(".");
	var fileType = fileExt[fileExt.length-1].toLowerCase();

	var fileicon = new Array();
	fileicon["gif"] = "../images/common/fileicon/img.png";
	fileicon["ai"] = "../images/common/fileicon/img.png";
	fileicon["jpg"] = "../images/common/fileicon/img.png";
	fileicon["jpeg"] = "../images/common/fileicon/img.png";
	fileicon["bmp"] = "../images/common/fileicon/img.png";
	fileicon["psd"] = "../images/common/fileicon/img.png";
	fileicon["png"] = "../images/common/fileicon/img.png";
	fileicon["xls"] = "../images/common/fileicon/excel.png";
	fileicon["xlsx"] = "../images/common/fileicon/excel.png";
	fileicon["txt"] = "../images/common/fileicon/txt.png";
	fileicon["csv"] = "../images/common/fileicon/excel.png";
	fileicon["doc"] = "../images/common/fileicon/word.png";
	fileicon["docx"] = "../images/common/fileicon/word.png";
	fileicon["ppt"] = "../images/common/fileicon/ppt.png";
	fileicon["pptx"] = "../images/common/fileicon/ppt.png";
	fileicon["hwp"] = "../images/common/fileicon/hwp.png";
	fileicon["zip"] = "../images/common/fileicon/zip.png";
	fileicon["rar"] = "../images/common/fileicon/zip.png";
	fileicon["alz"] = "../images/common/fileicon/zip.png";
	fileicon["avi"] = "../images/common/fileicon/mov.png";
	fileicon["mpg"] = "../images/common/fileicon/mov.png";
	fileicon["mpeg"] = "../images/common/fileicon/mov.png";
	fileicon["wmv"] = "../images/common/fileicon/mov.png";
	fileicon["php"] = "../images/common/fileicon/php.png";
	fileicon["phps"] = "../images/common/fileicon/php.png";
	fileicon["php3"] = "../images/common/fileicon/php.png";
	fileicon["php4"] = "../images/common/fileicon/php.png";
	fileicon["as"] = "../images/common/fileicon/as.png";
	fileicon["swf"] = "../images/common/fileicon/swf.png";
	fileicon["fla"] = "../images/common/fileicon/as.png";
	fileicon["unknown"] = "../images/common/fileicon/unknown.png";

	if (fileicon[fileType]) {
		return fileicon[fileType];
	} else {
		return fileicon["unknown"];
	}
}

// Tip
function Tip(mode,text,event) {
	var event = event ? event : window.event;
	var object = document.getElementById("TipLayer");

	if (mode == true) {
		if (event.clientY + 50 > document.documentElement.clientHeight) {
			var top = event.clientY - 58;
		} else {
			var top = event.clientY + 8;
		}
		var left = event.clientX+8;

		object.style.top = top+"px";
		object.style.left = left+"px";
		object.innerHTML = text;
		object.style.display = "";
	} else {
		object.style.display = "none";
	}
}

function GetNumberFormat(value) {
	if (!value) return "0";
	var str = value.toString();
	str = str.replace(/[^0-9\-\.]+/gi,"");

	if (str.length == 0) str = "0";

	var temp = str.split(".");
	str = temp[0];
	var isMinus = false;
	if (str.substr(0,1) == "-") {
		str = str.replace("-","");
		isMinus = true;
	}
	str = parseInt(str.replace(/[^\d]+/g,""));
	str = str.toString();

	var k = 0;
	var getNumber = "";

	for (i=str.length;i>0;i--) {
		getNumber+= str.substr(i-1,1);
		k++;
		if (k%3 == 0 && i != 1) {
			getNumber+= ",";
		}
	}

	var returnValue = "";
	k = getNumber.length-1;
	for (i=0;i<getNumber.length;i++) {
		returnValue+= getNumber.substr(k,1);
		k--;
	}

	if (isMinus == true) returnValue = "-"+returnValue;
	if (temp.length == 2) returnValue+= "."+temp[1];
	return returnValue;
}

function GetExtReplace(value) {
	if (!value || value == "") return "";
	else value = value.toString();
	return value.replace(/\[:line:\]/gi,"\n").replace(/\[:tab:\]/gi,"\t");
}

function GetFixNumberLength(value,length) {
	var fix = "";
	value = value.toString();
	for (var i=0, loop=length-value.length;i<loop;i++) {
		fix+= "0";
	}
	fix+= value;

	return fix;
}

// Form
function FormSubmitReturnValue(action,field) {
	var field = field !== undefined ? field : "id";
	if (action.response.responseXML.getElementsByTagName(field).length > 0 && action.response.responseXML.getElementsByTagName(field)[0].firstChild) {
		return action.response.responseXML.getElementsByTagName(field)[0].firstChild.nodeValue;
	} else {
		return "";
	}
}

function FormAddressFieldSet(formID,title,style,width,zipcode,address1,address2) {
	if (!title) title = "주소";
	if (!style) style = "margin:10px;";
	if (!width) width = 400;
	if (!zipcode) zipcode = "zipcode";
	if (!address1) address1 = "address1";
	if (!address2) address2 = "address2";

	var AddressStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/exec/Extjs.get.php?action=address"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["zipcode","address","value"]
		})
	});

	AddressStore.on("load",function(store) {
		if (store.getCount() == 0) {
			Ext.Msg.show({title:"에러",msg:"주소를 찾을수 없습니다. 다시 검색하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR,fn:function() { Ext.getCmp(formID+"-AddressSearch").focus(true,100);}});
		} else {
			Ext.getCmp(formID+"-AddressList").enable();
		}
	},AddressStore);

	var AddressFieldSet = new Ext.form.FieldSet({
		defaults:{msgTarget:"side"},
		title:"주소",
		layout:"table",
		layoutConfig:{columns:2},
		style:(style ? style : ""),
		autoWidth:true,
		autoHeight:true,
		items:[
			{
				border:false,
				layout:"form",
				items:[
					new Ext.form.TextField({
						fieldLabel:"우편번호검색",
						id:formID+"-AddressSearch",
						style:"padding-top:2px;",
						width:(width-80),
						emptyText:"읍.면.동을 입력하세요.",
						enableKeyEvents:true,
						listeners:{keydown:{fn:function(form,e) {
							if (e.keyCode == 13) {
								if (!form.getValue()) {
									Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR,fn:function(){form.focus();}});
									return false;
								}
								AddressStore.load({params:{keyword:form.getValue()}});
								e.stopEvent();
							}
						}}}
					})
				]
			},{
				border:false,
				items:[
					new Ext.Button({
						text:"우편번호검색",
						style:"margin-bottom:4px;",
						handler:function(p1,p2,p3) {
							if (!Ext.getCmp(formID+"-AddressSearch").getValue()) {
								Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR,fn:function(){Ext.getCmp(formID+"-AddressSearch").focus();}});
								return false;
							}
							AddressStore.load({params:{keyword:Ext.getCmp(formID+"-AddressSearch").getValue()}});
						}
					})
				]
			},{
				colspan:2,
				border:false,
				layout:"form",
				items:[
					new Ext.form.ComboBox({
						fieldLabel:"기본주소선택",
						id:formID+"-AddressList",
						disabled:true,
						width:width,
						typeAhead:true,
						lazyRender:false,
						listClass:"x-combo-list-small",
						store:AddressStore,
						editable:false,
						mode:"local",
						triggerAction:"all",
						displayField:"address",
						valueField:"value",
						emptyText:"기본주소를 선택하세요.",
						listeners:{
							select:{fn:function(object,store,idx) {
								Ext.getCmp(formID).getForm().findField(zipcode).setValue(store.get("zipcode"));
								Ext.getCmp(formID).getForm().findField(address1).setValue(store.get("value"));
								Ext.getCmp(formID).getForm().findField(address2).focus(false,100);
							}}
						}
					})
				]
			},{
				colspan:2,
				border:false,
				layout:"form",
				items:[
					new Ext.form.TextField({
						fieldLabel:"우편번호",
						name:zipcode,
						width:100,
						allowBlank:true,
						readOnly:true
					})
				]
			},{
				colspan:2,
				border:false,
				layout:"form",
				items:[
					new Ext.form.TextField({
						fieldLabel:"기본주소",
						name:address1,
						width:width,
						allowBlank:true,
						readOnly:true
					})
				]
			},{
				colspan:2,
				border:false,
				layout:"form",
				items:[
					new Ext.form.TextField({
						fieldLabel:"상세주소",
						name:address2,
						width:400,
						allowBlank:true
					})
				]
			}
		]
	})

	return AddressFieldSet;
}

// Ajax
function AjaxResult(XML,field) {
	try {
		return XML.responseXML.getElementsByTagName(field)[0].firstChild.nodeValue;
	} catch (e) { return ""; }
}

// Grid
var GridLoopNum = "font-family:tahoma; background:url(../images/extjs/grid/col-loopnum.gif) repeat-y 0; margin:0px; padding:0px;";
function GridNumberFormat(value,p,record,row,col,store) {
	if (value === undefined) value = 0;
	if (value.length == 0) {
		return;
	}

	if (value.toString().indexOf("\.") >= 0) return '<div style="text-align:right;font-family:arial;">'+Ext.util.Format.number(value,"0,0.00")+'</div>';
	else return '<div style="text-align:right;font-family:arial;">'+Ext.util.Format.number(value,"0,0")+'</div>';
}

function GridDateTimeFormat(value) {
	return '<div style="text-align:right;font-family:arial;">'+value+'</div>';
}

function GridSummaryCount(value) {
	return '<div style="font-family:verdana; font-size:10px;">('+GetNumberFormat(value)+' Item'+(value > 0 ? 's' : '')+')</div>';
}

function GridDateFormat(value,p,record) {
	if (value) {
		value = new Date(value).format("Y-m-d");
		return value;
	}
}

function GridAccount(value,p,record) {
	var data = value.split("||");
	return '['+data[1]+'] '+data[2]+' ('+data[0]+')';
}

function GridExtReplace(value) {
	if (!value || value == "") return "";
	else value = value.toString();
	return value.replace(/\[:line:\]/gi,' ').replace(/\[:tab:\]/gi,' ');
}

function GridInsertRow(grid,value,sort) {
	if (!value) value = [{}];
	if (!sort) sort = false;

	if (value.constructor !== Array) {
		value = [value];
	}

	var fields = new Array();
	for (var i=0, loop=grid.getStore().fields.length;i<loop;i++) {
		fields[i] = {name:grid.getStore().fields.items[i].name, type:grid.getStore().fields.items[i].type.type};
	}

	var record = Ext.data.Record.create(fields);

	var rows = new Array();

	for (var i=0, loop=value.length;i<loop;i++) {
		var row = new record();
		for (var j=0, loopj=row.fields.length;j<loopj;j++) {
			if (value[i][row.fields.items[j].name]) {
				row.set(row.fields.items[j].name,value[i][row.fields.items[j].name]);
			} else {
				if (row.fields.items[j].type.type == "int" || row.fields.items[j].type.type == "float") {
					row.set(row.fields.items[j].name,0);
				} else {
					row.set(row.fields.items[j].name,"");
				}
			}
		}
		rows.push(row);
	}

	grid.getStore().add(rows);

	if (sort == true) {
		grid.getStore().sort(grid.getStore().getSortState().field,grid.getStore().getSortState().direction);
	}
}

function GridDeleteRow(grid,msg) {
	if (!msg) msg = "삭제할 데이터를 선택하여 주십시오.";

	var checked = grid.selModel.getSelections();

	if (checked.length == 0) {
		Ext.Msg.show({title:"삭제오류",msg:msg,buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
	} else {
		for (var i=0, loop=checked.length;i<loop;i++) {
			grid.getStore().remove(checked[i]);
		}
	}
}

function GridContextmenuSelect(grid,idx) {
	grid.selModel.selectRow(idx);
}

// AutoMatch
var autoMatch = {start:false,ready:false};

function AutoMatchStart(form,store,display,value) {
	var top = form.getPosition()[1]+20;
	var left = form.getPosition()[0];
	var width = form.getWidth()-4;

	autoMatch.start = true;
	autoMatch.form = form;
	autoMatch.store = store;
	autoMatch.display = display;
	autoMatch.value = value;
	autoMatch.prevKeyword = null;
	autoMatch.position = -1;

	autoMatch.list = document.getElementById("AutoMatchLayer");
	autoMatch.list.style.top = top+"px";
	autoMatch.list.style.left = left+"px";

	autoMatch.store.on("load",function(store) {
		autoMatch.position = -1;
		autoMatch.prevKeyword = store.baseParams.query;

		if (store.getCount() == 0) {
			autoMatch.list.style.display = "none";
		} else {
			autoMatch.list.innerHTML = '<span style="display:block; width:'+width+'px; height:1px;"></div>';

			for (var i=0, loop=store.getCount();i<loop;i++) {
				var list = document.createElement("div");
				list.innerHTML = store.getAt(i).get(autoMatch.display);
				autoMatch.list.appendChild(list);
			}

			autoMatch.list.style.display = "";
		}

		autoMatch.timeout = setTimeout("AutoMatchTimeout()",200);
	});

	AutoMatchTimeout();
}

function AutoMatchStop() {
	clearTimeout(autoMatch.timeout);
	setTimeout("AutoMatchStopTimeout()",200);
}

function AutoMatchStopTimeout() {
	if (autoMatch.ready == false) {
		autoMatch = {start:false,ready:false};
	} else {
		autoMatch.prevKeyword = null;
	}
	document.getElementById("AutoMatchLayer").style.display = "none";
}

function AutoMatchKeyEvent(form,e) {
	if (autoMatch.store.getCount() == 0) return;

	if (e.keyCode == 40) {
		if (autoMatch.position != -1) autoMatch.list.getElementsByTagName("div")[autoMatch.position].className = "";
		autoMatch.position++;
		if (autoMatch.position >= autoMatch.store.getCount()) autoMatch.position = 0;
		autoMatch.list.getElementsByTagName("div")[autoMatch.position].className = "select";
	} else if (e.keyCode == 38) {
		if (autoMatch.position == -1) autoMatch.position = autoMatch.store.getCount();
		else autoMatch.list.getElementsByTagName("div")[autoMatch.position].className = "";
		autoMatch.position--;
		if (autoMatch.position < 0) autoMatch.position = autoMatch.store.getCount()-1;
		autoMatch.list.getElementsByTagName("div")[autoMatch.position].className = "select";
	}

	if (e.keyCode == 38 || e.keyCode == 40) {
		form.setValue(autoMatch.list.getElementsByTagName("div")[autoMatch.position].innerHTML.replace(/&gt;/g,">").replace(/&lt;/g,"<"));
		autoMatch.prevKeyword = autoMatch.form.getValue();
	}
}

function AutoMatchTimeout() {
	if (autoMatch.form.getValue() && autoMatch.prevKeyword != autoMatch.form.getValue()) {
		autoMatch.store.baseParams.query = autoMatch.form.getValue();
		autoMatch.store.reload();
	} else {
		autoMatch.timeout = setTimeout("AutoMatchTimeout()",200);
	}
}

// Events
function PressNumberOnly(form,e) {
	if (e.ctrlKey == false && e.altKey == false && e.keyCode != 9 && e.keyCode != 16 && e.keyCode != 8 && e.keyCode != 46 && e.keyCode != 37 && e.keyCode != 39 && e.keyCode != 110 && e.keyCode != 190) {
		if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <=105)) {
		} else {
			e.stopEvent();
		}
	}
}

function BlurNumberFormat(form,e) {
	form.setValue(GetNumberFormat(form.getValue()));
}

function FocusNumberOnly(form,e) {
	form.setValue(form.getValue().replace(/[^0-9\.]+/gi,""));
}

function BlurTelephoneFormat(form) {
	var value = form.getValue().replace(/[^0-9]+/gi,"");
	if (value.indexOf("-") < 0 && value.length >= 10) {
		if (value.indexOf("02") == 0) {
			if (value.length == 10) {
				var telephone = value.substr(0,2)+"-"+value.substr(2,4)+"-"+value.substr(6,4);
			} else {
				var telephone = value.substr(0,2)+"-"+value.substr(2,3)+"-"+value.substr(5,4);
			}
		} else {
			if (value.length == 11) {
				var telephone = value.substr(0,3)+"-"+value.substr(3,4)+"-"+value.substr(7,4);
			} else {
				var telephone = value.substr(0,3)+"-"+value.substr(3,3)+"-"+value.substr(6,4);
			}
		}
		form.setValue(telephone);
	}
}

function CheckCompanyNumber(str) {
	var value = str.replace(/[^0-9]+/gi,"");
	if (value.length == 10) {
		return true;
	} else {
		return "사업자등록번호가 올바르지 않습니다.";
	}
}

function BlurCompanyNumberFormat(form) {
	var value = form.getValue().replace(/[^0-9]+/gi,"");

	if (value.length == 10) {
		var companyNumber = value.substr(0,3)+"-"+value.substr(3,2)+"-"+value.substr(5,5);
		form.setValue(companyNumber);
	}
}

function CheckJumin(str) {
	var value = str.replace(/[^0-9]+/gi,"");

	if (value.length != 13) {
		return "주민등록번호가 올바르지 않습니다.";
	}

	var jumin1 = value.substr(0,6);
	var jumin2 = value.substr(6,7);
	var month = jumin1.substr(2,2).indexOf("0") == 0 ? parseInt(jumin1.substr(3,1)) : parseInt(jumin1.substr(2,2));
	var day = jumin1.substr(4,2).indexOf("0") == 0 ? parseInt(jumin1.substr(5,1)) : parseInt(jumin1.substr(4,2));
	var gender = parseInt(jumin2.substr(0,1));

	if (month < 1 || month > 12 || day < 1 || day > 31) {
		return "주민등록번호가 올바르지 않습니다.";
	}

	if (gender < 1 || gender > 8) {
		return "주민등록번호가 올바르지 않습니다.";
	}

	var n = 2;
	var sum = 0;
	for (var i=0;i<12;i++) {
		sum+= parseInt(value.substr(i,1))*n++;
		if (n == 10) n = 2;
	}

	var checksum = 11-sum%11;
	if (checksum == 11) checksum = 1;
	if (checksum == 10) checksum = 0;

	if (checksum != parseInt(value.substr(12,1))) {
		return "주민등록번호가 올바르지 않습니다.";
	} else {
		return true;
	}
}

function BlurJumin(form) {
	var value = form.getValue().replace(/[^0-9]+/gi,"");

	if (form.validate() == true) {
		form.setValue(value.substr(0,6)+"-"+value.substr(6,value.length-6));
	}
}

// AzUploader
/*
Ext.onReady(function(){
	new Ext.Window({
		id:"AzUploadProgress",
		title:"첨부파일등록",
		modal:true,
		width:600,
		resizable:false,
		closable:false,
		items:[
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"업로드 대기중",
				id:"AzFileProgressBar",
				cls:"left-align",
				border:false
			}),
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"업로드 대기중",
				id:"AzTotalProgressBar",
				cls:"left-align",
				border:false
			})
		],
		bbar:new Ext.ux.StatusBar({
			items:[
				new Ext.Toolbar.TextItem({
					id:"AzUploaderSpeed",
					cls:"x-status-text-panel",
					style:"margin-right:2px; font-family:돋움; font-size:11px;",
					text:"업로드속도: 0.00KiB"
				}),
				new Ext.Toolbar.TextItem({
					id:"AzUploaderTime",
					cls:"x-status-text-panel",
					style:"margin-right:2px; font-family:돋움; font-size:11px;",
					text:"경과시간: 00:00 / 예상남은시간: 00:00"
				})
			]
		})
	});
});
*/
function AzUploaderOnSelect(uploader,fileList) {
	if (fileList.length > 0) {
		uploader.errorFiles = new Array();
		Ext.getCmp("AzUploadProgress").show();
		uploader.upload();
	} else {
		uploader.onComplete();
	}
}

function AzUploaderOnProgress(uploader,file,fileUpload,totalUpload,time,speed) {
	Ext.getCmp("AzFileProgressBar").updateProgress(fileUpload.upload/fileUpload.total,file.name+" 업로드 중... ("+(100*fileUpload.upload/fileUpload.total).toFixed(2)+"%)");
	Ext.getCmp("AzTotalProgressBar").updateProgress(totalUpload.upload/totalUpload.total, "전체 "+totalUpload.count+"개의 파일중 "+fileUpload.count+"번째 파일 업로드 중... ("+(100*totalUpload.upload/totalUpload.total).toFixed(2)+"%)");
	Ext.getCmp("AzUploaderSpeed").setText("업로드속도: "+GetFileSize(speed.total*1024));

	var totalTime = "경과시간: ";
	var s = time.total%60 < 10 ? "0"+Math.floor(time.total%60) : Math.floor(time.total%60);
	var m = Math.floor(time.total/60);
	if (m > 60) {
		var h = Math.floor(m/60);
		var m = m%60 < 10 ? "0"+Math.floor(m%60) : Math.floor(m%60);
		totalTime+= h+":"
	}
	totalTime+= m+":"+s;

	var remainTime = "예상남은시간: ";
	var s = time.remain%60 < 10 ? "0"+Math.floor(time.remain%60) : Math.floor(time.remain%60);
	var m = Math.floor(time.remain/60);
	if (m > 60) {
		var h = Math.floor(m/60);
		var m = m%60 < 10 ? "0"+Math.floor(m%60) : Math.floor(m%60);
		remainTime+= h+":"
	}
	remainTime+= m+":"+s;

	Ext.getCmp("AzUploaderTime").setText(totalTime+" / "+remainTime);
}

function AzUploaderOnComplete(uploader) {
	if (uploader.errorFiles.length > 0) {
		var msg = "아래의 파일이 용량초과로 업로드 되지 않았습니다.";
		for (var i=0, loop=uploader.errorFiles.length;i<loop;i++) msg+= "<br />"+uploader.errorFiles[i];
		Ext.Msg.show({title:"에러",msg:msg,buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
	}
	Ext.getCmp("AzUploadProgress").hide();
}

function AzUploaderBeforeLoad(uploader) {
	if (!uploader.loadURL) return false;
	document.getElementById(uploader.id+"-image").innerHTML = "";
	document.getElementById(uploader.id+"-file").innerHTML = "";
}

function AzUploaderOnLoad(uploader,file) {
	if (file.server == "FALSE") {
		uploader.errorFiles.push(file.name);
		return;
	}

	var data = file.server.split("|");
	var sHTML = '<input type="hidden" name="uploaderfile[]" value="'+file.server+'" />';
	if (data[1] == "IMG") {
		var object = document.getElementById(uploader.id+"-image");

		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="AzUploaderPreviewImage">';
		sHTML+= '<div class="image"><img src="'+data[4]+'" alt="'+data[2]+'" style="width:71px;" title="클릭하시면, 본문에 삽입됩니다." onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\',\''+file.server+'\');" /></div>';
		sHTML+= '<div class="text">';
		sHTML+= '<div>'+GetFileSize(data[3])+'</div>';
		sHTML+= '<img src="'+ENV.dir+'/images/common/btn_file_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\',\''+file.server+'\');" class="pointer" />';
		sHTML+= '</div>';
		sHTML+= '</div>';
	} else {
		var object = document.getElementById(uploader.id+"-file");
		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="AzUploaderPreviewFile"><span class="text" title="클릭하시면, 본문에 삽입합니다." onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\',\''+file.server+'\');">'+data[2]+'</span> <span class="size">('+GetFileSize(data[3])+')</span> <span class="button"><img src="'+ENV.dir+'/images/common/btn_file_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\',\''+file.server+'\');" class="pointer" /></span></div>';
	}

	object.innerHTML+= sHTML;
}

function AzUploaderOnUpload(uploader,file) {
	if (file.server == "FALSE") {
		uploader.errorFiles.push(file.name);
		return;
	}

	var data = file.server.split("|");
	var sHTML = '<input type="hidden" name="uploaderfile[]" value="'+file.server+'" />';
	if (data[1] == "IMG") {
		var object = document.getElementById(uploader.id+"-image");

		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="AzUploaderPreviewImage">';
		sHTML+= '<div class="image"><img src="'+data[4]+'" alt="'+data[2]+'" style="width:71px;" title="클릭하시면, 본문에 삽입됩니다." onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\',\''+file.server+'\');" /></div>';
		sHTML+= '<div class="text">';
		sHTML+= '<div>'+GetFileSize(data[3])+'</div>';
		sHTML+= '<img src="'+ENV.dir+'/images/common/btn_file_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\',\''+file.server+'\');" class="pointer" />';
		sHTML+= '</div>';
		sHTML+= '</div>';
	} else {
		var object = document.getElementById(uploader.id+"-file");
		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="AzUploaderPreviewFile"><span class="text" title="클릭하시면, 본문에 삽입합니다." onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\',\''+file.server+'\');">'+data[2]+'</span> <span class="size">('+GetFileSize(data[3])+')</span> <span class="button"><img src="'+ENV.dir+'/images/common/btn_file_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\',\''+file.server+'\');" class="pointer" /></span></div>';
	}

	object.innerHTML+= sHTML;
}

function AzUploaderOnError(uploader,type,file) {
	uploader.errorFiles.push(file.name);
}

function AzUploaderInsertWysiwyg(id,file) {
	var uploader = AzUploaderComponent.get(id);
	var data = file.split("|");

	var sHTML = "";
	if (data[1] == "IMG") {
		sHTML+= '<img name="InsertFile" file="'+data[0]+'" src="'+uploader.moduleDir+'/exec/ShowImage.do.php?idx='+data[0]+'" />';
	} else {
		sHTML+= '<a name="InsertFile" file="'+data[0]+'" href="'+uploader.moduleDir+'/exec/FileDownload.do.php?idx='+data[0]+'" />'+data[2]+' ('+GetFileSize(data[3])+')</a>';
	}

	try {
		oEditors.getById[uploader.wysiwygElement].exec("PASTE_HTML",[sHTML]);
	} catch(e) {
		alert("에디터에 커서를 입력한 뒤에 삽입하여 주십시오.");
	}
}

function AzUploaderDeleteFile(id,file) {
	if (confirm("해당 파일을 정말 삭제하시겠습니까?\n본문에 삽입되어있는 파일을 삭제할 경우, 본문에서도 삭제됩니다.") == true) {
		var uploader = AzUploaderComponent.get(id);
		var data = file.split("|");
		var fileInput = uploader.formElement.getElementsByTagName("input");
		for (var i=0, loop=fileInput.length;i<loop;i++) {
			if (fileInput[i].getAttribute("name") == "uploaderfile[]" && fileInput[i].value == file) {
				fileInput[i].value = data[0];
				break;
			}
		}
		var content = oEditors.getById[uploader.wysiwygElement].getWYSIWYGDocument().body;
		if (data[1] == "IMG") {
			var insertFiles = content.getElementsByTagName("img");
		} else {
			var insertFiles = content.getElementsByTagName("a");
		}
		for (var i=insertFiles.length-1;i>=0;i--) {
			if (insertFiles[i].getAttribute("name") == "InsertFile" && insertFiles[i].getAttribute("file") == data[0]) {
				content.appendChild(insertFiles[i]);
				content.removeChild(insertFiles[i]);
			}
		}

		document.getElementById("AzUploadedFile-"+data[0]).style.display = "none";
	}
}