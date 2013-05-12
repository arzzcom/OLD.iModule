<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var AddUniversity = function(depth) {
		var parent = Ext.getCmp("University"+depth).getStore().getProxy().extraParams.parent;
		
		new Ext.Window({
			id:"UniversityAddWindow",
			title:(depth == 1 ? "지역추가" : "대학교추가"),
			width:350,
			modal:true,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"UniversityAddForm",
					border:false,
					fieldDefaults:{labelWidth:55,labelAlign:"right",anchor:"100%",allowBlank:false},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.TextField({
							fieldLabel:(depth == 1 ? "지역명" : "대학교명"),
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
						Ext.getCmp("UniversityAddForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=university&do=add&parent="+parent,
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"항목을 추가하고 있습니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"확인",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
									Ext.getCmp("University"+depth).getStore().add({idx:action.result.idx,title:action.result.title,sort:action.result.sort});
									if (button == "yes") {
										Ext.getCmp("UniversityAddWindow").getForm().reset();
										Ext.getCmp("UniversityAddForm").getForm().findField("title").focus(true,100);
									} else {
										Ext.getCmp("UniversityAddWindow").close();
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
						Ext.getCmp("UniversityAddWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					Ext.getCmp("UniversityAddForm").getForm().findField("title").focus(true,100);
				}}
			}
		}).show();
	}
	
	var RemoveUniversity = function(depth) {
		var parent = Ext.getCmp("University"+depth).getStore().getProxy().extraParams.parent;
		
		var checked = Ext.getCmp("University"+depth).getSelectionModel().getSelection();
		if (checked.length == 0) {
			Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			return false;
		}

		var idxs = new Array();
		for (var i=0, loop=checked.length;i<loop;i++) {
			idxs.push(checked[i].get("idx"));
		}
		
		if (depth == 2) {
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
								Ext.getCmp("University"+depth).getStore().remove(checked);
								for (var i=0, loop=Ext.getCmp("University"+depth).getStore().getCount();i<loop;i++) {
									Ext.getCmp("University"+depth).getStore().getAt(i).set("sort",i);
								}
							}});
						} else {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						}
					},
					failure:function() {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					},
					params:{"action":"university","do":"delete","idx":idxs.join(","),"parent":parent}
				});
			}
		}});
	}
	
	var SortUniversity = function(depth,dir) {
		var checked = Ext.getCmp("University"+depth).getSelectionModel().getSelection();

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
					Ext.getCmp("University"+depth).getStore().getAt(sort).set("sort",sort-1);
					Ext.getCmp("University"+depth).getStore().getAt(sort-1).set("sort",sort);
					Ext.getCmp("University"+depth).getStore().sort("sort","ASC");
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
				if (sort != Ext.getCmp("University"+depth).getStore().getCount()-1) {
					Ext.getCmp("University"+depth).getStore().getAt(sort).set("sort",sort+1);
					Ext.getCmp("University"+depth).getStore().getAt(sort+1).set("sort",sort);
					Ext.getCmp("University"+depth).getStore().sort("sort","ASC");
				} else {
					return false;
				}
			}
		}
		
		for (var i=0, loop=selecter.length;i<loop;i++) {
			Ext.getCmp("University"+depth).getSelectionModel().select(selecter[i],i!=0);
		}
	}
	
	var SaveUniversity = function(depth,is_reload) {
		is_reload = is_reload === true;
		var update = Ext.getCmp("University"+depth).getStore().getUpdatedRecords();
		
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
						Ext.getCmp("University"+depth).getStore().commitChanges();
						if (is_reload == true) {
							Ext.getCmp("University"+depth).getStore().reload();
						}
					} else {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					}
				},
				failure:function() {
					Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
				},
				params:{"action":"university","do":"modify","data":data}
			});
		} else {
			Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
		}
	}
	
	var BeforeLoadUniversity = function(depth) {
		var update = Ext.getCmp("University"+depth).getStore().getUpdatedRecords();
		if (update.length > 0) {
			Ext.Msg.show({title:"안내",msg:(depth == 1 ? "지역" : "대학교")+"목록에서 변경된 사항이 있습니다.<br />변경된 사항을 저장하고 데이터를 로딩하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "yes") {
					SaveUniversity(depth,true);
				} else {
					Ext.getCmp("University"+depth).getStore().commitChanges();
					Ext.getCmp("University"+depth).getStore().reload();
				}
			}});
			return false;
		} else {
			return true;
		}
	}

	var AddSubway = function(depth) {
		var parent = Ext.getCmp("Subway"+depth).getStore().getProxy().extraParams.parent;
		
		new Ext.Window({
			id:"SubwayAddWindow",
			title:(depth == 1 ? "노선추가" : "지하철역추가"),
			width:350,
			modal:true,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"SubwayAddForm",
					border:false,
					fieldDefaults:{labelWidth:65,labelAlign:"right",anchor:"100%",allowBlank:false},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.TextField({
							fieldLabel:(depth == 1 ? "노선명" : "지하철역명"),
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
						Ext.getCmp("SubwayAddForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=subway&do=add&parent="+parent,
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"항목을 추가하고 있습니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"확인",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
									Ext.getCmp("Subway"+depth).getStore().add({idx:action.result.idx,title:action.result.title,sort:action.result.sort});
									if (button == "yes") {
										Ext.getCmp("SubwayAddWindow").getForm().reset();
										Ext.getCmp("SubwayAddForm").getForm().findField("title").focus(true,100);
									} else {
										Ext.getCmp("SubwayAddWindow").close();
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
						Ext.getCmp("SubwayAddWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					Ext.getCmp("SubwayAddForm").getForm().findField("title").focus(true,100);
				}}
			}
		}).show();
	}
	
	var RemoveSubway = function(depth) {
		var parent = Ext.getCmp("Subway"+depth).getStore().getProxy().extraParams.parent;
		
		var checked = Ext.getCmp("Subway"+depth).getSelectionModel().getSelection();
		if (checked.length == 0) {
			Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			return false;
		}

		var idxs = new Array();
		for (var i=0, loop=checked.length;i<loop;i++) {
			idxs.push(checked[i].get("idx"));
		}
		
		if (depth == 2) {
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
								Ext.getCmp("Subway"+depth).getStore().remove(checked);
								for (var i=0, loop=Ext.getCmp("Subway"+depth).getStore().getCount();i<loop;i++) {
									Ext.getCmp("Subway"+depth).getStore().getAt(i).set("sort",i);
								}
							}});
						} else {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						}
					},
					failure:function() {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					},
					params:{"action":"subway","do":"delete","idx":idxs.join(","),"parent":parent}
				});
			}
		}});
	}
	
	var SortSubway = function(depth,dir) {
		var checked = Ext.getCmp("Subway"+depth).getSelectionModel().getSelection();

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
					Ext.getCmp("Subway"+depth).getStore().getAt(sort).set("sort",sort-1);
					Ext.getCmp("Subway"+depth).getStore().getAt(sort-1).set("sort",sort);
					Ext.getCmp("Subway"+depth).getStore().sort("sort","ASC");
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
				if (sort != Ext.getCmp("Subway"+depth).getStore().getCount()-1) {
					Ext.getCmp("Subway"+depth).getStore().getAt(sort).set("sort",sort+1);
					Ext.getCmp("Subway"+depth).getStore().getAt(sort+1).set("sort",sort);
					Ext.getCmp("Subway"+depth).getStore().sort("sort","ASC");
				} else {
					return false;
				}
			}
		}
		
		for (var i=0, loop=selecter.length;i<loop;i++) {
			Ext.getCmp("Subway"+depth).getSelectionModel().select(selecter[i],i!=0);
		}
	}
	
	var SaveSubway = function(depth,is_reload) {
		is_reload = is_reload === true;
		var update = Ext.getCmp("Subway"+depth).getStore().getUpdatedRecords();
		
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
						Ext.getCmp("Subway"+depth).getStore().commitChanges();
						if (is_reload == true) {
							Ext.getCmp("Subway"+depth).getStore().reload();
						}
					} else {
						Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
					}
				},
				failure:function() {
					Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
				},
				params:{"action":"subway","do":"modify","data":data}
			});
		} else {
			Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
		}
	}
	
	var BeforeLoadSubway = function(depth) {
		var update = Ext.getCmp("Subway"+depth).getStore().getUpdatedRecords();
		if (update.length > 0) {
			Ext.Msg.show({title:"안내",msg:(depth == 1 ? "노선" : "지하철역")+"목록에서 변경된 사항이 있습니다.<br />변경된 사항을 저장하고 데이터를 로딩하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "yes") {
					SaveSubway(depth,true);
				} else {
					Ext.getCmp("Subway"+depth).getStore().commitChanges();
					Ext.getCmp("Subway"+depth).getStore().reload();
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
		title:"매물등록기초자료관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.TabPanel({
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.Panel({
						title:"대학정보관리",
						border:false,
						layout:{type:"hbox",align:"stretch"},
						items:[
							new Ext.grid.GridPanel({
								id:"University1",
								title:"지역",
								margin:"5 5 5 5",
								flex:1,
								tbar:[
									new Ext.Button({
										text:"지역추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_add.png",
										handler:function() {
											AddUniversity(1);
										}
									}),
									new Ext.Button({
										text:"지역삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_delete.png",
										handler:function() {
											RemoveUniversity(1);
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											SaveUniversity(1);
										}
									})
								],
								columns:[{
									header:"지역명",
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
										extraParams:{action:"university",parent:"0"}
									},
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									autoLoad:true,
									pageSize:50,
									fields:["idx","title",{name:"sort",type:"int"}],
									listeners:{
										beforeload:{fn:function(store) {
											return BeforeLoadUniversity(1);
										}}
									}
								}),
								bbar:[
									{xtype:"tbtext",text:"순서변경 :"},
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											SortUniversity(1,"UP");
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											SortUniversity(1,"DOWN");
										}
									}),
									'->',
									{xtype:"tbtext",text:"목록더블클릭 : 수정"}
								],
								selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
								plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
								listeners:{
									select:{fn:function(selModel,record,e) {
										if (selModel.getSelection().length <= 1 && record.data.idx != Ext.getCmp("University2").getStore().getProxy().extraParams.parent) {
											Ext.getCmp("University2").getStore().getProxy().setExtraParam("parent",record.data.idx);
											Ext.getCmp("University2").getStore().reload();
										} else if (selModel.getSelection().length > 1) {
											Ext.getCmp("University2").getStore().getProxy().setExtraParam("parent","-1");
											Ext.getCmp("University2").getStore().reload();
										}
									}},
									selectionchange:{fn:function(selModel,selected) {
										if (selModel.getSelection().length == 1 && selModel.getSelection().shift().get("idx") != Ext.getCmp("University2").getStore().getProxy().extraParams.parent) {
											Ext.getCmp("University2").getStore().getProxy().setExtraParam("parent",selModel.getSelection().shift().get("idx"));
											Ext.getCmp("University2").getStore().reload();
										} else if (selModel.getSelection().length > 1) {
											Ext.getCmp("University2").getStore().getProxy().setExtraParam("parent","-1");
											Ext.getCmp("University2").getStore().reload();
										}
									}}
								}
							}),
							new Ext.grid.GridPanel({
								id:"University2",
								title:"대학교",
								margin:"5 5 5 0",
								flex:1,
								tbar:[
									new Ext.Button({
										text:"대학교추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_add.png",
										handler:function() {
											AddUniversity(2);
										}
									}),
									new Ext.Button({
										text:"대학교삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_delete.png",
										handler:function() {
											RemoveUniversity(2);
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											SaveUniversity(2);
										}
									})
								],
								columns:[{
									header:"대학교명",
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
										extraParams:{action:"university",parent:"-1"}
									},
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									autoLoad:true,
									pageSize:50,
									fields:["idx","title",{name:"sort",type:"int"}],
									listeners:{
										beforeload:{fn:function(store) {
											return BeforeLoadUniversity(2);
										}},
										load:{fn:function(store) {
											if (store.getProxy().extraParams.parent == "-1") {
												Ext.getCmp("University2").disable();
												Ext.getCmp("University2").setTitle("대학교 (지역목록에서 대학교를 볼 지역을 선택하세요.)");
											} else {
												Ext.getCmp("University2").enable();
												Ext.getCmp("University2").setTitle("대학교");
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
											SortUniversity(2,"UP");
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											SortUniversity(2,"DOWN");
										}
									}),
									'->',
									{xtype:"tbtext",text:"목록더블클릭 : 수정"}
								],
								selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
								plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})]
							})
						]
					}),
					new Ext.Panel({
						title:"지하철역정보관리",
						border:false,
						layout:{type:"hbox",align:"stretch"},
						items:[
							new Ext.grid.GridPanel({
								id:"Subway1",
								title:"노선",
								margin:"5 5 5 5",
								flex:1,
								tbar:[
									new Ext.Button({
										text:"노선추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_subway_add.png",
										handler:function() {
											AddSubway(1);
										}
									}),
									new Ext.Button({
										text:"노선삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_subway_delete.png",
										handler:function() {
											RemoveSubway(1);
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											SaveSubway(1);
										}
									})
								],
								columns:[{
									header:"노선명",
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
										extraParams:{action:"subway",parent:"0"}
									},
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									autoLoad:true,
									pageSize:50,
									fields:["idx","title",{name:"sort",type:"int"}],
									listeners:{
										beforeload:{fn:function(store) {
											return BeforeLoadSubway(1);
										}}
									}
								}),
								bbar:[
									{xtype:"tbtext",text:"순서변경 :"},
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											SortSubway(1,"UP");
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											SortSubway(1,"DOWN");
										}
									}),
									'->',
									{xtype:"tbtext",text:"목록더블클릭 : 수정"}
								],
								selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
								plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
								listeners:{
									select:{fn:function(selModel,record,e) {
										if (selModel.getSelection().length <= 1 && record.data.idx != Ext.getCmp("Subway2").getStore().getProxy().extraParams.parent) {
											Ext.getCmp("Subway2").getStore().getProxy().setExtraParam("parent",record.data.idx);
											Ext.getCmp("Subway2").getStore().reload();
										} else if (selModel.getSelection().length > 1) {
											Ext.getCmp("Subway2").getStore().getProxy().setExtraParam("parent","-1");
											Ext.getCmp("Subway2").getStore().reload();
										}
									}},
									selectionchange:{fn:function(selModel,selected) {
										if (selModel.getSelection().length == 1 && selModel.getSelection().shift().get("idx") != Ext.getCmp("Subway2").getStore().getProxy().extraParams.parent) {
											Ext.getCmp("Subway2").getStore().getProxy().setExtraParam("parent",selModel.getSelection().shift().get("idx"));
											Ext.getCmp("Subway2").getStore().reload();
										} else if (selModel.getSelection().length > 1) {
											Ext.getCmp("Subway2").getStore().getProxy().setExtraParam("parent","-1");
											Ext.getCmp("Subway2").getStore().reload();
										}
									}}
								}
							}),
							new Ext.grid.GridPanel({
								id:"Subway2",
								title:"지하철역",
								margin:"5 5 5 0",
								flex:1,
								tbar:[
									new Ext.Button({
										text:"지하철역추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_pin_add.png",
										handler:function() {
											AddSubway(2);
										}
									}),
									new Ext.Button({
										text:"지하철역삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_pin_delete.png",
										handler:function() {
											RemoveSubway(2);
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											SaveSubway(2);
										}
									})
								],
								columns:[{
									header:"지하철역명",
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
										extraParams:{action:"subway",parent:"-1"}
									},
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									autoLoad:true,
									pageSize:50,
									fields:["idx","title",{name:"sort",type:"int"}],
									listeners:{
										beforeload:{fn:function(store) {
											return BeforeLoadSubway(2);
										}},
										load:{fn:function(store) {
											if (store.getProxy().extraParams.parent == "-1") {
												Ext.getCmp("Subway2").disable();
												Ext.getCmp("Subway2").setTitle("지하철역 (노선목록에서 지하철역를 볼 노선을 선택하세요.)");
											} else {
												Ext.getCmp("Subway2").enable();
												Ext.getCmp("Subway2").setTitle("지하철역");
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
											SortSubway(2,"UP");
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											SortSubway(2,"DOWN");
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
					/*
					new Ext.Panel({
						title:"대학정보관리",
						layout:"hbox",
						border:false,
						layoutConfig:{align:"stretch"},
						items:[
							new Ext.grid.EditorGridPanel({
								id:"University1",
								title:"지역구분",
								margins:"5 5 5 5",
								tbar:[
									new Ext.Button({
										text:"지역구분추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_add.png",
										handler:function() {
											new Ext.Window({
												id:"UniversityAddWindow",
												title:"지역구분추가",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"UniversityAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"지역구분",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 지역구분을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("University1").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("UniversityAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("UniversityAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=university&do=add&parent=0",waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("UniversityAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"지역구분삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_delete.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"선택지역구분을 삭제하게 되면 해당 지역구분의 대학교도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("University1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"university","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("University1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"university","do":"modify","data":GetGridData(Ext.getCmp("University1"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("University1").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("University1").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("University1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University1").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("University1").getStore().getCount()-1) {
													Ext.getCmp("University1").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("University1").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("University1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University1").selModel.selectRow(selecter[i]);
											}
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"idx",
										hidden:true,
										hideable:false
									},{
										id:"title1",
										header:"지역구분명",
										dataIndex:"title",
										sortable:false,
										menuDisabled:true,
										resizable:false,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										dataIndex:"sort",
										hidden:true,
										hideable:false
									}
								]),
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"university",parent:"0"},
									listeners:{load:{fn:function() {
										Ext.getCmp("University2").getStore().baseParams.parent = "-1";
										Ext.getCmp("University2").getStore().load();
									}}}
								}),
								autoExpandColumn:"title1",
								flex:1,
								listeners:{rowclick:{fn:function(grid,idx,e) {
									Ext.getCmp("University2").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
									Ext.getCmp("University2").getStore().load();
								}}}
							}),
							new Ext.grid.EditorGridPanel({
								id:"University2",
								title:"대학교",
								margins:"5 5 5 0",
								tbar:[
									new Ext.Button({
										text:"대학교추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_add.png",
										handler:function() {
											if (Ext.getCmp("University2").getStore().baseParams.parent == "-1") {
												Ext.Msg.show({title:"에러",msg:"지역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
											
											var parent = Ext.getCmp("University1").getStore().find("idx",Ext.getCmp("University2").getStore().baseParams.parent,false,false);
											
											new Ext.Window({
												id:"UniversityAddWindow",
												title:"대학교추가 ("+Ext.getCmp("University1").getStore().getAt(parent).get("title")+")",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"UniversityAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"대학교명",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 대학교를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("University2").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("UniversityAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("UniversityAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=university&do=add&parent="+Ext.getCmp("University2").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("UniversityAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"대학교삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_delete.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 대학교명을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("University1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"university","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("University2").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"university","do":"modify","data":GetGridData(Ext.getCmp("University2"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 대학교를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("University2").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("University2").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("University2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University2").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 대학교를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("University2").getStore().getCount()-1) {
													Ext.getCmp("University2").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("University2").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("University2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University2").selModel.selectRow(selecter[i]);
											}
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"idx",
										hidden:true,
										hideable:false
									},{
										id:"title2",
										header:"대학명",
										dataIndex:"title",
										sortable:false,
										menuDisabled:true,
										resizable:false,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										dataIndex:"sort",
										hidden:true,
										hideable:false
									}
								]),
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"university",parent:"-1"},
									listeners:{load:{fn:function(store) {
										var parent = Ext.getCmp("University1").getStore().find("idx",store.baseParams.parent,false,false);
										if (parent != -1) {
											Ext.getCmp("University2").setTitle("대학교 ("+Ext.getCmp("University1").getStore().getAt(parent).get("title")+")");
										}
									}}}
								}),
								autoExpandColumn:"title2",
								flex:1
							})
						]
					}),
					new Ext.Panel({
						title:"지하철정보관리",
						layout:"hbox",
						border:false,
						layoutConfig:{align:"stretch"},
						items:[
							new Ext.grid.EditorGridPanel({
								id:"Subway1",
								title:"노선구분",
								margins:"5 5 5 5",
								tbar:[
									new Ext.Button({
										text:"노선구분추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_add.png",
										handler:function() {
											new Ext.Window({
												id:"SubwayAddWindow",
												title:"노선구분추가",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"SubwayAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"노선구분",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 노선구분을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("Subway1").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("SubwayAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("SubwayAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=subway&do=add&parent=0",waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("SubwayAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"노선구분삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_delete.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"선택노선구분을 삭제하게 되면 해당 노선구분의 지하철역도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("Subway1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"subway","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Subway1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"subway","do":"modify","data":GetGridData(Ext.getCmp("Subway1"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("Subway1").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("Subway1").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("Subway1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway1").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("Subway1").getStore().getCount()-1) {
													Ext.getCmp("Subway1").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("Subway1").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("Subway1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway1").selModel.selectRow(selecter[i]);
											}
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"idx",
										hidden:true,
										hideable:false
									},{
										id:"title1",
										header:"노선구분명",
										dataIndex:"title",
										sortable:false,
										menuDisabled:true,
										resizable:false,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										dataIndex:"sort",
										hidden:true,
										hideable:false
									}
								]),
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"subway",parent:"0"},
									listeners:{load:{fn:function() {
										Ext.getCmp("Subway2").getStore().baseParams.parent = "-1";
										Ext.getCmp("Subway2").getStore().load();
									}}}
								}),
								autoExpandColumn:"title1",
								flex:1,
								listeners:{rowclick:{fn:function(grid,idx,e) {
									Ext.getCmp("Subway2").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
									Ext.getCmp("Subway2").getStore().load();
								}}}
							}),
							new Ext.grid.EditorGridPanel({
								id:"Subway2",
								title:"지하철역",
								margins:"5 5 5 0",
								tbar:[
									new Ext.Button({
										text:"지하철역추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_add.png",
										handler:function() {
											if (Ext.getCmp("Subway2").getStore().baseParams.parent == "-1") {
												Ext.Msg.show({title:"에러",msg:"노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
											
											var parent = Ext.getCmp("Subway1").getStore().find("idx",Ext.getCmp("Subway2").getStore().baseParams.parent,false,false);
											
											new Ext.Window({
												id:"SubwayAddWindow",
												title:"지하철역추가 ("+Ext.getCmp("Subway1").getStore().getAt(parent).get("title")+")",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"SubwayAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"지하철역명",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 지하철역을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("Subway2").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("SubwayAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("SubwayAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=subway&do=add&parent="+Ext.getCmp("Subway2").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("SubwayAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"지하철역삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_delete.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 지하철역명을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("Subway1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"subway","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Subway2").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"subway","do":"modify","data":GetGridData(Ext.getCmp("Subway2"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지하철역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("Subway2").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("Subway2").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("Subway2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway2").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지하철역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("Subway2").getStore().getCount()-1) {
													Ext.getCmp("Subway2").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("Subway2").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("Subway2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway2").selModel.selectRow(selecter[i]);
											}
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"idx",
										hidden:true,
										hideable:false
									},{
										id:"title2",
										header:"지하철역명",
										dataIndex:"title",
										sortable:false,
										menuDisabled:true,
										resizable:false,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										dataIndex:"sort",
										hidden:true,
										hideable:false
									}
								]),
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"subway",parent:"-1"},
									listeners:{load:{fn:function(store) {
										var parent = Ext.getCmp("Subway1").getStore().find("idx",store.baseParams.parent,false,false);
										if (parent != -1) {
											Ext.getCmp("Subway2").setTitle("지하철역 ("+Ext.getCmp("Subway1").getStore().getAt(parent).get("title")+")");
										}
									}}}
								}),
								autoExpandColumn:"title2",
								flex:1
							})
						]
					})*/
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>