<style type="text/css">
#ImageView .item {border:1px solid #CCCCCC; padding:5px; width:160px; display:inline-block; margin:10px 0px 15px 10px;}
#ImageView .select {border:1px solid #044AA9; background:#DAE6F3;}
#ImageView .item .image {width:150px; height:120px;}
#ImageView .item .filename {width:150px; margin:5px 0px 0px 0px; font-size:12px; font-weight:bold; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;}
#ImageView .item .releasetitle {width:150px; margin:5px 0px 0px 0px; color:#666666; font-size:11px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;}
.x-view-selector {position:absolute;left:0;top:0;width:0;border:1px dotted;opacity: .5;-moz-opacity: .5;filter:alpha(opacity=50);zoom:1;background-color:#c3daf9;border-color:#3399bb;}.ext-strict .ext-ie .x-tree .x-panel-bwrap{position:relative;overflow:hidden;}
</style>
<script type="text/javascript">
Ext.require([
	'Ext.ux.DataView.DragSelector'
]);
function RetrenchProgressControl(dirName,dirCode,dirTotal,fileLimit,fileTotal,fileDelete) {
	Ext.getCmp("ProgressDir").updateProgress(dirCode/dirTotal,"첨부파일 폴더를 정리하고 있습니다. ("+dirCode+"/"+dirTotal+")",true);
	
	if (fileTotal != 0) {
		Ext.getCmp("ProgressFile").show();
		Ext.getCmp("ProgressFile").updateProgress(fileLimit/fileTotal,dirName+"폴더의 불필요한 첨부파일을 삭제중입니다. ("+GetNumberFormat(fileLimit)+"/"+GetNumberFormat(fileTotal)+", 삭제된파일 : "+GetNumberFormat(fileDelete)+"개)",true);
	} else {
		Ext.getCmp("ProgressFile").hide();
	}
	
	if (dirCode == dirTotal && fileLimit == fileTotal) {
		Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
			Ext.getCmp("ProgressWindow").close();
			Ext.getCmp("ListPanel1").getStore().loadPage(1);
			Ext.getCmp("ListPanel2").getStore().loadPage(1);
			Ext.getCmp("ListPanel3View").getStore().loadPage(1);
		}});
	}
}

function NoReptoProgressControl(fileLimit,fileTotal,fileDelete) {
	Ext.getCmp("ProgressDir").updateProgress(fileLimit/fileTotal,"첨부파일을 정리하고 있습니다. ("+GetNumberFormat(fileLimit)+"/"+GetNumberFormat(fileTotal)+", 삭제된파일 : "+GetNumberFormat(fileDelete)+"개)",true);
	
	if (fileLimit == fileTotal) {
		Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
			Ext.getCmp("ProgressWindow").close();
			Ext.getCmp("ListPanel1").getStore().loadPage(1);
			Ext.getCmp("ListPanel2").getStore().loadPage(1);
			Ext.getCmp("ListPanel3View").getStore().loadPage(1);
		}});
	}
}

function TempProgressControl(fileLimit,fileTotal,fileDelete) {
	Ext.getCmp("ProgressDir").updateProgress(fileLimit/fileTotal,"임시파일을 정리하고 있습니다. ("+GetNumberFormat(fileLimit)+"/"+GetNumberFormat(fileTotal)+", 삭제된파일 : "+GetNumberFormat(fileDelete)+"개)",true);
	
	if (fileLimit == fileTotal) {
		Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
			Ext.getCmp("ProgressWindow").close();
			Ext.getCmp("ListPanel1").getStore().loadPage(1);
			Ext.getCmp("ListPanel2").getStore().loadPage(1);
			Ext.getCmp("ListPanel3View").getStore().loadPage(1);
		}});
	}
}

