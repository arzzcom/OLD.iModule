<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"buy"}
		},
		remoteSort:true,
		sorters:[{property:"reg_date",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","reg_date","buyer","mno","point","price","status","payment","payment_info","payinfo"]
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
		title:"포인트구매내역",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
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
				text:"선택한 구매내역을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 구매내역 입금확인처리",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"구매내역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"선택 구매내역의 입금을 확인하고 구매회원에게 포인트를 적립하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("입금처리중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/point/exec/Admin.do.php",
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
										params:{"action":"buy","do":"status","value":"COMPLETE","idx":idxs.join(",")}
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
						header:"구매신청일",
						dataIndex:"reg_date",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"구매자명",
						dataIndex:"buyer",
						sortable:true,
						width:150
					},{
						header:"구매방법",
						dataIndex:"payment_info",
						sortable:true,
						minWidth:120,
						flex:1,
						renderer:function(value,p,record) {
							var sHTML = value;
							if (record.data.payinfo) {
								sHTML+= ' <span style="color:blue;">: '+record.data.payinfo+'</span>';
							}
							return sHTML;
						}
					},{
						header:"구매포인트",
						dataIndex:"point",
						sortable:true,
						width:100,
						renderer:function(value) {
							return '<div style="text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
						}
					},{
						header:"결제(입금)금액",
						dataIndex:"price",
						sortable:true,
						width:100,
						renderer:function(value) {
							return '<div style="text-align:right;">'+GetNumberFormat(value)+' 원</div>';
						}
					},{
						header:"상태",
						dataIndex:"status",
						sortable:false,
						width:100,
						renderer:function(value) {
							if (value == "COMPLETE") return '<span style="color:blue;">구매완료</span>';
							else return '<span style="color:red;">결제대기</span>';
						}
					}
				],
				store:store,
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>