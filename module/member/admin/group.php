<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();

		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"그룹아이디 변경",
			handler:function() {
				new Ext.Window({
					id:"GroupIDWindow",
					title:"그룹아이디 변경",
					width:400,
					modal:true,
					layout:"fit",
					items:[
						new Ext.form.FormPanel({
							id:"GroupIDForm",
							border:false,
							autoScroll:true,
							fieldDefaults:{labelAlign:"right",labelWidth:85,allowBlank:false,anchor:"100%"},
							bodyPadding:"5 5 0 5",
							items:[
								new Ext.form.TextField({
									name:"id",
									value:record.data.group
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:"확인",
							handler:function() {
								Ext.getCmp("GroupIDForm").getForm().submit({
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=group&do=id&group="+record.data.group,
									submitEmptyText:false,
									waitTitle:"잠시만 기다려주십시오.",
									waitMsg:"그룹아이디를 변경하고 있습니다.",
									success:function(form,action) {
										Ext.Msg.show({title:"안내",msg:"성공적으로 변경하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											Ext.getCmp("ListPanel").getStore().reload();
											Ext.getCmp("GroupIDWindow").close();
										}});
									},
									failure:function(form,action) {
										Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								});
							}
						}),
						new Ext.Button({
							text:"취소",
							handler:function() {
								Ext.getCmp("GroupIDWindow").close();
							}
						})
					]
				}).show();
			}
		});
		
		menu.add({
			text:"그룹명 변경",
			handler:function() {
				new Ext.Window({
					id:"GroupTitleWindow",
					title:"그룹명 변경",
					width:400,
					modal:true,
					layout:"fit",
					items:[
						new Ext.form.FormPanel({
							id:"GroupTitleForm",
							border:false,
							autoScroll:true,
							fieldDefaults:{labelAlign:"right",labelWidth:85,allowBlank:false,anchor:"100%"},
							bodyPadding:"5 5 0 5",
							items:[
								new Ext.form.TextField({
									name:"title",
									value:record.data.title
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:"확인",
							handler:function() {
								Ext.getCmp("GroupTitleForm").getForm().submit({
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=group&do=title&group="+record.data.group,
									submitEmptyText:false,
									waitTitle:"잠시만 기다려주십시오.",
									waitMsg:"그룹명을 변경하고 있습니다.",
									success:function(form,action) {
										Ext.Msg.show({title:"안내",msg:"성공적으로 변경하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											Ext.getCmp("ListPanel").getStore().reload();
											Ext.getCmp("GroupTitleWindow").close();
										}});
									},
									failure:function(form,action) {
										Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								});
							}
						}),
						new Ext.Button({
							text:"취소",
							handler:function() {
								Ext.getCmp("GroupTitleWindow").close();
							}
						})
					]
				}).show();
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"회원가입 활성화",
			checked:record.data.allow_signin == "TRUE",
			handler:function(item) {
				var value = item.checked == true ? "TRUE" : "FALSE";
				
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
					params:{"action":"group","do":"signinmode","group":record.data.group,"value":value}
				});
			}
		});
		
		menu.add({
			text:"회원가입즉시 승인",
			checked:record.data.allow_active == "TRUE",
			handler:function(item) {
				var value = item.checked == true ? "TRUE" : "FALSE";
				
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
					params:{"action":"group","do":"activemode","group":record.data.group,"value":value}
				});
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"선택그룹 삭제",
			handler:function() {
				new Ext.Window({
					id:"ChangeGroupWindow",
					title:"회원처리방법 선택",
					width:300,
					modal:true,
					resizable:false,
					layout:"fit",
					items:[
						new Ext.form.FormPanel({
							id:"ChangeGroupForm",
							border:false,
							autoScroll:true,
							bodyPadding:"0 5 0 5",
							items:[
								new Ext.form.Hidden({
									name:"group",
									value:record.data.group
								}),
								new Ext.form.ComboBox({
									name:"move",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{action:"group"}
										},
										remoteSort:false,
										sorters:[{property:"sort",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["group","title",{name:"sort",type:"int"}]
									}),
									anchor:"100%",
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"group",
									emptyText:"회원을 옮길 그룹을 선택하여 주십시오."
								}),
								new Ext.form.Checkbox({
									name:"delete",
									boxLabel:"삭제될 회원그룹에 등록된 회원들을 탈퇴처리",
									listeners:{change:{fn:function(form) {
										Ext.getCmp("ChangeGroupForm").getForm().findField("move").setDisabled(form.checked);
									}}}
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:"확인",
							handler:function() {
								if (!Ext.getCmp("ChangeGroupForm").getForm().findField("move").getValue() && Ext.getCmp("ChangeGroupForm").getForm().findField("delete").checked == false) {
									Ext.Msg.show({title:"안내",msg:"회원을 이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return;
								}
								
								Ext.getCmp("ChangeGroupForm").getForm().submit({
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=group&do=delete",
									submitEmptyText:false,
									waitTitle:"잠시만 기다려주십시오.",
									waitMsg:"그룹을 삭제하고 있습니다.",
									success:function(form,action) {
										Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											Ext.getCmp("ListPanel").getStore().reload();
											Ext.getCmp("ChangeGroupWindow").close();
										}});
									},
									failure:function(form,action) {
										Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								});
							}
						}),
						new Ext.Button({
							text:"취소",
							handler:function() {
								Ext.getCmp("ChangeGroupWindow").close();
							}
						})
					]
				}).show();
			}
		});
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"회원그룹관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						text:"그룹추가",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_folder_add.png",
						handler:function() {
							new Ext.Window({
								id:"GroupWindow",
								title:"그룹추가",
								width:400,
								modal:true,
								layout:"fit",
								items:[
									new Ext.form.FormPanel({
										id:"GroupForm",
										border:false,
										autoScroll:true,
										fieldDefaults:{labelAlign:"right",labelWidth:85,allowBlank:false,anchor:"100%"},
										bodyPadding:"10 10 5 10",
										items:[
											new Ext.form.TextField({
												fieldLabel:"그룹아이디",
												name:"group",
												emptyText:"그룹아이디는 영어소문자와 숫자만 가능합니다."
											}),
											new Ext.form.TextField({
												fieldLabel:"그룹명",
												name:"title"
											}),
											new Ext.form.Checkbox({
												fieldLabel:"회원가입",
												name:"allow_signin",
												boxLabel:"이 그룹의 회원가입을 허용합니다."
											}),
											new Ext.form.Checkbox({
												fieldLabel:"즉시승인",
												name:"allow_active",
												boxLabel:"이 그룹은 회원가입즉시 계정이 활성화됩니다."
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.getCmp("GroupForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=group&do=add",
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"그룹을 추가하고 있습니다.",
												success:function(form,action) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("ListPanel").getStore().reload();
														Ext.getCmp("GroupWindow").close();
													}});
												},
												failure:function(form,action) {
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("GroupWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.form.TextField({
						id:"keyword",
						emptyText:"그룹아이디, 그룹명",
						width:150
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("keyword").getValue());
							Ext.getCmp("ListPanel").getStore().reload();
						}
					}),
					'-',
					{xtype:"tbtext",text:"순서변경"},
					new Ext.Button({
						text:"위로",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_arrow_up.png",
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
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
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
									params:{"action":"group","do":"sort","data":data}
								});
							}
						}
					}),
					new Ext.Button({
						text:"아래로",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_arrow_down.png",
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
									url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
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
									params:{"action":"group","do":"sort","data":data}
								});
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 그룹을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"회원가입 활성화",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
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
										params:{"action":"group","do":"signinmode","group":groups.join(","),"value":"TRUE"}
									});
								}
							},{
								text:"회원가입 비활성화",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
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
										params:{"action":"group","do":"signinmode","group":groups.join(","),"value":"FALSE"}
									});
								}
							},'-',{
								text:"회원가입즉시 승인",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
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
										params:{"action":"group","do":"activemode","group":groups.join(","),"value":"TRUE"}
									});
								}
							},{
								text:"회원가입후 관리자승인 필요",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
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
										params:{"action":"group","do":"activemode","group":groups.join(","),"value":"FALSE"}
									});
								}
							},'-',{
								text:"선택그룹 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}

									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var groups = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										groups.push(checked[i].get("group"));
									}
									
									new Ext.Window({
										id:"ChangeGroupWindow",
										title:"회원처리방법 선택",
										width:300,
										modal:true,
										resizable:false,
										layout:"fit",
										items:[
											new Ext.form.FormPanel({
												id:"ChangeGroupForm",
												border:false,
												autoScroll:true,
												bodyPadding:"0 5 0 5",
												items:[
													new Ext.form.Hidden({
														name:"group",
														value:groups.join(",")
													}),
													new Ext.form.ComboBox({
														name:"move",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														store:new Ext.data.JsonStore({
															proxy:{
																type:"ajax",
																simpleSortMode:true,
																url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
																reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																extraParams:{action:"group"}
															},
															remoteSort:false,
															sorters:[{property:"sort",direction:"ASC"}],
															autoLoad:true,
															pageSize:50,
															fields:["group","title",{name:"sort",type:"int"}]
														}),
														anchor:"100%",
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"group",
														emptyText:"회원을 옮길 그룹을 선택하여 주십시오."
													}),
													new Ext.form.Checkbox({
														name:"delete",
														boxLabel:"삭제될 회원그룹에 등록된 회원들을 탈퇴처리",
														listeners:{change:{fn:function(form) {
															Ext.getCmp("ChangeGroupForm").getForm().findField("move").setDisabled(form.checked);
														}}}
													})
												]
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												handler:function() {
													if (!Ext.getCmp("ChangeGroupForm").getForm().findField("move").getValue() && Ext.getCmp("ChangeGroupForm").getForm().findField("delete").checked == false) {
														Ext.Msg.show({title:"안내",msg:"회원을 이동할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														return;
													}
													
													Ext.getCmp("ChangeGroupForm").getForm().submit({
														url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=group&do=delete",
														submitEmptyText:false,
														waitTitle:"잠시만 기다려주십시오.",
														waitMsg:"그룹을 삭제하고 있습니다.",
														success:function(form,action) {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
																Ext.getCmp("ListPanel").getStore().reload();
																Ext.getCmp("ChangeGroupWindow").close();
															}});
														},
														failure:function(form,action) {
															Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														}
													});
												}
											}),
											new Ext.Button({
												text:"취소",
												handler:function() {
													Ext.getCmp("ChangeGroupWindow").close();
												}
											})
										]
									}).show();
								}
							}]
						})
					})
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"그룹아이디",
						dataIndex:"group",
						width:150,
					},{
						header:"그룹명",
						dataIndex:"title",
						minWidth:200,
						flex:1
					},{
						header:"회원가입",
						dataIndex:"allow_signin",
						sortable:true,
						width:80,
						renderer:function(value) {
							if (value == "TRUE") {
								return '<span style="color:blue;">회원가입허용</span>';
							} else {
								return '<span style="color:red;">회원가입불가</span>';
							}
						}
					},{
						header:"회원승인",
						dataIndex:"allow_active",
						sortable:true,
						width:80,
						renderer:function(value) {
							if (value == "TRUE") {
								return '<span style="color:blue;">즉시승인</span>';
							} else {
								return '<span style="color:red;">승인필요</span>';
							}
						}
					},{
						header:"회원수",
						dataIndex:"membernum",
						width:80,
						renderer:GridNumberFormat
					}
				],
				sortableColumns:false,
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"group",is_member:"true"}
					},
					remoteSort:false,
					sorters:[{property:"sort",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					fields:["group","title","membernum","allow_signin","allow_active",{name:"sort",type:"int"}]
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