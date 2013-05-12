<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var CategoryStore1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},{name:"sort",type:"int"},"title","image","permission","image"]
		}),
		remoteSort:false,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{action:"category",get:"1"}
	});

	var CategoryStore2 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},{name:"sort",type:"int"},"title","image","permission","image"]
		}),
		remoteSort:false,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{action:"category",get:"2",repto:""}
	});

	var CategoryStore3 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},{name:"sort",type:"int"},"title","image","permission","image"]
		}),
		remoteSort:false,
		sortInfo:{field:"sort",direction:"ASC"},
		baseParams:{action:"category",get:"3",repto:""}
	});

	var CategoryCM = new Ext.grid.ColumnModel([
		new Ext.grid.CheckboxSelectionModel(),
		{
			dataIndex:"idx",
			hideable:false,
			hidden:true,
			sortable:false
		},{
			dataIndex:"sort",
			hideable:false,
			hidden:true,
			sortable:false
		},{
			header:"카테고리명",
			dataIndex:"title",
			width:180,
			sortable:false,
			renderer:function(value,p,record) {
				var sHTML = value;
				if (record.data.image) sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_image.png" style="vertical-align:middle; margin-left:3px;" onmouseover="Tip(true,\'<img src='+record.data.image+'?rnd='+Math.random()+'\',event);" onmouseout="Tip(false)" />';

				return sHTML;
			}
		}
	]);

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"카테고리관리",
		layout:"fit",
		items:[
			new Ext.Panel({
				border:false,
				layout:"column",
				autoScroll:true,
				items:[{
					border:false,
					columWinth:200,
					style:"margin:5px;",
					items:[
						new Ext.grid.GridPanel({
							title:"대분류",
							id:"CategoryList1",
							height:300,
							tbar:[
								new Ext.Button({
									id:"AddCategory1",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_add.png",
									text:"추가",
									handler:function() {
										Ext.getCmp("AddCategory1").disable();
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												CategoryStore1.reload();
												Ext.getCmp("AddCategory1").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("AddCategory1").enable();
											},
											headers:{},
											params:{action:"category",do:"add",depth:"1"}
										});
									}
								}),
								new Ext.Button({
									id:"DelCategory1",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_delete.png",
									text:"삭제",
									handler:function() {
										var checked = Ext.getCmp("CategoryList1").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"삭제할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"카테고리를 삭제하시면 서브카테고리 및 카테고리에 포함된 모든 상품이 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
											if (button == "ok") {
												Ext.getCmp("DelCategory1").disable();

												var idx = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idx[i] = checked[i].get("idx");
												}
												var idxs = idx.join(",");

												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
													success: function() {
														CategoryStore1.reload();
														Ext.getCmp("CategoryModify1").collapse(true);
														Ext.getCmp("DelCategory1").enable();
														CategoryStore2.removeAll();
														Ext.getCmp("CategoryForm2").getForm().reset();
														Ext.getCmp("CategoryModify2").collapse(true);
														CategoryStore3.removeAll();
														Ext.getCmp("CategoryForm3").getForm().reset();
														Ext.getCmp("CategoryModify3").collapse(true);
													},
													failure: function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("DelCategory1").enable();
													},
													headers:{},
													params:{action:"category",do:"delete",depth:"1",idxs:idxs}
												});
											}
										}});
									}
								}),
								'->',
								new Ext.Button({
									id:"UpCategory1",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_up.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList1").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == 1) {
												Ext.Msg.show({title:"안내",msg:"첫번째 카테고리는 순서를 위로 올릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("UpCategory1").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=1, loop=CategoryStore1.getCount();i<loop;i++) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore1.getAt(i).get("idx")) {
															CategoryStore1.getAt(i).set("sort",CategoryStore1.getAt(i).get("sort")-1);
															CategoryStore1.getAt(i-1).set("sort",CategoryStore1.getAt(i-1).get("sort")+1);
															CategoryStore1.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("UpCategory1").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("UpCategory1").enable();
											},
											headers:{},
											params:{action:"category",do:"up",depth:"1",idxs:idxs}
										});
									}
								}),
								new Ext.Button({
									id:"DownCategory1",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_down.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList1").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == CategoryStore1.getCount()) {
												Ext.Msg.show({title:"안내",msg:"마지막 카테고리는 순서를 아래로 내릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("DownCategory1").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=CategoryStore1.getCount()-2;i>=0;i--) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore1.getAt(i).get("idx")) {
															CategoryStore1.getAt(i).set("sort",CategoryStore1.getAt(i).get("sort")+1);
															CategoryStore1.getAt(i+1).set("sort",CategoryStore1.getAt(i+1).get("sort")-1);
															CategoryStore1.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("DownCategory1").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("DownCategory1").enable();
											},
											headers:{},
											params:{action:"category",do:"down",depth:"1",idxs:idxs}
										});
									}
								})
							],
							cm:CategoryCM,
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:CategoryStore1,
							listeners:{rowclick:{fn:function(grid,idx,e) {
								if (Ext.getCmp("CategoryList1").selModel.getSelections().length == 1) {
									Ext.getCmp("CategoryModify1").expand(true);
									var data = grid.getStore().getAt(idx);
									Ext.getCmp("CategoryForm1").getForm().findField("idx").setValue(data.get("idx"));
									Ext.getCmp("CategoryForm1").getForm().findField("title").setValue(data.get("title"));
									Ext.getCmp("CategoryForm1").getForm().findField("permission").setValue(data.get("permission"));

									CategoryStore2.baseParams.repto = grid.getStore().getAt(idx).get("idx");
									CategoryStore2.load();
								} else {
									Ext.getCmp("CategoryModify1").collapse(true);
									CategoryStore2.removeAll();
								}
							}}}
						}),
						new Ext.Panel({
							id:"CategoryModify1",
							title:"카테고리수정",
							style:"margin-top:5px;",
							collapsible:true,
							collapsed:false,
							items:[
								new Ext.form.FormPanel({
									id:"CategoryForm1",
									labelAlign:"top",
									border:false,
									style:"padding:5px;",
									fileUpload:true,
									errorReader:new Ext.form.XmlErrorReader(),
									items:[
										new Ext.form.Hidden({
											name:"idx"
										}),
										new Ext.form.TextField({
											fieldLabel:"카테고리명",
											name:"title",
											width:205,
											allowBlank:false
										}),
										new Ext.form.TextField({
											fieldLabel:"권한",
											name:"permission",
											width:205
										}),
										new Ext.ux.form.FileUploadField({
											fieldLabel:"카테고리 이미지",
											name:"image",
											width:205,
											buttonText:"",
											buttonCfg:{iconCls:"upload-image"},
											listeners:{
												focus:{fn:function(form) {
													if (form.getValue()) {
														Ext.Msg.show({title:"초기화선택",msg:"카테고리이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
										new Ext.form.Checkbox({
											hideLabel:true,
											boxLabel:"카테고리 이미지를 초기화합니다.",
											name:"is_del"
										})
									],
									listeners:{
										render:{fn:function() {
											setTimeout('Ext.getCmp("CategoryModify1").collapse(false)',1000);
										}},
										actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												CategoryStore1.reload();
												Ext.getCmp("CategoryModify1").collapse(false);
												form.reset();
											}
										}}
									}
								})
							],
							bbar:[
								'->',
								new Ext.Button({
									text:"수정하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
									handler:function() {
										Ext.getCmp("CategoryForm1").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=category&do=modify",waitMsg:"수정중입니다."});
									}
								})
							]
						})
					]
				},{
					border:false,
					columWinth:200,
					style:"margin:5px;",
					items:[
						new Ext.grid.GridPanel({
							title:"중분류",
							id:"CategoryList2",
							height:300,
							tbar:[
								new Ext.Button({
									id:"AddCategory2",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_add.png",
									text:"추가",
									handler:function() {
										var depth = Ext.getCmp("CategoryList1").selModel.getSelections();

										if (depth.length != 1) {
											Ext.Msg.show({title:"안내",msg:"대분류를 왼쪽에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										} else {
											var repto = depth[0].get("idx");

											Ext.getCmp("AddCategory2").disable();
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
												success: function() {
													CategoryStore2.reload();
													Ext.getCmp("AddCategory2").enable();
												},
												failure: function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("AddCategory2").enable();
												},
												headers:{},
												params:{action:"category",do:"add",depth:"2",repto:repto}
											});
										}
									}
								}),
								new Ext.Button({
									id:"DelCategory2",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_delete.png",
									text:"삭제",
									handler:function() {
										var checked = Ext.getCmp("CategoryList2").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"삭제할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"카테고리를 삭제하시면 서브카테고리 및 카테고리에 포함된 모든 상품이 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
											if (button == "ok") {
												Ext.getCmp("DelCategory2").disable();

												var idx = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idx[i] = checked[i].get("idx");
												}
												var idxs = idx.join(",");

												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
													success: function() {
														CategoryStore2.reload();
														Ext.getCmp("CategoryModify2").collapse(true);
														Ext.getCmp("DelCategory2").enable();
														CategoryStore3.removeAll();
														Ext.getCmp("CategoryForm3").getForm().reset();
														Ext.getCmp("CategoryModify3").collapse(true);
													},
													failure: function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("DelCategory2").enable();
													},
													headers:{},
													params:{action:"category",do:"delete",depth:"2",idxs:idxs}
												});
											}
										}});
									}
								}),
								'->',
								new Ext.Button({
									id:"UpCategory2",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_up.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList2").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == 1) {
												Ext.Msg.show({title:"안내",msg:"첫번째 카테고리는 순서를 위로 올릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("UpCategory2").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=1, loop=CategoryStore2.getCount();i<loop;i++) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore2.getAt(i).get("idx")) {
															CategoryStore2.getAt(i).set("sort",CategoryStore2.getAt(i).get("sort")-1);
															CategoryStore2.getAt(i-1).set("sort",CategoryStore2.getAt(i-1).get("sort")+1);
															CategoryStore2.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("UpCategory2").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("UpCategory2").enable();
											},
											headers:{},
											params:{action:"category",do:"up",depth:"2",idxs:idxs}
										});
									}
								}),
								new Ext.Button({
									id:"DownCategory2",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_down.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList2").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == CategoryStore2.getCount()) {
												Ext.Msg.show({title:"안내",msg:"마지막 카테고리는 순서를 아래로 내릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("DownCategory2").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=CategoryStore2.getCount()-2;i>=0;i--) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore2.getAt(i).get("idx")) {
															CategoryStore2.getAt(i).set("sort",CategoryStore2.getAt(i).get("sort")+1);
															CategoryStore2.getAt(i+1).set("sort",CategoryStore2.getAt(i+1).get("sort")-1);
															CategoryStore2.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("DownCategory2").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("DownCategory2").enable();
											},
											headers:{},
											params:{action:"category",do:"down",depth:"2",idxs:idxs}
										});
									}
								})
							],
							cm:CategoryCM,
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:CategoryStore2,
							listeners:{rowclick:{fn:function(grid,idx,e) {
								if (Ext.getCmp("CategoryList2").selModel.getSelections().length == 1) {
									Ext.getCmp("CategoryModify2").expand(true);
									var data = grid.getStore().getAt(idx);
									Ext.getCmp("CategoryForm2").getForm().findField("idx").setValue(data.get("idx"));
									Ext.getCmp("CategoryForm2").getForm().findField("title").setValue(data.get("title"));
									Ext.getCmp("CategoryForm2").getForm().findField("permission").setValue(data.get("permission"));

									CategoryStore3.baseParams.repto = grid.getStore().getAt(idx).get("idx");
									CategoryStore3.load();
								} else {
									Ext.getCmp("CategoryModify2").collapse(true);
									CategoryStore3.removeAll();
								}
							}}}
						}),
						new Ext.Panel({
							id:"CategoryModify2",
							title:"카테고리수정",
							style:"margin-top:5px;",
							collapsible:true,
							collapsed:false,
							items:[
								new Ext.form.FormPanel({
									id:"CategoryForm2",
									labelAlign:"top",
									border:false,
									style:"padding:5px;",
									fileUpload:true,
									errorReader:new Ext.form.XmlErrorReader(),
									items:[
										new Ext.form.Hidden({
											name:"idx"
										}),
										new Ext.form.TextField({
											fieldLabel:"카테고리명",
											name:"title",
											width:205,
											allowBlank:false
										}),
										new Ext.form.TextField({
											fieldLabel:"권한",
											name:"permission",
											width:205
										}),
										new Ext.ux.form.FileUploadField({
											fieldLabel:"카테고리 이미지",
											name:"image",
											width:205,
											buttonText:"",
											buttonCfg:{iconCls:"upload-image"},
											listeners:{
												focus:{fn:function(form) {
													if (form.getValue()) {
														Ext.Msg.show({title:"초기화선택",msg:"카테고리이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
										new Ext.form.Checkbox({
											hideLabel:true,
											boxLabel:"카테고리 이미지를 초기화합니다.",
											name:"is_del"
										})
									],
									listeners:{
										render:{fn:function() {
											setTimeout('Ext.getCmp("CategoryModify2").collapse(false)',1000);
										}},
										actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												CategoryStore2.reload();
												Ext.getCmp("CategoryModify2").collapse(false);
												form.reset();
											}
										}}
									}
								})
							],
							bbar:[
								'->',
								new Ext.Button({
									text:"수정하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
									handler:function() {
										Ext.getCmp("CategoryForm2").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=category&do=modify",waitMsg:"수정중입니다."});
									}
								})
							]
						})
					]
				},{
					border:false,
					columWinth:200,
					style:"margin:5px;",
					items:[
						new Ext.grid.GridPanel({
							title:"소분류",
							id:"CategoryList3",
							height:300,
							tbar:[
								new Ext.Button({
									id:"AddCategory3",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_add.png",
									text:"추가",
									handler:function() {
										var depth = Ext.getCmp("CategoryList2").selModel.getSelections();

										if (depth.length != 1) {
											Ext.Msg.show({title:"안내",msg:"중분류를 왼쪽에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										} else {
											var repto = depth[0].get("idx");

											Ext.getCmp("AddCategory3").disable();
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
												success: function() {
													CategoryStore3.reload();
													Ext.getCmp("AddCategory3").enable();
												},
												failure: function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("AddCategory3").enable();
												},
												headers:{},
												params:{action:"category",do:"add",depth:"3",repto:repto}
											});
										}
									}
								}),
								new Ext.Button({
									id:"DelCategory3",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_delete.png",
									text:"삭제",
									handler:function() {
										var checked = Ext.getCmp("CategoryList3").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"삭제할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"카테고리를 삭제하시면 서브카테고리 및 카테고리에 포함된 모든 상품이 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
											if (button == "ok") {
												Ext.getCmp("DelCategory3").disable();

												var idx = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idx[i] = checked[i].get("idx");
												}
												var idxs = idx.join(",");

												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
													success: function() {
														CategoryStore3.reload();
														Ext.getCmp("CategoryModify3").collapse(true);
														Ext.getCmp("DelCategory3").enable();
													},
													failure: function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("DelCategory3").enable();
													},
													headers:{},
													params:{action:"category",do:"delete",depth:"3",idxs:idxs}
												});
											}
										}});
									}
								}),
								'->',
								new Ext.Button({
									id:"UpCategory3",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_up.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList3").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == 1) {
												Ext.Msg.show({title:"안내",msg:"첫번째 카테고리는 순서를 위로 올릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("UpCategory3").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=1, loop=CategoryStore3.getCount();i<loop;i++) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore3.getAt(i).get("idx")) {
															CategoryStore3.getAt(i).set("sort",CategoryStore3.getAt(i).get("sort")-1);
															CategoryStore3.getAt(i-1).set("sort",CategoryStore3.getAt(i-1).get("sort")+1);
															CategoryStore3.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("UpCategory3").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("UpCategory3").enable();
											},
											headers:{},
											params:{action:"category",do:"up",depth:"3",idxs:idxs}
										});
									}
								}),
								new Ext.Button({
									id:"DownCategory3",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_down.png",
									handler:function() {
										var checked = Ext.getCmp("CategoryList3").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"안내",msg:"순서를 변경할 카테고리를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											return false;
										}

										var idx = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("sort") == CategoryStore3.getCount()) {
												Ext.Msg.show({title:"안내",msg:"마지막 카테고리는 순서를 아래로 내릴 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												return false;
											}
											idx[i] = checked[i].get("idx");
										}
										var idxs = idx.join(",");

										Ext.getCmp("DownCategory3").disable();

										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php",
											success: function() {
												for (var i=CategoryStore3.getCount()-2;i>=0;i--) {
													for (var m=0, loopm=idx.length;m<loopm;m++) {
														if (idx[m] == CategoryStore3.getAt(i).get("idx")) {
															CategoryStore3.getAt(i).set("sort",CategoryStore3.getAt(i).get("sort")+1);
															CategoryStore3.getAt(i+1).set("sort",CategoryStore3.getAt(i+1).get("sort")-1);
															CategoryStore3.sort("sort","ASC");
															break;
														}
													}
												}

												Ext.getCmp("DownCategory3").enable();
											},
											failure: function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 변경하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("DownCategory3").enable();
											},
											headers:{},
											params:{action:"category",do:"down",depth:"3",idxs:idxs}
										});
									}
								})
							],
							cm:CategoryCM,
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:CategoryStore3,
							listeners:{rowclick:{fn:function(grid,idx,e) {
								if (Ext.getCmp("CategoryList3").selModel.getSelections().length == 1) {
									Ext.getCmp("CategoryModify3").expand(true);
									var data = grid.getStore().getAt(idx);
									Ext.getCmp("CategoryForm3").getForm().findField("idx").setValue(data.get("idx"));
									Ext.getCmp("CategoryForm3").getForm().findField("title").setValue(data.get("title"));
									Ext.getCmp("CategoryForm3").getForm().findField("permission").setValue(data.get("permission"));
								} else {
									Ext.getCmp("CategoryModify3").collapse(true);
								}
							}}}
						}),
						new Ext.Panel({
							id:"CategoryModify3",
							title:"카테고리수정",
							style:"margin-top:5px;",
							collapsible:true,
							collapsed:false,
							items:[
								new Ext.form.FormPanel({
									id:"CategoryForm3",
									labelAlign:"top",
									border:false,
									style:"padding:5px;",
									fileUpload:true,
									errorReader:new Ext.form.XmlErrorReader(),
									items:[
										new Ext.form.Hidden({
											name:"idx"
										}),
										new Ext.form.TextField({
											fieldLabel:"카테고리명",
											name:"title",
											width:205,
											allowBlank:false
										}),
										new Ext.form.TextField({
											fieldLabel:"권한",
											name:"permission",
											width:205
										}),
										new Ext.ux.form.FileUploadField({
											fieldLabel:"카테고리 이미지",
											name:"image",
											width:205,
											buttonText:"",
											buttonCfg:{iconCls:"upload-image"},
											listeners:{
												focus:{fn:function(form) {
													if (form.getValue()) {
														Ext.Msg.show({title:"초기화선택",msg:"카테고리이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
										new Ext.form.Checkbox({
											hideLabel:true,
											boxLabel:"카테고리 이미지를 초기화합니다.",
											name:"is_del"
										})
									],
									listeners:{
										render:{fn:function() {
											setTimeout('Ext.getCmp("CategoryModify3").collapse(false)',1000);
										}},
										actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												CategoryStore3.reload();
												Ext.getCmp("CategoryModify3").collapse(false);
												form.reset();
											}
										}}
									}
								})
							],
							bbar:[
								'->',
								new Ext.Button({
									text:"수정하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
									handler:function() {
										Ext.getCmp("CategoryForm3").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=category&do=modify",waitMsg:"수정중입니다."});
									}
								})
							]
						})
					]
				}]
			})
		]
	});

	CategoryStore1.load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>