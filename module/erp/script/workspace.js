var AddressStore = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/exec/Extjs.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:["zipcode","address","value"]
	}),
	baseParams:{"action":"address"}
});

AddressStore.on("load",function(store) {
	if (store.getCount() == 0) {
		Ext.Msg.show({title:"에러",msg:"주소를 찾을수 없습니다. 다시 검색하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").setValue(""); Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").focus();}});
	} else {
		Ext.getCmp("WorkspaceForm").getForm().findField("select_address").enable();
	}
},AddressStore);

/************************************************************************************************
 * 현장목록 STORE
 ***********************************************************************************************/
var WorkspaceListstore1 = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","cost"]
	}),
	remoteSort:true,
	sortInfo:{field:"title",direction:"ASC"},
	baseParams:{"action":"workspace","get":"list","category":"working"}
});

var WorkspaceListstore2 = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","cost"]
	}),
	remoteSort:true,
	sortInfo:{field:"title",direction:"ASC"},
	baseParams:{"action":"workspace","get":"list","category":"estimate"}
});

var WorkspaceListstore3 = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","cost"]
	}),
	remoteSort:true,
	sortInfo:{field:"title",direction:"ASC"},
	baseParams:{"action":"workspace","get":"list","category":"end"}
});

var WorkspaceListstore4 = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","cost"]
	}),
	remoteSort:true,
	sortInfo:{field:"title",direction:"ASC"},
	baseParams:{"action":"workspace","get":"list","category":"backup"}
});

/************************************************************************************************
 * 현장목록 COLUMN
 ***********************************************************************************************/
var WorkspaceListCm = new Ext.grid.ColumnModel([
	new Ext.grid.RowNumberer(),
	{
		dataIndex:"idx",
		hidden:true,
		hidable:false
	},{
		header:"현장명",
		dataIndex:"title",
		sortable:true,
		width:250
	},{
		header:"발주처",
		dataIndex:"orderer",
		sortable:true,
		width:100
	},{
		header:"공사기간",
		dataIndex:"workstart_date",
		sortable:true,
		width:150,
		renderer:function(value,p,record) {
			var data = "";
			if (record.data.workstart_date != "1970-01-01") {
				data+= record.data.workstart_date;
			}
			if (record.data.workend_date != "1970-01-01") {
				data+= " ~ "+record.data.workend_date;
			}

			return data;
		}
	},{
		header:"소장명",
		dataIndex:"master",
		sortable:false,
		width:100
	},{
		header:"근로자수",
		dataIndex:"worker",
		sortable:false,
		width:80,
		renderer:GridNumberFormat
	},{
		header:"현장연락처",
		dataIndex:"telephone",
		sortable:false,
		width:100
	},{
		header:"공정율",
		dataIndex:"workpercent",
		sortable:false,
		width:90,
		renderer:function(value) {
			var data = '<div style="font-family:tahoma; font-size:10px;">';
			data+= '<span style="font-weight:bold; letter-spacing:-3px;">';
			for (var i=10;i<=100;i=i+10) {
				if (i < value) data+= '<span style="color:#EF5600;">|</span>';
				else data+= '<span style="color:#CCCCCC;">|</span>';
			}
			data+= '</span>';

			data+= " "+value+"%";

			return data;
		}
	},{
		header:"현장상황",
		dataIndex:"type",
		sortable:true,
		width:80,
		renderer:function(value) {
			if (value == "WORKING") return "공사중";
			else if (value == "ESTIMATE") return "견적중";
			else return "준공완료";
		}
	}
]);

/************************************************************************************************
 * 현장관련 함수정의부
 ***********************************************************************************************/

