<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/module/wysiwyg/css/default.css" type="text/css" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript">
var oEditors = {};
var AzUploaderVars = "UploadPath=<?php echo urlencode($_ENV['dir'].'/module/shop/exec/FileUpload.do.php?action=item'); ?>&ButtonPath=<?php echo $_ENV['dir']; ?>/module/shop/images/admin/btn_imageupload.gif";
var AzUploaderWidth = 75;
var AzUploaderHeight = 20;
var AzUploaderURL = "<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf?rnd=<?php echo rand(10000,99999); ?>";

function AzUploaderErrorByUser(msg,id) {
	alert(msg);
	UploadEnd(id);
}

function AzUploaderSelectedFileByUser(fileInfor,id) {
	Ext.getCmp("AzFileProgressWindow").show();
	if (fileInfor.length > 0) {
		AzUploaderUpload(id);
	}
}

function AzUploaderProgressByUser(fileInfor,id) {
	Ext.getCmp("AzFileProgressBar").updateProgress(fileInfor.uploaded.file/fileInfor.file.size, fileInfor.file.name+" 업로드 중... ("+Math.round(100*fileInfor.uploaded.file/fileInfor.file.size)+"%)");
	Ext.getCmp("AzTotalProgressBar").updateProgress(fileInfor.uploaded.total/fileInfor.total.size, "전체 "+fileInfor.total.count+"개의 파일중 "+fileInfor.total.upload+"번째 파일 업로드 중... ("+Math.round(100*fileInfor.uploaded.total/fileInfor.total.size)+"%)");
}

function AzUploaderUploadedFileByUser(fileInfor) {
	if (fileInfor.server != "FAIL") {
		var data = fileInfor.server.split("|");
		var sHTML = '<input id="UploaderForm'+data[0]+'" type="hidden" name="file[]" value="'+fileInfor.server+'" />';

		var object = document.getElementById("UploaderPreviewImage");
		sHTML+= '<div id="UploaderFile'+data[0]+'" style="float:left; margin:5px 5px 5px 0px; width:75px;">';

		sHTML+= '<div id="UploaderImage'+data[0]+'"><img src="'+data[1]+'" style="width:71px; height:50px; border:2px solid #CCCCCC; cursor:pointer;" onclick="FileInsert(\''+data[1]+'\');" /></div>';
		sHTML+= '<div style="margin-top:3px;">';
		sHTML+= '<div style="float:left; font-weight:bold; font-family:verdana; font-size:10px;">'+AzUploaderFileSize(data[2])+'</div>';
		sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/btn_imagedelete.gif" alt="삭제" onclick="FileDelete('+data[0]+');" style="float:right; cursor:pointer; margin-top:2px;" />';
		sHTML+= '</div>';
		sHTML+= '</div>';

		object.innerHTML+= sHTML;
	}
}

function FileInsert(filepath) {
	oEditors.getById["wysiwyg"].exec("PASTE_HTML",['<img src="'+filepath+'" />']);
}

function FileDelete(idx) {
	Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
		if (button == "ok") {
			document.getElementById("UploaderForm"+idx).value = idx;
			document.getElementById("UploaderFile"+idx).style.display = "none";
		}
	}});
}

function AzUploaderUploadedCompleteByUser(id) {
	Ext.getCmp("AzFileProgressWindow").hide();
}
</script>

