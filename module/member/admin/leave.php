<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"leave",get:"list",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"leave_date",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:[{name:"idx",type:"int"},"user_id","group","name","nickname","jumin","email","phone","reg_date","last_login","leave_date","point","msg"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.name+'('+record.data.user_id+')</b>');

		menu.add({
			text:"회원관련 모든 정보삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"회원관련 모든정보(게시물, 회원이 올린 첨부파일 등)를 서버에서 삭제합니다.<br />이 작업은 취소할 수 없으며, 시간이 많이 소요될 수 있습니다.<br />계속 진행하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
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
							params:{action:"leave","do":"delete","idx":record.data.idx}
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
		title:"탈퇴회원관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.form.TextField({
						id:"keyword",
						emptyText:"아이디, 실명, 닉네임",
						width:150
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_magnifier.png",
						handler:function() {
							MemberAll.getProxy().setExtraParam("keyword",Ext.getCmp("keyword").getValue());
							MemberAll.reload();
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 탈퇴회원을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_group_gear.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"회원관련 모든정보 일괄삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.show({title:"확인",msg:"회원관련 모든정보(게시물, 회원이 올린 첨부파일 등)를 서버에서 삭제합니다.<br />이 작업은 취소할 수 없으며, 시간이 많이 소요될 수 있습니다.<br />계속 진행하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
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
												params:{action:"leave","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							}]
						})
					})
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"아이디",
						dataIndex:"user_id",
						width:110,
						renderer:function(value,p,record) {
							return '<div style="font-family:verdana;">'+value+' <span style="font-family:tahoma; font-size:10px; color:#C6C6C6;">['+GetNumberFormat(record.data.idx)+']</span></div>';
						}
					},{
						header:"이름",
						dataIndex:"name",
						width:60
					},{
						header:"닉네임",
						dataIndex:"nickname",
						width:80
					},{
						header:"탈퇴사유",
						dataIndex:"msg",
						minWidth:150,
						flex:1,
						renderer:function(value) {
							if (value == "SYSTEM") return '시스템에 의한 삭제';
							else return value;
						}
					},{
						header:"주민등록번호",
						dataIndex:"jumin",
						width:100,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"이메일",
						dataIndex:"email",
						width:180,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"연락처",
						dataIndex:"phone",
						sortable:false,
						width:120,
						renderer:function(value) {
							var data = value.split("||");

							if (data.length == 2) {
								return '<div style="font-family:tahoma;">'+data[0]+' <span style="color:#C6C6C6;">['+data[1]+']</span></div>';
							} else {
								return '<div style="font-family:tahoma;">'+value+'</div>';
							}
						}
					},{
						header:"포인트",
						dataIndex:"point",
						width:65,
						renderer:GridNumberFormat
					},{
						header:"가입일",
						dataIndex:"reg_date",
						width:100,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"최종접속일",
						dataIndex:"last_login",
						width:100,
						renderer:function(value,p,record) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"탈퇴일",
						dataIndex:"leave_date",
						width:100,
						renderer:function(value,p,record) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					}
				],
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				store:store,
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
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