<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var AddCategory = function(depth) {
		var parent = Ext.getCmp("Category"+depth).getStore().getProxy().extraParams.parent;
		
		new Ext.Window({
			id:"CategoryAddWindow",
			title:depth+"차카테고리추가",
			width:350,
			modal:true,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"CategoryAddForm",
					border:false,
					fieldDefaults:{labelWidth:60,labelAlign:"right",anchor:"100%",allowBlank:false},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.TextField({
							fieldLabel:"카테고리명",
							name:"title",
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
							url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=category&do=add&parent="+parent,
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"항목을 추가하고 있습니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"확인",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
									Ext.getCmp("Category"+depth).getStore().add({idx:action.result.idx,title:action.result.title,sort:action.result.sort});
									if (button == "yes") {
										Ext.getCmp("CategoryAddForm").getForm().reset();
										Ext.getCmp("CategoryAddForm").getForm().findField("title").focus(true,100);
									} else {
										Ext.getCmp("CategoryAddWindow").close();
									}
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.title) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.title,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
					Ext.getCmp("CategoryAddForm").getForm().findField("title").focus(true,100);
				}}
			}
		}).show();
	}
	
	var RemoveCategory = function(depth) {
		var parent = Ext.getCmp("Category"+depth).getStore().getProxy().extraParams.parent;
		
		var checked = Ext.getCmp("Category"+depth).getSelectionModel().getSelection();
		if (checked.length == 0) {
			Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			return false;
		}

		var idxs = new Array();
		for (var i=0, loop=checked.length;i<loop;i++) {
			idxs.push(checked[i].get("idx"));
		}
		
		if (depth == 3) {
			var msg = "선택항목을 정말 삭제하시겠습니까?";
		} else {
			var msg = "선택항목을 삭제하게 되면, 해당 항목의 하위항목도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?";
		}
		
		Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
			if (button == "yes") {
				Ext.Msg.wait("항목을 삭제하고 있습니다.","잠시만 기다려주십시오.");
				Ext.Ajax.request({
					url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
					success:function(response) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success == true) {
							Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								Ext.getCmp("Category"+depth).getStore().remove(checked);
								for (var i=0, loop=Ext.getCmp("Category"+depth).getStore().getCount();i<loop;i++) {
									Ext.getCmp("Category"+depth).getStore().getAt(i).set("sort",i);
								}
							}});
						} else {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						}
					},
					failure:function() {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					},
					params:{"action":"category","do":"delete","idx":idxs.join(","),"parent":parent}
				});
			}
		}});
	}
	
	var SortCategory = function(depth,dir) {
		var checked = Ext.getCmp("Category"+depth).getSelectionModel().getSelection();

		if (checked.length == 0) {
			Ext.Msg.show({title:"에러",msg:"이동할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			return false;
		}
		
		var selecter = new Array();

		if (dir == "UP") {
			for (var i=0, loop=checked.length;i<loop;i++) {
				selecter.push(checked[i].get("sort")-1);
			}
			
			for (var i=0, loop=checked.length;i<loop;i++) {
				var sort = checked[i].get("sort");
				if (sort != 0) {
					Ext.getCmp("Category"+depth).getStore().getAt(sort).set("sort",sort-1);
					Ext.getCmp("Category"+depth).getStore().getAt(sort-1).set("sort",sort);
					Ext.getCmp("Category"+depth).getStore().sort("sort","ASC");
				} else {
					return false;
				}
			}
		} else {
			for (var i=0, loop=checked.length;i<loop;i++) {
				selecter.push(checked[i].get("sort")+1);
			}
			
			for (var i=checked.length-1;i>=0;i--) {
				var sort = checked[i].get("sort");
				if (sort != Ext.getCmp("Category"+depth).getStore().getCount()-1) {
					Ext.getCmp("Category"+depth).getStore().getAt(sort).set("sort",sort+1);
					Ext.getCmp("Category"+depth).getStore().getAt(sort+1).set("sort",sort);
					Ext.getCmp("Category"+depth).getStore().sort("sort","ASC");
				} else {
					return false;
				}
			}
		}
		
		for (var i=0, loop=selecter.length;i<loop;i++) {
			Ext.getCmp("Category"+depth).getSelectionModel().select(selecter[i],i!=0);
		}
	}
	
	var SaveCategory = function(depth,is_reload) {
		is_reload = is_reload === true;
		var update = Ext.getCmp("Category"+depth).getStore().getUpdatedRecords();
		
		if (update.length > 0) {
			var data = new Array();
			for (var i=0, loop=update.length;i<loop;i++) {
				data.push(update[i].data);
			}
			data = Ext.JSON.encode(data);
			
			Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
			Ext.Ajax.request({
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
				success:function(response) {
					var data = Ext.JSON.decode(response.responseText);
					if (data.success == true) {
						Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
						Ext.getCmp("Category"+depth).getStore().commitChanges();
						if (is_reload == true) {
							Ext.getCmp("Category"+depth).getStore().reload();
						}
					} else {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					}
				},
				failure:function() {
					Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
				},
				params:{"action":"category","do":"modify","data":data}
			});
		} else {
			Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
		}
	}
	
	var BeforeLoadCategory = function(depth) {
		var update = Ext.getCmp("Category"+depth).getStore().getUpdatedRecords();
		if (update.length > 0) {
			Ext.Msg.show({title:"안내",msg:depth+"차카테고리목록에서 변경된 사항이 있습니다.<br />변경된 사항을 저장하고 데이터를 로딩하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "yes") {
					SaveCategory(depth,true);
				} else {
					Ext.getCmp("Category"+depth).getStore().commitChanges();
					Ext.getCmp("Category"+depth).getStore().reload();
				}
			}});
			return false;
		} else {
			return true;
		}
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"카테고리관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.Panel({
				border:false,
				layout:{type:"hbox",align:"stretch"},
				items:[
					new Ext.grid.GridPanel({
						id:"Category1",
						title:"1차카테고리",
						margin:"5 5 5 5",
						flex:1,
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_add.png",
								handler:function() {
									AddCategory(1);
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_delete.png",
								handler:function() {
									RemoveCategory(1);
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
								handler:function() {
									SaveCategory(1);
								}
							})
						],
						columns:[{
							header:"카테고리명",
							dataIndex:"title",
							sortable:false,
							menuDisabled:true,
							resizable:false,
							flex:1,
							editor:new Ext.form.TextField({selectOnFocus:true})
						}],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"category",parent:"0"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["idx","title",{name:"sort",type:"int"}],
							listeners:{
								beforeload:{fn:function(store) {
									return BeforeLoadCategory(1);
								}}
							}
						}),
						bbar:[
							{xtype:"tbtext",text:"순서변경 :"},
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
								handler:function() {
									SortCategory(1,"UP");
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
								handler:function() {
									SortCategory(1,"DOWN");
								}
							}),
							'->',
							{xtype:"tbtext",text:"목록더블클릭 : 수정"}
						],
						selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
						plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
						listeners:{
							select:{fn:function(selModel,record,e) {
								if (selModel.getSelection().length <= 1 && record.data.idx != Ext.getCmp("Category2").getStore().getProxy().extraParams.parent) {
									Ext.getCmp("Category2").getStore().getProxy().setExtraParam("parent",record.data.idx);
									Ext.getCmp("Category2").getStore().reload();
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category3").getStore().reload();
								} else if (selModel.getSelection().length > 1) {
									Ext.getCmp("Category2").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category2").getStore().reload();
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category3").getStore().reload();
								}
							}},
							selectionchange:{fn:function(selModel,selected) {
								if (selModel.getSelection().length == 1 && selModel.getSelection().shift().get("idx") != Ext.getCmp("Category2").getStore().getProxy().extraParams.parent) {
									Ext.getCmp("Category2").getStore().getProxy().setExtraParam("parent",selModel.getSelection().shift().get("idx"));
									Ext.getCmp("Category2").getStore().reload();
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category3").getStore().reload();
								} else if (selModel.getSelection().length > 1) {
									Ext.getCmp("Category2").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category2").getStore().reload();
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent","-1");
									Ext.getCmp("Category3").getStore().reload();
								}
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"Category2",
						title:"2차카테고리",
						margin:"5 5 5 0",
						flex:1,
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_add.png",
								handler:function() {
									AddCategory(2);
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_delete.png",
								handler:function() {
									RemoveCategory(2);
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
								handler:function() {
									SaveCategory(2);
								}
							})
						],
						columns:[{
							header:"카테고리명",
							dataIndex:"title",
							sortable:false,
							menuDisabled:true,
							resizable:false,
							flex:1,
							editor:new Ext.form.TextField({selectOnFocus:true})
						}],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"category",parent:"-1"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["idx","title",{name:"sort",type:"int"}],
							listeners:{
								beforeload:{fn:function(store) {
									return BeforeLoadCategory(2);
								}},
								load:{fn:function(store) {
									if (store.getProxy().extraParams.parent == "-1") {
										Ext.getCmp("Category2").disable();
										Ext.getCmp("Category2").setTitle("2차카테고리 (1차카테고리 목록에서 2차카테고리을 볼 카테고리을 선택하세요.)");
									} else {
										Ext.getCmp("Category2").enable();
										Ext.getCmp("Category2").setTitle("2차카테고리");
									}
								}}
							}
						}),
						bbar:[
							{xtype:"tbtext",text:"순서변경 :"},
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
								handler:function() {
									SortCategory(2,"UP");
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
								handler:function() {
									SortCategory(2,"DOWN");
								}
							}),
							'->',
							{xtype:"tbtext",text:"목록더블클릭 : 수정"}
						],
						selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
						plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
						listeners:{
							select:{fn:function(selModel,record,e) {
								if (selModel.getSelection().length <= 1 && record.data.idx != Ext.getCmp("Category3").getStore().getProxy().extraParams.parent) {
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent",record.data.idx);
									Ext.getCmp("Category3").getStore().reload();
								}
							}},
							selectionchange:{fn:function(selModel,selected) {
								if (selModel.getSelection().length == 1 && selModel.getSelection().shift().get("idx") != Ext.getCmp("Category3").getStore().getProxy().extraParams.parent) {
									Ext.getCmp("Category3").getStore().getProxy().setExtraParam("parent",selModel.getSelection().shift().get("idx"));
									Ext.getCmp("Category3").getStore().reload();
								}
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"Category3",
						title:"3차카테고리",
						margin:"5 5 5 0",
						flex:1,
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_add.png",
								handler:function() {
									AddCategory(3);
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_category_delete.png",
								handler:function() {
									RemoveCategory(3);
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
								handler:function() {
									SaveCategory(3);
								}
							})
						],
						columns:[{
							header:"카테고리명",
							dataIndex:"title",
							sortable:false,
							menuDisabled:true,
							resizable:false,
							flex:1,
							editor:new Ext.form.TextField({selectOnFocus:true})
						}],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"category",parent:"-1"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["idx","title",{name:"sort",type:"int"}],
							listeners:{
								beforeload:{fn:function(store) {
									return BeforeLoadCategory(3);
								}},
								load:{fn:function(store) {
									if (store.getProxy().extraParams.parent == "-1") {
										Ext.getCmp("Category3").disable();
										Ext.getCmp("Category3").setTitle("3차카테고리 (2차카테고리 목록에서 3차카테고리을 볼 카테고리을 선택하세요.)");
									} else {
										Ext.getCmp("Category3").enable();
										Ext.getCmp("Category3").setTitle("3차카테고리");
									}
								}}
							}
						}),
						bbar:[
							{xtype:"tbtext",text:"순서변경 :"},
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
								handler:function() {
									SortCategory(3,"UP");
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
								handler:function() {
									SortCategory(3,"DOWN");
								}
							}),
							'->',
							{xtype:"tbtext",text:"목록더블클릭 : 수정"}
						],
						selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
						plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})]
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>