var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store1 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"register",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","postidx","postdelete","repto","rid","width","releasetitle","category","type","title","mno","name","nickname","reg_date","filename","filetype","filesize","filepath","hit"]
	});
	
	var store2 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"temp",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","postidx","postdelete","repto","rid","width","releasetitle","category","type","title","mno","name","nickname","reg_date","filename","filetype","filesize","filepath","hit"]
	});

	var store3 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"image",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","image","postidx","postdelete","repto","rid","width","releasetitle","category","type","title","mno","name","nickname","reg_date","filename","filetype","filesize","filepath","hit"]
	});
	
	function ItemDblClick(grid,record,row,index,e) {
		if (record.data.postidx) {
			new Ext.Window({
				title:record.data.title,
				width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
				height:500,
				layout:"fit",
				maximizable:true,
				html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/release/release.php?rid='+record.data.rid+'&mode=view&idx='+record.data.postidx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
			}).show();
		} else {
			Ext.Msg.show({title:"에러",msg:"이 파일은 게시물이 등록되지 않았거나, 첨부된 게시물이 삭제되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
		}
	}
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.filename+'</b>');
		
		menu.add({
			text:"파일다운로드",
			handler:function() {
				execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/FileDownload.do.php?idx="+record.data.idx;
			}
		});
		
		menu.add({
			text:"파일경로보기",
			handler:function(item) {
				Ext.Msg.show({title:record.data.filename,msg:record.data.filepath,buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
			}
		});
		
		if (record.data.postidx != 0) {
			menu.add('-');
			
			menu.add({
				text:"파일이 첨부된 게시물보기",
				handler:function(item) {
					new Ext.Window({
						title:record.data.title,
						width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
						height:500,
						layout:"fit",
						maximizable:true,
						html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/release/release.php?rid='+record.data.rid+'&mode=view&idx='+record.data.postidx+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
					}).show();
				}
			});
		}
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"첨부파일관리",
		layout:"fit",
		items:[
			new Ext.tab.Panel({
				id:"ListTab",
				border:false,
				tabPosition:"bottom",
				activeTab:0,
				tbar:[
					new Ext.form.TextField({
						id:"Keyword",
						width:180,
						emptyText:"검색어를 입력하세요."
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_magnifier.png",
						handler:function() {
							if (Ext.getCmp("ListTab").getActiveTab().getId() == "ListPanel3") {
								store3.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
								store3.loadPage(1);
							} else {
								Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
								Ext.getCmp("ListTab").getActiveTab().getStore().loadPage(1);
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 파일을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"파일삭제",
								handler:function() {
									if (Ext.getCmp("ListTab").getActiveTab().getId() == "ListPanel3") {
										var checked = Ext.getCmp("ListPanel3View").getSelectionModel().getSelection();
									} else {
										var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
									}
									
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 파일을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 파일을 정말 삭제하시겠습니까?<br />삭제된 파일은 복구가 불가능합니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 파일을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															store1.loadPage(1);
															store2.loadPage(1);
															store3.loadPage(1);
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"file","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							},{
								text:"첨부된 게시물/댓글 삭제",
								handler:function() {
									if (Ext.getCmp("ListTab").getActiveTab().getId() == "ListPanel3") {
										var checked = Ext.getCmp("ListPanel3View").getSelectionModel().getSelection();
									} else {
										var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
									}
									
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"게시물을 삭제할 파일을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 파일이 첨부된 게시물/댓글을 정말 삭제하시겠습니까?<br />삭제된 게시물은 휴지통에서 확인가능합니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 파일이 첨부된 게시물/댓글을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															store1.loadPage(1);
															store2.loadPage(1);
															store3.loadPage(1);
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"file","do":"reptodelete","idx":idxs.join(",")}
											});
										}
									}});
								}
							}]
						})
					}),
					'-',
					new Ext.Button({
						id:"BtnRetrench",
						text:"미기록파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_link_error.png",
						handler:function() {
							new Ext.Window({
								id:"RetrenchWindow",
								title:"미기록파일정리",
								width:450,
								modal:true,
								resizable:false,
								items:[
									new Ext.form.FormPanel({
										id:"RetrenchForm",
										border:false,
										bodyPadding:"10 10 5 10",
										fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
										items:[
											new Ext.form.FieldContainer({
												fieldLabel:"기준연월",
												layout:"hbox",
												items:[
													new Ext.form.ComboBox({
														name:"year",
														typeAhead:true,
														lazyRender:false,
														width:80,
														store:new Ext.data.ArrayStore({
															fields:["value","display"],
															data:[
																<?php for ($i=date('Y');$i>1970;$i--) { ?>
																["<?php echo $i; ?>","<?php echo $i; ?>년"],
																<?php } ?>
																["1970","1970년"]
															]
														}),
														editable:false,
														mode:"local",
														displayField:"display",
														valueField:"value",
														triggerAction:"all",
														value:"<?php echo date('Y'); ?>"
													}),
													new Ext.form.ComboBox({
														name:"month",
														typeAhead:true,
														lazyRender:false,
														width:60,
														style:{marginLeft:"5px"},
														store:new Ext.data.ArrayStore({
															fields:["value","display"],
															data:[
																["01","1월"],
																["02","2월"],
																["03","3월"],
																["04","4월"],
																["05","5월"],
																["06","6월"],
																["07","7월"],
																["08","8월"],
																["09","9월"],
																["10","10월"],
																["11","11월"],
																["12","12월"]
															]
														}),
														editable:false,
														mode:"local",
														displayField:"display",
														valueField:"value",
														triggerAction:"all",
														value:"<?php echo date('m'); ?>"
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;이후 정리",
														flex:1
													}),
													new Ext.form.Checkbox({
														name:"all",
														boxLabel:"전체기간",
														style:{marginLeft:"5px"},
														listeners:{change:{fn:function(form) {
															if (form.checked == true) {
																Ext.getCmp("RetrenchForm").getForm().findField("year").disable();
																Ext.getCmp("RetrenchForm").getForm().findField("month").disable();
															} else {
																Ext.getCmp("RetrenchForm").getForm().findField("year").enable();
																Ext.getCmp("RetrenchForm").getForm().findField("month").enable();
															}
														}}}
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.Msg.show({title:"안내",msg:"선택한 기간 이후 첨부된 파일 중 DB에서 관리되고 있지 않은 첨부파일을 찾아 전부 삭제합니다.<br />이 작업은 첨부파일폴더전체를 검색하기때문에 시간이 많이 소요될 수 있습니다.<br />기록되지 않은 파일은 찾아 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "yes") {
													new Ext.Window({
														id:"ProgressWindow",
														width:500,
														title:"미기록파일정리",
														modal:true,
														closable:false,
														resizable:false,
														draggable:false,
														bodyPadding:"5 5 5 5",
														items:[
															new Ext.ProgressBar({
																id:"ProgressDir",
																text:"첨부파일 폴더구조를 파악중입니다."
															}),
															new Ext.ProgressBar({
																hidden:true,
																id:"ProgressFile",
																text:"폴더내의 파일삭제를 준비중입니다.",
																style:{marginTop:"5px"}
															})
														],
														listeners:{show:{fn:function() {
															if (Ext.getCmp("RetrenchForm").getForm().findField("all").checked == true) {
																execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php?action=file&do=retrench";
															} else {
																execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php?action=file&do=retrench&date="+Ext.getCmp("RetrenchForm").getForm().findField("year").getValue()+""+Ext.getCmp("RetrenchForm").getForm().findField("month").getValue();
															}
															
															Ext.getCmp("RetrenchWindow").close();
														}}}
													}).show();
												}
											}});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("RetrenchWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					new Ext.Button({
						id:"BtnNoRepto",
						text:"첨부파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_link_break.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"파일이 첨부된 게시물 또는 댓글이 삭제된 파일을 서버에서 삭제합니다.<br />휴지통에 보관된 게시물의 첨부파일은 삭제되지 않으므로 휴지통비우기를 실행하여 주십시오.<br />이 작업은 시간이 많이 소요될 수 있습니다. 첨부파일정리를 계속 하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									new Ext.Window({
										id:"ProgressWindow",
										width:500,
										title:"첨부파일정리",
										modal:true,
										closable:false,
										resizable:false,
										draggable:false,
										bodyPadding:"5 5 5 5",
										items:[
											new Ext.ProgressBar({
												id:"ProgressDir",
												text:"첨부된 게시물 또는 댓글이 삭제된 파일을 검색중입니다."
											})
										],
										listeners:{show:{fn:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php?action=file&do=norepto";
										}}}
									}).show();
								}
							}});
						}
					}),
					new Ext.Button({
						id:"BtnRemovetemp",
						text:"임시파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_link_delete.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"게시물에 첨부되지 않고 임시로 업로드된 파일을 정리합니다.<br />임시파일정리를 계속 하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									new Ext.Window({
										id:"ProgressWindow",
										width:500,
										title:"임시파일정리",
										modal:true,
										closable:false,
										resizable:false,
										draggable:false,
										bodyPadding:"5 5 5 5",
										items:[
											new Ext.ProgressBar({
												id:"ProgressDir",
												text:"게시물에 첨부되지 않고 임시로 업로드된 파일을 검색중입니다."
											})
										],
										listeners:{show:{fn:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php?action=file&do=removetemp";
										}}}
									}).show();
								}
							}});
						}
					}),
					'-',
					new Ext.Toolbar.TextItem({
						text:"총 첨부된 파일용량 : 계산중...",
						listeners:{render:{fn:function(button) {
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
								success:function(response) {
									var data = Ext.JSON.decode(response.responseText);
									button.setText("총 첨부된 파일용량 : "+GetFileSize(data.totalsize));
								},
								failure:function() {
								},
								headers:{},
								params:{"action":"file","get":"totalsize"}
							});
						}}}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel1",
						title:"첨부파일",
						border:false,
						autoScroll:true,
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
								header:"파일명",
								dataIndex:"filename",
								sortable:true,
								width:250
							},{
								header:"파일용량",
								dataIndex:"filesize",
								sortable:true,
								width:80,
								renderer:function(value) {
									return '<div style="text-align:right; font-family:tahoma;">'+GetFileSize(value)+'</div>';
								}
							},{
								header:"다운",
								dataIndex:"hit",
								menuDisabled:true,
								sortable:true,
								width:40,
								renderer:GridNumberFormat
							},{
								header:"게시판명",
								dataIndex:"releasetitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									if (value) return value+'<span style="font-family:tahoma;">('+record.data.rid+')</span>';
									else if (record.data.repto == 0) return '<span style="color:#999999;">첨부되지않은파일</span>';
									else return '<span style="color:#EF5600;">첨부글이 삭제된 파일</span>';
								}
							},{
								header:"파일이 첨부된 게시물",
								dataIndex:"title",
								sortable:true,
								minWidth:200,
								flex:1,
								renderer:function(value,p,record) {
									var sHTML = "";

									if (value) {
										if (record.data.type == "POST") {
											if (record.data.postdelete == "TRUE") sHTML+= '<span style="color:#99BBE8;">[휴지통] </span>';
											else sHTML+= '<span style="color:#99BBE8;">[게시물] </span>';
										} else {
											sHTML+= '<span style="color:#EF5600;">[댓글] </span>';
										}
										sHTML+= value;
									} else {
										if (record.data.repto == 0) sHTML+= '<span style="color:#999999;">임시파일정리를 이용하여 정리할 수 있습니다.</span>';
										else sHTML+= '<span style="color:#999999;">첨부파일정리를 이용하여 정리할 수 있습니다.</span>';
									}

									return sHTML;
								}
							},{
								header:"작성자",
								dataIndex:"name",
								sortable:true,
								width:120,
								renderer:function(value,p,record) {
									if (record.data.mno == "0") return value;
									else if (record.data.nickname) return '<b>'+value+"("+record.data.nickname+")</b>";
									else return '<b>'+value+'</b>';
								}
							},{
								header:"첨부일",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							}
						],
						store:store1,
						columnLines:true,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						bbar:new Ext.PagingToolbar({
							store:store1,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:ItemDblClick,
							itemcontextmenu:ItemContextMenu
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListPanel2",
						title:"임시파일",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer({
								header:"번호",
								dataIndex:"idx",
								sortable:true,
								width:60,
								renderer:function(value,p,record) {
									p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
									return GridNumberFormat(value);
								}
							}),{
								header:"파일명",
								dataIndex:"filename",
								sortable:true,
								width:250
							},{
								header:"파일용량",
								dataIndex:"filesize",
								sortable:true,
								width:80,
								renderer:function(value) {
									return '<div style="text-align:right; font-family:tahoma;">'+GetFileSize(value)+'</div>';
								}
							},{
								header:"다운",
								dataIndex:"hit",
								menuDisabled:true,
								sortable:true,
								width:40,
								renderer:GridNumberFormat
							},{
								header:"게시판명",
								dataIndex:"releasetitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									if (value) return value+'<span style="font-family:tahoma;">('+record.data.rid+')</span>';
									else if (record.data.repto == 0) return '<span style="color:#999999;">첨부되지않은파일</span>';
									else return '<span style="color:#EF5600;">첨부글이 삭제된 파일</span>';
								}
							},{
								header:"파일이 첨부된 게시물",
								dataIndex:"title",
								sortable:true,
								minWidth:200,
								flex:1,
								renderer:function(value,p,record) {
									var sHTML = "";

									if (value) {
										if (record.data.type == "POST") {
											if (record.data.postdelete == "TRUE") sHTML+= '<span style="color:#99BBE8;">[휴지통] </span>';
											else sHTML+= '<span style="color:#99BBE8;">[게시물] </span>';
										} else {
											sHTML+= '<span style="color:#EF5600;">[댓글] </span>';
										}
										sHTML+= value;
									} else {
										if (record.data.repto == 0) sHTML+= '<span style="color:#999999;">임시파일정리를 이용하여 정리할 수 있습니다.</span>';
										else sHTML+= '<span style="color:#999999;">첨부파일정리를 이용하여 정리할 수 있습니다.</span>';
									}

									return sHTML;
								}
							},{
								header:"작성자",
								dataIndex:"name",
								sortable:true,
								width:120,
								renderer:function(value,p,record) {
									if (record.data.mno == "0") return value;
									else if (record.data.nickname) return '<b>'+value+"("+record.data.nickname+")</b>";
									else return '<b>'+value+'</b>';
								}
							},{
								header:"첨부일",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							}
						],
						store:store2,
						columnLines:true,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						bbar:new Ext.PagingToolbar({
							store:store2,
							displayInfo:true
						}),
						listeners:{
							itemdblclick:ItemDblClick,
							itemcontextmenu:ItemContextMenu
						}
					}),
					new Ext.Panel({
						id:"ListPanel3",
						title:"이미지뷰어",
						border:false,
						layout:"fit",
						items:[
							new Ext.DataView({
								id:"ListPanel3View",
								border:false,
								autoScroll:true,
								layout:"fit",
								tpl:new Ext.XTemplate(
									'<div id="ImageView">',
									'<tpl for=".">',
									'<div class="item">',
										'<div class="image"><img src="{image}" /></div>',
										'<div class="filename">{filename}</div>',
										'<div class="releasetitle"><span style="color:#99BBE8;"><tpl if="postdelete == \'TRUE\'">휴지통<tpl elseif="type == \'POST\'">게시물<tpl else>댓글</tpl></span>, <tpl if="releasetitle != \'\'">{releasetitle}<tpl else>원본게시물없음</tpl></div>',
									'</div>',
									'</tpl>',
									'</div>'
								),
								store:store3,
								itemSelector:"div.item",
								overItemCls:"over",
								selectedItemCls:"select",
								multiSelect:true,
								trackOver:true,
								plugins:[
									new Ext.ux.DataView.DragSelector({onBeforeStart:function() { return true; }})
								],
								listeners:{
									itemdblclick:{fn:function(view,record,item,index,e) {
										new Ext.Window({
											id:"PreviewWindow",
											title:"이미지보기",
											modal:true,
											maxWidth:800,
											maxHeight:500,
											autoScroll:true,
											html:'<img src="<?php echo $_ENV['dir']; ?>/module/release/exec/ShowImage.do.php?idx='+record.data.idx+'" onload="Ext.getCmp(\'PreviewWindow\').doLayout().center()" />'
										}).show();
									}},
									itemcontextmenu:ItemContextMenu
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							store:store3,
							displayInfo:true,
							items:[
								'-',
								new Ext.Button({
									id:"SortIDX",
									icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_calendar.png",
									text:"등록순으로 보기",
									pressed:true,
									handler:function(button) {
										store3.sort("idx","DESC");
										Ext.getCmp("SortIDX").toggle(false);
										Ext.getCmp("SortHit").toggle(false);
										Ext.getCmp("SortSize").toggle(false);
										button.toggle(true);
									}
								}),
								new Ext.Button({
									id:"SortHit",
									text:"다운로드순으로 보기",
									icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_download.png",
									handler:function(button) {
										store3.sort("hit","DESC");
										Ext.getCmp("SortIDX").toggle(false);
										Ext.getCmp("SortHit").toggle(false);
										Ext.getCmp("SortSize").toggle(false);
										button.toggle(true);
									}
								}),
								new Ext.Button({
									id:"SortSize",
									text:"파일크기순으로 보기",
									icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_disk.png",
									handler:function(button) {
										store3.sort("filesize","DESC");
										Ext.getCmp("SortIDX").toggle(false);
										Ext.getCmp("SortHit").toggle(false);
										Ext.getCmp("SortSize").toggle(false);
										button.toggle(true);
									}
								})
							]
						})
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>