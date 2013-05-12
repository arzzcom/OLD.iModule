<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	var MemberAll = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"member",get:"list",keyword:"","group":"","active":"all"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:[{name:"idx",type:"int"},"is_active","user_id","group","type","name","nickname","jumin","email","phone","reg_date","last_login","last_week","exp","point"]
	});
	
	<?php
	$group = $mDB->DBfetchs($_ENV['table']['group'],'*','','sort,asc');
	for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
	?>
	var Member<?php echo $group[$i]['group']; ?> = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"member",get:"list",keyword:"","group":"<?php echo $group[$i]['group']; ?>","active":"all"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:[{name:"idx",type:"int"},"is_active","user_id","group","type","name","nickname","jumin","email","phone","reg_date","last_login","last_week","exp","point"]
	});
	<?php } ?>
	
	function GetMemberSignField(group) {
		var fields = {};
		
		<?php
		$getValues = array();
		$signin = $mDB->DBfetchs($_ENV['table']['signin'],array('name','group','type','title','allowblank'),'','sort,asc');
		for ($i=0, $loop=sizeof($signin);$i<$loop;$i++) {
		?>
			if (fields["<?php echo $signin[$i]['group']; ?>"] === undefined) fields["<?php echo $signin[$i]['group']; ?>"] = new Array();
		
			<?php if ($signin[$i]['type'] == 'name') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.TextField({
					name:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					inputWidth:150,
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			<?php } elseif ($signin[$i]['type'] == 'nickname') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.FieldContainer({
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					anchor:"100%",
					layout:"hbox",
					items:[
						new Ext.form.TextField({
							name:"<?php echo $signin[$i]['name']; ?>",
							inputWidth:150,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>,
							style:{marginRight:"5px"}
						}),
						new Ext.form.DisplayField({
							name:"nickcon_display",
							value:""
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"닉이미지",
					anchor:"100%",
					layout:"hbox",
					items:[
						new Ext.form.FileUploadField({
							name:"nickcon",
							buttonText:"",
							buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
							allowBlank:true,
							style:{marginRight:"5px"},
							flex:1
						}),
						new Ext.form.Checkbox({
							name:"delete_nickcon",
							boxLabel:"닉이미지삭제"
						})
					]
				})
			);
			<?php } elseif ($signin[$i]['type'] == 'email' || $signin[$i]['type'] == 'homepage') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.TextField({
					name:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					anchor:"100%",
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			<?php } elseif ($signin[$i]['type'] == 'birthday') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.FieldContainer({
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					anchor:"100%",
					layout:"hbox",
					items:[
						new Ext.form.ComboBox({
							name:"birthday1",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["text","value"],
								data:[
									["연도",""]
									<?php for ($j=1950;$j<=date('Y');$j++) { ?>,["<?php echo $j; ?>년",<?php echo $j; ?>]<?php } ?>
								]
							}),
							inputWidth:80,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							style:{marginRight:"5px"},
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							name:"birthday2",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["text","value"],
								data:[
									["월",""]
									<?php for ($j=1;$j<=12;$j++) { ?>,["<?php echo $j; ?>월",<?php echo $j; ?>]<?php } ?>
								]
							}),
							inputWidth:80,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							style:{marginRight:"5px"},
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							name:"birthday3",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["text","value"],
								data:[
									["일",""]
									<?php for ($j=1;$j<=31;$j++) { ?>,["<?php echo $j; ?>일",<?php echo $j; ?>]<?php } ?>
								]
							}),
							inputWidth:80,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						})
					]
				})
			);
			<?php } elseif ($signin[$i]['type'] == 'cellphone') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.FieldContainer({
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					anchor:"100%",
					layout:"hbox",
					items:[
						new Ext.form.ComboBox({
							name:"provider",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["text","value"],
								data:[["통신사",""],["SKT","SKT"],["KT","KT"],["LGT","LGT"]]
							}),
							inputWidth:70,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							style:{marginRight:"5px"},
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							name:"cellphone1",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["value"],
								data:[["010"],["011"],["016"],["017"],["018"],["019"]]
							}),
							inputWidth:70,
							editable:false,
							mode:"local",
							displayField:"value",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;-&nbsp;"
						}),
						new Ext.form.TextField({
							name:"cellphone2",
							inputWidth:50,
							minLength:3,
							maxLength:4,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;-&nbsp;"
						}),
						new Ext.form.TextField({
							name:"cellphone3",
							inputWidth:50,
							minLength:4,
							maxLength:4,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						})
					]
				})
			);
			<?php } elseif ($signin[$i]['type'] == 'gender') { ?>
			fields["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.ComboBox({
					name:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["text","value"],
						data:[["남자","MALE"],["여자","FEMALE"]]
					}),
					inputWidth:200,
					editable:false,
					mode:"local",
					displayField:"text",
					valueField:"value",
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			<?php } ?>
		<?php } ?>
		
		fields[group].push(
			new Ext.form.FieldContainer({
				fieldLabel:"회원이미지",
				anchor:"100%",
				layout:"hbox",
				items:[
					new Ext.ux.form.FileUploadField({
						name:"photo",
						buttonText:"",
						buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
						allowBlank:true,
						flex:1,
						style:{marginRight:"5px"}
					}),
					new Ext.form.Checkbox({
						name:"delete_photo",
						boxLabel:"이미지삭제"
					})
				]
			})
		);
		
		return fields[group];
	}
	
	function GetMemberInfo(grid,record,row,index,e) {
		new Ext.Window({
			id:"MemberWindow",
			title:"회원정보보기",
			width:600,
			height:500,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"MemberForm",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:85},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.FieldSet({
							title:"회원정보",
							padding:"10 10 10 10",
							items:[
								new Ext.Panel({
									layout:"border",
									height:65,
									border:false,
									items:[
										new Ext.Panel({
											region:"west",
											width:70,
											border:false,
											html:'<img id="MemberPhoto" src="<?php echo $_ENV['dir']; ?>/images/common/nomempic60.gif" style="border:2px solid #E5E5E5; width:60px; height:60px;" />'
										}),
										new Ext.Panel({
											region:"center",
											border:false,
											html:'<div id="MemberUserID" style="font-family:tahoma; font-size:14px; font-weight:bold;">UnKnown</div><div style="height:10px;"></div>Joined : <span id="MemberRegDate" style="font-family:tahoma; font-size:11px; font-weight:bold;">1970.01.01 12:00:00</span><br />Last Login : <span id="MemberLastLogin" style="font-family:tahoma; font-size:11px; font-weight:bold;">1970.01.01 12:00:00</span>'
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"정보수정",
							items:GetMemberSignField(record.data.group)
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"수정하기",
					handler:function() {
						Ext.getCmp("MemberForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=member&do=modify&idx="+record.data.idx,
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"회원정보를 수정하고 있습니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									Ext.getCmp("MemberWindow").close();
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.photo) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.photo,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									if (action.result.errors.nickcon) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.nickcon,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
								}
								Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							}
						});
					}
				}),
				new Ext.Button({
					text:"닫기",
					handler:function() {
						Ext.getCmp("MemberWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("MemberForm").getForm().load({
					url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php?action=member&get=data&idx="+record.data.idx,
					submitEmptyText:false,
					waitTitle:"잠시만 기다려주십시오.",
					waitMsg:"데이터를 로딩중입니다.",
					success:function(form,action) {
						document.getElementById("MemberUserID").innerHTML = action.result.data.user_id;
						document.getElementById("MemberLastLogin").innerHTML = action.result.data.last_login;
						document.getElementById("MemberPhoto").src = action.result.data.photo;
						document.getElementById("MemberRegDate").innerHTML = action.result.data.reg_date;
						
						if (action.result.data.nickcon) {
							form.findField("nickcon_display").setValue('<img src="'+action.result.data.nickcon+'" />');
							form.findField("delete_nickcon").enable();
						} else {
							form.findField("delete_nickcon").disable();
						}
					},
					failure:function(form,action) {
						Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
					}
				});
			}}}
		}).show();
	}

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();

		menu.add('<b class="menu-title">'+record.data.name+'('+record.data.user_id+')</b>');
		menu.add({
			text:"회원정보수정",
			handler:function(item) {
				GetMemberInfo(grid,record,row,index,e);
			}
		});
		
		menu.add('-');
		menu.add({
			text:"포인트 관리",
			handler:function(item) {
				var PointStore = new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"point",get:"list",idx:record.data.idx}
					},
					remoteSort:true,
					sorters:[{property:"idx",direction:"DESC"}],
					autoLoad:true,
					pageSize:50,
					fields:[{name:"idx",type:"int"},"reg_date","msg",{name:"point",type:"int"}]
				});
				
				new Ext.Window({
					id:"PointWindow",
					title:"포인트 관리",
					width:600,
					height:400,
					modal:true,
					layout:"fit",
					tbar:[
						new Ext.Button({
							text:"포인트 증감설정",
							icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_coins.png",
							handler:function() {
								
							}
						}),
						'->',
						new Ext.toolbar.TextItem({
							text:"전체포인트 : "+GetNumberFormat(record.data.point)+" 포인트"
						})
					],
					items:[
						new Ext.grid.GridPanel({
							id:"PointList",
							border:false,
							columns:[
								new Ext.grid.RowNumberer(),
								{
									header:"변경날짜",
									dataIndex:"reg_date",
									width:120,
									renderer:function(value,p,record) {
										return '<div style="font-family:tahoma;">'+value+'</div>';
									}
								},{
									header:"변경사유",
									dataIndex:"msg",
									flex:1,
									renderer:function(value) {
										return value;
									}
								},{
									header:"변경포인트",
									dataIndex:"point",
									width:120,
									renderer:function(value) {
										if (value < 0) return '<div style="color:red; text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
										return '<div style="color:blue; text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
									}
								}
							],
							store:PointStore,
							columnLines:true,
							bbar:new Ext.PagingToolbar({
								store:PointStore,
								displayInfo:true
							})
						})
					]
				}).show();
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function MemberAdd(group) {
		new Ext.Window({
			id:"MemberWindow",
			title:"회원추가",
			width:600,
			height:500,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"MemberForm",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:85,allowBlank:false},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.TextField({
									fieldLabel:"회원아이디",
									name:"user_id",
									inputWidth:200
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"패스워드",
									layout:"hbox",
									items:[
										new Ext.form.TextField({
											name:"password1",
											inputType:"password",
											emptyText:"패스워드",
											inputWidth:200,
											style:{marginRight:"5px"}
										}),
										new Ext.form.TextField({
											name:"password2",
											inputType:"password",
											emptyText:"패스워드확인",
											inputWidth:200
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"그룹정보",
							items:GetMemberSignField(group)
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					handler:function() {
						Ext.getCmp("MemberForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php?action=member&do=add&group="+group,
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"회원을 추가하고 있습니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListTab").setActiveTab(Ext.getCmp("List-"+group));
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									Ext.getCmp("MemberWindow").close();
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.photo) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.photo,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									if (action.result.errors.nickcon) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.nickcon,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
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
						Ext.getCmp("MemberWindow").close();
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"회원목록",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						id:"BtnMemberAdd",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_user_add.png",
						text:"회원추가",
						handler:function() {
							var tab = Ext.getCmp("ListTab").getActiveTab();
							if (tab.getId() == "ListAll") {
								new Ext.Window({
									id:"MemberGroupWindow",
									title:"회원그룹선택",
									width:300,
									modal:true,
									resizable:false,
									layout:"fit",
									items:[
										new Ext.form.FormPanel({
											id:"MemberGroupForm",
											border:false,
											autoScroll:true,
											bodyPadding:"5 5 0 5",
											items:[
												new Ext.form.ComboBox({
													name:"group",
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
													emptyText:"회원을 추가할 회원그룹을 선택하여 주십시오."
												})
											]
										})
									],
									buttons:[
										new Ext.Button({
											text:"확인",
											handler:function() {
												if (!Ext.getCmp("MemberGroupForm").getForm().findField("group").getValue()) {
													Ext.Msg.show({title:"안내",msg:"회원을 추가할 그룹을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													return;
												}
												
												MemberAdd(Ext.getCmp("MemberGroupForm").getForm().findField("group").getValue());
												Ext.getCmp("MemberGroupWindow").close();
											}
										}),
										new Ext.Button({
											text:"취소",
											handler:function() {
												Ext.getCmp("MemberGroupWindow").close();
											}
										})
									]
								}).show();
							} else {
								MemberAdd(tab.getId().split("-").pop());
							}
						}
					}),
					'-',
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
						id:"BtnListAll",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox_on.png",
						text:"전체",
						pressed:true,
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("BtnListAll").toggle(false);
								Ext.getCmp("BtnListActive").toggle(false);
								Ext.getCmp("BtnListUnActive").toggle(false);
								button.toggle(true);
								Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("active","all");
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
							}
						},
						listeners:{toggle:{fn:function(button,toggle) {
							if (toggle == true) {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox_on.png");
							} else {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox.png");
							}
						}}}
					}),
					new Ext.Button({
						id:"BtnListActive",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox.png",
						text:"활성계정",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("BtnListAll").toggle(false);
								Ext.getCmp("BtnListActive").toggle(false);
								Ext.getCmp("BtnListUnActive").toggle(false);
								button.toggle(true);
								Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("active","TRUE");
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
							}
						},
						listeners:{toggle:{fn:function(button,toggle) {
							if (toggle == true) {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox_on.png");
							} else {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox.png");
							}
						}}}
					}),
					new Ext.Button({
						id:"BtnListUnActive",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox.png",
						text:"비활성계정",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("BtnListAll").toggle(false);
								Ext.getCmp("BtnListActive").toggle(false);
								Ext.getCmp("BtnListUnActive").toggle(false);
								button.toggle(true);
								Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("active","FALSE");
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
							}
						},
						listeners:{toggle:{fn:function(button,toggle) {
							if (toggle == true) {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox_on.png");
							} else {
								button.setIcon("<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_checkbox.png");
							}
						}}}
					}),
					'-',
					new Ext.Button({
						text:"선택한 회원을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_group_gear.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"계정 활성화",
								handler:function() {
									var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListTab").getActiveTab().getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{action:"member","do":"activemode","idx":idxs.join(","),"value":"TRUE"}
									});
								}
							},{
								text:"계정 비활성화",
								handler:function() {
									var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/member/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListTab").getActiveTab().getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{action:"member","do":"activemode","idx":idxs.join(","),"value":"FALSE"}
									});
								}
							}]
						})
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListAll",
						title:"전체그룹",
						border:false,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"아이디",
								dataIndex:"user_id",
								minWidth:100,
								flex:1,
								renderer:function(value,p,record) {
									return '<div style="font-family:verdana;">'+value+' <span style="font-family:tahoma; font-size:10px; color:#C6C6C6;">['+GetNumberFormat(record.data.idx)+']</span></div>';
								}
							},{
								header:"회원종류",
								dataIndex:"type",
								width:80,
								renderer:function(value) {
									if (value == "ADMINISTRATOR") {
										return "최고관리자";
									} else if (value == "MODERATOR") {
										return "관리자";
									} else {
										return "일반회원";
									}
								}
							},{
								header:"상태",
								dataIndex:"is_active",
								width:60,
								renderer:function(value) {
									if (value == "TRUE") {
										return '<span style="color:blue;">활성</span>';
									} else {
										return '<span style="color:red;">비활성</span>';
									}
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
								header:"경험치",
								dataIndex:"exp",
								sortable:false,
								width:110,
								renderer:function(value) {
									var data = value.split(",");
									var onwidth = Math.ceil(parseInt(data[1])/parseInt(data[2])*60);
									var offwidth = 60-onwidth;
									var sHTML = '<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100px;"><col width="30" /><col width="1" /><col width="'+onwidth+'" /><col width="'+offwidth+'" /><col width="2" /><tr style="height:11px; overflow:hidden;" title="'+data[1]+'/'+data[2]+'">';
									sHTML+= '<td style="font-family:verdana; font-weight:bold; font-size:9px;">LV.<span style="color:#EF5600;">'+data[0]+'</span></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_start.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_on.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_off.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_end.gif) repeat-x 50%;"></td>';
									sHTML+= '</tr></table>';
		
									return sHTML;
								}
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
									if (record.data.last_week == "TRUE") return '<div style="font-family:tahoma;">'+value+'</div>';
									else return '<div style="font-family:tahoma; color:#C6C6C6;">'+value+'</div>';
								}
							}
						],
						columnLines:true,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						store:MemberAll,
						bbar:new Ext.PagingToolbar({
							store:MemberAll,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:{fn:GetMemberInfo},
							itemcontextmenu:ItemContextMenu
						}
					})
					<?php
					$group = $mDB->DBfetchs($_ENV['table']['group'],'*','','sort,asc');
					for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
					?>,
					new Ext.grid.GridPanel({
						id:"List-<?php echo $group[$i]['group']; ?>",
						title:"<?php echo $group[$i]['title']; ?>",
						border:false,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"아이디",
								dataIndex:"user_id",
								sortable:true,
								minWidth:100,
								flex:1,
								renderer:function(value,p,record) {
									return '<div style="font-family:verdana;">'+value+' <span style="font-family:tahoma; font-size:10px; color:#C6C6C6;">['+GetNumberFormat(record.data.idx)+']</span></div>';
								}
							},{
								header:"회원종류",
								dataIndex:"type",
								sortable:true,
								width:80,
								renderer:function(value) {
									if (value == "ADMINISTRATOR") {
										return "최고관리자";
									} else if (value == "MODERATOR") {
										return "관리자";
									} else {
										return "일반회원";
									}
								}
							},{
								header:"상태",
								dataIndex:"is_active",
								sortable:true,
								width:60,
								renderer:function(value) {
									if (value == "TRUE") {
										return '<span style="color:blue;">활성</span>';
									} else {
										return '<span style="color:red;">비활성</span>';
									}
								}
							},{
								header:"이름",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"닉네임",
								dataIndex:"nickname",
								sortable:true,
								width:80
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"이메일",
								dataIndex:"email",
								sortable:true,
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
								sortable:true,
								width:65,
								renderer:GridNumberFormat
							},{
								header:"경험치",
								dataIndex:"exp",
								sortable:false,
								width:110,
								renderer:function(value) {
									var data = value.split(",");
									var onwidth = Math.ceil(parseInt(data[1])/parseInt(data[2])*60);
									var offwidth = 60-onwidth;
									var sHTML = '<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100px;"><col width="30" /><col width="1" /><col width="'+onwidth+'" /><col width="'+offwidth+'" /><col width="2" /><tr style="height:11px; overflow:hidden;" title="'+data[1]+'/'+data[2]+'">';
									sHTML+= '<td style="font-family:verdana; font-weight:bold; font-size:9px;">LV.<span style="color:#EF5600;">'+data[0]+'</span></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_start.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_on.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_off.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/module/member/images/admin/exp_end.gif) repeat-x 50%;"></td>';
									sHTML+= '</tr></table>';
		
									return sHTML;
								}
							},{
								header:"가입일",
								dataIndex:"reg_date",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"최종접속일",
								dataIndex:"last_login",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									if (record.data.last_week == "TRUE") return '<div style="font-family:tahoma;">'+value+'</div>';
									else return '<div style="font-family:tahoma; color:#C6C6C6;">'+value+'</div>';
								}
							}
						],
						columnLines:true,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						store:Member<?php echo $group[$i]['group']; ?>,
						bbar:new Ext.PagingToolbar({
							store:Member<?php echo $group[$i]['group']; ?>,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:{fn:GetMemberInfo},
							itemcontextmenu:ItemContextMenu
						}
					})
					<?php } ?>
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getId() == "ListAll") {
						Ext.getCmp("BtnMemberAdd").setText("회원추가");
					} else {
						Ext.getCmp("BtnMemberAdd").setText(tab.title+" 회원추가");
					}
					Ext.getCmp("BtnListAll").toggle(false);
					Ext.getCmp("BtnListActive").toggle(false);
					Ext.getCmp("BtnListUnActive").toggle(false);
					
					if (tab.getStore().getProxy().extraParams.active == "all") {
						Ext.getCmp("BtnListAll").toggle(true);
					} else if (tab.getStore().getProxy().extraParams.active == "TRUE") {
						Ext.getCmp("BtnListActive").toggle(true);
					} else if (tab.getStore().getProxy().extraParams.active == "FALSE") {
						Ext.getCmp("BtnListUnActive").toggle(true);
					}
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>