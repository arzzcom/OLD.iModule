<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$idx = Request('idx');
$mDB = &DB::instance();
$mDatabase = new ModuleDatabase();
$data = $mDB->DBfetch($mDatabase->table['table'],array('field'),"where `idx`=$idx");

$field = GetUnSerialize($data['field']);
$primary = '';
$cm = array();
$file = array();
$store = array();
for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
	if (isset($field[$i]['index']) == true && $field[$i]['index'] == 'PRIMARY') $primary = $field[$i]['name'];

	if (isset($field[$i]['option']) == false || $field[$i]['option'] != 'AUTO_INCREMENT') {
		if ($field[$i]['type'] == 'DATE') {
			$width = 80;
		} elseif ($field[$i]['type'] == 'TEXT' || $field[$i]['type'] == 'HTML') {
			$width = 200;
		} elseif ($field[$i]['type'] == 'SELECT') {
			$width = 80;
		} else {
			$width = $field[$i]['length'] < 50 ? 100 : ($field[$i]['length'] < 100 ? 200 : 300);
		}

		if ($field[$i]['type'] == 'FILE') {
			$cm[] = '{dataIndex:"'.$field[$i]['name'].'",tooltip:"파일정보를 보시려면 클릭하세요.",header:"'.GetString($field[$i]['info'],'ext').'",width:120,renderer:FileFieldRenderer}';
			$file[] = '"'.$field[$i]['name'].'"';
		} else {
			$cm[] = '{dataIndex:"'.$field[$i]['name'].'",header:"'.GetString($field[$i]['info'],'ext').'",width:'.$width.',sortable:true,renderer:GridExtReplace}';
		}

		if ($field[$i]['type'] == 'INT') $store[] = '{name:"'.$field[$i]['name'].'",type:"int"}';
		else $store[] = '"'.$field[$i]['name'].'"';
	}
}
$store[] = '{name:"'.$primary.'",type:"int"}';
?>
<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>테이블보기</title>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
</head>
<body>

<div id="admin">
<script type="text/javascript">
function FileFieldRenderer(value,p,record,row,col) {
	if (value) {
		var temp = value.split("|");
		return '<div style="cursor:pointer; font-family:arial;"><img src="<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_disk.png" style="vertical-align:middle;" />'+temp[1]+'</div>';
	}
}

Ext.QuickTips.init();

var FileList = [<?php echo implode(',',$file); ?>];
var WysiwygList = {};

