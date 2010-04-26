<?php

  return array(
  
    // ---------------------------------------------------
    //  Administration tools
    // ---------------------------------------------------
    
    'administration tool name test_mail_settings' => '测试邮件设置',
    'administration tool desc test_mail_settings' => '使用简单工具发送测试邮件检查是否OpenGoo邮件系统是否配置正确',
    'administration tool name mass_mailer' => '批量邮件',
    'administration tool desc mass_mailer' => '简单的工具可以让你发送文本消息到组中所有注册用户。',
  
    // ---------------------------------------------------
    //  Configuration categories and options
    // ---------------------------------------------------
  
    'configuration' => '配置',
    
    'mail transport mail()' => '缺省的PHP配置',
    'mail transport smtp' => 'SMTP 服务器',
    
    'secure smtp connection no'  => '否',
    'secure smtp connection ssl' => '是，使用SSL',
    'secure smtp connection tls' => '是, 使用TLS',
    
    'file storage file system' => '文件系统',
    'file storage mysql' => '数据库(MySQL)',
    
    // Categories
    'config category name general' => '通用',
    'config category desc general' => '通用 OpenGoo 设置.',
    'config category name mailing' => '发送邮件',
    'config category desc mailing' => '使用这些配置可以设置OpenGoo如何处理电子邮件的发送。你可以使用你的php.ini提供的配置选项或者设置为使用其他的SNMP服务器。',
  	'config category name modules' => '模块',
    'config category desc modules' => '使用这些配置可以设置OpenGoo模块是否启用。禁用一个模块仅会图形界面中隐藏，它并不移除用户创建或者编辑的内容对象的权限。',
    
    // ---------------------------------------------------
    //  Options
    // ---------------------------------------------------
    
    // General
    'config option name site_name' => '站点名称',
    'config option desc site_name' => '这个信息将显示在仪表板页面作为站点名称',
    'config option name file_storage_adapter' => '文件存储空间',
    'config option desc file_storage_adapter' => '选择你想要保存上载文档的位置，警告：切换存储空间将使以前上载的文件失效。',
    'config option name default_project_folders' => '缺省工作区文件夹',
    'config option desc default_project_folders' => '这些文件夹将在工作区创建时同时被创建，每一行是一个文件夹名称，相同的或者空行将被忽略。',
    'config option name theme' => '主题',
    'config option desc theme' => '使用主题你可以改变OpenGoo的缺省观感，需要刷新以生效。',
  	'config option name days_on_trash' => '垃圾保留天数',
    'config option desc days_on_trash' => '一个内容对象将在保留在垃圾箱中多少天后被自动删除，如果为0，对象将不会自动删除。',

  	'config option name enable_notes_module' => '启用笔记模块',
  	'config option name enable_email_module' => '启用电子邮件模块',
  	'config option name enable_contacts_module' => '启用联系人模块',
  	'config option name enable_calendar_module' => '启用日历模块',
  	'config option name enable_documents_module' => '启用文档模块',
  	'config option name enable_tasks_module' => '启用任务模块',
  	'config option name enable_weblinks_module' => '启用网站链接模块',
  	'config option name enable_time_module' => '启用时间模块',
  	'config option name enable_reporting_module' => '启用报告模块',
  
    'config option name upgrade_check_enabled' => '启用升级检测',
    'config option desc upgrade_check_enabled' => '如果启用系统即将每天一次检查是否有一个新版本的OpenGoo可以下载。',
	'config option name work_day_start_time' => '工作日开始时间',
  	'config option desc work_day_start_time' => '指定工作日的时间',
      
  	'config option name use_minified_resources' => '使用压缩的资源',
  	'config option desc use_minified_resources' => '使用压缩的Javascript 和 CSS 来改进性能。如果你编辑了它们，那么你需要重新压缩JS和CSS，请使用Opengoo目录下的public/tools。',
 
    // Mailing
    'config option name exchange_compatible' => 'Microsoft Exchange 兼容模式',
    'config option desc exchange_compatible' => '如果你使用Microsoft Exchange 服务器，设置这个选项为 yes 来避免已知的邮件问题。',
    'config option name mail_transport' => '邮件传递',
    'config option desc mail_transport' => '你可以使用缺省的PHP配置或者指定SMTP服务器来发送邮件。',
    'config option name smtp_server' => 'SMTP 服务器',
    'config option name smtp_port' => 'SMTP 端口',
    'config option name smtp_authenticate' => '使用 SMTP 认证',
    'config option name smtp_username' => 'SMTP 用户名',
    'config option name smtp_password' => 'SMTP 密码',
    'config option name smtp_secure_connection' => '使用安全 SMTP 连接',
  
 	'can edit company data' => '可以编辑公司数据',
  	'can manage security' => '可以管理安全',
  	'can manage workspaces' => '可以管理工作区',
  	'can manage configuration' => '可以管理系统配置',
  	'can manage contacts' => '可以管理联系人',
  	'group users' => '组用户',
    
  	
  	'user ws config category name dashboard' => '仪表盘选项',
  	'user ws config category name task panel' => '任务选项',
  	'user ws config category name general' => '通用',
  	'user ws config option name show pending tasks widget' => '显示挂起任务的小工具',
  	'user ws config option name pending tasks widget assigned to filter' => '显示任务分配给',
  	'user ws config option name show late tasks and milestones widget' => '显示超期的任务和里程碑的小工具',
  	'user ws config option name show messages widget' => '显示笔记的小工具',
  	'user ws config option name show comments widget' => '显示评论的小工具',
  	'user ws config option name show documents widget' => '显示文档的小工具',
  	'user ws config option name show calendar widget' => '显示小日历的小工具',
  	'user ws config option name show charts widget' => '显示图表的小工具',
  	'user ws config option name show emails widget' => '显示电子邮件的小工具',
  	'user ws config option name localization' => '本地化',
  	'user ws config option desc localization' => '标签和日期将依照这个本地设置被显示，需要刷新以生效。',
  	'user ws config option name initialWorkspace' => '初始工作区',
  	'user ws config option desc initialWorkspace' => '这个设置可以配置在你登录时自动选择的工作区，或者你可以选择记住你查看的最后的工作区。',
  	'user ws config option name rememberGUIState' => '记住用户界面状态',
  	'user ws config option desc rememberGUIState' => '这允许你保存图形界面的状态（面板大小，收缩/展开状态等等），在你下次登录时自动启用。警告：这是一个BETA测试版本的特性。',
  	'user ws config option name time_format_use_24' => '使用24小时制显示时间',
  	'user ws config option desc time_format_use_24' => '如果启用，时间信息将显示为：\'hh:mm\'的格式，从00:00 到 23:59, 否则，小时数将从 1 到 12 并使用 AM 或 PM.',
  	'user ws config option name work_day_start_time' => '工作日开始时间',
	'user ws config option desc work_day_start_time' => '指定当工作日开始的时间',
  	
  	'user ws config option name my tasks is default view' => '分配给我的任务是缺省的视图',
  	'user ws config option desc my tasks is default view' => '如果选择否，任务面板的缺省视图将显示所有的任务。',
  	'user ws config option name show tasks in progress widget' => '显示 \'处理中任务\' 小工具',
  	'user ws config option name can notify from quick add' => '缺省检查任务通告',
  	'user ws config option desc can notify from quick add' => '通告可选框允许当任务被添加或者更新时通知指派的用户。',
 	
  	'backup process desc' => '备份保存整个应用程序的当前状态到一个压缩的文件夹， 它可以被用来简单备份一个完整的OpenGoo安装。<br> 生成关于数据库和文件系统的备份可能超过几秒，制作一个备份的处理过程有以下三步： <br>1、运行备份程序。 <br>2、下载这个备份包。 <br> 3、可选的，在未来不需要时，一个备份可以被手工删除。<br> ',
  	'start backup' => '运行备份程序',
    'start backup desc' => '运行备份程序将删除上一次的备份数据，并生成一个新的。',
  	'download backup' => '下载备份',
    'download backup desc' => '可以允许下载一个备份，你必须首先生成一份备份数据。',
  	'delete backup' => '删除备份',
    'delete backup desc' => '删除最后的备份，它将不能被下载。强烈建议在下载后删除备份。',
    'backup' => '备份',
    'backup menu' => '备份菜单',
   	'last backup' => '最后一次备份被创建于',
   	'no backups' => '没有备份数据可以下载',
   	
   	'user ws config option name always show unread mail in dashboard' => '总是显示未读的电子邮件在仪表盘中',
   	'user ws config option desc always show unread mail in dashboard' => '当选择NO时，活动工作区中的电子邮件将被显示在仪表盘。',
   	'workspace emails' => '工作区邮件',
  	'user ws config option name tasksShowWorkspaces' => '显示工作区',
  	'user ws config option name tasksShowTime' => '显示时间',
  	'user ws config option name tasksShowDates' => '显示日期',
  	'user ws config option name tasksShowTags' => '显示标签',
  	'user ws config option name tasksGroupBy' => '分组通过',
  	'user ws config option name tasksOrderBy' => '排序通过',
  	'user ws config option name task panel status' => '状态',
  	'user ws config option name task panel filter' => '过滤通过',
  	'user ws config option name task panel filter value' => '过滤信息',
  
  	'templates' => '模板',
	'add template' => '添加模板',
	'confirm delete template' => '你是否确认删除这个模板?',
	'no templates' => '没有模板',
	'template name required' => '需要模板\'名称',
	'can manage templates' => '可以管理模板',
	'new template' => '新模板',
	'edit template' => '编辑模板',
	'template dnx' => '模板不存在',
	'success edit template' => '模板成功修改',
	'log add cotemplates' => '{0} 已添加',
	'log edit cotemplates' => '{0} 已修改',
	'success delete template' => '模板删除成功',
	'error delete template' => '删除模板错误',
	'objects' => '对象',
	'objects in template' => '模板中的对象',
	'no objects in template' => '这个模板中没有对象',
	'add to a template' => '添加一个模板',
  	'add an object to template' => '添加一个对象到这个模板',
	'you are adding object to template' => '你正在添加{0} \'{1}\' 到一个模板。在下面选择一个模板或者为{0}创建一个新模板 .',
	'success add object to template' => '对象添加到模板成功',
	'object type not supported' => '这个对象类型不被模板支持',
  	'assign template to workspace' => '指定模板到工作区',
  
  	'cron events' => '定时事件',
  	'about cron events' => '关于定时事件...',
  	'cron events info' => '定时事件可以让你在OpenGoo中定期执行任务，而不需要登录到系统中。为了启用定时事件你需要配置一个定时作业来周期性的执行文件："cron.php"，这个文件在OpenGoo的根目录下面。这个你运行定时作业的周期决定了你可以运行定时事件的最小时间间隔。 例如：如果你配置定时作业每5分钟一次，并且你配置检查更新的定时事件为每分钟一次， 那么实际上将每5分钟检查一次更新。如何配置定时作业请询问你的系统管理员或者主机提供商。',
  	'cron event name check_mail' => '检查邮件',
  	'cron event desc check_mail' => '这个定时事件将检查所有系统中电子邮件帐户中的新邮件。',
  	'cron event name purge_trash' => '清理垃圾桶',
  	'cron event desc purge_trash' => '这个定时事件将删除超期的垃圾桶中的对象。',
  	'cron event name send_reminders' => '发送提醒',
  	'cron event desc send_reminders' => '这个定时事件将自动发送电子邮件提醒。',
  	'cron event name check_upgrade' => '检查更新',
  	'cron event desc check_upgrade' => '这个定时事件将检测OpenGoo的新版本。',
  	'cron event name create_backup' => '创建一个备份',
  	'cron event desc create_backup' => '创建一个备份让你可以从超级管理员的备份区下载。',
  	'next execution' => '下一次执行',
  	'delay between executions' => '在两次执行中的间隔',
  	'enabled' => '启用',
  	'no cron events to display' => '没有定时事件显示',
  	'success update cron events' => '定时时间更新成功',
  
  	'manual upgrade' => '手动升级',
  	'manual upgrade desc' => '为了手动升级OpenGoo你需要下载最新版本的OpenGoo,解压缩到你安装的根目录然后在浏览器中定位到<a href="public/upgrade">\'public/upgrade\'</a> 运行升级程序。',
  	'automatic upgrade' => '自动升级',
  	'automatic upgrade desc' => '自动更新将自动下载并且解压缩新版本，并且运行升级程序，web服务器需要对所有的文件夹拥有写权限。',
  	'start automatic upgrade' => '开始自动升级',
  ); // array

?>