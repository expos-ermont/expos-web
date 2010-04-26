<?php

  /**
  * Error messages
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */

  // Return langs
  return array(
  
    // General
    'invalid email address' => '电子邮件地址格式无效',
   
    // Company validation errors
    'company name required' => '需要公司/组织名称',
    'company homepage invalid' => '主页不是一个有效的地址',
    
    // User validation errors
    'username value required' => '需要用户名称',
    'username must be unique' => '抱歉，选择的用户名已经存在。',
    'email value is required' => '需要电子邮件地址',
    'email address must be unique' => '抱歉，选择的电子邮件地址已经存在',
    'company value required' => '用户必须在一个公司/组织里',
    'password value required' => '需要密码',
    'passwords dont match' => '密码不匹配',
    'old password required' => '需要旧密码',
    'invalid old password' => '旧密码无效',
    'users must belong to a company' => '创建用户时联系人必须属于一个公司',
    'contact linked to user' => '联系人已被连接到用户 {0}',
    
    // Avatar
    'invalid upload type' => '无效的文件类型，允许的类型是 {0}',
    'invalid upload dimensions' => '无效的图像分辨率，最大图像分辨率为{0}x{1}点',
    'invalid upload size' => '无效的图像大小. 最大为 {0}',
    'invalid upload failed to move' => '移动上载的文件失败',
    
    // Registration form
    'terms of services not accepted' => '在请求创建帐户时你必须阅读并接收我们的服务条款',
    
    // Init company website
    'failed to load company website' => '载入公司网站失败， 所有者公司未发现',
    'failed to load project' => '加载活动工作区失败',
    
    // Login form
    'username value missing' => '请输入用户名',
    'password value missing' => '请输入密码',
    'invalid login data' => '登录失败，请检查你的登录信息并重试。',
    
    // Add project form
    'project name required' => '需要工作区名称',
    'project name unique' => '工作区名称必须唯一',
    
    // Add message form
    'message title required' => '需要标题',
    'message title unique' => '在这个工作区里标题必须唯一',
    'message text required' => '需要文本信息',
    
    // Add comment form
    'comment text required' => '需要评论文本',
    
    // Add milestone form
    'milestone name required' => '需要里程碑名字',
    'milestone due date required' => '需要里程碑到期日期',
    
    // Add task list
    'task list name required' => '需要任务名',
    'task list name unique' => '任务名在本工作区必须唯一',
    'task title required' => '需要任务标题',
  
    // Add task
    'task text required' => '需要任务文本',
    
    // Add event
    'event subject required' => '需要事件主题',
    'event description maxlength' => '描述必须在3000字以下',
    'event subject maxlength' => '主题必须在100字以下',
    
    // Add project form
    'form name required' => '需要表单名',
    'form name unique' => '表单名必须唯一',
    'form success message required' => '需要成功信息',
    'form action required' => '需要表单动作',
    'project form select message' => '请选择一个笔记',
    'project form select task lists' => '请选择任务',
    
    // Submit project form
    'form content required' => '请输入内容到文本字段',
    
    // Validate project folder
    'folder name required' => '需要文件夹名',
    'folder name unique' => '文件夹名在本工作区中必须唯一。',
    
    // Validate add / edit file form
    'folder id required' => '请选择文件夹',
    'filename required' => '需要文件名',
    
    // File revisions (internal)
    'file revision file_id required' => '版本需要连接到一个文件',
    'file revision filename required' => '需要文件名',
    'file revision type_string required' => '未知文件类型',
    
    // Test mail settings
    'test mail recipient required' => '需要接收地址',
    'test mail recipient invalid format' => '无效的接收地址格式',
    'test mail message required' => '需要邮件消息',
    
    // Mass mailer
    'massmailer subject required' => '需要消息主题',
    'massmailer message required' => '需要消息正文',
    'massmailer select recepients' => '请选择接收这个邮件的用户',
    
  	//Email module
  	'mail account name required' => '需要帐户名',
  	'mail account id required' => '需要帐户ID',
  	'mail account server required' => '需要服务器',
  	'mail account password required' => '需要密码',
	'send mail error' => '发送邮件错误，可能SMTP设置不正确。',
    'email address already exists' => '此电子邮件地址已经被使用。',  
  	'session expired error' => '会话超期导致用户失效，请重新登录',
  	'unimplemented type' => '未实现类型',
  	'unimplemented action' => '未实现动作',
  
  	'workspace own parent error' => '一个工作区不能是它自己的父亲',
  	'task own parent error' => '一个任务不能是它自己的父亲',
  	'task child of child error' => '一个任务不能是它后代的子任务',
  
  	'chart title required' => '需要图表标题。',
  	'chart title unique' => '图表标题必须唯一。',
    'must choose at least one workspace error' => '当放置对象时你至少选择一个工作区。',
    
    
    'user has contact' => '已经有一个联系人被指定到这个用户',
    
    'maximum number of users reached error' => '已经到达用户最大数量',
	'maximum number of users exceeded error' => '已经超过最大用户数量，本应用程序将停止工作直到这个问题解决。',
	'maximum disk space reached' => '你的磁盘空间不足，请在添加新的之前删除一些对象，或者联系技术支持来允许更多的用户。',
	'error db backup' => '创建数据库备份错误: {0}',
  	'backup command failed' => '备份命令失败。 检查 MYSQLDUMP_COMMAND 常量。',
  	'success db backup' => '备份被创建成功。',
	'error create backup folder' => '创建备份目录失败，不能完成备份',
	'error delete backup' => '删除数据库备份失败',
	'success delete backup' => '备份已被删除',
    'name must be unique' => '抱歉，选择的名称已经存在',
  	'not implemented' => '未实现',
  	'return code' => '返回码: {0}',
  	'task filter criteria not recognised' => '任务过滤条件 \'{0}\' 不能识别',
   ); // array

?>