function ItemFunction(idx) {
	new Ext.Window({
		id:"ItemWindow",
		title:(idx ? "데이터수정" : "데이터추가"),
		width:700,
		modal:true,
		resizable:false,
		layout:"fit",
		autoScroll:true,
		items:[
			new Ext.form.FormPanel({
				id:"ItemForm",
				labelAlign:"right",
				labelWidth:85,
				border:false,
				autoWidth:true,
				autoHeight:true,
				style:"background:#FFFFFF; padding:10px;",
				reader:new Ext.data.XmlReader(
					{record:"form",success:"@success",errormsg:"@errormsg"},
					[<?php echo implode(',',$store); ?>]
				),
				errorReader:new Ext.form.XmlErrorReader(),
				fileUpload:true,
				items:[
					<?php $isFirst = false; for ($i=0, $loop=sizeof($field);$i<$loop;$i++) { if (isset($field[$i]['option']) == false || $field[$i]['option'] != 'AUTO_INCREMENT') { if ($isFirst == true) echo ','; $isFirst = true; ?>
					<?php if ($field[$i]['type'] == 'VARCHAR') { ?>
					new Ext.form.TextField({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						width:550,
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>
					})
					<?php } elseif ($field[$i]['type'] == 'INT') { ?>
					new Ext.form.NumberField({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						width:250,
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>
					})
					<?php } elseif ($field[$i]['type'] == 'DATE') { ?>
					new Ext.form.DateField({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						format:"Y-m-d",
						width:100,
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>
					})
					<?php } elseif ($field[$i]['type'] == 'FILE') { ?>
					new Ext.ux.form.FileUploadField({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						width:550,
						buttonText:"",
						buttonCfg:{iconCls:"upload-file"},
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>,
						listeners:{
							focus:{fn:function(form) {
								if (form.getValue()) {
									Ext.Msg.show({title:"초기화선택",msg:"첨부파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
					new Ext.Panel({
						id:"<?php echo $field[$i]['name']; ?>_delete_area",
						hidden:true,
						layout:"form",
						border:false,
						style:"padding-left:90px;",
						items:[
							new Ext.form.Checkbox({
								hideLabel:true,
								name:"<?php echo $field[$i]['name']; ?>_delete",
								boxLabel:"첨부된 파일을 삭제합니다."
							})
						]
					})
					<?php } elseif ($field[$i]['type'] == 'SELECT') { ?>
					new Ext.form.ComboBox({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						hiddenName:"<?php echo $field[$i]['name']; ?>",
						width:200,
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						listClass:"x-combo-list-small",
						store:new Ext.data.SimpleStore({
							fields:["value"],
							data:[<?php $values = array(); $data = preg_match_all("|'([^,]+)'|",$field[$i]['length'],$match); foreach ($match[1] as $key=>$value) $values[$key] = '["'.$value.'"]'; echo implode(',',$values); ?>]
						}),
						editable:false,
						mode:"local",
						displayField:"value",
						valueField:"value"
					})
					<?php } elseif ($field[$i]['type'] == 'TEXT') { ?>
					new Ext.form.TextArea({
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						width:550,
						height:100,
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>
					})
					<?php } else { ?>
					new Ext.form.TextArea({
						id:"wysiwyg-<?php echo $field[$i]['name']; ?>",
						fieldLabel:"<?php echo $field[$i]['info']; ?>",
						name:"<?php echo $field[$i]['name']; ?>",
						width:550,
						height:250,
						allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>,
						listeners:{render:{fn:function() {
							nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"wysiwyg-<?php echo $field[$i]['name']; ?>",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?mode=simple",fCreator:"createSEditorInIFrame"});
							WysiwygList["<?php echo $field[$i]['name']; ?>"] = true;
						}}}
					}),
					new Ext.Panel({
						border:false,
						style:"padding:0px 0px 5px 90px;",
						html:'<div id="uploader-<?php echo $field[$i]['name']; ?>-area"></div><div id="uploader-<?php echo $field[$i]['name']; ?>-image"></div><div id="uploader-<?php echo $field[$i]['name']; ?>-file"></div>'
					})
					<?php } } } ?>
				],
				listeners:{
					actioncomplete:{fn:function(form,action) {
						if (action.type == "load") {
							for (var i=0, loop=FileList.length;i<loop;i++) {
								if (Ext.getCmp("ItemForm").getForm().findField(FileList[i]).getValue()) {
									if (Ext.getCmp("ItemForm").getForm().findField(FileList[i]+"_delete")) {
										Ext.getCmp(FileList[i]+"_delete_area").show();
									}
								}
							}
						}
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:(idx ? "데이터를 수정하였습니다." : "데이터를 추가하였습니다."),buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("ItemWindow").close();
							ListStore.reload();
						}
					}}
				}
			})
		],
		buttons:[
			new Ext.Button({
				text:"확인",
				icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
				handler:function() {
					for (wysiwyg in WysiwygList) {
						oEditors.getById["wysiwyg-"+wysiwyg].exec("UPDATE_IR_FIELD",[]);
					}
					if (idx) {
						Ext.getCmp("ItemForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=item&do=modify&tno=<?php echo $idx; ?>&idx="+idx,waitMsg:"데이터를 수정중입니다."});
					} else {
						Ext.getCmp("ItemForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=item&do=add&tno=<?php echo $idx; ?>",waitMsg:"데이터를 추가중입니다."});
					}
				}
			})
		],
		listeners:{show:{fn:function() {
			if (idx) {
				Ext.getCmp("ItemForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php?action=item&get=data&tno=<?php echo $idx; ?>&idx="+idx,waitMsg:"데이터를 로딩중입니다."});
			}
			for (wysiwyg in WysiwygList) {
				if (document.getElementById("uploader-"+wysiwyg+"-area")) {
					new AzUploader({
						id:"uploader-"+wysiwyg,
						autoRender:false,
						autoLoad:(idx ? true : false),
						flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
						uploadURL:"<?php echo $_ENV['dir']; ?>/module/database/exec/FileUpload.do.php?tno=<?php echo $idx; ?>&type=HTML&wysiwyg=wysiwyg-"+wysiwyg,
						loadURL:"<?php echo $_ENV['dir']; ?>/module/database/exec/FileLoad.do.php?type=HTML&wysiwyg=wysiwyg-"+wysiwyg+"&repto="+idx,
						buttonURL:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_file_button.gif",
						width:75,
						height:20,
						moduleDir:"<?php echo $_ENV['dir']; ?>/module/database",
						wysiwygElement:"wysiwyg-"+wysiwyg,
						formElement:Ext.getCmp("ItemForm").getForm().el.dom,
						maxFileSize:0,
						maxTotalSize:100,
						listeners:{
							beforeLoad:AzUploaderBeforeLoad,
							onSelect:AzUploaderOnSelect,
							onProgress:AzUploaderOnProgress,
							onComplete:AzUploaderOnComplete,
							onLoad:AzUploaderOnLoad,
							onUpload:AzUploaderOnUpload,
							onError:AzUploaderOnError
						}
					}).render("uploader-"+wysiwyg+"-area");
				}
			}

			if (Ext.getCmp("ItemWindow").getHeight() > 420) {
				Ext.getCmp("ItemWindow").setHeight(420);
			}
			Ext.getCmp("ItemWindow").center();
		}}}
	}).show();
}

var ListStore = new Ext.data.Store({
	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php"}),
	reader:new Ext.data.JsonReader({
		root:"lists",
		totalProperty:"totalCount",
		fields:[<?php echo implode(',',$store); ?>]
	}),
	remoteSort:true,
	sortInfo:{field:"<?php echo $primary; ?>",direction:"DESC"},
	baseParams:{action:"item","get":"list",idx:"<?php echo $idx; ?>",key:"",keyword:""}
});

ListStore.on("load",function() {
	if (ListStore.baseParams.keyword) Ext.getCmp("SearchCancel").enable();
	else Ext.getCmp("SearchCancel").disable();
});

BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel",
						region:"center",
						layout:"fit",
						border:false,
						tbar:[
							new Ext.Button({
								text:"데이터추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_add.png",
								handler:function() {
									ItemFunction();
								}
							}),
							new Ext.Button({
								text:"데이터삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_delete.png",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}

									Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?<br />삭제된 항목은 복원되지 않습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs[i] = checked[i].get("<?php echo $primary; ?>");
											}
											var idx = idxs.join(",");

											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("ListPanel").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"item","do":"delete","tno":"<?php echo $idx; ?>","idx":idx}
											});
										}
									}});
								}
							}),
							'-',
							new Ext.form.ComboBox({
								id:"SearchKey",
								width:100,
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								listClass:"x-combo-list-small",
								store:new Ext.data.SimpleStore({
									fields:["key","name"],
									data:[<?php $isFirst = true; for ($i=0, $loop=sizeof($field);$i<$loop;$i++) { if ($field[$i]['index'] != 'PRIMARY' && $field[$i]['type'] != 'FILE') { if ($isFirst == false) { echo ','; } echo '["'.$field[$i]['name'].'","'.$field[$i]['info'].'"]'; $isFirst = false; }} ?>]
								}),
								editable:false,
								mode:"local",
								displayField:"name",
								valueField:"key",
								emptyText:"검색영역"
							}),
							' ',
							new Ext.form.TextField({
								id:"SearchKeyword",
								width:180,
								emptyText:"검색어를 입력하세요."
							}),
							' ',
							new Ext.Button({
								text:"검색",
								icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_magnifier.png",
								handler:function() {
									if (!Ext.getCmp("SearchKey").getValue()) {
										Ext.Msg.show({title:"에러",msg:"검색영역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}
									if (!Ext.getCmp("SearchKeyword").getValue()) {
										Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}
									ListStore.baseParams.key = Ext.getCmp("SearchKey").getValue();
									ListStore.baseParams.keyword = Ext.getCmp("SearchKeyword").getValue();
									ListStore.reload();
								}
							}),
							new Ext.Button({
								id:"SearchCancel",
								disabled:true,
								text:"검색취소",
								icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_magifier_zoom_out.png",
								handler:function() {
									ListStore.baseParams.key = "";
									Ext.getCmp("SearchKey").setValue("");
									ListStore.baseParams.keyword = "";
									Ext.getCmp("SearchKeyword").setValue("");
									ListStore.reload();
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							<?php echo implode(',',$cm); ?>,
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:ListStore,
						viewConfig:{},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:ListStore,
							displayInfo:true,
							displayMsg:"{0} - {1} of {2}",
							emptyMsg:"데이터없음"
						}),
						listeners:{
							cellclick:{fn:function(grid,idx,col,e) {
								if (grid.colModel.getColumnTooltip(col) == "파일정보를 보시려면 클릭하세요.") {
									var data = grid.getStore().getAt(idx).get(grid.colModel.getDataIndex(col));
									if (data) {
										var temp = data.split("|");

										var menu = new Ext.menu.Menu();
										menu.add('<b class="menu-title">'+temp[1]+'</b>');
										menu.add({
											text:"파일 다운로드 ("+GetFileSize(temp[3])+", <b>"+GetNumberFormat(temp[4])+"</b>Hits)",
											icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_file.png",
											handler:function() {
												downloadFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/database/exec/FileDownload.do.php?idx="+temp[0]+"&tno=<?php echo $idx; ?>";
											}
										});
										menu.add({
											text:"파일 경로보기",
											icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_drive_file.png",
											handler:function() {
												Ext.Msg.show({title:"파일 경로보기",msg:ENV.dir+temp[2],buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											}
										});

										menu.showAt(e.getXY());
									}
								}
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);

								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add({
									text:"데이터수정",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_table_row_insert.png"),
									handler:function(item) {
										ItemFunction(data.get("idx"));
									}
								});
								menu.add({
									text:"데이터삭제",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_table_row_delete.png"),
									handler:function() {
										Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?<br />삭제된 항목은 복원되지 않습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												var idx = data.get("<?php echo $primary; ?>");

												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("ListPanel").getStore().reload();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"item","do":"delete","tno":"<?php echo $idx; ?>","idx":idx}
												});
											}
										}});
									}
								});

								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					})
				]
			});
			this.viewport.doLayout();
			this.viewport.syncSize();
			ListStore.load({params:{start:0,limit:30}});
		}
	}
}();




Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);
</script>

</div>

<iframe name="downloadFrame" style="display:none;"></iframe>

</body>
</html>
