<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"post",get:"list",key:"",keyword:"",category:"",pid:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","pid","polltitle","title","mno","name","nickname","width","newment",{name:"ment",type:"int"},{name:"voter",type:"int"},"reg_date","end_date","file","ip"]
	});
	
	function PostFormFunction(idx) {
		new Ext.Window({
			id:"PostWindow",
			title:(idx ? "설문글수정" : "설문글추가"),
			width:700,
			height:500,
			minWidth:700,
			minHeight:400,
			modal:true,
			autoScroll:true,
			items:[
				new Ext.form.FormPanel({
					id:"PostForm",
					bodyPadding:"10 10 5 10",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"설문조사선택",
									name:"pid",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{"action":"list","is_all":"false"}
										},
										remoteSort:false,
										sorters:[{property:"pid",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["pid","title","option"]
									}),
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"pid",
									emptyText:"설문조사명"
								}),
								new Ext.form.TextField({
									fieldLabel:"제목",
									name:"title"
								}),
								new Ext.form.TextArea({
									fieldLabel:"상세내용",
									name:"content",
									height:100
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"설문조사 옵션",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"설문조사 종료일",
									layout:"hbox",
									items:[
										new Ext.form.DateField({
											name:"end_date",
											width:100,
											format:"Y-m-d"
										}),
										new Ext.form.DisplayField({
											value:"",
											flex:1
										}),
										new Ext.form.Checkbox({
											name:"unlimit",
											boxLabel:"종료일없음(무제한)"
										})
									]
								}),
								new Ext.form.Checkbox({
									fieldLabel:"설문방식",
									name:"is_multi",
									boxLabel:"항목을 여러개 선택할 수 있습니다. (체크해제시 단일항목선택)"
								}),
								new Ext.form.FileUploadField({
									name:"image",
									fieldLabel:"설문이미지",
									buttonText:"",
									buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
									allowBlank:true,
									emptyText:(idx ? "설문이미지를 수정하시려면 새로운 이미지를 선택하여 주십시오." : "목록에 보일 설문이미지를 선택하여 주십시오.")
								})
							]
						}),
						new Ext.form.Hidden({
							name:"item"
						})
					]
				}),
				new Ext.Panel({
					border:false,
					style:{background:"#FFFFFF"},
					items:[
						new Ext.grid.GridPanel({
							id:"PostItemList",
							title:"설문항목설정",
							height:300,
							margin:"0 10 10 10",
							tbar:[
								new Ext.Button({
									text:"항목추가",
									icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_tick.png",
									handler:function() {
										new Ext.Window({
											title:"항목추가",
											id:"AddPostItemWindow",
											width:500,
											modal:true,
											resizable:false,
											layout:"fit",
											items:[
												new Ext.form.FormPanel({
													id:"AddPostItemForm",
													border:false,
													fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
													bodyPadding:"5 5 0 5",
													items:[
														new Ext.form.TextArea({
															fieldLabel:"항목내용",
															name:"title",
															height:200,
															emptyText:"엔터키로 여러개의 항목을 한번에 추가할 수 있습니다."
														})
													]
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													handler:function() {
														if (Ext.getCmp("AddPostItemForm").isValid() == true) {
															var temp = Ext.getCmp("AddPostItemForm").getForm().findField("title").getValue().split("\n");
															for (var i=0, loop=temp.length;i<loop;i++) {
																if (temp[i]) {
																	Ext.getCmp("PostItemList").getStore().add({idx:'-1',title:temp[i],sort:Ext.getCmp("PostItemList").getStore().getCount()});
																}
															}
														} else {
															Ext.Msg.show({title:"에러",msg:"항목내용을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														}
														Ext.getCmp("AddPostItemWindow").close();
													}
												}),
												new Ext.Button({
													text:"취소",
													handler:function() {
														Ext.getCmp("AddPostItemWindow").close();
													}
												})
											]
										}).show();
									}
								}),
								new Ext.Button({
									text:"항목삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_cross.png",
									handler:function() {
										var checked = Ext.getCmp("PostItemList").getSelectionModel().getSelection();
		
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}

										for (var i=0, loop=checked.length;i<loop;i++) {
											Ext.getCmp("PostItemList").getStore().remove(checked[i]);
										}
										
										for (var i=0, loop=Ext.getCmp("PostItemList").getStore().getCount();i<loop;i++) {
											Ext.getCmp("PostItemList").getStore().getAt(i).set("sort",i);
										}
									}
								}),
								'-',
								{xtype:"tbtext",text:"순서변경"},
								new Ext.Button({
									text:"위로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_up.png",
									handler:function() {
										var checked = Ext.getCmp("PostItemList").getSelectionModel().getSelection();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
		
										var selecter = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											selecter.push(checked[i].get("sort")-1);
										}
										for (var i=0, loop=checked.length;i<loop;i++) {
											var sort = checked[i].get("sort");
											if (sort != 0) {
												Ext.getCmp("PostItemList").getStore().getAt(sort).set("sort",sort-1);
												Ext.getCmp("PostItemList").getStore().getAt(sort-1).set("sort",sort);
												Ext.getCmp("PostItemList").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}
										
										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("PostItemList").getSelectionModel().select(selecter[i],i!=0);
										}
									}
								}),
								new Ext.Button({
									text:"아래로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_down.png",
									handler:function() {
										var checked = Ext.getCmp("PostItemList").getSelectionModel().getSelection();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
		
										var selecter = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											selecter.push(checked[i].get("sort")+1);
										}
										for (var i=checked.length-1;i>=0;i--) {
											var sort = checked[i].get("sort");
											if (sort != Ext.getCmp("PostItemList").getStore().getCount()-1) {
												Ext.getCmp("PostItemList").getStore().getAt(sort).set("sort",sort+1);
												Ext.getCmp("PostItemList").getStore().getAt(sort+1).set("sort",sort);
												Ext.getCmp("PostItemList").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}
										
										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("PostItemList").getSelectionModel().select(selecter[i],i!=0);
										}
									}
								}),
								'->',
								{xtype:"tbtext",text:"더블클릭 : 수정"}
							],
							columns:[
								new Ext.grid.RowNumberer(),
								{
									header:"항목내용",
									dataIndex:"title",
									flex:1,
									width:100,
									editor:new Ext.form.TextField({
										selectOnFocus:true,
										allowBlank:false
									})
								}
							],
							store:new Ext.data.JsonStore({
								proxy:{
									type:"ajax",
									simpleSortMode:true,
									url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
									reader:{type:"json",root:"lists",totalProperty:"totalCount"},
									extraParams:{action:"post",get:"item",idx:(idx ? idx : "0")}
								},
								remoteSort:false,
								sorters:[{property:"sort",direction:"ASC"}],
								autoLoad:true,
								pageSize:50,
								fields:["idx","title",{name:"sort",type:"int"}]
							}),
							columnLines:true,
							plugins:[
								new Ext.grid.plugin.CellEditing({
									clicksToEdit:2
								})
							],
							selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"})
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					handler:function() {
						var items = new Array();
						for (var i=0, loop=Ext.getCmp("PostItemList").getStore().getCount();i<loop;i++) {
							items.push(Ext.getCmp("PostItemList").getStore().getAt(i).data);
						}
						
						if (items.length < 2) {
							Ext.Msg.show({title:"에러",msg:"설문항목을 2개이상 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
						
						Ext.getCmp("PostForm").getForm().findField("item").setValue(Ext.JSON.encode(items));
						
						Ext.getCmp("PostForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php?action=post&do="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(idx ? "설문글을 수정하고 있습니다." : "설문글을 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("PostWindow").close();
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.image) {
										Ext.Msg.show({title:"에러",msg:action.result.error.image,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return;
									}
									
									if (action.result.errors.item) {
										Ext.Msg.show({title:"에러",msg:action.result.error.item,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
						Ext.getCmp("PostWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("PostForm").getForm().load({
						url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php?action=post&get=data&&idx="+(idx ? idx : ""),
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"데이터를 로딩중입니다.",
						success:function(form,action) {
						},
						failure:function(form,action) {
							Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					});
				}
			}}}
		}).show();
	}
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"설문글 수정",
			handler:function() {
				PostFormFunction(record.data.idx);
			}
		});
		
		menu.add({
			text:"설문글 삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택한 설문글을 정말 삭제하시겠습니까?<br />삭제된 설문글은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 설문글을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().loadPage(1);
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
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"설문글관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_table_add.png",
						text:"설문글추가",
						handler:function() {
							PostFormFunction();
						}
					}),
					'-',
					new Ext.form.ComboBox({
						id:"PollID",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"list","is_all":"true"}
							},
							remoteSort:false,
							sorters:[{property:"pid",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["pid","title","option"]
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"pid",
						emptyText:"설문조사명",
						listeners:{
							select:{fn:function(form,record) {
								if (record.shift().data.option.split(",").shift() == "TRUE") {
									Ext.getCmp("PollCategory").getStore().getProxy().setExtraParam("pid",form.getValue());
									Ext.getCmp("PollCategory").getStore().loadPage(1);
									Ext.getCmp("PollCategory").show();
								} else {
									Ext.getCmp("PollCategory").reset();
									Ext.getCmp("PollCategory").hide();
								}
								store.getProxy().setExtraParam("pid",form.getValue());
								store.loadPage(1);
							}}
						}
					}),
					new Ext.form.ComboBox({
						id:"PollCategory",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						hidden:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"category","is_all":"true","is_none":"true","pid":""}
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
						icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_magnifier.png",
						handler:function() {
							store.getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
							store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							store.loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 설문글을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"설문글 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 설문글을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 설문글을 정말 삭제하시겠습니까?<br />삭제된 설문글은 휴지통으로 이동됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 설문글을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/poll/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().loadPage(1);
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
						header:"설문조사명",
						dataIndex:"polltitle",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							return value+'<span style="font-family:tahoma;">('+record.data.pid+')</span>';
						}
					},{
						header:"제목",
						dataIndex:"title",
						sortable:true,
						minWidth:200,
						flex:1,
						renderer:function(value,p,record) {
							var sHTML = "";
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
							if (value == "TRUE") {
								p.tdCls = Ext.baseCSSPrefix + 'pointer';
								sHTML+= '<div style="height:10px; background:url(<?php echo $_ENV['dir']; ?>/module/poll/images/admin/icon_bullet_disk.png) no-repeat 50% 50%;)"></div>';
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
						header:"마감일",
						dataIndex:"end_date",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"참여수",
						dataIndex:"voter",
						sortable:true,
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
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/poll/poll.php?pid='+record.data.pid+'&mode=view&idx='+record.data.idx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
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
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/poll/exec/FileDownload.do.php?idx="+fileInfor[0];
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