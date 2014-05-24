<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"ment",key:"",keyword:"",category:"",rid:""}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","rid","releasetitle","posttitle","postdelete","width","repto","name","nickname","mno","content","reg_date","file","ip"]
	});
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title" style="width:150px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+record.data.content+'</b>');
		
		menu.add({
			text:"댓글삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택한 댓글을 정말 삭제하시겠습니까?<br />삭제된 댓글은 복구할 수 없습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 댓글을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
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
							params:{"action":"ment","do":"delete","idx":record.data.idx}
						});
					}
				}});
			}
		});
		
		menu.add({
			text:"댓글삭제 및 IP차단",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"선택한 댓글을 정말 삭제 및 차단하시겠습니까?<br />삭제된 댓글은 복구할 수 없습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 댓글을 삭제 및 차단하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
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
							params:{"action":"ment","do":"spam","idx":record.data.idx}
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
		title:"댓글관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.form.ComboBox({
						id:"ReleaseID",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"list","is_all":"true"}
							},
							remoteSort:false,
							sorters:[{property:"rid",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["rid","title","option"]
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"rid",
						emptyText:"릴리즈게시판명",
						listeners:{
							select:{fn:function(form,record) {
								store.getProxy().setExtraParam("rid",form.getValue());
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
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_magnifier.png",
						handler:function() {
							store.getProxy().setExtraParam("key",Ext.getCmp("Key").getValue());
							store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							store.loadPage(1);
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 댓글을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"댓글삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 댓글을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 댓글을 정말 삭제하시겠습니까?<br />삭제된 댓글은 복구할 수 없습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 댓글을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
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
												params:{"action":"ment","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							},{
								text:"댓글삭제 및 IP차단",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제 및 차단할 댓글을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"선택한 댓글을 정말 삭제하시겠습니까?<br />삭제된 댓글은 복구할 수 없습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 댓글을 삭제 및 차단하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/release/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제 및 차단하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"ment","do":"spam","idx":idxs.join(",")}
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
						header:"릴리즈게시판명",
						dataIndex:"releasetitle",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							return value+'<span style="font-family:tahoma;">('+record.data.rid+')</span>';
						}
					},{
						header:"게시물제목",
						dataIndex:"posttitle",
						sortable:false,
						minWidth:150
					},{
						header:"댓글내용",
						dataIndex:"content",
						sortable:false,
						minWidth:200,
						flex:1,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (record.data.postdelete == "TRUE") sHTML+= '<span style="color:#99BBE8;">[삭제된게시물]</span> ';
							sHTML+= value;

							return sHTML;
						}
					},{
						header:"첨부",
						dataIndex:"file",
						sortable:false,
						menuDisabled:true,
						width:35,
						css:"font:0/0 arial; text-align:center;",
						renderer:function(value,p,record) {
							var sHTML = "";
							if (value) {
								p.tdCls = Ext.baseCSSPrefix + 'pointer';
								sHTML+= '<div style="height:10px; background:url(<?php echo $_ENV['dir']; ?>/module/release/images/admin/icon_bullet_disk.png) no-repeat 50% 50%;)"></div>';
							}

							return sHTML;
						}
					},{
						header:"작성자",
						dataIndex:"name",
						sortable:false,
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
							title:record.data.posttitle,
							width:record.data.width.indexOf("%") > -1 ? 800 : parseInt(record.data.width),
							height:500,
							layout:"fit",
							maximizable:true,
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/release/release.php?rid='+record.data.rid+'&mode=view&idx='+record.data.repto+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
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
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/release/exec/FileDownload.do.php?idx="+fileInfor[0];
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