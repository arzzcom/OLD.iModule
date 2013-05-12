<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var AddressStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Extjs.get.php?action=address"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["zipcode","address","value"]
		})
	});

	AddressStore.on("load",function(store) {
		if (store.getCount() == 0) {
			Ext.Msg.show({title:"에러",msg:"주소를 찾을수 없습니다. 다시 검색하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").setValue(""); Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
		} else {
			Ext.getCmp("AddOutsourcingForm").getForm().findField("select_address").enable();
		}
	},AddressStore);

	function OutsourcingFormFunction(idx) {
		new Ext.Window({
			title:(idx ? "외주업체수정" : "외주업체등록"),
			id:"AddOutsourcingWindow",
			width:600,
			height:400,
			modal:true,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"AddOutsourcingForm",
					style:"background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:100,
					autoScroll:true,
					border:false,
					autoWidth:true,
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["title","company_number","type","master","telephone","zipcode","address1","address2"]
					),
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							msgTarget:"side",
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									name:"title",
									fieldLabel:"업체명",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"company_number",
									fieldLabel:"사업자등록번호",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									allowBlank:false,
									validator:CheckCompanyNumber,
									listeners:{
										focus:{fn:FocusNumberOnly},
										blur:{fn:BlurCompanyNumberFormat}
									}
								}),
								new Ext.form.TextField({
									name:"type",
									fieldLabel:"업태/업종",
									width:200
								}),
								new Ext.form.TextField({
									name:"master",
									fieldLabel:"대표자",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"telephone",
									fieldLabel:"대표번호",
									width:200,
									allowBlank:false,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"주소",
							layout:"table",
							layoutConfig:{columns:2},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								{
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
													if (!Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()) {
														Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
														return false;
													}
													AddressStore.load({params:{keyword:Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()}});
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
												if (!Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()) {
													Ext.Msg.show({title:"에러",msg:"주소를 검색할 읍.면.동을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").focus();}});
													return false;
												}
												AddressStore.load({params:{keyword:Ext.getCmp("AddOutsourcingForm").getForm().findField("search_zipcode").getValue()}});
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
											listClass:"x-combo-list-small",
											store:AddressStore,
											editable:false,
											mode:"local",
											displayField:"address",
											valueField:"value",
											emptyText:"기본주소를 선택하세요.",
											listeners:{
												select:{fn:function(object,store,idx) {
													Ext.getCmp("AddOutsourcingForm").getForm().findField("zipcode").setValue(store.get("zipcode"));
													Ext.getCmp("AddOutsourcingForm").getForm().findField("address1").setValue(store.get("value"));
													Ext.getCmp("AddOutsourcingForm").getForm().findField("address2").focus(false,100);
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
						})
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							if (idx == 0) Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							else Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("ListPanel").getStore().reload();
							Ext.getCmp("AddOutsourcingWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					handler:function() {
						if (idx == 0) {
						Ext.getCmp("AddOutsourcingForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=attend&do=add_outsourcing&wno=<?php echo $this->wno; ?>",waitMsg:"외주업체를 추가중입니다."});
						} else {
							Ext.getCmp("AddOutsourcingForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=attend&do=modify_outsourcing&wno=<?php echo $this->wno; ?>&idx="+idx,waitMsg:"외주업체를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("AddOutsourcingWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx != 0) {
					Ext.getCmp("AddOutsourcingForm").getForm().load({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php?action=attend&get=outsourcing_data&cno="+idx,waitMsg:"데이터를 로딩중입니다."});
				}
			}}}
		}).show();
	}

	function ContractFunction(idx) {
		new Ext.Window({
			title:"계약내역보기",
			id:"ContractWindow",
			width:650,
			height:400,
			modal:true,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"ContractList",
					border:false,
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							dataIndex:"group",
							hideable:false
						},{
							header:"계약명",
							dataIndex:"title",
							width:380
						},{
							header:"계약금액",
							dataIndex:"price",
							width:100,
							renderer:GridNumberFormat,
							summaryType:"sum"
						},{
							header:"계약일자",
							dataIndex:"date",
							width:110
						}
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["group","title",{name:"price",type:"int"},"date"]
						}),
						remoteSort:false,
						sortInfo:{field:"title",direction:"ASC"},
						groupField:"group",
						baseParams:{"wno":"<?php echo $this->wno; ?>","action":"attend","get":"outsourcing_contract","cno":idx}
					}),
					plugins:new Ext.ux.grid.GroupSummary(),
					view:new Ext.grid.GroupingView({
						enableGroupingMenu:false,
						hideGroupedColumn:true,
						showGroupName:false,
						enableNoGroups:false,
						headersDisabled:false,
						showGroupHeader:false
					})
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("ContractList").getStore().load();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"외주업체관리",
		layout:"fit",
		tbar:[
			new Ext.Button({
				text:"외주업체등록",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_building_add.png",
				handler:function() {
					OutsourcingFormFunction(0);
				}
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				autoScroll:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"업체명",
						dataIndex:"title",
						sortable:true,
						width:200
					},{
						header:"사업자등록번호",
						dataIndex:"company_number",
						sortable:true,
						width:120
					},{
						header:"업태/업종",
						dataIndex:"type",
						sortable:false,
						width:100
					},{
						header:"대표자",
						dataIndex:"master",
						sortable:true,
						width:80
					},{
						header:"발주계약",
						dataIndex:"contract",
						width:60,
						renderer:GridNumberFormat
					},{
						header:"계약금액",
						dataIndex:"contract_price",
						sortable:true,
						width:120,
						renderer:GridNumberFormat
					},{
						header:"최근계약일",
						dataIndex:"contract_date",
						sortable:true,
						width:110
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:[{name:"idx",type:"int"},"title","type","contract","company_number","master",{name:"contract_price",type:"int"},"contract_date"]
					}),
					remoteSort:false,
					sortInfo:{field:"title",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"attend","get":"outsourcing"}
				}),
				trackMouseOver:true,
				loadMask:{msg:"데이터를 로딩중입니다."},
				viewConfig:{forceFit:false},
				listeners:{
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);
						var data = grid.getStore().getAt(idx);

						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+data.get("title")+'</b>');
						menu.add({
							text:"외주업체수정",
							icon:(Ext.isIE6 ? "" : "<?php echo $this->moduleDir; ?>/images/common/icon_building_edit.png"),
							handler:function(item) {
								OutsourcingFormFunction(data.get("idx"));
							}
						});
						menu.add({
							text:"계약내역보기",
							icon:(Ext.isIE6 ? "" : "<?php echo $this->moduleDir; ?>/images/common/icon_table.png"),
							handler:function(item) {
								ContractFunction(data.get("idx"));
							}
						});
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	Ext.getCmp("ListPanel").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>