<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	function ItemContextMenu(grid,record,row,index,e) {
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.dbcode+'</b>');
		
		menu.add({
			text:"DB서버 삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"DB서버를 삭제하면 해당DB서버를 사용하는 모듈이 더이상 DB를 사용할 수 없습니다.<br />선택한 DB서버를 정말 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 DB서버를 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"server","do":"delete","dbcode":record.data.dbcode}
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
		title:"멀티DB서버관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						text:"DB서버추가",
						icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_database_add.png",
						handler:function() {
							new Ext.Window({
								id:"AddDBWindow",
								title:"DB서버추가",
								width:500,
								modal:true,
								resizable:false,
								items:[
									new Ext.form.FormPanel({
										id:"AddDBForm",
										border:false,
										fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
										bodyPadding:"10 10 5 10",
										items:[
											new Ext.form.TextField({
												fieldLabel:"DB코드",
												name:"dbcode",
												emptyText:"등록할 DB정보를 호출할 고유코드를 입력하세요."
											}),
											new Ext.form.ComboBox({
												fieldLabel:"DB종류",
												name:"dbtype",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												listClass:"x-combo-list-small",
												store:new Ext.data.SimpleStore({
													fields:["value","display"],
													data:[["mysql","MYSQL"]]
												}),
												editable:false,
												mode:"local",
												displayField:"display",
												valueField:"value"
											}),
											new Ext.form.TextField({
												fieldLabel:"DB호스트",
												name:"dbhost",
												emptyText:"DB서버의 아이피 또는 도메인이나 localhost를 입력하세요."
											}),
											new Ext.form.TextField({
												fieldLabel:"접속아이디",
												name:"dbid",
												emptyText:"DB서버의 접속아이디를 입력하세요."
											}),
											new Ext.form.TextField({
												fieldLabel:"접속패스워드",
												name:"dbpassword",
												inputType:"password"
											}),
											new Ext.form.TextField({
												fieldLabel:"디비명",
												name:"dbname",
												emptyText:"DB명을 입력하세요. (에 : user_yourid)"
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.getCmp("AddDBForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=server&do=add",
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"DB서버를 추가중입니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListPanel").getStore().reload();
														Ext.getCmp("AddDBWindow").close();
													}});
												},
												failure:function(form,action) {
													if (action.result && action.result.errors.connect) {
														Ext.Msg.show({title:"에러",msg:action.result.errors.connect,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														return;
													}
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("AddDBWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 DB서버를&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"DB서버 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 DB서버를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var dbcodes = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										dbcodes[i] = checked[i].get("dbcode");
									}
									
									Ext.Msg.show({title:"확인",msg:"DB서버를 삭제하면 해당DB서버를 사용하는 모듈이 더이상 DB를 사용할 수 없습니다.<br />선택한 DB서버를 정말 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 DB서버를 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"server","do":"delete","dbcode":dbcodes.join(",")}
											});
										}
									}});
								}
							}]
						})
					}),
					'->',
					{xtype:"tbtext",text:"더블클릭 : 정보수정 / 우클릭 : 상세메뉴"}
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"DB코드",
						dataIndex:"dbcode",
						minWidth:100,
						flex:1
					},{
						header:"DB종류",
						dataIndex:"dbtype",
						width:100
					},{
						header:"DB호스트",
						dataIndex:"dbhost",
						width:200
					},{
						header:"접속아이디",
						dataIndex:"dbid",
						width:150
					},{
						header:"DB명",
						dataIndex:"dbname",
						width:150
					},{
						header:"상태",
						dataIndex:"status",
						width:100,
						renderer:function(value) {
							if (value == "TRUE") return '<div style="text-align:center; color:blue;">접속가능</div>';
							else return '<div style="text-align:center; color:red;">접속불가</div>';
						}
					}
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"server",get:"list",keyword:""}
					},
					remoteSort:false,
					sorters:[{property:"name",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					fields:["dbcode","dbtype","dbhost","dbid","dbname","status"]
				}),
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						new Ext.Window({
							id:"ModifyDBWindow",
							title:"DB서버수정",
							width:500,
							modal:true,
							resizable:false,
							items:[
								new Ext.form.FormPanel({
									id:"ModifyDBForm",
									border:false,
									fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
									bodyPadding:"10 10 5 10",
									items:[
										new Ext.form.TextField({
											fieldLabel:"DB코드",
											name:"dbcode",
											readOnly:true
										}),
										new Ext.form.ComboBox({
											fieldLabel:"DB종류",
											name:"dbtype",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											listClass:"x-combo-list-small",
											store:new Ext.data.SimpleStore({
												fields:["value","display"],
												data:[["mysql","MYSQL"]]
											}),
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value"
										}),
										new Ext.form.TextField({
											fieldLabel:"DB호스트",
											name:"dbhost",
											emptyText:"DB서버의 아이피 또는 도메인이나 localhost를 입력하세요."
										}),
										new Ext.form.TextField({
											fieldLabel:"접속아이디",
											name:"dbid",
											emptyText:"DB서버의 접속아이디를 입력하세요."
										}),
										new Ext.form.TextField({
											fieldLabel:"접속패스워드",
											name:"dbpassword",
											inputType:"password"
										}),
										new Ext.form.TextField({
											fieldLabel:"디비명",
											name:"dbname",
											emptyText:"DB명을 입력하세요. (에 : user_yourid)"
										})
									]
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									handler:function() {
										Ext.getCmp("ModifyDBForm").getForm().submit({
											url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=server&do=modify",
											submitEmptyText:false,
											waitTitle:"잠시만 기다려주십시오.",
											waitMsg:"DB서버를 수정중입니다.",
											success:function(form,action) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													Ext.getCmp("ListPanel").getStore().reload();
													Ext.getCmp("ModifyDBWindow").close();
												}});
											},
											failure:function(form,action) {
												if (action.result && action.result.errors.connect) {
													Ext.Msg.show({title:"에러",msg:action.result.errors.connect,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													return;
												}
												Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										});
									}
								}),
								new Ext.Button({
									text:"취소",
									handler:function() {
										Ext.getCmp("ModifyDBWindow").close();
									}
								})
							],
							listeners:{show:{fn:function() {
								Ext.getCmp("ModifyDBForm").getForm().load({
									url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php?action=server&get=data&dbcode="+record.data.dbcode,
									waitTitle:"잠시만 기다려주십시오.",
									waitMsg:"데이터를 로딩중입니다.",
									failure:function(form,action) {
										Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								});
							}}}
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