// 현장등록 및 수정
function WorkspaceFormFunction(mode,idx) {
	if (mode == "add") {
		var winid = "WorkspaceAddWindow";
	} else {
		var winid = "WorkspaceModifyWindow";
	}

	new Ext.Window({
		id:"WorkspaceAddWindow",
		title:(mode == "add" ? "신규현장등록" : "현장정보수정"),
		width:600,
		height:500,
		minWidth:600,
		minHeight:400,
		modal:true,
		maximizable:true,
		layout:"fit",
		style:"background:#FFFFFF;",
		items:[
			new Ext.form.FormPanel({
				id:"WorkspaceForm",
				labelAlign:"right",
				labelWidth:85,
				border:false,
				autoWidth:true,
				autoScroll:true,
				errorReader:new Ext.form.XmlErrorReader(),
				reader:new Ext.data.XmlReader(
					{record:"form",success:"@success",errormsg:"@errormsg"},
					["title","orderer","contract_date","workstart_date","workend_date","area","totalarea","size","structure","buildarea","buildingcoverage","buildpercent","purpose","zone","zipcode","address1","address2","telephone","master","master_view","architects"]
				),
				items:[
					new Ext.form.FieldSet({
						defaults:{msgTarget:"side"},
						title:"기본정보",
						autoWidth:true,
						autoHeight:true,
						style:"margin:10px",
						items:[
							new Ext.form.TextField({
								fieldLabel:"현장명",
								name:"title",
								width:190,
								allowBlank:false
							}),
							new Ext.form.TextField({
								fieldLabel:"발주처",
								name:"orderer",
								width:190,
								allowBlank:false
							}),
							new Ext.form.DateField({
								fieldLabel:"계약일자",
								format:"Y-m-d",
								name:"contract_date",
								width:100
							}),
							new Ext.form.DateField({
								fieldLabel:"공사시작일",
								format:"Y-m-d",
								name:"workstart_date",
								width:100
							}),
							new Ext.form.DateField({
								fieldLabel:"공사종료일",
								format:"Y-m-d",
								name:"workend_date",
								width:100
							})
						]
					}),
					new Ext.form.FieldSet({
						defaults:{msgTarget:"side"},
						title:"현장정보",
						autoWidth:true,
						autoHeight:true,
						style:"margin:10px",
						items:[
							new Ext.form.TextField({
								fieldLabel:"대지면적(㎡)",
								name:"area",
								width:100,
								style:"text-align:right;",
								emptyText:"㎡",
								allowBlank:true,
								enableKeyEvents:true,
								listeners:{
									keydown:{fn:PressNumberOnly},
									blur:{fn:BlurNumberFormat},
									focus:{fn:FocusNumberOnly}
								}
							}),
							new Ext.form.TextField({
								fieldLabel:"건물규모",
								name:"size",
								width:400,
								allowBlank:true
							}),
							new Ext.form.TextField({
								fieldLabel:"건축구조",
								name:"structure",
								width:400,
								allowBlank:true
							}),
							new Ext.form.NumberField({
								fieldLabel:"건축면적(㎡)",
								name:"buildarea",
								width:100,
								style:"text-align:right;",
								emptyText:"㎡",
								allowBlank:true,
								enableKeyEvents:true,
								listeners:{
									keydown:{fn:PressNumberOnly},
									blur:{fn:BlurNumberFormat},
									focus:{fn:FocusNumberOnly}
								}
							}),
							new Ext.form.NumberField({
								fieldLabel:"연면적(㎡)",
								name:"totalarea",
								width:100,
								style:"text-align:right;",
								emptyText:"㎡",
								allowBlank:true,
								enableKeyEvents:true,
								listeners:{
									keydown:{fn:PressNumberOnly},
									blur:{fn:BlurNumberFormat},
									focus:{fn:FocusNumberOnly}
								}
							}),
							new Ext.form.NumberField({
								fieldLabel:"건폐율(%)",
								name:"buildingcoverage",
								width:100,
								style:"text-align:right;",
								emptyText:"%",
								allowBlank:true,
								enableKeyEvents:true,
								listeners:{
									keydown:{fn:PressNumberOnly},
									blur:{fn:BlurNumberFormat},
									focus:{fn:FocusNumberOnly}
								}
							}),
							new Ext.form.NumberField({
								fieldLabel:"용적률(%)",
								name:"buildpercent",
								width:100,
								style:"text-align:right;",
								emptyText:"%",
								allowBlank:true,
								enableKeyEvents:true,
								listeners:{
									keydown:{fn:PressNumberOnly},
									blur:{fn:BlurNumberFormat},
									focus:{fn:FocusNumberOnly}
								}
							}),
							new Ext.form.TextField({
								fieldLabel:"건물용도",
								name:"purpose",
								width:400,
								allowBlank:true
							}),
							new Ext.form.TextField({
								fieldLabel:"설계사무소",
								name:"architects",
								width:400,
								allowBlank:true
							})
						]
					}),
					new Ext.form.FieldSet({
						defaults:{msgTarget:"side"},
						title:"현장주소",
						layout:"table",
						layoutConfig:{columns:2},
						autoWidth:true,
						autoHeight:true,
						style:"margin:10px;",
						items:[
							{
								colspan:2,
								border:false,
								layout:"form",
								items:[
									new Ext.form.TextField({
										fieldLabel:"지역/지구",
										name:"zone",
										width:400,
										allowBlank:true
									})
								]
							},{
								border:false,
								layout:"form",
								items:[
									new Ext.form.TextField({
										fieldLabel:"우편번호검색",
										name:"search_zipcode",
										style:"padding-top:2px;",
										width:320,
										emptyText:"읍.면.동을 입력하세요.",
										enableKeyEvents:true,
										listeners:{keydown:{fn:function(form,e) {
											if (e.keyCode == 13) {
												if (!Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").getValue()) {
													Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").focus();}});
													return false;
												}
												AddressStore.load({params:{keyword:Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").getValue()}});
												e.stopEvent();
											}
										}}}
									})
								]
							},{
								style:"padding-left:5px; padding-bottom:5px;",
								border:false,
								items:[
									new Ext.Button({
										text:"우편번호검색",
										handler:function(p1,p2,p3) {
											if (!Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").getValue()) {
												Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").focus();}});
												return false;
											}
											AddressStore.load({params:{keyword:Ext.getCmp("WorkspaceForm").getForm().findField("search_zipcode").getValue()}});
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
										name:"select_address",
										disabled:true,
										width:400,
										typeAhead:true,
										lazyRender:false,
										store:AddressStore,
										editable:false,
										mode:"local",
										displayField:"address",
										valueField:"value",
										emptyText:"기본주소를 선택하세요.",
										listeners:{
											select:{fn:function(object,store,idx) {
												Ext.getCmp("WorkspaceForm").getForm().findField("zipcode").setValue(store.get("zipcode"));
												Ext.getCmp("WorkspaceForm").getForm().findField("address1").setValue(store.get("value"));
												Ext.getCmp("WorkspaceForm").getForm().findField("address2").focus(false,100);
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
										name:"zipcode",
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
										name:"address1",
										width:400,
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
										name:"address2",
										width:400,
										allowBlank:true
									})
								]
							}
						]
					}),
					new Ext.form.FieldSet({
						defaults:{msgTarget:"side"},
						title:"관리정보",
						autoHeight:true,
						autoWidth:true,
						layout:"table",
						layoutConfig:{columns:2},
						style:"margin:10px;",
						items:[
							{
								colspan:2,
								border:false,
								layout:"form",
								items:[
									new Ext.form.TextField({
										fieldLabel:"현장연락처",
										name:"telephone",
										style:"padding-top:2px;",
										width:200,
										emptyText:"'-' 는 제외하고 입력하세요.",
										listeners:{
											blur:{fn:BlurTelephoneFormat},
											focus:{fn:FocusNumberOnly}
										}
									})
								]
							},{
								border:false,
								width:290,
								layout:"form",
								items:[
									new Ext.form.Hidden({
										name:"master",
										allowBlank:true
									}),
									new Ext.form.TextField({
										fieldLabel:"현장소장",
										name:"master_view",
										width:200,
										readOnly:true,
										disabled:true,
										allowBlank:true
									})
								]
							},{
								border:false,
								width:95,
								style:"padding-left:5px; padding-bottom:4px;",
								items:[
									new Ext.Button({
										text:"현장소장검색",
										handler:function() {
											var WorkSpaceMasterSearchStore = new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/exec/Extjs.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","name","nickname","user_id"]
												}),
												remoteSort:true,
												sortInfo:{field:"name",direction:"ASC"},
												baseParams:{action:"member",keyword:""}
											});

											new Ext.Window({
												id:"WorkspaceMasterWindow",
												title:"현장소장검색",
												width:500,
												height:400,
												modal:true,
												resizable:false,
												layout:"border",
												items:[
													new Ext.grid.GridPanel({
														id:"WorkspaceMasterMember",
														region:"west",
														border:false,
														title:"회원검색",
														width:320,
														cm:new Ext.grid.ColumnModel([
															new Ext.grid.CheckboxSelectionModel(),
															{
																dataIndex:"idx",
																hidden:true,
																hideable:false
															},{
																header:"이름",
																dataIndex:"name",
																sortable:true,
																width:70
															},{
																header:"닉네임",
																dataIndex:"nickname",
																sortable:true,
																width:105
															},{
																header:"아이디",
																dataIndex:"user_id",
																sortable:true,
																width:70
															}
														]),
														sm:new Ext.grid.CheckboxSelectionModel(),
														store:WorkSpaceMasterSearchStore,
														tbar:[
															new Ext.form.TextField({
																id:"WorkSpaceMasterSearchText",
																width:150,
																emptyText:"검색어를 입력하세요.",
																enableKeyEvents:true,
																listeners:{keydown:{fn:function(form,e) {
																	if (e.keyCode == 13) {
																		if (!Ext.getCmp("WorkSpaceMasterSearchText").getValue()) {
																			Ext.Msg.show({title:"에러",msg:"검색어(이름,닉네임,아이디)를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkSpaceMasterSearchText").getValue().focus();}});
																			return false;
																		}
																		WorkSpaceMasterSearchStore.baseParams.keyword = Ext.getCmp("WorkSpaceMasterSearchText").getValue();
																		WorkSpaceMasterSearchStore.load({params:{start:"0",limit:"30"}});
																		e.stopEvent();
																	}
																}}}
															}),
															' ',
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
																text:"검색",
																handler:function() {
																	if (!Ext.getCmp("WorkSpaceMasterSearchText").getValue()) {
																		Ext.Msg.show({title:"에러",msg:"검색어(이름,닉네임,아이디)를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkSpaceMasterSearchText").getValue().focus();}});
																		return false;
																	}
																	WorkSpaceMasterSearchStore.baseParams.keyword = Ext.getCmp("WorkSpaceMasterSearchText").getValue();
																	WorkSpaceMasterSearchStore.load({params:{start:"0",limit:"30"}});
																}
															}),
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_magifier_out.png",
																text:"취소",
																handler:function() {
																	Ext.getCmp("WorkSpaceMasterSearchText").setValue("");
																	WorkSpaceMasterSearchStore.baseParams.keyword = "";
																	WorkSpaceMasterSearchStore.load({params:{start:"0",limit:"30"}});
																}
															}),
															'->',
															'-',
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_arrow_right.png",
																iconAlign:"right",
																text:"추가",
																handler:function() {
																	var checked = Ext.getCmp("WorkspaceMasterMember").selModel.getSelections();
																	if (checked.length == 0) {
																		Ext.Msg.show({title:"에러",msg:"추가할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																		return false;
																	}

																	var record = Ext.data.Record.create([{name:"idx",type:"int"},{name:"name",type:"string"}]);
																	for (var i=0, loop=checked.length;i<loop;i++) {
																		if (Ext.getCmp("WorkspaceMasterList").getStore().find("idx",checked[i].get("idx"),0,false,false) == -1) {
																			Ext.getCmp("WorkspaceMasterList").getStore().add(new record({idx:checked[i].get("idx"),name:checked[i].get("name")}));
																		}
																	}
																	Ext.getCmp("WorkspaceMasterList").getStore().sort("name","ASC");
																}
															})
														],
														bbar:new Ext.PagingToolbar({
															pageSize:30,
															store:WorkSpaceMasterSearchStore,
															displayInfo:true,
															displayMsg:'{0} - {1} of {2}',
															emptyMsg:"데이터없음"
														})
													}),
													new Ext.grid.GridPanel({
														id:"WorkspaceMasterList",
														margins:"-1 -1 -1 0",
														region:"center",
														title:"등록된 현장소장목록",
														autoScroll:true,
														tbar:[
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_arrow_left.png",
																text:"삭제",
																handler:function() {
																	var checked = Ext.getCmp("WorkspaceMasterList").selModel.getSelections();
																	if (checked.length == 0) {
																		Ext.Msg.show({title:"에러",msg:"삭제할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																		return false;
																	}

																	for (var i=0, loop=checked.length;i<loop;i++) {
																		Ext.getCmp("WorkspaceMasterList").getStore().remove(checked[i]);
																	}
																}
															})
														],
														cm:new Ext.grid.ColumnModel([
															new Ext.grid.CheckboxSelectionModel(),
															{
																dataIndex:"idx",
																hidden:true,
																hideable:false
															},{
																header:"이름",
																dataIndex:"name",
																sortable:true,
																width:110
															}
														]),
														sm:new Ext.grid.CheckboxSelectionModel(),
														store:new Ext.data.SimpleStore({
															fields:["idx","name"],
															data:[],
															sortInfo:{field:"name",direction:"ASC"}
														}),
														bbar:[
															'->',
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
																text:"확인",
																handler:function() {
																	var data = Ext.getCmp("WorkspaceMasterList").getStore();

																	if (data.getCount() == 0) {
																		Ext.Msg.show({title:"에러",msg:"등록된 현장소장이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																		return false;
																	}

																	var workmasterValue = new Array();
																	var workmasterName = new Array();
																	for (var i=0, loop=data.getCount();i<loop;i++) {
																		workmasterValue.push(data.getAt(i).get("idx")+":"+data.getAt(i).get("name"));
																		workmasterName.push(data.getAt(i).get("name"));
																	}
																	workmasterValue = workmasterValue.join("@");
																	workmasterName.join(",");

																	Ext.getCmp("WorkspaceForm").getForm().findField("master").setValue(workmasterValue);
																	Ext.getCmp("WorkspaceForm").getForm().findField("master_view").setValue(workmasterName);

																	Ext.getCmp("WorkspaceMasterWindow").close();
																}
															}),
															new Ext.Button({
																icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
																text:"취소",
																handler:function() {
																	Ext.getCmp("WorkspaceMasterWindow").close();
																}
															})
														]
													})
												],
												listeners:{show:{fn:function() {
													WorkSpaceMasterSearchStore.load({params:{start:"0",limit:"30"}});
													if (Ext.getCmp("WorkspaceForm").getForm().findField("master").getValue()) {
														var data = Ext.getCmp("WorkspaceForm").getForm().findField("master").getValue().split("@");
														var record = Ext.data.Record.create([{name:"idx",type:"int"},{name:"name",type:"string"}]);
														for (var i=0, loop=data.length;i<loop;i++) {
															var temp = data[i].split(":");
															Ext.getCmp("WorkspaceMasterList").getStore().add(new record({idx:temp[0],name:temp[1]}));
														}
														Ext.getCmp("WorkspaceMasterList").getStore().sort("name","ASC");
													}
												}}}
											}).show();
										}
									})
								]
							}
						]
					})
				],
				listeners:{
					actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function() {
								if (Ext.getCmp("ListTab1")) Ext.getCmp("ListTab1").getStore().reload();
								if (Ext.getCmp("ListTab2")) Ext.getCmp("ListTab2").getStore().reload();
								if (Ext.getCmp("ListTab3")) Ext.getCmp("ListTab3").getStore().reload();
								if (Ext.getCmp("ListTab4")) Ext.getCmp("ListTab4").getStore().reload();
								Ext.getCmp("WorkspaceAddWindow").close();
							}});
						}
					}},
					actionfailed:{fn:function(form,action) {
						if (action.type == "load") {
							Ext.Msg.show({title:"에러",msg:action.response.responseXML.getElementsByTagName("errormsg")[0].firstChild.nodeValue,buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){ Ext.getCmp("WorkspaceAddWindow").close(); }});
						}
					}}
				}
			})
		],
		buttons:[
			new Ext.Button({
				icon:ENV.dir+"/module/erp/images/common/icon_tick.png",
				text:"확인",
				handler:function() {
					if (mode == "add") {
						Ext.getCmp("WorkspaceForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=workspace&do=add",waitMsg:"현장을 추가중입니다."});
					}

					if (mode == "modify") {
						Ext.getCmp("WorkspaceForm").getForm().submit({url:ENV.dir+"/module/erp/exec/Admin.do.php?action=workspace&do=modify&idx="+idx,waitMsg:"현장정보를 수정중입니다."});
					}
				}
			}),
			new Ext.Button({
				icon:ENV.dir+"/module/erp/images/common/icon_cross.png",
				text:"취소",
				handler:function() {
					Ext.getCmp("WorkspaceAddWindow").close();
				}
			})
		],
		listeners:{show:{fn:function() {
			if (mode == "modify") {
				Ext.getCmp("WorkspaceForm").load({url:ENV.dir+"/module/erp/exec/Admin.get.php?action=workspace&get=data&idx="+idx,waitMsg:"정보를 로딩중입니다."});
			}
		}}}
	}).show();
}