<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var ItemStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"category","title","image","selltype","image",{name:"price",type:"int"},{name:"point",type:"int"},{name:"remain",type:"int"},{name:"limit",type:"int"},"is_soldout"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"item",keyword:"",category:""}
	});

	var CategoryStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:["category","title","display"]
		}),
		remoteSort:false,
		baseParams:{action:"categoryform"}
	});
	CategoryStore.load();

	function ItemFunction(idx) {
		var WithStore = new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.get.php"}),
			reader:new Ext.data.JsonReader({
				root:'lists',
				totalProperty:'totalCount',
				fields:[{name:"idx",type:"int"},"category","title"]
			}),
			remoteSort:true,
			sortInfo:{field:"idx",direction:"DESC"},
			baseParams:{action:"item",keyword:"",category:""}
		});
		WithStore.load({params:{start:0,limit:30}});

		if (idx) {
			var title = "상품수정하기";
		} else {
			idx = "";
			var title = "상품추가";
		}

		new Ext.Window({
			id:"ItemWindow",
			title:title,
			modal:true,
			width:600,
			height:450,
			minWidth:600,
			maximizable:true,
			autoScroll:true,
			items:[
				new Ext.form.FormPanel({
					id:"ItemForm",
					border:false,
					layout:"fit",
					labelWidth:80,
					errorReader:new Ext.form.XmlErrorReader(),
					fileUpload:true,
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									fieldLabel:"상품명",
									name:"title",
									width:400,
									allowBlank:false
								}),
								new Ext.form.TextField({
									fieldLabel:"상품코드",
									name:"code",
									width:100
								}),
								new Ext.Panel({
									border:false,
									html:'<div style="padding:1px 0px 5px 85px;">입력하지 않으면 자동으로 상품코드가 입력됩니다.</div>'
								}),
								new Ext.form.ComboBox({
									fieldLabel:"카테고리",
									hiddenName:"category",
									width:300,
									typeAhead:true,
									lazyRender:false,
									listClass:"x-combo-list-small",
									store:CategoryStore,
									tpl:'<tpl for="."><div class="x-combo-list-item">{title}</div></tpl>',
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"category",
									allowBlank:false
								}),
								new Ext.form.TextField({
									fieldLabel:"원가",
									name:"cost",
									width:100,
									style:"text-align:right;",
									selectOnFocus:true,
									enableKeyEvents:true,
									value:"0",
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.ComboBox({
									fieldLabel:"발매형태",
									hiddenName:"type",
									width:100,
									typeAhead:true,
									lazyRender:false,
									triggerAction:"all",
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["type","text"],
										data:[["1","국내발매"],["2","직접수입"],["3","정식수입"]]
									}),
									editable:false,
									mode:"local",
									value:"1",
									displayField:"text",
									valueField:"type"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"판매정보",
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									fieldLabel:"판매가",
									name:"price",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									selectOnFocus:true,
									value:"0",
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:function(form) {
											var percent = parseInt(Ext.getCmp("ItemForm").getForm().findField("point_setup").getValue());
											var price = parseInt(Ext.getCmp("ItemForm").getForm().findField("price").getValue().replace(/,/g,""));

											var point = Math.floor((price*percent/100)/10)*10;

											Ext.getCmp("ItemForm").getForm().findField("point").setValue(GetNumberFormat(point));
											BlurNumberFormat(form);
										}},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.ComboBox({
									fieldLabel:"포인트설정",
									name:"point_setup",
									width:100,
									typeAhead:true,
									lazyRender:false,
									triggerAction:"all",
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["percent","point"],
										data:[["0","적립없음"],["2","2% 적립"],["5","5% 적립"],["10","10% 적립"]]
									}),
									editable:false,
									mode:"local",
									value:"0",
									displayField:"point",
									valueField:"percent",
									listeners:{select:{fn:function(form) {
										var percent = parseInt(Ext.getCmp("ItemForm").getForm().findField("point_setup").getValue());
										var price = parseInt(Ext.getCmp("ItemForm").getForm().findField("price").getValue().replace(/,/g,""));

										var point = Math.floor((price*percent/100)/10)*10;

										Ext.getCmp("ItemForm").getForm().findField("point").setValue(GetNumberFormat(point));
									}}}
								}),
								new Ext.form.TextField({
									fieldLabel:"예상포인트",
									name:"point",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									readOnly:true,
									value:"0"
								}),
								new Ext.form.TextField({
									fieldLabel:"배송료",
									name:"delivery_price",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									selectOnFocus:true,
									value:"2,500",
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.ComboBox({
									fieldLabel:"판매형태",
									hiddenName:"selltype",
									width:400,
									typeAhead:true,
									lazyRender:false,
									triggerAction:"all",
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["value","text","view"],
										data:[
											["1","일반판매","<b>일반판매: </b>일반적인 결제방식 및 결제방법설정에 따라 구매할 수 있습니다."],
											["2","포인트 특가상품","<b>포인트 특가상품: </b>판매금액의 전액을 포인트로만 구매할 수 있습니다."],
											["3","현금 특가상품","<b>현금 특가상품: </b>현금 또는 포인트로만 구매할 수 있습니다."]
										]
									}),
									tpl:'<tpl for="."><div class="x-combo-list-item">{view}</div></tpl>',
									editable:false,
									mode:"local",
									value:"1",
									displayField:"text",
									valueField:"value",
									listeners:{select:{fn:function(form) {
										if (form.getValue() == "1") {
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").enable();
											Ext.getCmp("ItemForm").getForm().findField("pay_card").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_card").enable();
											Ext.getCmp("ItemForm").getForm().findField("pay_point").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_point").enable();
											Ext.getCmp("ItemForm").getForm().findField("pay_with").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_with").enable();
										} else if (form.getValue() == "2") {
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").setValue(false);
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").disable();
											Ext.getCmp("ItemForm").getForm().findField("pay_card").setValue(false);
											Ext.getCmp("ItemForm").getForm().findField("pay_card").disable();
											Ext.getCmp("ItemForm").getForm().findField("pay_point").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_point").disable();
											Ext.getCmp("ItemForm").getForm().findField("pay_with").setValue(false);
											Ext.getCmp("ItemForm").getForm().findField("pay_with").disable();
										} else if (form.getValue() == "3") {
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_cash").disable();
											Ext.getCmp("ItemForm").getForm().findField("pay_card").setValue(false);
											Ext.getCmp("ItemForm").getForm().findField("pay_card").disable();
											Ext.getCmp("ItemForm").getForm().findField("pay_point").setValue(true);
											Ext.getCmp("ItemForm").getForm().findField("pay_point").enable();
											Ext.getCmp("ItemForm").getForm().findField("pay_with").setValue(false);
											Ext.getCmp("ItemForm").getForm().findField("pay_with").disable();
										}
									}}}
								}),
								new Ext.form.CheckboxGroup({
									fieldLabel:"결제방식",
									columns:1,
									width:400,
									items:[
										new Ext.form.Checkbox({
											boxLabel:"현금결제가능 (무통장입금 / 계좌이체 / 에스크로)",
											name:"pay_cash",
											value:"TRUE",
											style:"margin-top:4px;",
											checked:true
										}),
										new Ext.form.Checkbox({
											boxLabel:"카드결제가능 (휴대폰결제 / 신용카드 등)",
											name:"pay_card",
											value:"TRUE",
											checked:true
										}),
										new Ext.form.Checkbox({
											boxLabel:"포인트결제가능 (일부 또는 전액)",
											name:"pay_point",
											value:"TRUE",
											checked:true
										}),
										new Ext.form.Checkbox({
											boxLabel:"다른상품과 함께 구매할 수 있습니다.",
											name:"pay_with",
											value:"TRUE",
											checked:true
										})
									],
									listeners:{afterRender:{fn:function() {
										Ext.form.CheckboxGroup.superclass.afterRender.apply(this, arguments);
										var form = this.findParentByType('form').getForm();
										form.add.apply(form,this.items.items);
									}}}
								}),
								new Ext.form.Checkbox({
									fieldLabel:"매진표시",
									boxLabel:"재고와 관계없이 매진으로 표시하며, 구매를 하지 못하도록 합니다.",
									name:"is_soldout",
									value:"TRUE"
								}),
								new Ext.form.TextField({
									fieldLabel:"구매제한",
									name:"limit",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									selectOnFocus:true,
									value:"0",
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.Panel({
									border:false,
									html:'<div style="padding:1px 0px 5px 85px;">"0"으로 입력하시면 제한없이 구매할 수 있습니다.</div>'
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"상품이미지",
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.ux.form.FileUploadField({
									fieldLabel:"목록이미지",
									name:"list_image",
									width:400,
									buttonText:"",
									buttonCfg:{iconCls:"upload-image"},
									emptyText:"가로, 세로 150px 이상의 이미지는 썸네일을 만듭니다.",
									listeners:{
										focus:{fn:function(form) {
											if (form.getValue()) {
												Ext.Msg.show({title:"초기화선택",msg:"목록이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
								new Ext.ux.form.FileUploadField({
									fieldLabel:"상품이미지",
									name:"view_image",
									width:400,
									buttonText:"",
									buttonCfg:{iconCls:"upload-image"},
									emptyText:"상품상세정보페이지에 보일 이미지를 선택하세요.",
									listeners:{
										focus:{fn:function(form) {
											if (form.getValue()) {
												Ext.Msg.show({title:"초기화선택",msg:"상품이미지를 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"상품아이콘/종류",
							defaults:{msgTarget:"side",hideLabel:true},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
									new Ext.form.Checkbox({
									name:"is_hot",
									boxLabel:"인기상품으로 설정합니다."
								}),
								new Ext.form.Checkbox({
									name:"is_new",
									boxLabel:"신규상품으로 설정합니다."
								}),
								new Ext.form.Checkbox({
									name:"is_package",
									boxLabel:"패키지상품으로 설정합니다."
								}),
								new Ext.form.Checkbox({
									name:"is_sale",
									boxLabel:"할인상품으로 설정합니다."
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"상세설명",
							defaults:{hideLabel:true,msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextArea({
									id:"wysiwyg",
									name:"content",
									layout:"fit",
									style:"width:100%;",
									height:300,
									listeners:{render:{fn:function() {
										nhn.husky.EZCreator.createInIFrame({
											oAppRef:oEditors,
											elPlaceHolder:"wysiwyg",
											sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php",
											fCreator:"createSEditorInIFrame"
										});
									}}}
								}),
								new Ext.Panel({
									border:false,
									html:'<div id="UploadButton" style="margin-top:3px;"></div><div id="UploaderPreviewImage"></div>',
									listeners:{render:{fn:function() {
										setTimeout('AzUploaderRenderer("uploader","UploadButton")',1500);
									}}}
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"구매권한",
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									name:"permission",
									width:400,
									fieldLabel:"구매권한"
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"관련상품",
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.Panel({
									layout:"border",
									height:400,
									border:false,
									items:[
										new Ext.grid.GridPanel({
											id:"WithItemList",
											region:"west",
											width:360,
											title:"상품목록",
											margins:"0 -1 0 0",
											autoScroll:true,
											tbar:[
												new Ext.form.ComboBox({
													fieldLabel:"카테고리",
													width:130,
													typeAhead:true,
													lazyRender:false,
													listClass:"x-combo-list-small",
													store:CategoryStore,
													tpl:'<tpl for="."><div class="x-combo-list-item">{title}</div></tpl>',
													editable:false,
													mode:"local",
													displayField:"display",
													valueField:"category",
													emptyText:"카테고리선택"
												}),
												' ',
												new Ext.form.TextField({
													id:"WithItemKeyword",
													width:110,
													emptyText:"검색어를 입력하세요",
												}),
												' ',
												new Ext.Button({
													text:"검색",
													icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_magnifier.png"
												}),
												'->',
												new Ext.Button({
													text:"추가",
													icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_arrow_right.png",
													iconAlign:"right"
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
													header:"상품명",
													dataIndex:"title",
													width:310,
													sortable:true,
													renderer:function(value,p,record) {
														var sHTML = '<span style="color:#666666;">['+record.data.category+']</span> '+value;

														if (record.data.image) {
															sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_image.png" style="vertical-align:middle; margin-left:5px;" onmouseover="Tip(true,\'<img src='+record.data.image+' />\',event);" onmouseout="Tip(false);" />';
														}

														return sHTML;
													}
												}
											]),
											store:WithStore,
											bbar:new Ext.PagingToolbar({
												pageSize:30,
												store:WithStore,
												displayInfo:true,
												displayMsg:'{0} - {1} of {2}',
												emptyMsg:"데이터없음"
											})
										}),
										new Ext.grid.GridPanel({
											title:"관련상품",
											region:"center",
											cm:new Ext.grid.ColumnModel([
												new Ext.grid.CheckboxSelectionModel(),
												{
													dataIndex:"idx",
													hideable:false,
													hidden:true,
													sortable:false
												},{
													header:"상품명",
													dataIndex:"title",
													width:150,
													sortable:false
												},{
													header:"상품명",
													dataIndex:"title",
													width:230,
													sortable:true
												}
											]),
											store:new Ext.data.SimpleStore({
												fields:["idx","title"],
												data:[]
											}),
											bbar:[{
												xbtype:"text",
												text:""
											}]
										})
									]
								})
							]
						})
					],
					listeners:{
						beforeaction:{fn:function(form,action) {
							if (action.type == "submit") {
								oEditors.getById["wysiwyg"].exec("UPDATE_IR_FIELD",[]);
							}
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								Ext.getCmp("ListPanel").getStore().reload();
								if (!idx) {
									var newidx;
									Ext.each(action.result.errors,function(item,index,allItems) { newidx = item.id; });
									alert(newidx);
									Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								} else {

								}
								Ext.getCmp("ItemWindow").close();
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_tick.png",
					handler:function() {
						Ext.getCmp("ItemForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/shop/exec/Admin.do.php?action=item&do=add",waitMsg:"데이터를 전송중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("ItemWindow").close();
					}
				})
			],
			listeners:{close:{fn:function() {
				oEditors = {};
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"상품관리",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"ListPanel",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.Button({
						text:"상품추가",
						icon:"<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_brick_add.png",
						handler:function() {
							ItemFunction();
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
						header:"카테고리",
						dataIndex:"category",
						width:150,
						sortable:false
					},{
						header:"상품명",
						dataIndex:"title",
						width:300,
						sortable:true,
						renderer:function(value,p,record) {
							var sHTML = value;

							if (record.data.image) {
								sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_image.png" style="vertical-align:middle; margin-left:5px;" onmouseover="Tip(true,\'<img src='+record.data.image+' />\',event);" onmouseout="Tip(false);" />';
							}

							return sHTML;
						},
						editor:new Ext.form.TextField({allowBlank:false,selectOnFocus:true})
					},{
						header:"판매가",
						dataIndex:"price",
						width:90,
						sortable:false,
						renderer:function(value,p,record) {
							var sHTML = '<div style="font-family:arial; text-align:right;">';
							sHTML+= GetNumberFormat(value);

							if (record.data.selltype == "2") {
								sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_point.gif" style="margin:0px 0px 1px 3px; vertical-align:middle;" />';
							} else {
								sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/shop/images/admin/icon_won.gif" style="margin:0px 0px 1px 3px; vertical-align:middle;" />';
							}

							sHTML+= '</div>';
							return sHTML;
						},
						editor:new Ext.form.NumberField({allowBlank:false,selectOnFocus:true})
					},{
						header:"포인트",
						dataIndex:"point",
						width:75,
						sortable:false,
						renderer:function(value,p,record) {
							if (value == 0) {
								return "적립없음";
							} else {
								return '<span style="font-weight:bold;">'+value+'%</span> 적립';
							}
						},
						editor:new Ext.form.ComboBox({
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["value","display"],
								data:[["0","적립없음"],["2","2% 적립"],["5","5% 적립"],["10","10% 적립"]]
							}),
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"value"
						})
					},{
						header:"구매제한",
						dataIndex:"limit",
						width:60,
						sortable:false,
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+' EA</div>';
						},
						editor:new Ext.form.NumberField({allowBlank:false,selectOnFocus:true})
					},{
						header:"재고",
						dataIndex:"remain",
						width:60,
						sortable:false,
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+GetNumberFormat(value)+' EA</div>';
						},
						editor:new Ext.form.NumberField({allowBlank:false,selectOnFocus:true})
					},{
						header:"상태",
						dataIndex:"is_soldout",
						width:75,
						renderer:function(value) {
							if (value == "TRUE") {
								return "품절";
							} else {
								return "판매중";
							}
						},
						editor:new Ext.form.ComboBox({
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["value","display"],
								data:[["TRUE","품절"],["FALSE","판매중"]]
							}),
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"value"
						})
					}
				]),
				store:ItemStore,
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:ItemStore,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				})
			})
		]
	});

	ItemStore.load({params:{start:0,limit:30}});

	new Ext.Window({
		id:"AzFileProgressWindow",
		title:"사진첨부",
		modal:true,
		width:600,
		resizable:false,
		items:[
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"업로드 대기중",
				id:"AzFileProgressBar",
				cls:"left-align",
				border:false
			}),
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"업로드 대기중",
				id:"AzTotalProgressBar",
				cls:"left-align",
				border:false
			})
		]
	})
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>