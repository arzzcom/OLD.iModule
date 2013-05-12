<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"배너관리",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:"border",
				border:false,
				items:[
					new Ext.grid.GridPanel({
						id:"CodeList",
						title:"배너목록",
						layout:"fit",
						autoScroll:true,
						region:"west",
						width:300,
						split:true,
						margins:"5 0 5 5",
						tbar:[
							new Ext.Button({
								text:"새 배너추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_photo_add.png",
								handler:function() {
									new Ext.Window({
										id:"AddWindow",
										title:"새 배너추가",
										width:400,
										style:"background:#FFFFFF;",
										items:[
											new Ext.form.FormPanel({
												id:"AddForm",
												border:false,
												style:"padding:5px; background:#FFFFFF;",
												errorReader:new Ext.form.XmlErrorReader(),
												labelWidth:60,
												items:[
													new Ext.form.TextField({
														fieldLabel:"배너코드",
														width:300,
														name:"code",
														allowBlank:false,
														emptyText:"영문 및 숫자로만 입력하여 주세요."
													}),
													new Ext.form.TextField({
														fieldLabel:"배너설명",
														width:300,
														name:"info",
														allowBlank:false,
														emptyText:"어떤 배너인지 확인할 수 있는 설명을 입력하세요."
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO});
														Ext.getCmp("CodeList").getStore().reload();
														Ext.getCmp("BannerList").getStore().removeAll();
														Ext.getCmp("AddWindow").close();
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("AddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=banner&do=add",waitMsg:"등록중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("AddWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_photo_delete.png",
								handler:function() {
									var checked = Ext.getCmp("CodeList").selModel.getSelections();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"삭제할 배너코드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										return false;
									}

									Ext.Msg.show({title:"안내",msg:"배너코드를 삭제하시면 해당 배너코드에 포함된 모든 배너이미지도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
										if (button == "ok") {
											var code = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												code[i] = checked[i].get("code");
											}
											var codes = code.join(",");

											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
												success: function() {
													Ext.getCmp("CodeList").getStore().reload();
													Ext.getCmp("BannerList").getStore().removeAll();
												},
												failure: function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{action:"banner",do:"delete",codes:codes}
											});
										}
									}});
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"배너코드",
								dataIndex:"code",
								width:190,
								sortable:false,
								renderer:function(value,p,record) {
									return '<div onmouseover="Tip(true,\''+record.data.info+'\',event);" onmouseout="Tip(false)">'+value+'</div>';
								}
							},{
								header:"배너수",
								dataIndex:"banner",
								width:60,
								sortable:false,
								renderer:GridNumberFormat
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:'lists',
								totalProperty:'totalCount',
								fields:["code","info",{name:"banner",type:"int"}]
							}),
							remoteSort:false,
							sortInfo:{field:"code",direction:"ASC"},
							baseParams:{action:"banner",get:"list"}
						}),
						listeners:{rowclick:{fn:function(grid,idx,e) {
							if (Ext.getCmp("CodeList").selModel.getSelections().length == 1) {
								Ext.getCmp("BannerList").getStore().baseParams.code = Ext.getCmp("CodeList").getStore().getAt(idx).get("code");
								Ext.getCmp("BannerList").getStore().reload();
							} else {
								Ext.getCmp("BannerList").getStore().removeAll();
							}
						}}}
					}),
					new Ext.grid.GridPanel({
						id:"BannerList",
						title:"배너이미지목록",
						layout:"fit",
						autoScroll:true,
						region:"center",
						margins:"5 5 5 0",
						autoScroll:true,
						tbar:[
							new Ext.Button({
								text:"새 배너이미지추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_photo_link.png",
								handler:function() {
									var checked = Ext.getCmp("CodeList").selModel.getSelections();
									if (checked.length != 1) {
										Ext.Msg.show({title:"안내",msg:"배너목록에서 배너코드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										return false;
									}

									var code = checked[0].get("code");

									new Ext.Window({
										id:"AddBannerWindow",
										title:"새 배너이미지추가",
										width:400,
										style:"background:#FFFFFF;",
										items:[
											new Ext.form.FormPanel({
												id:"AddBannerForm",
												border:false,
												style:"padding:5px; background:#FFFFFF;",
												errorReader:new Ext.form.XmlErrorReader(),
												labelWidth:70,
												fileUpload:true,
												items:[
													new Ext.ux.form.FileUploadField({
														fieldLabel:"배너이미지",
														name:"image",
														width:290,
														buttonText:"",
														buttonCfg:{iconCls:"upload-image"},
														emptyText:"이미지 또는 플래시파일을 업로드하여 주십시오.",
														listeners:{
															focus:{fn:function(form) {
																if (form.getValue()) {
																	Ext.Msg.show({title:"초기화선택",msg:"배너이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																		if (button == "ok") {
																			form.reset();
																		}
																	}});
																}
															}},
															invalid:{fn:function(form,text) {
																if (form.getValue()) {
																	form.reset();
																	form.markInvalid(text);
																}
															}}
														}
													}),
													new Ext.form.TextField({
														fieldLabel:"링크",
														width:290,
														name:"url",
														allowBlank:false,
														emptyText:"배너클릭시 이동할 주소를 입력하여 주세요."
													}),
													new Ext.form.ComboBox({
														fieldLabel:"타켓설정",
														hiddenName:"link_target",
														width:100,
														typeAhead:true,
														lazyRender:false,
														listClass:"x-combo-list-small",
														store:new Ext.data.SimpleStore({
															fields:["target","text"],
															data:[["_SELF","현재창에서"],["_BLANK","새창으로"]]
														}),
														editable:false,
														mode:"local",
														triggerAction:"all",
														displayField:"text",
														valueField:"target",
														value:"_SELF",
														allowBlank:false
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO});
														Ext.getCmp("BannerList").getStore().load();
														Ext.getCmp("AddBannerWindow").close();
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("AddBannerForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=banner&do=addimage&code="+code,waitMsg:"등록중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("AddBannerWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_photo_delete.png",
								handler:function() {
									var checked = Ext.getCmp("BannerList").selModel.getSelections();
									if (checked.length == 0) {
										Ext.Msg.show({title:"안내",msg:"삭제할 배너이미지를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										return false;
									}

									Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
										if (button == "ok") {
											var idx = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idx[i] = checked[i].get("idx");
											}
											var idxs = idx.join(",");

											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
												success: function() {
													Ext.getCmp("BannerList").getStore().reload();
												},
												failure: function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{action:"banner",do:"deleteimage",idxs:idxs}
											});
										}
									}});
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								dataIndex:"idx",
								hideable:false,
								hidden:true,
								sortable:false
							},{
								header:"종류",
								dataIndex:"type",
								width:65,
								sortable:false,
								renderer:function(value,p,record) {
									var sHTML = "";
									if (value == "SWF") {
										sHTML+= "플래시";
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_image.png" style="vertical-align:middle; margin-left:5px;" onmouseover="Tip(true,\'<embed src='+record.data.filepath+'></embed>\',event);" onmouseout="Tip(false);" />';
									} else {
										sHTML+= "이미지";
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_image.png" style="vertical-align:middle; margin-left:5px;" onmouseover="Tip(true,\'<img src='+record.data.filepath+' />\',event);" onmouseout="Tip(false);" />';
									}

									return sHTML;
								}
							},{
								header:"링크주소",
								dataIndex:"url",
								width:250,
								sortable:false
							},{
								header:"타겟",
								dataIndex:"target",
								width:80,
								sortable:false,
								renderer:function(value) {
									if (value == "_SELF") {
										return "현재창";
									} else {
										return "새창으로";
									}
								}
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:'lists',
								totalProperty:'totalCount',
								fields:[{name:"idx",type:"int"},"type","url","target","filepath"]
							}),
							remoteSort:false,
							sortInfo:{field:"idx",direction:"ASC"},
							baseParams:{action:"banner",get:"image"}
						})
					})
				]
			})
		]
	});

	Ext.getCmp("CodeList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>