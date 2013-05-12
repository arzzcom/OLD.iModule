<script type="text/javascript">
function TakePhotoNew(result) {
	var object = document.getElementById("TakePhoto");
	if (result == false) {
		Ext.Msg.show({title:"에러",msg:"사진을 저장하지 못하였습니다.<br />다시 한번 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
			object.cancel();
		}});
	} else {
		if (result == "FAIL") {
			Ext.Msg.show({title:"에러",msg:"사진을 저장하지 못하였습니다.<br />다시 한번 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
				object.cancel();
			}});
		} else {
			result = result.split("|");
			var image = document.images["PreviewPhoto"];
			image.src = result[1]+"?rnd="+Math.random();

			Ext.getCmp("DayworkerForm").getForm().findField("photo").setValue(result[0]);
			Ext.getCmp("TakePhotoWindow").close();
		}
	}
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	function DayworkerMenuFunction(grid,idx,e) {
		GridContextmenuSelect(grid,idx);
		var data = grid.getStore().getAt(idx);

		var menu = new Ext.menu.Menu();
		menu.add('<b class="menu-title">'+data.get("name")+'</b>');
		menu.add({
			text:"근로자정보수정",
			icon:"<?php echo $this->moduleDir; ?>/images/common/icon_user_edit.png",
			handler:function(item) {
				DayDayworkerFormFunction(data.get("idx"));
			}
		});
		menu.add('-');
		menu.add({
			text:"근로자삭제",
			icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Msg.wait("처리중입니다.","Please Wait...");
						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								grid.getStore().reload();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"worker","do":"dayworker","mode":"delete","idx":data.get("idx"),"wno":"<?php echo $this->wno; ?>"}
						});
					}
				}});
			}
		});
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function DayDayworkerFormFunction(idx) {
		new Ext.Window({
			id:"DayworkerWindow",
			title:(idx ? "근로자정보수정" : "신규근로자등록"),
			width:600,
			height:400,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			style:"background:#FFFFFF;",
			items:[
				new Ext.form.FormPanel({
					id:"DayworkerForm",
					labelAlign:"right",
					labelWidth:110,
					border:false,
					autoWidth:true,
					autoScroll:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["name","jumin","type","workstart_date","workend_date","job","payment","account_name","account_bank","account_number","telephone","cellphone","gno","tno","contract"]
					),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"종류",
									hiddenName:"type",
									store:new Ext.data.SimpleStore({
										fields:["type"],
										data:[["개인"],["업체"]]
									}),
									displayField:"type",
									valueField:"type",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									width:100,
									editable:false,
									value:"개인",
									listeners:{select:{fn:function(form) {
										if (form.getValue() == "개인") {
											Ext.getCmp("DayworkerForm").getForm().findField("job").enable();
											Ext.getCmp("DayworkerForm").getForm().findField("gno").enable();
											Ext.getCmp("DayworkerForm").getForm().findField("contract").enable();
										} else {
											Ext.getCmp("DayworkerForm").getForm().findField("job").disable();
											Ext.getCmp("DayworkerForm").getForm().findField("gno").disable();
											Ext.getCmp("DayworkerForm").getForm().findField("tno").disable();
											Ext.getCmp("DayworkerForm").getForm().findField("contract").disable();
										}
									}}}
								}),
								new Ext.form.TextField({
									msgTarget:"side",
									fieldLabel:"이름/회사명",
									name:"name",
									width:100,
									allowBlank:false,
									listeners:{blur:{fn:function(form){
										Ext.getCmp("DayworkerForm").getForm().findField("account_name").setValue(form.getValue());
									}}}
								}),
								new Ext.form.TextField({
									fieldLabel:"주민(사업자)번호",
									name:"jumin",
									width:150,
									allowBlank:false,
									validator:function(str) {
										if (Ext.getCmp("DayworkerForm").getForm().findField("type").getValue() == "개인") {
											return CheckJumin(str);
										} else {
											return CheckCompanyNumber(str);
										}
									},
									listeners:{blur:{fn:function(form) {
										if (Ext.getCmp("DayworkerForm").getForm().findField("type").getValue() == "개인") {
											BlurJumin(form);
										} else {
											BlurCompanyNumberFormat(form);
										}
									}}}
								}),
								new Ext.form.DateField({
									fieldLabel:"근무시작일",
									format:"Y-m-d",
									name:"workstart_date",
									width:100,
									value:new Date().format("Y-m-d")
								}),
								new Ext.form.DateField({
									msgTarget:"side",
									fieldLabel:"근무종료일",
									format:"Y-m-d",
									name:"workend_date",
									width:100
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"급여/계좌정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"직공",
									hiddenName:"job",
									width:100,
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									listClass:'x-combo-list-small',
									store:JobSimpleStore,
									editable:false,
									mode:"local",
									value:"일반공",
									allowBlank:false,
									displayField:"job",
									valueField:"job"
								}),
								new Ext.form.TextField({
									fieldLabel:"단가",
									name:"payment",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"예금주",
									name:"account_name",
									width:100
								}),
								new Ext.form.ComboBox({
									fieldLabel:"은행명",
									hiddenName:"account_bank",
									store:BankSimpleStore,
									displayField:"bank",
									valueField:"bank",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									emptyText:"은행을 선택하세요.",
									width:190,
									editable:false
								}),
								new Ext.form.TextField({
									fieldLabel:"계좌번호",
									name:"account_number",
									width:190
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"계약정보(업체)",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"공종그룹",
									hiddenName:"gno",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									disabled:true,
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["idx","workgroup","sort"]
										}),
										remoteSort:false,
										sortInfo:{field:"sort",direction:"ASC"},
										baseParams:{"action":"workspace","get":"workgroup","wno":"<?php echo $this->wno; ?>","is_all":"false"},
									}),
									width:260,
									editable:false,
									mode:"local",
									displayField:"workgroup",
									valueField:"idx",
									emptyText:"공종그룹을 선택하세요.",
									listeners:{
										render:{fn:function(form) {
											form.getStore().load();
										}},
										select:{fn:function(form) {
											if (form.getValue() == "") {
												Ext.getCmp("DayworkerForm").getForm().findField("tno").disable();
											} else {
												Ext.getCmp("DayworkerForm").getForm().findField("tno").enable();
												Ext.getCmp("DayworkerForm").getForm().findField("tno").getStore().baseParams.gno = form.getValue();
												Ext.getCmp("DayworkerForm").getForm().findField("tno").getStore().load();
											}
										}}
									}
								}),
								new Ext.form.ComboBox({
									fieldLabel:"공종명",
									hiddenName:"tno",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									disabled:true,
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["idx","worktype","sort"]
										}),
										remoteSort:false,
										sortInfo:{field:"sort",direction:"ASC"},
										baseParams:{"action":"workspace","get":"worktype","wno":"<?php echo $this->wno; ?>","is_all":"false","gno":""},
									}),
									width:260,
									editable:false,
									mode:"local",
									displayField:"worktype",
									valueField:"idx",
									emptyText:"공종명을 선택하세요."
								}),
								new Ext.form.TextField({
									fieldLabel:"계약금액",
									name:"contract",
									width:100,
									disabled:true,
									style:"text-align:right;",
									enableKeyEvents:true,
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"연락처정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.TextField({
									fieldLabel:"전화번호",
									name:"telephone",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"핸드폰번호",
									name:"cellphone",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						})
					],
					listeners:{
						actioncomplete:{fn:function(form,action) {
							if (action.type == "load") {
								if (form.findField("type").getValue() == "개인") {
									form.findField("job").enable();
									form.findField("gno").enable();
									form.findField("contract").enable();
								} else {
									form.findField("job").disable();
									form.findField("job").clearInvalid();
									form.findField("gno").enable();
									form.findField("contract").enable();

									form.findField("tno").enable();
									form.findField("tno").getStore().baseParams.gno = form.findField("gno").getValue();
									form.findField("tno").getStore().load();
									form.findField("tno").getStore().on("load",function() {
										form.findField("tno").setValue(form.findField("tno").getValue());
									});
								}
								form.findField("jumin").clearInvalid();
							}
							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(){ Ext.getCmp("ListTab").getActiveTab().getStore().reload(); Ext.getCmp("DayworkerWindow").close(); }});
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					text:"확인",
					handler:function() {
						if (idx) {
							Ext.getCmp("DayworkerForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=worker&do=dayworker&mode=modify&wno=<?php echo $this->wno; ?>&idx="+idx,waitMsg:"근로자를 수정중입니다.",submitEmptyText:false});
						} else {
							Ext.getCmp("DayworkerForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=worker&do=dayworker&mode=add&wno=<?php echo $this->wno; ?>",waitMsg:"근로자를 추가중입니다.",submitEmptyText:false});
						}
					}
				}),
				new Ext.Button({
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					text:"취소",
					handler:function() {
						Ext.getCmp("DayworkerWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("DayworkerForm").load({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php?action=worker&get=dayworker&mode=data&idx="+idx,waitMsg:"정보를 로딩중입니다."});
				}
			}}}
		}).show();
	}

	var DayworkspaceListCm = new Ext.grid.ColumnModel([
		new Ext.grid.RowNumberer(),
		{
			dataIndex:"idx",
			hidden:true,
			hideable:false
		},{
			header:"그룹",
			dataIndex:"group",
			sortable:false,
			width:60
		},{
			header:"이름/회사명",
			dataIndex:"name",
			sortable:true,
			width:120
		},{
			header:"주민(사업자)번호",
			dataIndex:"jumin",
			sortable:true,
			width:110
		},{
			header:"전화번호",
			dataIndex:"phone",
			sortable:true,
			width:90
		},{
			header:"단가",
			dataIndex:"payment",
			sortable:true,
			width:70,
			renderer:GridNumberFormat
		},{
			header:"근무시작일",
			dataIndex:"workstart_date",
			sortable:true,
			width:90
		},{
			header:"근무종료일",
			dataIndex:"workend_date",
			sortable:true,
			width:90
		},{
			header:"계좌번호",
			dataIndex:"account",
			sortable:true,
			width:250,
			renderer:GridAccount
		},
		new Ext.grid.CheckboxSelectionModel()
	]);

	var DayworkerListStore1 = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"name","phone","group","grade","workernum","jumin","enter_date","workstart_date","workend_date","pay_type",{name:"payment",type:"int"},"account"]
		}),
		remoteSort:false,
		groupField:"group",
		sortInfo:{field:"name",direction:"ASC"},
		baseParams:{"wno":"<?php echo $this->wno; ?>","action":"worker","get":"dayworker","mode":"list","type":"all","keyword":""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"일용직근로자관리",
		layout:"fit",
		tbar:[
			new Ext.form.TextField({
				id:"KeywordInput",
				width:150,
				emptyText:"검색어를 입력하세요."
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_magnifier.png",
				text:"검색",
				handler:function() {
					Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("KeywordInput").getValue();
					Ext.getCmp("ListTab").getActiveTab().getStore().load();
				}
			}),
			'-',
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_user_add.png",
				text:"신규근로자등록",
				handler:function() {
					DayDayworkerFormFunction();
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
						id:"ListPanel",
						title:"전체근로자",
						border:false,
						cm:DayworkspaceListCm,
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:DayworkerListStore1,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							groupTextTpl:'{text}'
						}),
						listeners:{
							rowcontextmenu:{fn:DayworkerMenuFunction}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					tab.getStore().load();
					Ext.getCmp("KeywordInput").setValue(tab.getStore().baseParams.keyword);
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>