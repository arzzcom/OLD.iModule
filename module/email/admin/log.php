<script type="text/javascript">
var ShowProgress = function(count,total) {
	if (!Ext.getCmp("ProgressWindow")) {
		new Ext.Window({
			id:"ProgressWindow",
			width:500,
			title:"메일발송",
			modal:true,
			closable:true,
			resizable:false,
			draggable:false,
			bodyPadding:"5 5 5 5",
			items:[
				new Ext.ProgressBar({
					id:"ProgressBar",
					text:"메일발송 대기중..."
				})
			],
			listeners:{beforeclose:SendCancel}
		}).show();
	}

	if (count == total) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)",true);
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 완료되었습니다.<br />전송기록을 확인하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
			Ext.getCmp("ProgressWindow").close();
			if (button == "ok") {
				location.href = location.href.replace("category=send","category=log");
			}
		}});
	} else if (count > 0) {
		Ext.getCmp("ProgressBar").updateProgress(count/total,"현재 총 "+total+"명 중 "+count+"명 전송완료... ("+(100*count/total).toFixed(2)+"%)",true);
	} else if (count == -1) {
		Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		Ext.Msg.show({title:"안내",msg:"전송이 취소되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() { Ext.getCmp("ProgressWindow").close(); }});
	}
}

var SendCancel = function() {
	Ext.Msg.show({title:"안내",msg:"전송작업을 취소하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
		if (button == "yes") {
			execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=cancel&key="+gKey;
			Ext.getCmp("ProgressWindow").removeListener("beforeclose",SendCancel);
		}
	}});

	return false;
};

