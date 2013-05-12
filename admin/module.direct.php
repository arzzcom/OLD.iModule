<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"상단메뉴에 고정",
			checked:record.data.is_direct == "TRUE",
			handler:function(item) {
				var value = item.checked == true ? "TRUE" : "FALSE";
				
				Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
				Ext.Ajax.request({
					url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
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
					params:{action:"module","do":"direct","value":value,"module":record.data.module}
				});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}
	
	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"모듈바로가기관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_arrow_refresh.png",
						text:"변경된 순서를 반영하기 위해 관리자페이지 새로고침",
						handler:function() {
							location.href = location.href;
						}
					}),
					'-',
					{xtype:"tbtext",text:"순서변경"},
					new Ext.Button({
						text:"위로",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_arrow_up.png",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}

							for (var i=0, loop=checked.length;i<loop;i++) {
								var sort = checked[i].get("sort");
								if (sort != 0) {
									Ext.getCmp("ListPanel").getStore().getAt(sort).set("sort",sort-1);
									Ext.getCmp("ListPanel").getStore().getAt(sort-1).set("sort",sort);
									Ext.getCmp("ListPanel").getStore().sort("sort","ASC");
								} else {
									return false;
								}
							}
							
							var update = Ext.getCmp("ListPanel").getStore().getUpdatedRecords();
							if (update.length > 0) {
								var data = new Array();
								for (var i=0, loop=update.length;i<loop;i++) {
									data.push(update[i].data);
								}
								data = Ext.JSON.encode(data);
								
								Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.getCmp("ListPanel").getStore().commitChanges();
											Ext.Msg.hide();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"module","do":"sort","data":data}
								});
							}
						}
					}),
					new Ext.Button({
						text:"아래로",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_arrow_down.png",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}

							for (var i=checked.length-1;i>=0;i--) {
								var sort = checked[i].get("sort");
								if (sort != Ext.getCmp("ListPanel").getStore().getCount()-1) {
									Ext.getCmp("ListPanel").getStore().getAt(sort).set("sort",sort+1);
									Ext.getCmp("ListPanel").getStore().getAt(sort+1).set("sort",sort);
									Ext.getCmp("ListPanel").getStore().sort("sort","ASC");
								} else {
									return false;
								}
							}
							
							var update = Ext.getCmp("ListPanel").getStore().getUpdatedRecords();
							if (update.length > 0) {
								var data = new Array();
								for (var i=0, loop=update.length;i<loop;i++) {
									data.push(update[i].data);
								}
								data = Ext.JSON.encode(data);
								
								Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.getCmp("ListPanel").getStore().commitChanges();
											Ext.Msg.hide();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"module","do":"sort","data":data}
								});
							}
						}
					})
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"모듈이름",
						dataIndex:"title",
						sortable:true,
						width:130,
						summaryType:"count",
						summaryRenderer:function(value) {
							return '총 '+value+'개 모듈';
						}
					},{
						header:"모듈절대경로",
						dataIndex:"path",
						sortable:true,
						minWidth:300,
						flex:1,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"모듈버전",
						dataIndex:"version",
						sortable:false,
						width:60,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:center;">'+value+'</div>';
						}
					}
				],
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"module",get:"direct",keyword:""}
					},
					remoteSort:false,
					sorters:[{property:"sort",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					groupDir:"DESC",
					fields:["module","title","version","path",{name:"sort",type:"int"}]
				}),
				listeners:{
					itemdblclick:{fn:function() {
						Ext.Msg.show({title:"안내",msg:"현재 설정화면은 더블클릭으로 실행되는 동작이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>