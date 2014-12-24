<script type="text/javascript">
new Ext.form.FormPanel({
	id:"ConfigForm",
	border:false,
	autoScroll:true,
	labelAlign:"right",
	labelWidth:100,
	errorReader:new Ext.form.XmlErrorReader(),
	items:[
		new Ext.form.FieldSet({
			title:"중개업소/담당자 등록설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.Checkbox({
					fieldLabel:"중개업소 승인",
					name:"auto_confirm_agent",
					boxLabel:"중개업소 신청시 자동으로 승인됩니다. 체크해제시 최고관리자의 승인이 필요합니다.",
					checked:<?php echo $config['auto_confirm_agent'] == 'on' ? 'true' : 'false'; ?>
				}),
				new Ext.form.Checkbox({
					fieldLabel:"중개담당자 승인",
					name:"auto_confirm_dealer",
					boxLabel:"중개담당자 신청시 자동으로 승인됩니다. 체크해제시 중개업소관리자의 승인이 필요합니다.",
					checked:<?php echo $config['auto_confirm_dealer'] == 'on' ? 'true' : 'false'; ?>
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"중개업소 포인트",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"agent_point",
							width:80,
							value:"<?php echo $config['agent_point'] ? $config['agent_point'] : '0'; ?>",
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"포인트 (중개업소 등록시 차감할 포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"중개담장자 포인트",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"dealer_point",
							width:80,
							value:"<?php echo $config['dealer_point'] ? $config['dealer_point'] : '0'; ?>",
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"포인트 (중개담당자 등록시 차감할 포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.TextArea({
					id:"wysiwyg-register_agreement",
					fieldLabel:"등록약관",
					name:"register_agreement",
					width:550,
					height:400,
					allowBlank:true,
					value:"<?php echo addslashes($config['register_agreement']); ?>",
					listeners:{render:{fn:function(object) {
						object.setSize(Ext.getCmp("ConfigForm").getWidth()-180,100);
						nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"wysiwyg-register_agreement",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?mode=simple",fCreator:"createSEditorInIFrame"});
					}}}
				}),
				new Ext.form.TextArea({
					id:"wysiwyg-register_info",
					fieldLabel:"등록안내문구",
					name:"register_info",
					width:550,
					height:400,
					allowBlank:true,
					value:"<?php echo addslashes($config['register_info']); ?>",
					listeners:{render:{fn:function(object) {
						object.setSize(Ext.getCmp("ConfigForm").getWidth()-180,100);
						nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"wysiwyg-register_info",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?mode=simple",fCreator:"createSEditorInIFrame"});
					}}}
				}),
				new Ext.Panel({
					border:false,
					style:"padding:0px 0px 5px 105px;",
					html:'<div id="uploader-register_info-area"></div><div id="uploader-register_info-image"></div><div id="uploader-register_info-file"></div>',
					listeners:{
						render:{fn:function(object) {
							object.setSize(Ext.getCmp("ConfigForm").getWidth()-180,100);
						}},
						afterrender:{fn:function(object) {
							new AzUploader({
								id:"uploader-register_info",
								autoRender:false,
								autoLoad:true,
								flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
								uploadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileUpload.do.php?type=admin&repto=1",
								loadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileLoad.do.php?type=admin&repto=1",
								buttonURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_file_button.gif",
								width:75,
								height:20,
								moduleDir:"<?php echo $_ENV['dir']; ?>/module/oneroom",
								wysiwygElement:"wysiwyg-register_info",
								formElement:Ext.getCmp("ConfigForm").getForm().el.dom,
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
							}).render("uploader-register_info-area");
						}}
					}
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"매물등록설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.Checkbox({
					fieldLabel:"개인등록사용",
					name:"use_private",
					boxLabel:"중개담당자가 아닌 일반 개인회원도 매물을 등록할 수 있습니다.",
					checked:<?php echo $config['use_private'] == 'on' ? 'true' : 'false'; ?>
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"매물등록포인트",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"register_point",
							width:80,
							value:"<?php echo $config['register_point'] ? $config['register_point'] : '0'; ?>",
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"포인트 (매물등록시 차감할 포인트를 설정합니다.)"
						})
					]
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"프리미엄매물설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"매물갯수",
					width:400,
					items:[
						new Ext.form.DisplayField({
							html:"가로"
						}),
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"premium_x",
							width:30,
							minValue:1,
							value:<?php echo $config['premium_x'] ? $config['premium_x'] : '4'; ?>,
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"개, 세로"
						}),
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"premium_y",
							width:30,
							minValue:1,
							value:<?php echo $config['premium_y'] ? $config['premium_y'] : '4'; ?>,
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"개"
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"최소입찰포인트",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"premium_point",
							width:80,
							value:<?php echo $config['premium_point'] ? $config['premium_point'] : '1000'; ?>,
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"(프리미엄매물 공간 경매시 최소입찰포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"입찰참가포인트",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"premium_auction_point",
							width:80,
							value:<?php echo $config['premium_auction_point'] ? $config['premium_auction_point'] : '1000'; ?>,
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"(프리미엄매물 공간 경매시 참가포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"입찰참여제한",
					width:400,
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"premium_auction_limit",
							width:80,
							value:<?php echo $config['premium_auction_limit'] ? $config['premium_auction_limit'] : '3'; ?>,
							allowBlank:false
						}),
						new Ext.form.DisplayField({
							html:"회/월 (프리미엄매물 공간 경매제한횟수를 설정합니다.)"
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:100,
					labelAlign:"right",
					fieldLabel:"경매기간",
					width:400,
					items:[
						new Ext.form.ComboBox({
							hiddenName:"premium_auction_start",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["date","display"],
								data:[<?php for ($i=1;$i<=20;$i++) { if ($i != 1) echo ','; echo '["'.$i.'","매달 '.$i.'일"]'; } ?>]
							}),
							width:80,
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"date",
							value:"1"
						}),
						new Ext.form.DisplayField({
							html:"부터"
						}),
						new Ext.form.ComboBox({
							hiddenName:"premium_auction_end",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["date","display"],
								data:[<?php for ($i=10;$i<=25;$i++) { if ($i != 10) echo ','; echo '["'.$i.'","매달 '.$i.'일"]'; } ?>]
							}),
							width:80,
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"date",
							value:"25"
						}),
						new Ext.form.DisplayField({
							html:"까지"
						})
					]
				})
			]
		})
	],
	listeners:{beforeaction:{fn:function(form,action) {
		if (action.type == "submit") {
			oEditors.getById["wysiwyg-register_agreement"].exec("UPDATE_IR_FIELD",[]);
			oEditors.getById["wysiwyg-register_info"].exec("UPDATE_IR_FIELD",[]);
		}
	}}}
});
</script>