ContentArea = function(viewport) {
	this.viewport = viewport;

	var EmailStore = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"list",type:"group"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:[{name:"idx",type:"int"},"from","to","repto","subject","send_date","read_date",{name:"success",type:"int"},{name:"fail",type:"int"},{name:"wait",type:"int"},{name:"read",type:"int"},{name:"total",type:"int"}]
	});
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.subject+'</b>');
		
		menu.add({
			text:"메일보기",
			handler:function() {
				new Ext.Window({
					title:record.data.subject,
					width:600,
					modal:true,
					html:'<div id="ShowPreview" style="background:#FFFFFF; overflow-y:scroll; height:400px; padding:10px;"></div>',
					listeners:{show:{fn:function() {
						Ext.Msg.wait("메일 본문을 불러오고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									document.getElementById("ShowPreview").innerHTML = data.body;
									Ext.Msg.hide();
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{action:"email",idx:record.data.idx,mode:Ext.getCmp("ListPanel").getStore().getProxy().extraParams.type}
						});
					}}}
				}).show();
			}
		});

		if (Ext.getCmp("ListPanel").getStore().getProxy().extraParams.type == "group") {
			menu.add({
				text:"메일재발송",
				handler:function(item) {
					Ext.Msg.show({title:"확인",msg:"대기중인 메일에 대하여 메일을 재발송하시겠습니까?<br />이 기능은 메일전송중 오류로 인해 메일전송이 중단된 경우에만 사용하시기 바랍니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
						if (button == "yes") {
							Ext.Msg.wait("메일을 재발송하기 위해 준비중입니다.","잠시만 기다려주십시오.");
							execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=log&do=resend&repto="+record.data.idx+"&is_smtp=true";
						}
					}});
				}
			});
		}
		
		menu.add({
			text:"기록삭제",
			handler:function(item) {
				Ext.Msg.show({title:"확인",msg:"선택한 메일기록을 정말 삭제하시겠습니까?<br />메일을 받은 유저가 다시 메일을 확인할 때 메일에 포함된 이미지 및 파일이 더이상 보이지 않게 됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 기록을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php",
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
							params:{"action":"log","do":"delete","idx":record.data.idx,"mode":Ext.getCmp("ListPanel").getStore().getProxy().extraParams.type}
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
		title:"메일발송기록",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				layout:"fit",
				tbar:[
					new Ext.form.TextField({
						id:"Keyword",
						width:150,
						emptyText:"제목, 내용"
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							Ext.getCmp("ListPanel").getStore().loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						id:"BtnGroup",
						text:"동일메일묶어보기",
						pressed:true,
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox_on.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("type","group");
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword","");
								Ext.getCmp("ListPanel").getStore().loadPage(1);
								button.toggle(true);
								Ext.getCmp("BtnEach").toggle(false);
								Ext.getCmp("Keyword").emptyText = "제목, 내용";
								Ext.getCmp("Keyword").reset();
							}
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox.png");
						}}}
					}),
					new Ext.Button({
						id:"BtnEach",
						text:"개별보기",
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox.png",
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("type","each");
								Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword","");
								Ext.getCmp("ListPanel").getStore().loadPage(1);
								button.toggle(true);
								Ext.getCmp("BtnGroup").toggle(false);
								Ext.getCmp("Keyword").emptyText = "보낸사람, 받는사람";
								Ext.getCmp("Keyword").reset();
							}
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_checkbox.png");
						}}}
					}),
					'-',
					new Ext.Button({
						text:"선택한 기록을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"기록삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 기록을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 메일기록을 정말 삭제하시겠습니까?<br />메일을 받은 유저가 다시 메일을 확인할 때 메일에 포함된 이미지 및 파일이 더이상 보이지 않게 됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 기록을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php",
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
												params:{"action":"log","do":"delete","idx":idxs.join(","),"mode":Ext.getCmp("ListPanel").getStore().getProxy().extraParams.type}
											});
										}
									}});
								}
							}]
						})
					}),
					'->',
					{xtype:"tbtext",text:"마우스 우클릭 : 상세메뉴 / 더블클릭 : 메일보기"}
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"보낸사람",
						dataIndex:"from",
						width:140
					},{
						header:"받는사람",
						dataIndex:"to",
						width:200,
						renderer:function(value,p,record) {
							var total = record.data.success + record.data.fail + record.data.wait - 1;
							if (total > 0) {
								return value+" 외 "+GetNumberFormat(total)+"명";
							} else {
								return value;
							}
						}
					},{
						header:"제목",
						dataIndex:"subject",
						minWidth:200,
						flex:1
					},{
						header:"발신시간",
						dataIndex:"send_date",
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"성공",
						dataIndex:"success",
						width:50,
						renderer:function(value) {
							return '<div style="font-family:tahoma; color:blue; text-align:right;">'+GetNumberFormat(value)+'</div>';
						}
					},{
						header:"실패",
						dataIndex:"fail",
						width:50,
						renderer:function(value) {
							return '<div style="font-family:tahoma; color:red; text-align:right;">'+GetNumberFormat(value)+'</div>';
						}
					},{
						header:"대기",
						dataIndex:"wait",
						width:50,
						renderer:function(value) {
							return '<div style="font-family:tahoma; color:gray; text-align:right;">'+GetNumberFormat(value)+'</div>';
						}
					},{
						header:"읽음",
						dataIndex:"read",
						width:50,
						renderer:function(value) {
							return '<div style="font-family:tahoma; color:green; text-align:right;">'+GetNumberFormat(value)+'</div>';
						}
					},{
						header:"수신확인",
						dataIndex:"read_date",
						width:120,
						renderer:function(value) {
							if (value) return '<div style="font-family:tahoma; color:blue;">'+value+'</div>';
						}
					}
				],
				store:EmailStore,
				sortableColumns:false,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				bbar:new Ext.PagingToolbar({
					store:EmailStore,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						new Ext.Window({
							title:record.data.subject,
							width:600,
							modal:true,
							html:'<div id="ShowPreview" style="background:#FFFFFF; overflow-y:scroll; height:400px; padding:10px;"></div>',
							listeners:{show:{fn:function() {
								Ext.Msg.wait("메일 본문을 불러오고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											document.getElementById("ShowPreview").innerHTML = data.body;
											Ext.Msg.hide();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{action:"email",idx:record.data.idx,mode:Ext.getCmp("ListPanel").getStore().getProxy().extraParams.type}
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