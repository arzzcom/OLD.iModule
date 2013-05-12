<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"list"}
		},
		remoteSort:true,
		sorters:[{property:"bid",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["bid","title","width","skin","option",{name:"postnum",type:"int"},"last_date"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'('+record.data.bid+')</b>');
		
		menu.add({
			text:"게시판설정",
			handler:function() {
				BoardFormFunction(record.data.bid);
			}
		});

		var option = record.data.option.split(",");
		var width = record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width);
		
		if (option[0] == "TRUE") {
			menu.add({
				text:"카테고리설정",
				handler:function() {
					BoardCategoryFunction(record.data.bid);
				}
			});
		}
		
		menu.add({
			text:"게시판바로가기",
			handler:function() {
				new Ext.Window({
					title:record.data.title,
					width:width,
					height:500,
					layout:"fit",
					maximizable:true,
					html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
				}).show();
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"게시판삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"게시판을 삭제하면 해당 게시판의 모든 글과 자료가 삭제됩니다.<br />게시판을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("게시판을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().loadPage(1);
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"board","do":"delete","bid":record.data.bid}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"게시판관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"결제방식추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_add.png",
				handler:function() {
					
				}
			}),
			'-',
			new Ext.form.TextField({
				id:"Keyword",
				width:180,
				emptyText:"검색어를 입력하세요."
			}),
			new Ext.Button({
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 게시판을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 게시판 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"게시판을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var bids = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								bids.push(checked[i].get("bid"));
							}
							
							Ext.Msg.show({title:"확인",msg:"게시판을 삭제하면 해당 게시판의 모든 글과 자료가 삭제됩니다.<br />게시판을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("게시판을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"board","do":"delete","bid":bids.join(",")}
									});
								}
							}});
						}
					}]
				})
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"상태",
						dataIndex:"is_use",
						sortable:true,
						width:80,
						renderer:function(value) { 
							if (value == "TRUE") return '<span style="color:blue;">활성화중</span>';
							else return '<span style="color:red;">비활성화</span>';
						}
					},{
						header:"결제방식",
						dataIndex:"type",
						sortable:true,
						width:100,
						renderer:function(value) { 
							var type = {BANKING:"무통장입금"};
							return type[value];
						}
					},{
						header:"결제정보",
						dataIndex:"value",
						sortable:true,
						minWidth:150,
						flex:1
					},{
						header:"최소결제",
						dataIndex:"min_point",
						sortable:true,
						width:120,
						renderer:function(value) {
							if (value == "0") return "제한없음";
							else return '<div style="font-family:tahoma;">'+GetNumberFormat(value)+'points</div>';
						}
					},{
						header:"최대결제",
						dataIndex:"max_point",
						sortable:true,
						width:120,
						renderer:function(value) {
							if (value == "0") return "제한없음";
							else return '<div style="font-family:tahoma;">'+GetNumberFormat(value)+'points</div>';
						}
					}
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"list"}
					},
					remoteSort:true,
					sorters:[{property:"bid",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					fields:["bid","title","width","skin","option",{name:"postnum",type:"int"},"last_date"]
				}),
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						new Ext.Window({
							title:record.data.title,
							width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
							height:500,
							layout:"fit",
							maximizable:true,
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
						}).show();
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>