// 현장메뉴
function WorkspaceMenuFunction(grid,idx,e) {
	GridContextmenuSelect(grid,idx);
	var data = grid.getStore().getAt(idx);

	var menu = new Ext.menu.Menu();
	menu.add('<b class="menu-title">'+data.get("title")+'</b>');
	menu.add({
		text:"현장정보수정",
		icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_building_edit.png"),
		handler:function(item) {
			WorkspaceFormFunction("modify",data.get("idx"));
		}
	});
	if (Ext.getCmp("AzFileProgressBar")) {
		menu.add({
			text:"현장사진등록/관리",
			icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_picture.png"),
			handler:function(item) {
				WorkspacePhotoFunction(data.get("idx"));
			}
		});
	}
	menu.add({
		text:"견적내역서",
		icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_page_white_paste.png"),
		handler:function(item) {
			if (data.get("estimate") == "0") {
				Ext.Msg.show({title:"에러",msg:"등록된 견적내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
			} else {
				CostView("ESTIMATE",data.get("idx"),data.get("estimate"),"견적내역서");
			}
		}
	});
	menu.add({
		text:"실행내역서",
		icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_asterisk_orange.png"),
		handler:function(item) {
			if (data.get("cost") == "0") {
				Ext.Msg.show({title:"에러",msg:"등록된 실행내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
			} else {
				CostView("COST",data.get("idx"),data.get("cost"),"실행내역서");
			}
		}
	});
	menu.add({
		text:"계약내역서",
		icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_coins.png"),
		handler:function(item) {
			if (data.get("contract") == "0") {
				Ext.Msg.show({title:"에러",msg:"등록된 계약내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
			} else {
				CostView("CONTRACT",data.get("idx"),data.get("contract"),"계약내역서");
			}
		}
	});
	if (data.get("type") == "WORKING") {
		menu.add({
			text:"현장관리프로그램 실행",
			icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_building_go.png"),
			handler:function(item) {
				window.open(ENV.dir+'/module/erp/workspace.php?wno='+data.get("idx")+'&mode=manager');
			}
		});
		menu.add('-');
		menu.add({
			text:"공사완료현장으로 변경",
			icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_stop.png"),
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"공사중인 현장의 상태를 변경하면, 더이상 현장관리프로그램을 사용할 수 없습니다.<br />현장의 상태를 변경하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Ajax.request({
							url:ENV.dir+"/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								Ext.getCmp("ListTab3").getStore().reload();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"workspace","do":"status","type":"END","idx":data.get("idx")}
						});
					}
				}});
			}
		});
		menu.add({
			text:"견적현장으로 변경",
			icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_paste_plain.png"),
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"공사중인 현장의 상태를 변경하면, 더이상 현장관리프로그램을 사용할 수 없습니다.<br />현장의 상태를 변경하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Ajax.request({
							url:ENV.dir+"/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"workspace","do":"status","type":"ESTIMATE","idx":data.get("idx")}
						});
					}
				}});
			}
		});
	}
	if (data.get("type") != "BACKUP") {
		menu.add('-');
		menu.add({
			text:"현장백업",
			icon:(Ext.isIE6 ? "" : ENV.dir+"/module/erp/images/common/icon_server_database.png"),
			handler:function(item) {
				Ext.Ajax.request({
					url:ENV.dir+"/module/erp/exec/Admin.do.php",
					success:function() {
						Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						if (Ext.getCmp("ListTab4")) Ext.getCmp("ListTab4").getStore().reload();
					},
					failure:function() {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
					},
					headers:{},
					params:{"action":"workspace","do":"backup","idx":data.get("idx")}
				});
			}
		});
	}
	e.stopEvent();
	menu.showAt(e.getXY());
}