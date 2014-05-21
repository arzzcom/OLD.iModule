<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"post",key:"",keyword:"",category:"",bid:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","bid","boardtitle","category","title","mno","name","nickname","width","newment",{name:"ment",type:"int"},{name:"trackback",type:"int"},{name:"hit",type:"int"},{name:"vote",type:"int"},{name:"avgvote",type:"float"},"reg_date","file","ip"]
	});
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"게시물 이동",
			handler:function() {
				new Ext.Window({
					id:"MoveWindow",
					title:"게시물 이동",
					width:400,
					modal:true,
					maximizable:false,
					resizable:false,
					layout:"fit",
					items:[
						new Ext.form.FormPanel({
							id:"MoveForm",
							bodyPadding:"10 10 5 10",
							border:false,
							fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"이동할 게시판",
									name:"bid",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{"action":"list","is_all":"false"}
										},
										remoteSort:false,
										sorters:[{property:"bid",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["bid","title","option"]
									}),
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"bid",
									emptyText:"게시판명",
									listeners:{
										select:{fn:function(form,record) {
											if (record.shift().data.option.split(",").shift() == "TRUE") {
												Ext.getCmp("MoveForm").getForm().findField("category").getStore().getProxy().setExtraParam("bid",form.getValue());
												Ext.getCmp("MoveForm").getForm().findField("category").getStore().loadPage(1);
												Ext.getCmp("MoveForm").getForm().findField("category").enable();
											} else {
												Ext.getCmp("MoveForm").getForm().findField("category").reset();
												Ext.getCmp("MoveForm").getForm().findField("category").disable();
											}
										}}
									}
								}),
								new Ext.form.ComboBox({
									fieldLabel:"이동할 카테고리",
									name:"category",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{"action":"category","is_all":"false","is_none":"true","bid":""}
										},
										remoteSort:false,
										sorters:[{property:"sort",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["idx","category",{name:"sort",type:"int"}]
									}),
									disabled:true,
									editable:false,
									mode:"local",
									displayField:"category",
									valueField:"idx",
									emptyText:"카테고리"
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:"확인",
							handler:function() {
								Ext.getCmp("MoveForm").getForm().submit({
									url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=post&do=move&idx="+record.data.idx,
									submitEmptyText:false,
									waitTitle:"잠시만 기다려주십시오.",
									waitMsg:"게시물을 이동중입니다.",
									success:function(form,action) {
										Ext.Msg.show({title:"안내",msg:"성공적으로 이동하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											Ext.getCmp("ListPanel").getStore().loadPage(1);
											Ext.getCmp("MoveWindow").close();
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
								Ext.getCmp("MoveWindow").close();
							}
						})
					]
				}).show();
			}
		});
		
		menu.add('-');
		
		menu.add({
			text:"게시물 삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택한 게시물을 정말 삭제하시겠습니까?<br />삭제된 게시물은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 게시물을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
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
							params:{"action":"post","do":"delete","idx":record.data.idx}
						});
					}
				}});
			}
		});
		
		menu.add({
			text:"게시물 삭제 및 IP차단",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택한 게시물을 정말 삭제 및 차단하시겠습니까?<br />삭제된 게시물은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 게시물을 삭제 및 차단하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
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
							params:{"action":"post","do":"spam","idx":record.data.idx}
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
		title:"게시물관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
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
								if (record.shift().data.option.split(",").shift() == "TRUE") {
									Ext.getCmp("BoardCategory").getStore().getProxy().setExtraParam("bid",form.getValue());
									Ext.getCmp("BoardCategory").getStore().loadPage(1);
									Ext.getCmp("BoardCategory").show();
								} else {
									Ext.getCmp("BoardCategory").reset();
									Ext.getCmp("BoardCategory").hide();
								}
								store.getProxy().setExtraParam("bid",form.getValue());
								store.loadPage(1);
							}}
						}
					}),
					new Ext.form.ComboBox({
						id:"BoardCategory",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						hidden:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"category","is_all":"true","is_none":"true","bid":""}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["idx","category",{name:"sort",type:"int"}]
						}),
						width:100,
						editable:false,
						mode:"local",
						displayField:"category",
						valueField:"idx",
						emptyText:"카테고리",
						listeners:{
							select:{fn:function(form) {
								store.getProxy().setExtraParam("category",form.getValue());
								store.loadPage(1);
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
							data:[["content","컨텐츠"],["name","작성자"],["ment","댓글"],["ip","아이피"]]
						}),
						width:80,
						editable:false,
						mode:"local",
						displayField:"text",
						valueField:"keytype",
						value:"content"
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
							store.getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
							store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							store.loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 게시물을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"게시물 이동",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 게시물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									new Ext.Window({
										id:"MoveWindow",
										title:"게시물 이동",
										width:400,
										modal:true,
										maximizable:false,
										resizable:false,
										layout:"fit",
										items:[
											new Ext.form.FormPanel({
												id:"MoveForm",
												bodyPadding:"10 10 5 10",
												border:false,
												fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
												items:[
													new Ext.form.ComboBox({
														fieldLabel:"이동할 게시판",
														name:"bid",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														store:new Ext.data.JsonStore({
															proxy:{
																type:"ajax",
																simpleSortMode:true,
																url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
																reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																extraParams:{"action":"list","is_all":"false"}
															},
															remoteSort:false,
															sorters:[{property:"bid",direction:"ASC"}],
															autoLoad:true,
															pageSize:50,
															fields:["bid","title","option"]
														}),
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"bid",
														emptyText:"게시판명",
														listeners:{
															select:{fn:function(form,record) {
																if (record.shift().data.option.split(",").shift() == "TRUE") {
																	Ext.getCmp("MoveForm").getForm().findField("category").getStore().getProxy().setExtraParam("bid",form.getValue());
																	Ext.getCmp("MoveForm").getForm().findField("category").getStore().loadPage(1);
																	Ext.getCmp("MoveForm").getForm().findField("category").enable();
																} else {
																	Ext.getCmp("MoveForm").getForm().findField("category").reset();
																	Ext.getCmp("MoveForm").getForm().findField("category").disable();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														fieldLabel:"이동할 카테고리",
														name:"category",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														store:new Ext.data.JsonStore({
															proxy:{
																type:"ajax",
																simpleSortMode:true,
																url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
																reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																extraParams:{"action":"category","is_all":"false","is_none":"true","bid":""}
															},
															remoteSort:false,
															sorters:[{property:"sort",direction:"ASC"}],
															autoLoad:true,
															pageSize:50,
															fields:["idx","category",{name:"sort",type:"int"}]
														}),
														disabled:true,
														editable:false,
														mode:"local",
														displayField:"category",
														valueField:"idx",
														emptyText:"카테고리"
													})
												]
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												handler:function() {
													var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
													var idxs = new Array();
													for (var i=0, loop=checked.length;i<loop;i++) {
														idxs[i] = checked[i].get("idx");
													}
													
													Ext.getCmp("MoveForm").getForm().submit({
														url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php?action=post&do=move&idx="+idxs.join(","),
														submitEmptyText:false,
														waitTitle:"잠시만 기다려주십시오.",
														waitMsg:"게시물을 이동중입니다.",
														success:function(form,action) {
															Ext.Msg.show({title:"안내",msg:"성공적으로 이동하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
																Ext.getCmp("ListPanel").getStore().loadPage(1);
																Ext.getCmp("MoveWindow").close();
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
													Ext.getCmp("MoveWindow").close();
												}
											})
										]
									}).show();
								}
							},'-',{
								text:"게시물 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 게시물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 게시물을 정말 삭제하시겠습니까?<br />삭제된 게시물은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 게시물을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
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
												params:{"action":"post","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							},{
								text:"게시물 삭제 및 IP차단",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제 및 차단할 게시물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 게시물을 정말 삭제 및 차단하시겠습니까?<br />삭제된 게시물은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 게시물을 삭제 및 차단하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제 및 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"post","do":"spam","idx":idxs.join(",")}
											});
										}
									}});
								}
							}]
						})
					})
				],
				columns:[
					new Ext.grid.RowNumberer({
						header:"번호",
						dataIndex:"idx",
						sortable:true,
						width:60,
						align:"left",
						renderer:function(value,p,record) {
							p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
							return GridNumberFormat(value);
						}
					}),{
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
						sortable:true,
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
						header:"첨부",
						dataIndex:"file",
						sortable:false,
						menuDisabled:true,
						width:35,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (value) {
								p.tdCls = Ext.baseCSSPrefix + 'pointer';
								sHTML+= '<div style="height:10px; background:url(<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_bullet_disk.png) no-repeat 50% 50%;)"></div>';
							}

							return sHTML;
						}
					},{
						header:"작성자",
						dataIndex:"name",
						sortable:true,
						width:120,
						renderer:function(value,p,record) {
							p.tdCls = Ext.baseCSSPrefix + 'pointer';
							
							if (record.data.mno == "0") return value;
							else if (record.data.nickname) return '<b>'+value+"("+record.data.nickname+")</b>";
							else return '<b>'+value+'</b>';
						}
					},{
						header:"작성일",
						dataIndex:"reg_date",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"조회",
						dataIndex:"hit",
						sortable:true,
						menuDisabled:true,
						width:50,
						renderer:GridNumberFormat
					},{
						header:"추천",
						dataIndex:"vote",
						sortable:true,
						menuDisabled:true,
						width:50,
						renderer:GridNumberFormat
					},{
						header:"평점",
						dataIndex:"avgvote",
						sortable:false,
						menuDisabled:true,
						width:50,
						renderer:GridNumberFormat
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
						new Ext.Window({
							title:record.data.title,
							width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
							height:500,
							layout:"fit",
							maximizable:true,
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+record.data.bid+'&mode=view&idx='+record.data.idx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
						}).show();
					}},
					cellclick:{fn:function(grid,td,col,record,tr,row,e) {
						if (col == 3) {
							var file = record.data.file;

							if (file) {
								var files = file.split(",");
								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">첨부파일 다운로드</b>');
								for (var i=0, total=files.length;i<total;i++) {
									var fileInfor = files[i].split("|");
									menu.add({
										text:"<span style='font-weight:bold;'>"+fileInfor[1]+"</span> <span style='font-family:tahoma; font-size:10px;'>("+GetFileSize(fileInfor[2])+", <span style='font-weight:bold;'>"+Ext.util.Format.number(fileInfor[3],"0,0")+"</span> Hits)</span>",
										icon:GetFileIcon(fileInfor[1]),
										handler:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/board/exec/FileDownload.do.php?idx="+fileInfor[0];
										}
									});
								}
								menu.showAt(e.getXY());
								e.stopEvent();
							}
						}
						
						if (col == 4) {
							var menu = new Ext.menu.Menu();
							menu.add('<b class="menu-title">작성자 검색</b>');
							menu.add({
								text:"이름으로 검색("+record.data.name+")",
								handler:function() {
									Ext.getCmp("Key").setValue("name");
									Ext.getCmp("Keyword").setValue(record.data.name);
									store.getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
									store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
									store.loadPage(1);
								}
							});
							
							if (record.data.ip) {
								menu.add({
									text:"아이피로 검색("+record.data.ip+")",
									handler:function() {
										Ext.getCmp("Key").setValue("ip");
										Ext.getCmp("Keyword").setValue(record.data.ip);
										store.getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
										store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
										store.loadPage(1);
									}
								});
							}
							menu.showAt(e.getXY());
							e.stopEvent();
						}
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>