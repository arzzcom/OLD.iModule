<?php $mOneroom = new ModuleOneroom(); REQUIRE_ONCE $mOneroom->modulePath.'/admin/item.inc.php'; ?>
<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"item",get:"list",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:"",idx:"",is_open:"TRUE"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","region","mno","name","category","title","real_areasize","is_buy","is_rent_all","is_rent_month","is_rent_short","price_type","price","price_buy","price_rent_all","price_rent_deposit","price_rent_month","is_open","is_premium","is_regionitem","is_default_premium","is_default_regionitem","reg_date","end_date"]
	});

	var ItemDblClick = function(grid,record) {
		new Ext.Window({
			title:record.data.title,
			width:800,
			height:500,
			modal:true,
			resizable:false,
			html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/oneroom/manager/preview.php?idx='+record.data.idx+'" style="width:100%; height:100%;" frameborder="0"></iframe>'
		}).show();
	}
	
	var ItemContextMenu = function(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');

		menu.add({
			text:"기본프리미엄매물로 설정",
			checked:record.data.is_default_premium == "TRUE",
			handler:function(item) {
				var value = item.checked == true ? "TRUE" : "FALSE";
				
				Ext.Msg.wait("선택한 작업을 처리하고 있습니다.","잠시만 기다려주십시오.");
				Ext.Ajax.request({
					url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
					success:function(response) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success == true) {
							Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								grid.getStore().reload();
							}});
						} else {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						}
					},
					failure:function() {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					},
					params:{"action":"item","do":"defaultpremiummode","value":value,"idx":record.data.idx}
				});
			}
		});
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기본프리미엄매물관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.form.NumberField({
						id:"Idx",
						width:80,
						emptyText:"매물번호",
						hideTrigger:true,
						mouseWheelEnabled:false,
						checkChangeBuffer:500,
						listeners:{change:{fn:function(form) {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("idx",form.getValue());
							Ext.getCmp("ListPanel").getStore().reload();
						}}}
					}),
					'-',
					new Ext.form.ComboBox({
						id:"Category1",
						typeAhead:true,
						triggerAction:"all",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"category","is_all":"true"}
							},
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							fields:["idx","title",{name:"sort",type:"int"}]
						}),
						width:90,
						editable:false,
						displayField:"title",
						valueField:"idx",
						emptyText:"1차카테고리",
						listeners:{
							select:{fn:function(form,selected) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("category1",form.getValue());
								Ext.getCmp("ListPanel").getStore().reload();
							}}
						}
					}),
					new Ext.form.ComboBox({
						id:"Region1",
						typeAhead:true,
						triggerAction:"all",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"region","is_all":"true"}
							},
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							fields:["idx","title",{name:"sort",type:"int"}]
						}),
						width:90,
						editable:false,
						displayField:"title",
						valueField:"idx",
						emptyText:"1차지역",
						listeners:{
							select:{fn:function(form,selected) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("region1",form.getValue());
								Ext.getCmp("ListPanel").getStore().reload();
							}}
						}
					}),
					new Ext.form.TextField({
						id:"Keyword",
						width:120,
						emptyText:"검색어 입력"
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							Ext.getCmp("ListPanel").getStore().reload();
						}
					}),
					'-',
					new Ext.Button({
						id:"ListAll",
						text:"전체",
						pressed:true,
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox_on.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListAll").toggle(false);
								Ext.getCmp("ListPremium").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("is_default_premium","");
								store.loadPage(1);
							}
						},
						listeners:{toggle:{fn:function(button,pressed) {
							if (pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png");
						}}}
					}),
					new Ext.Button({
						id:"ListPremium",
						text:"기본프리미엄매물",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListAll").toggle(false);
								Ext.getCmp("ListPremium").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("is_default_premium","TRUE");
								store.loadPage(1);
							}
						},
						listeners:{toggle:{fn:function(button,pressed) {
							if (pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png");
						}}}
					}),
					new Ext.Button({
						id:"ListDefault",
						text:"일반매물",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListAll").toggle(false);
								Ext.getCmp("ListPremium").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("is_default_premium","FALSE");
								store.loadPage(1);
							}
						},
						listeners:{toggle:{fn:function(button,pressed) {
							if (pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png");
						}}}
					}),
					'-',
					new Ext.Button({
						text:"선택한 매물을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"선택한 매물을 기본프리미엄매물로 설정",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"먼저 목록에서 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.wait("선택한 작업을 처리하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"item","do":"defaultpremiummode","value":"TRUE","idx":idxs.join(",")}
									});
								}
							},{
								text:"선택한 매물을 일반매물로 설정",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"먼저 목록에서 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.wait("선택한 작업을 처리하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"item","do":"defaultpremiummode","value":"FALSE","idx":idxs.join(",")}
									});
								}
							}]
						})
					}),
					'->',
					{xtype:"tbtext",text:"목록마우스우클릭 : 상세메뉴 / 목록더블클릭 : 매물보기"}
				],
				columns:[
					new Ext.grid.RowNumberer({
						header:"번호",
						dataIndex:"idx",
						sortable:true,
						align:"left",
						width:60,
						renderer:function(value,p,record) {
							p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
							return GridNumberFormat(value);
						}
					}),{
						header:"프리미엄여부",
						dataIndex:"is_default_premium",
						width:85,
						renderer:function(value) {
							if (value == "TRUE") return '<span style="color:red;">기본프리미엄</span>';
							else return '일반매물';
						}
					},{
						header:"매물명",
						dataIndex:"title",
						sortable:true,
						minWidth:250,
						flex:1,
						renderer:function(value,p,record) {
							return '<span class="blue">['+record.data.category+']</span> '+value;
						}
					},{
						header:"지역",
						dataIndex:"region",
						sortable:false,
						width:100
					},{
						header:"매매",
						dataIndex:"price_buy",
						sortable:true,
						width:70,
						renderer:function(value,p,record) {
							if (record.data.is_buy == "TRUE") return '<div style="text-align:right;">'+GetNumberFormat(value)+'만</div>';
						}
					},{
						header:"전세",
						dataIndex:"price_rent_all",
						sortable:true,
						width:65,
						renderer:function(value,p,record) {
							if (record.data.is_rent_all == "TRUE") return '<div style="text-align:right;">'+GetNumberFormat(value)+'만</div>';
						}
					},{
						header:"보증금",
						dataIndex:"price_rent_deposit",
						sortable:true,
						width:60,
						renderer:function(value,p,record) {
							if (record.data.is_rent_month == "TRUE") return '<div style="text-align:right;">'+GetNumberFormat(value)+'만</div>';
						}
					},{
						header:"월세",
						dataIndex:"price_rent_month",
						sortable:true,
						width:50,
						renderer:function(value,p,record) {
							if (record.data.is_rent_month == "TRUE" || record.data.is_rent_short == "TRUE") return '<div style="text-align:right;">'+GetNumberFormat(value)+'만</div>';
						}
					},{
						header:"등록자",
						dataIndex:"name",
						width:100
					},{
						header:"등록일",
						dataIndex:"reg_date",
						width:70,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>'
						}
					},{
						header:"만료일",
						dataIndex:"end_date",
						width:70,
						renderer:function(value,p,record) {
							if (record.data.is_open == "TRUE" && value == 0) {
								return '<div style="text-align:center; color:blue;">무제한</div>';
							} else if (value == 0) {
								return '<div style="text-align:center; color:red;">공개중아님</div>';
							} else {
								return '<div style="font-family:tahoma;">'+value+'</div>';
							}
						}
					}
				],
				columnLines:true,
				store:store,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:ItemDblClick,
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>