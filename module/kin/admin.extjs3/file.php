<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","pid","repto","bid","width","boardtitle","category","type","title","name","reg_date","filename","filetype","filesize","filepath","hit"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"file",get:"register",keyword:""}
	});

	var store2 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","pid","repto","bid","width","boardtitle","category","type","title","name","reg_date","filename","filetype","filesize","filepath","hit"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"file",get:"temp",keyword:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"첨부파일관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListPanel",
				border:false,
				tabPosition:"bottom",
				activeTab:0,
				tbar:[
					new Ext.form.TextField({
						id:"Keyword",
						width:180,
						emptyText:"검색어를 입력하세요."
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
						handler:function() {
							if (!Ext.getCmp("Keyword").getValue()) {
								Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}
							Ext.getCmp("ListPanel").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("Keyword").getValue();
							Ext.getCmp("ListPanel").getActiveTab().getStore().load({params:{start:0,limit:30}});
						}
					}),
					new Ext.Button({
						text:"검색취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magifier_out.png",
						handler:function() {
							Ext.getCmp("Keyword").setValue("");
							Ext.getCmp("ListPanel").getActiveTab().getStore().baseParams.keyword = "";
							Ext.getCmp("ListPanel").getActiveTab().getStore().load({params:{start:0,limit:30}});
						}
					}),
					'-',
					new Ext.Button({
						text:"첨부파일관리",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_disk.png",
						menu:new Ext.menu.Menu({
							items:[
								new Ext.menu.Item({
									text:"첨부파일삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_disk_delete.png",
									handler:function() {
										var checked = Ext.getCmp("ListPanel").getActiveTab().selModel.getSelections();
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 첨부파일을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"선택한 첨부파일을 정말 삭제하시겠습니까?<br />삭제한 첨부파일은 복원할 수 없습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												var idxs = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idxs[i] = checked[i].get("idx");
												}
												var idx = idxs.join(",");

												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("ListPanel").getActiveTab().getStore().reload();
														Ext.getCmp("TotalSize").fireEvent("render");
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"file","do":"delete","idx":idx}
												});
											}
										}});
									}
								})
							]
						})
					}),
					new Ext.Button({
						id:"BtnRetrench",
						text:"첨부파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_link_break.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"파일이 첨부된 게시물 또는 댓글이 삭제된 파일을 서버에서 삭제합니다.<br />휴지통에 보관된 게시물의 첨부파일은 삭제되지 않으므로 휴지통비우기를 실행하여 주십시오.<br />첨부파일정리를 계속 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
								if (button == "ok") {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"첨부파일정리를 완료하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("ListPanel1").getStore().reload();
											Ext.getCmp("TotalSize").fireEvent("render");
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"file","do":"retrench"}
									});
								}
							}});
						}
					}),
					new Ext.Button({
						id:"BtnRemovetemp",
						text:"임시파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_link_delete.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"게시물에 첨부되지 않고 임시로 업로드된 파일을 정리합니다.<br />임시파일정리를 계속 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
								if (button == "ok") {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"첨부파일정리를 완료하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("ListPanel1").getStore().reload();
											Ext.getCmp("TotalSize").fireEvent("render");
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"file","do":"removetemp"}
									});
								}
							}});
						}
					}),
					'-',
					new Ext.Toolbar.TextItem({
						id:"TotalSize",
						text:"총 첨부된 파일용량 : 계산중...",
						listeners:{render:{fn:function() {
							Ext.getCmp("TotalSize").setText("총 첨부된 파일용량 : 계산중...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
								success:function(XML) {
									var totalsize = AjaxResult(XML,"totalsize");
									Ext.getCmp("TotalSize").setText("총 첨부된 파일용량 : "+GetFileSize(totalsize));
								},
								failure:function() {
								},
								headers:{},
								params:{"action":"file","do":"totalsize"}
							});
						}}}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel1",
						layout:"fit",
						title:"첨부파일",
						border:false,
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							{
								header:"번호",
								dataIndex:"idx",
								sortable:true,
								width:60,
								css:GridLoopNum,
								renderer:GridNumberFormat
							},{
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
								dataIndex:"boardtitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									if (value) return value+'<span style="font-family:tahoma;">('+record.data.bid+')</span>';
									else if (record.data.repto == 0) return '<span style="color:#999999;">첨부되지않은파일</span>';
									else return '<span style="color:#EF5600;">첨부글이 삭제된 파일</span>';
								}
							},{
								header:"파일이 첨부된 게시물",
								dataIndex:"title",
								sortable:true,
								width:300,
								renderer:function(value,p,record) {
									var sHTML = "";

									if (value) {
										if (record.data.type == "POST") {
											sHTML+= '<span style="color:#99BBE8;">[게시물] </span>';
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
								width:100
							},{
								header:"첨부일",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:arial; text-align:right;">'+value+'</div>';
								}
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						store:store1,
						sm:new Ext.grid.CheckboxSelectionModel(),
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:store1,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						listeners:{
							cellclick:{fn:function(grid,idx,col,e) {
								if (col == 3) {
									var file = grid.getStore().getAt(idx).get("file");

									if (file) {
										var files = file.split(",");
										var menu = new Ext.menu.Menu();
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
							}},
							rowdblclick:{fn:function(grid,idx,e) {
								execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/board/exec/FileDownload.do.php?idx="+grid.getStore().getAt(idx).get("idx");
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								var menu = new Ext.menu.Menu();
								var data = grid.getStore().getAt(idx);
								menu.add('<b class="menu-title">'+data.get("filename")+'</b>');
								if (data.get("pid")) {
									menu.add({
										text:"첨부된 게시물보기",
										icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table.png",
										handler:function(item) {
											var data = grid.getStore().getAt(idx);
											if (data.get("width").indexOf("%") != -1) {
												var width = 800;
											} else {
												var width = parseInt(data.get("width"))+10;
											}

											new Ext.Window({
												title:data.get("title"),
												width:width,
												height:500,
												layout:"fit",
												maximizable:true,
												style:"background:#FFFFFF;",
												html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'&mode=view&idx='+data.get("pid")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
											}).show();
										}
									});
								}

								menu.add({
									text:"첨부파일경로보기",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_link.png"),
									handler:function(item) {
										Ext.Msg.show({title:data.get("filename"),msg:data.get("filepath"),buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									}
								});
								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListPanel2",
						layout:"fit",
						title:"임시파일",
						border:false,
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							{
								header:"번호",
								dataIndex:"idx",
								sortable:true,
								width:60,
								css:GridLoopNum,
								renderer:GridNumberFormat
							},{
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
								dataIndex:"boardtitle",
								sortable:false,
								width:130,
								renderer:function(value,p,record) {
									if (value) return value+'<span style="font-family:tahoma;">('+record.data.bid+')</span>';
									else if (record.data.pid == 0) return '<span style="color:#999999;">첨부되지않은파일</span>';
									else return '<span style="color:#EF5600;">첨부글이 삭제된 파일</span>';
								}
							},{
								header:"파일이 첨부된 게시물",
								dataIndex:"title",
								sortable:true,
								width:300,
								renderer:function(value,p,record) {
									var sHTML = "";

									if (value) {
										if (record.data.type == "POST") {
											sHTML+= '<span style="color:#99BBE8;">[게시물] </span>';
										} else {
											sHTML+= '<span style="color:#EF5600;">[댓글] </span>';
										}
										sHTML+= value;
									} else {
										if (record.data.pid == 0) sHTML+= '<span style="color:#999999;">임시파일정리를 이용하여 정리할 수 있습니다.</span>';
										else sHTML+= '<span style="color:#999999;">첨부파일정리를 이용하여 정리할 수 있습니다.</span>';
									}

									return sHTML;
								}
							},{
								header:"작성자",
								dataIndex:"name",
								sortable:true,
								width:100
							},{
								header:"첨부일",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:arial; text-align:right;">'+value+'</div>';
								}
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						store:store2,
						sm:new Ext.grid.CheckboxSelectionModel(),
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:store2,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						listeners:{
							cellclick:{fn:function(grid,idx,col,e) {
								if (col == 3) {
									var file = grid.getStore().getAt(idx).get("file");

									if (file) {
										var files = file.split(",");
										var menu = new Ext.menu.Menu();
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
							}},
							rowdblclick:{fn:function(grid,idx,e) {
								execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/board/exec/FileDownload.do.php?idx="+grid.getStore().getAt(idx).get("idx");
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								var menu = new Ext.menu.Menu();
								var data = grid.getStore().getAt(idx);
								menu.add('<b class="menu-title">'+data.get("filename")+'</b>');
								if (data.get("pid")) {
									menu.add({
										text:"첨부된 게시물보기",
										icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table.png",
										handler:function(item) {
											var data = grid.getStore().getAt(idx);
											if (data.get("width").indexOf("%") != -1) {
												var width = 800;
											} else {
												var width = parseInt(data.get("width"))+10;
											}

											new Ext.Window({
												title:data.get("title"),
												width:width,
												height:500,
												layout:"fit",
												maximizable:true,
												style:"background:#FFFFFF;",
												html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'&mode=view&idx='+data.get("pid")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
											}).show();
										}
									});
								}

								menu.add({
									text:"첨부파일경로보기",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_link.png"),
									handler:function(item) {
										Ext.Msg.show({title:data.get("filename"),msg:data.get("filepath"),buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									}
								});
								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab) {
						Ext.getCmp("Keyword").setValue(tab.getStore().baseParams.keyword);

						if (tab.getId() == "ListPanel1") {
							Ext.getCmp("BtnRetrench").show();
							Ext.getCmp("BtnRemovetemp").hide();
						} else {
							Ext.getCmp("BtnRetrench").hide();
							Ext.getCmp("BtnRemovetemp").show();
						}
					}
				}}}
			})
		]
	});

	store1.load({params:{start:0,limit:30}});
	store2.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>