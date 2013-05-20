<script type="text/javascript">
function ModuleProgressControl(tableName,tableCode,tableTotal,dataLimit,dataTotal) {
	Ext.getCmp("ProgressTable").updateProgress(tableCode/tableTotal,"테이블구조를 업데이트 하고 있습니다. ("+tableCode+"/"+tableTotal+")",true);
	
	if (dataTotal != 0) {
		Ext.getCmp("ProgressData").show();
		Ext.getCmp("ProgressData").updateProgress(dataLimit/dataTotal,tableName+" 자료를 복구중입니다. ("+GetNumberFormat(dataLimit)+"/"+GetNumberFormat(dataTotal)+")",true);
	} else {
		Ext.getCmp("ProgressData").hide();
	}
	
	if (tableCode == tableTotal && dataLimit == dataTotal) {
		Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
			Ext.getCmp("ProgressWindow").close();
			Ext.getCmp("ListPanel").getStore().reload();
		}});
	}
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		if (record.data.is_setup == "TRUE") {
			if (record.data.is_config == "TRUE") {
				menu.add({
					text:"모듈기본설정",
					handler:function() {
						new Ext.Window({
							id:"ConfigWindow",
							modal:true,
							title:record.data.title+" 설정하기",
							width:800,
							height:500,
							html:'<iframe id="ConfigFrame" src="./module.config.php?module='+record.data.module+'" style="width:100%; height:100%;" frameborder="0"></iframe>',
							buttons:[
								new Ext.Button({
									text:"수정하기",
									handler:function() {
										document.getElementById("ConfigFrame").contentWindow.Ext.getCmp("ConfigForm").getForm().submit({
											url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=config&module="+record.data.module,
											submitEmptyText:false,
											waitTitle:"잠시만 기다려주십시오.",
											waitMsg:"모듈설정을 수정하고 있습니다.",
											success:function(form,action) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													Ext.getCmp("ConfigWindow").close();
												}});
											},
											failure:function(form,action) {
												if (action.result) {
													if (action.result.message) {
														Ext.Msg.show({title:"에러",msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
										Ext.getCmp("ConfigWindow").close();
									}
								})
							]
						}).show();
					}
				});
				
				menu.add('-');
			}
			
			menu.add({
				text:"모듈 폴더구조 업데이트",
				handler:function() {
					Ext.Msg.show({title:"확인",msg:"모듈에 필요한 폴더를 생성하고, 퍼미션을 조정합니다.<br />해당 작업은 시간이 많이 소요될 수 있습니다. 계속 진행하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
						if (button == "yes") {
							Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
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
								params:{action:"module","do":"update_folder","module":record.data.module}
							});
						}
					}});
				}
			});
			
			menu.add({
				text:"모듈 디비구조 업데이트",
				handler:function() {
					Ext.Msg.show({title:"확인",msg:"모듈에 필요한 디비를 업데이트합니다.<br />만약 기존에 생성된 디비가 있다면 [디비명(BK날짜)]으로 백업되고 디비구조만 업데이트 합니다.<br />해당 작업은 시간이 많이 소요될 수 있습니다. 계속 진행하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
						if (button == "yes") {
							new Ext.Window({
								id:"ProgressWindow",
								width:500,
								title:"모듈 디비구조 업데이트",
								modal:true,
								closable:false,
								resizable:false,
								draggable:false,
								bodyPadding:"5 5 5 5",
								items:[
									new Ext.ProgressBar({
										id:"ProgressTable",
										text:"모듈 디비구조 업데이트를 준비중입니다."
									}),
									new Ext.ProgressBar({
										hidden:true,
										id:"ProgressData",
										text:"데이터복구 준비중입니다.",
										style:{marginTop:"5px"}
									})
								],
								listeners:{show:{fn:function() {
									execFrame.location.href = "<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=update_db&module="+record.data.module;
								}}}
							}).show();
						}
					}});
				}
			});
			
			if (record.data.is_manager == "TRUE") {
				menu.add('-');
				
				menu.add({
					text:"상단메뉴에 고정",
					checked:record.data.is_direct == "TRUE",
					handler:function(item) {
						var value = item.checked == true ? "TRUE" : "FALSE";
						
						Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
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
							params:{action:"module","do":"direct","value":value,"module":record.data.module}
						});
					}
				});
			}
		} else if (record.data.is_setup == "FALSE") {
			menu.add({
				text:"모듈설치하기",
				handler:function() {
					if (record.data.is_config == "TRUE") {
						new Ext.Window({
							id:"InstallWindow",
							modal:true,
							title:record.data.title+" 설치하기",
							width:800,
							height:500,
							html:'<iframe id="InstallFrame" src="./module.config.php?module='+record.data.module+'" style="width:100%; height:100%;" frameborder="0"></iframe>',
							buttons:[
								new Ext.Button({
									text:"설치하기",
									handler:function() {
										document.getElementById("InstallFrame").contentWindow.Ext.getCmp("ConfigForm").getForm().submit({
											url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=install&module="+record.data.module,
											submitEmptyText:false,
											waitTitle:"잠시만 기다려주십시오.",
											waitMsg:"모듈을 설치하고 있습니다.",
											success:function(form,action) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 설치하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													location.href = location.href;
												}});
											},
											failure:function(form,action) {
												if (action.result) {
													if (action.result.message) {
														Ext.Msg.show({title:"에러",msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
										Ext.getCmp("InstallWindow").close();
									}
								})
							]
						}).show();
					} else {
						Ext.Msg.wait("모듈을 설치하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 설치하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{action:"module","do":"install","module":record.data.module}
						});
					}
				}
			});
		}

		e.stopEvent();
		menu.showAt(e.getXY());
	}
	
	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"모듈관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						text:"디비/파일용량 재계산",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_arrow_refresh.png",
						handler:function() {
							Ext.Msg.show({title:"확인",msg:"디비/파일용량 계산은 서버에 부담을 주어 시간이 오래걸릴 수 있습니다.<br />디비 및 파일용량을 재계산하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("calcSize","true");
									Ext.getCmp("ListPanel").getStore().reload();
								}
							}});
						}
					}),
					'->',
					{xtype:"tbtext",text:"마우스 우클릭 : 상세메뉴 / 더블클릭 : 모듈관리"}
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"모듈이름",
						dataIndex:"title",
						sortable:true,
						width:130,
						summaryType:"count",
						summaryRenderer:function(value) {
							return '총 '+value+'개 모듈';
						}
					},{
						header:"모듈절대경로",
						dataIndex:"path",
						sortable:true,
						minWidth:300,
						flex:1,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"모듈버전",
						dataIndex:"version",
						sortable:false,
						width:60,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:center;">'+value+'</div>';
						}
					},{
						header:"디비구조",
						dataIndex:"db",
						sortable:false,
						width:80,
						renderer:function(value,p,record) {
							if (record.data.is_setup == "DISABLE") return '<div style="color:#666666; text-align:center;">설치필요없음</div>';
							if (record.data.is_setup == "FALSE") return '<div style="color:#666666; text-align:center;">설치되지않음</div>';
							if (value == record.data.version) return '<div style="color:blue; text-align:center;">구조정상</div>';
							else return '<div style="color:red; text-align:center;">업데이트필요</div>';
						}
					},{
						header:"폴더구조",
						dataIndex:"folder",
						sortable:false,
						width:80,
						renderer:function(value,p,record) {
							if (record.data.is_setup == "DISABLE") return '<div style="color:#666666; text-align:center;">설치필요없음</div>';
							if (record.data.is_setup == "FALSE") return '<div style="color:#666666; text-align:center;">설치되지않음</div>';
							if (value == "TRUE") return '<div style="color:blue; text-align:center;">구조정상</div>';
							else return '<div style="color:red; text-align:center;">업데이트필요</div>';
						}
					},{
						header:"디비용량",
						dataIndex:"dbsize",
						sortable:true,
						width:90,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetFileSize(value)+'</div>';
						},
						summaryType:"sum"
					},{
						header:"파일용량",
						dataIndex:"filesize",
						sortable:true,
						width:90,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetFileSize(value)+'</div>';
						},
						summaryType:"sum"
					},{
						header:"세부정보",
						dataIndex:"detail",
						sortable:false,
						width:160,
						renderer:function(value,p,record) {
							var sHTML = "";
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_setup_'+record.data.is_setup.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_config_'+record.data.is_config.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_manager_'+record.data.is_manager.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_direct_'+record.data.is_direct.toLowerCase()+'.gif" style="margin-right:2px;" />';

							return sHTML;
						}
					}
				],
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				features:[{
					ftype:"groupingsummary",
					groupHeaderTpl:'<tpl if="name == \'TRUE\'">현재 설치된 모듈<tpl elseif="name == \'FALSE\'">설치는 되지 않았으나, 모듈디렉토리에 존재하는 모듈<tpl else>설치가 필요없는 모듈</tpl>',
					hideGroupedHeader:false,
					enableGroupingMenu: false
				}],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"module",get:"list",keyword:""}
					},
					remoteSort:false,
					sorters:[{property:"title",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					groupField:"is_setup",
					groupDir:"DESC",
					fields:["module","title","version","db","folder",{name:"dbsize",type:"int"},{name:"filesize",type:"int"},"is_setup","is_config","is_manager","is_direct","path"]
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						if (record.data.is_manager == "TRUE") {
							location.href = "./?page=module&subpage="+record.data.module;
						} else {
							Ext.Msg.show({title:"안내",msg:"해당 모듈은 관리자모드가 존재하지 않습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
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