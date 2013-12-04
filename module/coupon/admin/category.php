<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"category",get:"list"}
		},
		remoteSort:false,
		sorters:[{property:"sort",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","category",{name:"sort",type:"int"}]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.category+'</b>');
		
		menu.add({
			text:"카테고리삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"카테고리를 삭제하면 해당 카테고리의 모든 쿠폰이 삭제됩니다.<br />카테고리를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("카테고리를 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().remove(record);
										for (var i=0, loop=Ext.getCmp("ListPanel").getStore().getCount();i<loop;i++) {
											Ext.getCmp("ListPanel").getStore().getAt(i).set("sort",i);
										}
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"category","do":"delete","idx":record.data.idx}
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
		title:"카테고리관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"카테고리추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_category_add.png",
				handler:function() {
					new Ext.Window({
						id:"CategoryAddWindow",
						title:"카테고리추가",
						width:350,
						modal:true,
						resizable:false,
						layout:"fit",
						items:[
							new Ext.form.FormPanel({
								id:"CategoryAddForm",
								border:false,
								fieldDefaults:{labelWidth:80,labelAlign:"right",anchor:"100%",allowBlank:false},
								bodyPadding:"10 10 5 10",
								items:[
									new Ext.form.TextField({
										fieldLabel:"카테고리명",
										name:"category",
										width:200
									})
								]
							})
						],
						buttons:[
							new Ext.Button({
								text:"확인",
								handler:function() {
									Ext.getCmp("CategoryAddForm").getForm().submit({
										url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php?action=category&do=add",
										submitEmptyText:false,
										waitTitle:"잠시만 기다려주십시오.",
										waitMsg:"항목을 추가하고 있습니다.",
										success:function(form,action) {
											Ext.Msg.show({title:"확인",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												Ext.getCmp("ListPanel").getStore().add({idx:action.result.idx,category:action.result.category,sort:action.result.sort});
												if (button == "yes") {
													Ext.getCmp("CategoryAddForm").getForm().reset();
													Ext.getCmp("CategoryAddForm").getForm().findField("category").focus(true,100);
												} else {
													Ext.getCmp("CategoryAddWindow").close();
												}
											}});
										},
										failure:function(form,action) {
											if (action.result) {
												if (action.result.errors.category) {
													Ext.Msg.show({title:"에러",msg:action.result.errors.category,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													return false;
												}
											}
											Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									});
								}
							}),
							new Ext.Button({
								text:"취소",
								handler:function() {
									Ext.getCmp("CategoryAddWindow").close();
								}
							})
						],
						listeners:{
							show:{fn:function() {
								Ext.getCmp("CategoryAddForm").getForm().findField("category").focus(true,100);
							}}
						}
					}).show();
				}
			}),
			'-',
			{xtype:"tbtext",text:"순서변경"},
			new Ext.Button({
				text:"위로",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_arrow_up.png",
				handler:function() {
					var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();

					if (checked.length == 0) {
						Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						return false;
					}
					
					var selecter = new Array();
					
					for (var i=0, loop=checked.length;i<loop;i++) {
						selecter.push(checked[i].get("sort")-1);
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
					
					for (var i=0, loop=selecter.length;i<loop;i++) {
						Ext.getCmp("ListPanel").getSelectionModel().select(selecter[i],i!=0);
					}
				}
			}),
			new Ext.Button({
				text:"아래로",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_arrow_down.png",
				handler:function() {
					var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();

					if (checked.length == 0) {
						Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						return false;
					}
					
					var selecter = new Array();
					
					for (var i=0, loop=checked.length;i<loop;i++) {
						selecter.push(checked[i].get("sort")+1);
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
					
					for (var i=0, loop=selecter.length;i<loop;i++) {
						Ext.getCmp("ListPanel").getSelectionModel().select(selecter[i],i!=0);
					}
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 카테고리를&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 카테고리 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"카테고리를 삭제하면 해당 카테고리의 모든 쿠폰이 삭제됩니다.<br />카테고리를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("카테고리를 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().remove(checked);
													for (var i=0, loop=Ext.getCmp("ListPanel").getStore().getCount();i<loop;i++) {
														Ext.getCmp("ListPanel").getStore().getAt(i).set("sort",i);
													}
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"category","do":"delete","idx":idxs.join(",")}
									});
								}
							}});
						}
					}]
				})
			}),
			'-',
			new Ext.Button({
				text:"변경사항저장",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_disk.png",
				handler:function() {
					var data = new Array();
					for (var i=0, loop=Ext.getCmp("ListPanel").getStore().getCount();i<loop;i++) {
						data[i] = Ext.getCmp("ListPanel").getStore().getAt(i).data;
					}
					
					data = Ext.JSON.encode(data);
					
					Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
					Ext.Ajax.request({
						url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php",
						success:function(response) {
							var data = Ext.JSON.decode(response.responseText);
							if (data.success == true) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
								Ext.getCmp("ListPanel").getStore().commitChanges();
							} else {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							}
						},
						failure:function() {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						},
						params:{"action":"category","do":"modify","data":data}
					});
				}
			}),
			'->',
			{xtype:"tbtext",text:"목록더블클릭 : 수정 / 우클릭 : 상세메뉴"}
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"카테고리",
						dataIndex:"category",
						flex:1,
						sortable:false,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						dataIndex:"sort",
						hidden:true,
						hideable:false
					}
				],
				store:store,
				columnLines:true,
				selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
				plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
				listeners:{
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>