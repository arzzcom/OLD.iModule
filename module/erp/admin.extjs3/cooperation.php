<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var CooperationStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"title","company_number","type","master","telephone"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"cooperation","get":"list","keyword":""}
	});

	function CooperationFunction(idx) {
		new Ext.Window({
			title:(idx ? "협력업체수정" : "협력업체등록"),
			id:"CooperationWindow",
			width:600,
			height:400,
			layout:"fit",
			modal:true,
			items:[
				new Ext.form.FormPanel({
					id:"CooperationForm",
					style:"background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:100,
					autoScroll:true,
					border:false,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["title","company_number","type","master","telephone","zipcode","address1","address2"]
					),
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
						FormAddressFieldSet("CooperationForm")
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("ListPanel").getStore().load();
							Ext.getCmp("CooperationWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (!idx) {
							Ext.getCmp("CooperationForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=cooperation&do=add",waitMsg:"협력업체를 추가중입니다."});
						} else {
							Ext.getCmp("CooperationForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=cooperation&do=modify&idx="+idx,waitMsg:"협력업체를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("CooperationWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("CooperationForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=cooperation&get=data&idx="+idx,waitMsg:"데이터를 로딩중입니다."});
				}
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"협력업체관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.form.TextField({
						id:"CooperationKeyword",
						width:150,
						emptyText:"검색어를 입력하세요.",
						enableKeyEvents:true,
						listeners:{keydown:{fn:function(form,e) {
							if (e.keyCode == 13) {
								CooperationStore.baseParams.keyword = Ext.getCmp("CooperationKeyword").getValue();
								CooperationStore.load({params:{start:0,limit:30}});
							}
						}}}
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
						handler:function() {
							CooperationStore.baseParams.keyword = Ext.getCmp("CooperationKeyword").getValue();
							CooperationStore.load({params:{start:0,limit:30}});
						}
					}),
					'-',
					new Ext.Button({
						text:"협력업체등록",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_add.png",
						handler:function() {
							CooperationFunction();
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hideable:false,
						hidden:true,
						sortable:false
					},{
						header:"업체명",
						dataIndex:"title",
						width:200
					},{
						header:"사업자등록번호",
						dataIndex:"company_number",
						width:100
					},{
						header:"업종/업태",
						dataIndex:"type",
						width:100
					},{
						header:"대표",
						dataIndex:"master",
						width:100
					},{
						header:"연락처",
						dataIndex:"telephone",
						width:100
					}
				]),
				store:CooperationStore,
				trackMouseOver:true,
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:CooperationStore,
					displayInfo:true,
					displayMsg:"{0} - {1} of {2}",
					emptyMsg:"데이터없음"
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx) {
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);
						var data = grid.getStore().getAt(idx);

						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+data.get("title")+'</b>');
						menu.add({
							text:"협력업체수정",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_edit.png",
							handler:function() {
								CooperationFunction(data.get("idx"));
							}
						});
						menu.add({
							text:"협력업체삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_delete.png",
							handler:function() {
								Ext.Msg.show({title:"안내",msg:"협력업체를 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
									if (btn == "ok") {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function(XML) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("ListPanel").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											},
											headers:{},
											params:{"action":"cooperation","do":"delete","idx":data.get("idx")}
										});
									}
								}});
							}
						});
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	CooperationStore.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>