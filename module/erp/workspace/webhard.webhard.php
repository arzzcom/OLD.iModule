<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/webhard/script/script.js"></script>
<script type="text/javascript">
var WebHardUploaderVars = "UploadPath=<?php echo urlencode($_ENV['dir'].'/module/webhard/exec/FileUpload.do.php'); ?>&ButtonPath=<?php echo $_ENV['dir']; ?>/module/webhard/images/admin/btn_fileupload.gif";
var WebHardUploaderWidth = 82;
var WebHardUploaderHeight = 22;
var WebHardUploaderURL = "<?php echo $_ENV['dir']; ?>/module/webhard/flash/WebhardUploader.swf?rnd=<?php echo rand(10000,99999); ?>";

function WebHardUploaderErrorByUser(msg,id) {
	alert(msg);
	UploadEnd(id);
}

function WebHardUploaderSelectedFileByUser(fileInfor,id) {
	Ext.getCmp("WebHardFileProgressWindow").show();
	if (fileInfor.length > 0) {
		WebHardUploaderUpload(id,Ext.getCmp("WebHardList").getStore().baseParams.dir);
	}
}

function WebHardUploaderProgressByUser(fileInfor,id) {
	Ext.getCmp("WebHardFileProgressBar").updateProgress(fileInfor.uploaded.file/fileInfor.file.size, fileInfor.file.name+" 업로드 중... ("+Math.round(100*fileInfor.uploaded.file/fileInfor.file.size)+"%)");
	Ext.getCmp("AzTotalProgressBar").updateProgress(fileInfor.uploaded.total/fileInfor.total.size, "전체 "+fileInfor.total.count+"개의 파일중 "+fileInfor.total.upload+"번째 파일 업로드 중... ("+Math.round(100*fileInfor.uploaded.total/fileInfor.total.size)+"%)");
}

function WebHardUploaderUploadedFileByUser(fileInfor) {
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

function WebHardUploaderUploadedCompleteByUser(id) {
	Ext.getCmp("WebHardFileProgressWindow").hide();
	Ext.getCmp("WebHardList").getStore().reload();
}
</script>

<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var FileStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/webhard/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","filename","dir","filesize","reg_date","modify_date","download"]
		}),
		remoteSort:false,
		sortInfo:{field:"filename",direction:"ASC"},
		baseParams:{action:"list",dir:"/"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"웹하드관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"WebHardList",
				border:false,
				tbar:[
					{
						xtype:"tbtext",
						text:"<span id='WebHardUploaderArea' style='font:0/0 arial; margin:-3px 0px -1px 0px; display:inline-block; height:22px; overflow:hidden;'></span>",
						listeners:{render:{fn:function() {
							WebHardUploaderRenderer("uploader","WebHardUploaderArea");
						}}}
					},
					new Ext.Button({
						text:"새폴더",
						icon:"<?php echo $_ENV['dir']; ?>/module/webhard/images/admin/icon_folder_add.png",
						handler:function() {
							new Ext.Window({
								id:"NewFolderWindow",
								title:"새폴더",
								modal:true,
								width:400,
								items:[
									new Ext.form.FormPanel({
										id:"NewFolderForm",
										labelAlign:"right",
										labelWidth:85,
										border:false,
										autoWidth:true,
										autoScroll:true,
										errorReader:new Ext.form.XmlErrorReader(),
										style:"background:#FFFFFF; padding:10px;",
										items:[
											new Ext.form.Hidden({
												name:"dir",
												value:FileStore.baseParams.dir
											}),
											new Ext.form.TextField({
												fieldLabel:"폴더명",
												width:260,
												name:"name",
												allowBlank:false
											})
										],
										listeners:{actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												FileStore.reload();
												Ext.getCmp("NewFolderWindow").close();
												return false;
											}
										}}}
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										icon:"<?php echo $_ENV['dir']; ?>/module/webhard/images/admin/icon_tick.png",
										handler:function() {
											Ext.getCmp("NewFolderForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/webhard/exec/Admin.do.php?action=folder",waitMsg:"폴더를 추가중입니다."});
										}
									})
								]
							}).show();
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.CheckboxSelectionModel(),
					{
						header:"파일명",
						dataIndex:"filename",
						width:350,
						sortable:true,
						renderer:function(value,p,record) {
							var temp = value.split("-");
							var type = temp.shift();
							var sHTML = '<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100%;"><col width="20" /><col width="100%" /><tr><td style="font:0/0 arial;">';
							if (type == "DIRUP") {
								sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/webhard/images/admin/icon_folder_go.png" style="margin:-1px;" />';
								sHTML+= '</td><td style="padding-top:1px;">';
								sHTML+= "상위폴더";
								sHTML+= '</td></tr></table>';
							} else {
								if (type == "DIR") {
									sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/webhard/images/admin/icon_folder.png" style="margin:-1px;" />';
								} else {
									sHTML+= '<img src="'+GetFileIcon(value)+'" style="margin:-1px;" />';
								}
								sHTML+= '</td><td style="padding-top:1px;">';
								sHTML+= temp.join("-");
								sHTML+= '</td></tr></table>';
							}

							return sHTML;
						}
					},{
						header:"파일용량",
						dataIndex:"filesize",
						width:75,
						sortable:true,
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+GetFileSize(value)+'</div>';
						}
					},{
						header:"등록일",
						dataIndex:"reg_date",
						width:130,
						sortable:true,
						renderer:function(value) {
							return '<div style="font-family:arial;">'+value+'</div>';
						}
					},{
						header:"최종수정일",
						dataIndex:"modify_date",
						width:130,
						sortable:true,
						renderer:function(value) {
							return '<div style="font-family:arial;">'+value+'</div>';
						}
					},{
						header:"다운수",
						dataIndex:"download",
						width:50,
						sortable:true,
						renderer:GridNumberFormat
					}
				]),
				store:FileStore,
				bbar:new Ext.ux.StatusBar({
					enableOverflow:false,
					items:[
						new Ext.Toolbar.TextItem({
							id:"PathText",
							cls:"x-status-text-panel",
							style:"margin-right:2px; padding:4px 5px 0px 3px;",
							height:24,
							text:"/"
						})
					]
				}),
				listeners:{rowdblclick:{fn:function(grid,row,event) {
					var data = grid.getStore().getAt(row);
					var temp = data.get("filename").split("-");
					var type = temp.shift();
					var filename = temp.join("-");

					if (type == "DIRUP") {
						var temp = grid.getStore().baseParams.dir.split("/");
						temp.pop();
						temp.pop();
						grid.getStore().baseParams.dir = temp.join("/")+"/";
						grid.getStore().reload();
					} else if (type == "DIR") {
						grid.getStore().baseParams.dir = data.get("dir")+filename+"/"
						grid.getStore().reload();
					} else {
						execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/webhard/exec/FileDownload.do.php?idx="+data.get("idx");
					}
				}}}
			})
		]
	});

	FileStore.load();
	FileStore.on("load",function() {
		Ext.getCmp("PathText").setText(FileStore.baseParams.dir);
	});

	new Ext.Window({
		id:"WebHardFileProgressWindow",
		title:"파일업로드",
		modal:true,
		width:600,
		resizable:false,
		items:[
			new Ext.ProgressBar({
				style:"margin:5px;",
				text:"업로드 대기중",
				id:"WebHardFileProgressBar",
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