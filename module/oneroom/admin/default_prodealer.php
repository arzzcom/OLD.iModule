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
			extraParams:{action:"prodealer",type:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","status","region","mno","name","user_id","itemcount","region1","region1_title","region1_count","region2","region2_title","region2_count","region3","region3_title","region3_count"]
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
			text:"기본지역전문가 설정",
			checked:record.data.is_default_regionitem == "TRUE",
			handler:function(item) {

			}
		});
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}
	
	var SetDefaultProDealer = function(isAuto) {
		var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
		if (checked.length == 0) {
			Ext.Msg.show({title:"에러",msg:"먼저 목록에서 회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			return false;
		}
		
		if (isAuto == true) {
			var data = new Array();
			for (var i=0, loop=checked.length;i<loop;i++) {
				data[i] = {idx:checked[i].get("idx"),region1:checked[i].get("region1"),region2:checked[i].get("region2"),region3:checked[i].get("region3")};
			}
			
			data = Ext.JSON.encode(data);

			Ext.Msg.show({title:"확인",msg:"선택한 사용자를 기본지역전문가로 설정하시겠습니까?<br />자동으로 지역을 설정할 수 없는 경우 기본지역전문가로 등록되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.INFO,fn:function(button) {
				if (button == "yes") {
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
						params:{"action":"item","do":"defaultprodealermode","value":"TRUE","mode":"auto","data":data}
					});
				}
			}});
		}
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기본지역전문가관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
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
								Ext.getCmp("ListProDealer").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("type","");
								store.loadPage(1);
							}
						},
						listeners:{toggle:{fn:function(button,pressed) {
							if (pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png");
						}}}
					}),
					new Ext.Button({
						id:"ListProDealer",
						text:"기본지역전문가",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListAll").toggle(false);
								Ext.getCmp("ListProDealer").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("type","prodealer");
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
						text:"일반회원",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_checkbox.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListAll").toggle(false);
								Ext.getCmp("ListProDealer").toggle(false);
								Ext.getCmp("ListDefault").toggle(false);
								
								button.toggle(true);
								store.getProxy().setExtraParam("type","default");
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
						text:"선택한 사용자를&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"기본지역전문가로 설정(최상위지역으로 자동설정)",
								handler:function() {
									SetDefaultProDealer(true);
								}
							},{
								text:"기본지역전문가에서 삭제",
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
										params:{"action":"item","do":"defaultregionitemmode","value":"FALSE","idx":idxs.join(",")}
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
						header:"지역전문가여부",
						dataIndex:"status",
						width:90,
						renderer:function(value) {
							if (value == "TRUE") return '<span style="color:red;">기본지역전문가</span>';
							else return '일반회원';
						}
					},{
						header:"회원정보",
						dataIndex:"name",
						sortable:true,
						width:150,
						renderer:function(value,p,record) {
							return value+'('+record.data.user_id+')';
						}
					},{
						header:"지역전문가 설정지역",
						dataIndex:"region",
						sortable:false,
						minWidth:200,
						flex:1,
						renderer:function(value) {
							if (value) return value;
							else return '<span style="color:#666666;">기본지역전문가로 설정되어 있지 않음</span>';
						}
					},{
						header:"최상위 1차지역",
						dataIndex:"region1",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							if (value != "0") return record.data.region1_title+' <span style="color:blue;">('+GetNumberFormat(record.data.region1_count)+'개)</span>';
							else return "없음";
						}
					},{
						header:"최상위 2차지역",
						dataIndex:"region2",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							if (value != "0") return record.data.region2_title+' <span style="color:blue;">('+GetNumberFormat(record.data.region2_count)+'개)</span>';
							else return "없음";
						}
					},{
						header:"최상위 3차지역",
						dataIndex:"region3",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							if (value != "0") return record.data.region3_title+' <span style="color:blue;">('+GetNumberFormat(record.data.region3_count)+'개)</span>';
							else return "없음";
						}
					},{
						header:"공개매물수",
						dataIndex:"itemcount",
						sortable:true,
						width:100,
						renderer:function(value) {
							return '<div style="text-align:right;">'+GetNumberFormat(value)+'개</div>';
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