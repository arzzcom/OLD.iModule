<style type="text/css">
#ImageView .item {border:1px solid #CCCCCC; padding:5px; width:160px; display:inline-block; margin:10px 0px 15px 10px;}
#ImageView .select {border:1px solid #044AA9; background:#DAE6F3;}
#ImageView .item .image {width:150px; height:120px;}
#ImageView .item .image IMG {width:150px; height:120px;}
#ImageView .item .filename {width:150px; margin:5px 0px 0px 0px; font-size:12px; font-weight:bold; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;}
#ImageView .item .mailtitle {width:150px; margin:5px 0px 0px 0px; color:#666666; font-size:11px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;}
.x-view-selector {position:absolute;left:0;top:0;width:0;border:1px dotted;opacity: .5;-moz-opacity: .5;filter:alpha(opacity=50);zoom:1;background-color:#c3daf9;border-color:#3399bb;}.ext-strict .ext-ie .x-tree .x-panel-bwrap{position:relative;overflow:hidden;}
</style>
<script type="text/javascript">
Ext.require([
	'Ext.ux.DataView.DragSelector'
]);
function RetrenchProgressControl(fileLimit,fileTotal,fileDelete) {
	Ext.getCmp("ProgressFile").updateProgress(fileLimit/fileTotal,"폴더의 불필요한 첨부파일을 삭제중입니다. ("+GetNumberFormat(fileLimit)+"/"+GetNumberFormat(fileTotal)+", 삭제된파일 : "+GetNumberFormat(fileDelete)+"개)",true);
	
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
	Ext.getCmp("ProgressFile").updateProgress(fileLimit/fileTotal,"임시파일을 정리하고 있습니다. ("+GetNumberFormat(fileLimit)+"/"+GetNumberFormat(fileTotal)+", 삭제된파일 : "+GetNumberFormat(fileDelete)+"개)",true);
	
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
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"register",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","repto","subject","filename","filetype","filesize","filepath","hit"]
	});
	
	var store2 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"temp",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","repto","subject","filename","filetype","filesize","filepath","hit"]
	});

	var store3 = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"file",get:"image",keyword:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","image","repto","subject","filename","filetype","filesize","filepath","hit"]
	});
	
	function ItemDblClick(grid,record,row,index,e) {
		if (record.data.repto && record.data.subject) {
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
						params:{action:"email",idx:record.data.repto,mode:"group"}
					});
				}}}
			}).show();
		} else {
			Ext.Msg.show({title:"에러",msg:"이 파일은 메일이 발송되지 않았거나, 첨부된 메일내역이 삭제되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
		}
	}
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.filename+'</b>');
		
		menu.add({
			text:"파일다운로드",
			handler:function() {
				execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/FileDownload.do.php?idx="+record.data.idx;
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
				text:"파일이 첨부된 메일보기",
				handler:function(item) {
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
								params:{action:"email",idx:record.data.repto,mode:"group"}
							});
						}}}
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
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_magnifier.png",
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
						id:"BtnRetrench",
						text:"미기록파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_link_error.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"DB에서 관리되고 있지 않은 첨부파일을 찾아 전부 삭제합니다.<br />이 작업은 첨부파일폴더전체를 검색하기때문에 시간이 많이 소요될 수 있습니다.<br />기록되지 않은 파일은 찾아 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
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
												id:"ProgressFile",
												text:"DB에서 관리되고 있지 않은 첨부파일을 검색중입니다."
											})
										],
										listeners:{show:{fn:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=file&do=retrench";
										}}}
									}).show();
								}
							}});
						}
					}),
					new Ext.Button({
						id:"BtnRemovetemp",
						text:"임시파일정리",
						icon:"<?php echo $_ENV['dir']; ?>/module/email/images/admin/icon_link_delete.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"메일에 첨부되지 않고 임시로 업로드된 파일을 정리합니다.<br />임시파일정리를 계속 하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
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
												id:"ProgressFile",
												text:"메일 첨부되지 않고 임시로 업로드된 파일을 검색중입니다."
											})
										],
										listeners:{show:{fn:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.do.php?action=file&do=removetemp";
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
								url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php",
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
								header:"파일이 첨부된 메일",
								dataIndex:"subject",
								sortable:true,
								minWidth:200,
								flex:1
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
								header:"파일이 첨부된 메일",
								dataIndex:"subject",
								sortable:true,
								minWidth:200,
								flex:1,
								renderer:function(value,p,record) {
									return '<span style="color:#999999;">임시파일정리를 이용하여 정리할 수 있습니다.</span>';
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
										'<div class="mailtitle"><tpl if="subject != \'\'">{subject}<tpl else>원본게시물없음</tpl></div>',
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
											html:'<img src="<?php echo $_ENV['dir']; ?>/module/email/exec/ShowImage.do.php?idx='+record.data.idx+'" onload="Ext.getCmp(\'PreviewWindow\').doLayout().center()" />'
										}).show();
									}},
									itemcontextmenu:ItemContextMenu
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							store:store3,
							displayInfo:true
						})
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>