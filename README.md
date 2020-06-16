#### 说明
yii2后台管理系统api接口，开箱即用，包含如下功能：
- rbac权限全套（菜单、权限、角色、管理员、操作日志）
- 文件上传
- 表单异步导出excel（支持大量数据）
- echart数据

表结构参见：`console/controllers/InitController.php`里的SQL_TABLE_SCHEMA

#### 初始化

1 修改`common/config/main-local.php` `common/config/download_job_file_root`配置文件

2 执行以下命令
```
composer install
php /your_project_path/console/yii init/run

# 需要异步导出excel时启动队列服务
crontab -e
* * * * * /usr/bin/php /your_project_path/console/yii queue/run
```

#### 接口地址
https://documenter.getpostman.com/view/3858621/SzfDvjN2?version=latest