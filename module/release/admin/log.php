<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store1 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"log",get:"HIT",key:"name",keyword:"",bid:""}
		},
		remoteSort:true,
		sorters:[{property:"reg_date",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","postidx","reg_date","bid","boardtitle","category","title","mno","name","nickname","width","newment",{name:"ment",type:"int"},"ip"]
	});

	var store2 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"log",get:"VOTE",key:"name",keyword:"",bid:""}
		},
		remoteSort:true,
		sorters:[{property:"reg_date",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","postidx","reg_date","bid","boardtitle","category","title","mno","name","nickname","width","newment",{name:"ment",type:"int"},{name:"vote",type:"int"},"ip"]
	});
	
	function ItemDblClick(grid,record) {
		new Ext.Window({
			title:record.data.title,
			width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
			height:500,
			layout:"fit",
			maximizable:true,
			html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'&mode=trash&idx='+record.data.postidx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
		}).show();
	}
	
	function CellClick(grid,td,col,record,tr,row,e) {
		if (col == 4) {
			if (record.data.mno != "0") {
				var menu = new Ext.menu.Menu();
				menu.add('<b class="menu-title">기록대상자 검색</b>');
				menu.add({
					text:record.data.name,
					handler:function() {
						Ext.getCmp("Key").setValue("name");
						Ext.getCmp("Keyword").setValue(record.data.name);
						Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
						Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
						Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
					}
				});
				menu.showAt(e.getXY());
				e.stopEvent();
			}
		}
		
		if (col == 5) {
			var menu = new Ext.menu.Menu();
			
			menu.add('<b class="menu-title">기록아이피 검색</b>');
			menu.add({
				text:record.data.ip,
				handler:function() {
					Ext.getCmp("Key").setValue("ip");
					Ext.getCmp("Keyword").setValue(record.data.ip);
					Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
					Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
				}
			});
			menu.showAt(e.getXY());
			e.stopEvent();
		}
	}
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"게시물 보기",
			handler:function() {
				new Ext.Window({
					title:record.data.title,
					width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
					height:500,
					layout:"fit",
					maximizable:true,
					html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'&mode=trash&idx='+record.data.postidx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
				}).show();
			}
		});
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"게시물로그관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.form.ComboBox({
						id:"BoardID",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"list","is_all":"true"}
							},
							remoteSort:false,
							sorters:[{property:"bid",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["bid","title","option"]
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"bid",
						emptyText:"게시판명",
						listeners:{
							select:{fn:function(form,record) {
								Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("bid",form.getValue());
								Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
							}}
						}
					}),
					new Ext.form.ComboBox({
						id:"Key",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.ArrayStore({
							fields:["keytype","text"],
							data:[["name","기록대상자"],["ip","기록아이피"]]
						}),
						width:80,
						editable:false,
						mode:"local",
						displayField:"text",
						valueField:"keytype",
						value:"name"
					}),
					new Ext.form.TextField({
						id:"Keyword",
						width:150,
						emptyText:"검색어를 입력하세요."
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
							Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						text:"1개월이전 로그데이터 삭제",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_trash.png",
						handler:function() {
							Ext.Msg.show({title:"확인",msg:"1개월이전 로그데이터를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("로그를 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 이전로그를 비웠습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel1").getStore().loadPage(1);
													Ext.getCmp("ListPanel2").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"log","do":"delete"}
									});
								}
							}});
						}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel1",
						title:"게시물조회기록",
						border:false,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"로그일시",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"게시판명",
								dataIndex:"boardtitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									return value+'<span style="font-family:tahoma;">('+record.data.bid+')</span>';
								}
							},{
								header:"제목",
								dataIndex:"title",
								sortable:false,
								minWidth:200,
								flex:1,
								renderer:function(value,p,record) {
									var sHTML = "";
		
									if (record.data.category) {
										sHTML+= '<span style="color:#99BBE8;">['+record.data.category+'] </span>';
									}
									sHTML+= value;
									if (record.data.ment > 0) sHTML+= ' <span style="color:#EF5600; font-family:tahoma; font-size:10px;">['+Ext.util.Format.number(record.data.ment,"0,0")+(record.data.newment == "TRUE" ? '+' : '')+']</span>';
		
									return sHTML;
								}
							},{
								header:"기록대상자",
								dataIndex:"name",
								sortable:true,
								width:120,
								renderer:function(value,p,record) {
									if (record.data.mno != "0") p.tdCls = Ext.baseCSSPrefix + 'pointer';
									
									if (record.data.mno == "0") return value;
									else if (record.data.nickname) return '<b>'+value+"("+record.data.nickname+")</b>";
									else return '<b>'+value+'</b>';
								}
							},{
								header:"기록아이피",
								dataIndex:"ip",
								sortable:true,
								menuDisabled:true,
								width:120,
								renderer:function(value,p,record) {
									p.tdCls = Ext.baseCSSPrefix + 'pointer';
									return value;
								}
							}
						],
						store:store1,
						columnLines:true,
						selModel:new Ext.selection.RowModel({mode:"SINGLE"}),
						bbar:new Ext.PagingToolbar({
							store:store1,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:ItemDblClick,
							cellclick:CellClick,
							itemcontextmenu:ItemContextMenu
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListPanel2",
						title:"게시물추천기록",
						border:false,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"로그일시",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"게시판명",
								dataIndex:"boardtitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									return value+'<span style="font-family:tahoma;">('+record.data.bid+')</span>';
								}
							},{
								header:"제목",
								dataIndex:"title",
								sortable:false,
								minWidth:200,
								flex:1,
								renderer:function(value,p,record) {
									var sHTML = "";
		
									if (record.data.category) {
										sHTML+= '<span style="color:#99BBE8;">['+record.data.category+'] </span>';
									}
									sHTML+= value;
									if (record.data.ment > 0) sHTML+= ' <span style="color:#EF5600; font-family:tahoma; font-size:10px;">['+Ext.util.Format.number(record.data.ment,"0,0")+(record.data.newment == "TRUE" ? '+' : '')+']</span>';
		
									return sHTML;
								}
							},{
								header:"기록대상자",
								dataIndex:"name",
								sortable:true,
								width:120,
								renderer:function(value,p,record) {
									if (record.data.mno != "0") p.tdCls = Ext.baseCSSPrefix + 'pointer';
									
									if (record.data.mno == "0") return value;
									else if (record.data.nickname) return '<b>'+value+"("+record.data.nickname+")</b>";
									else return '<b>'+value+'</b>';
								}
							},{
								header:"기록아이피",
								dataIndex:"ip",
								sortable:true,
								menuDisabled:true,
								width:120,
								renderer:function(value,p,record) {
									p.tdCls = Ext.baseCSSPrefix + 'pointer';
									return value;
								}
							},{
								header:"추천점수",
								dataIndex:"vote",
								sortable:true,
								menuDisabled:true,
								width:60,
								renderer:GridNumberFormat
							}
						],
						store:store2,
						columnLines:true,
						selModel:new Ext.selection.RowModel({mode:"SINGLE"}),
						bbar:new Ext.PagingToolbar({
							store:store2,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:ItemDblClick,
							cellclick:CellClick,
							itemcontextmenu:ItemContextMenu
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getStore().getProxy().extraParams.bid) {
						Ext.getCmp("BoardID").setValue(tab.getStore().getProxy().extraParams.bid);
					} else {
						Ext.getCmp("BoardID").reset();
					}
					Ext.getCmp("Key").setValue(tab.getStore().getProxy().extraParams.key);
					Ext.getCmp("Keyword").setValue(tab.getStore().getProxy().extraParams.keyword);
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>