<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"대시보드",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"최신업데이트확인",
				icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_github.png",
				handler:function() {
					window.open("https://github.com/arzzcom/iModule");
				}
			}),
			new Ext.Button({
				text:"도움말",
				icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_github.png",
				handler:function() {
					window.open("https://github.com/arzzcom/iModule/wiki");
				}
			}),
			new Ext.Button({
				text:"질문/버그신고/이슈페이지",
				icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_github.png",
				handler:function() {
					window.open("https://github.com/arzzcom/iModule/issues");
				}
			})
		],
		items:[
		
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>