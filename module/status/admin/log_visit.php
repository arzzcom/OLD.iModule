<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"log_visit",date:"<?php echo date('Y-m-d'); ?>",type:"ALL"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:[{name:"idx",type:"int"},"visit_time","pageurl","ip","name","user_id","mno","user_agent","refererurl"]
	});
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.visit_time+'</b>');
		
		menu.add({
			text:"현재 아이피기록만 보기",
			handler:function() {
				Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip",record.data.ip);
				Ext.getCmp("ListPanel").getStore().loadPage(1);
			}
		});
		
		if (record.data.mno != "0") {
			menu.add({
				text:"현재 회원기록만 보기",
				handler:function() {
					Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno",record.data.mno);
					Ext.getCmp("ListPanel").getStore().loadPage(1);
				}
			});
		}
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"방문자로그",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue());
							var move = Ext.Date.add(today,Ext.Date.DAY,-1);
							Ext.getCmp("today").setValue(Ext.Date.format(move,"Y-m-d"));
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
							Ext.getCmp("ListPanel").getStore().loadPage(1);
						}
					}),
					new Ext.form.DateField({
						id:"today",
						format:"Y-m-d",
						width:90,
						value:"<?php echo date('Y-m-d'); ?>",
						listeners:{select:{fn:function(form,date) {
							Ext.getCmp("today").setValue(Ext.Date.format(date,"Y-m-d"));
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
							Ext.getCmp("ListPanel").getStore().loadPage(1);
						}}}
					}),
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue());
							var move = Ext.Date.add(today,Ext.Date.DAY,1);
							Ext.getCmp("today").setValue(Ext.Date.format(move,"Y-m-d"));
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
							Ext.getCmp("ListPanel").getStore().loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						id:"TypeAll",
						text:"전체기록",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox_on.png",
						enableToggle:false,
						pressed:true,
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("type","ALL");
								Ext.getCmp("ListPanel").getStore().loadPage(1);
							}
							Ext.getCmp("TypeAll").toggle(true);
							Ext.getCmp("TypeMember").toggle(false);
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox.png");
						}}}
					}),
					new Ext.Button({
						id:"TypeMember",
						text:"회원기록",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox.png",
						enableToggle:false,
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("type","MEMBER");
								Ext.getCmp("ListPanel").getStore().loadPage(1);
							}
							Ext.getCmp("TypeAll").toggle(false);
							Ext.getCmp("TypeMember").toggle(true);
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_checkbox.png");
						}}}
					}),
					'-',
					new Ext.Button({
						text:"검색조건초기화",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_magifier_zoom_out.png",
						handler:function() {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
							Ext.getCmp("ListPanel").getStore().loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						text:"로그지우기",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_table_delete.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"방문자로그를 모두 초기화하시겠습니까?<br />방문카운터 등은 초기화되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.do.php",
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
										params:{action:"log_visit_delete"}
									});
								}
							}});
						}
					}),
					'->',
					{xtype:"tbtext",text:"마우스 우클릭 : 상세메뉴"}
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"방문시간",
						dataIndex:"visit_time",
						sortable:true,
						width:120,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"방문페이지",
						dataIndex:"pageurl",
						minWidth:200,
						flex:1,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"아이피",
						dataIndex:"ip",
						width:100,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"회원정보",
						dataIndex:"name",
						sortable:false,
						width:100,
						renderer:function(value,p,record) {
							if (value) return value+'('+record.data.user_id+')';
							else return '<span style="color:#999999;">비회원</span>';
						}
					},{
						header:"브라우져",
						dataIndex:"user_agent",
						sortable:false,
						width:250,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"이전페이지",
						dataIndex:"refererurl",
						minWidth:200,
						sortable:false,
						flex:1,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					}
				],
				store:store,
				columnLines:true,
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
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