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
			title:"회원가입설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.Checkbox({
					fieldLabel:"공개가입",
					name:"is_open",
					boxLabel:"초대장없이 공개된 가입주소로 회원가입을 허용합니다.",
					checked:<?php echo $config['is_open'] == 'on' ? 'true' : 'false'; ?>
				}),
				new Ext.form.Checkbox({
					fieldLabel:"초대장가입",
					name:"is_invite",
					boxLabel:"배포된 초대장으로 회원가입을 허용합니다.",
					checked:<?php echo $config['is_open'] == 'on' ? 'true' : 'false'; ?>
				}),
				new Ext.form.ComboBox({
					fieldLabel:"회원가입스킨",
					hiddenName:"signin_skin",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.SimpleStore({
						fields:["skin"],
						data:[
							<?php
							$skinPath = @opendir($_ENV['path'].'/module/member/templet/signin');
							$i = 0;
							$skins = array();
							while ($skin = @readdir($skinPath)) {
								if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/member/templet/signin/'.$skin) == true) {
									$skins[] = '["'.$skin.'"]';
								}
							}
							@closedir($skinPath);
							echo implode(',',$skins);
							?>
						]
					}),
					width:150,
					editable:false,
					mode:"local",
					displayField:"skin",
					valueField:"skin",
					value:"<?php echo $config['signin_skin'] ? $config['signin_skin'] : 'default'; ?>"
				}),
				new Ext.form.ComboBox({
					fieldLabel:"회원그룹",
					hiddenName:"member_group",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["group","title"]
						}),
						remoteSort:false,
						sortInfo:{field:"group",direction:"ASC"},
						baseParams:{"action":"member","get":"group","is_all":"true"}
					}),
					width:150,
					editable:false,
					mode:"local",
					displayField:"title",
					valueField:"group",
					value:"<?php echo $config['member_group'] ? $config['member_group'] : 'default'; ?>"
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"토렌트페이지 설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.TextField({
					fieldLabel:"토렌트페이지경로",
					name:"torrentURL",
					width:550,
					allowBlank:true,
					value:"<?php echo $config['torrentURL'] ? $config['torrentURL'] : '/torrents'; ?>"
				}),
				new Ext.form.NumberField({
					fieldLabel:"목록수",
					name:"listnum",
					width:100,
					value:<?php echo $config['listnum'] ? $config['listnum'] : '30'; ?>
				}),
				new Ext.form.NumberField({
					fieldLabel:"페이지수",
					name:"pagenum",
					width:100,
					value:<?php echo $config['pagenum'] ? $config['pagenum'] : '10'; ?>
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"어나운스(트래커) 설정",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.TextField({
					fieldLabel:"트래커주소",
					name:"trackerURL",
					width:550,
					allowBlank:true,
					value:"<?php echo $config['trackerURL'] ? $config['trackerURL'] : 'http://'.$_SERVER['HTTP_HOST'].$_ENV['dir'].'/module/tracker/exec/Announce.php'; ?>"
				}),
				new Ext.form.NumberField({
					fieldLabel:"갱신시간(초)",
					name:"tracker_time",
					width:100,
					value:<?php echo $config['tracker_time'] ? $config['tracker_time'] : '600'; ?>
				}),
				new Ext.form.NumberField({
					fieldLabel:"갱신제한시간(초)",
					name:"tracker_min_time",
					width:100,
					value:<?php echo $config['tracker_min_time'] ? $config['tracker_min_time'] : '120'; ?>
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"다음API등록",
			autoHeight:true,
			autoWidth:true,
			defaults:{msgTarget:"side"},
			style:"margin:10px;",
			items:[
				new Ext.form.TextField({
					fieldLabel:"다음API키",
					name:"daum_api",
					width:550,
					allowBlank:true,
					value:"<?php echo $config['daum_api'] ? $config['daum_api'] : ''; ?>"
				})
			]
		})
	]
});
</script>