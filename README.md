#### 说明
yii2后台管理系统api接口，开箱即用，包含如下功能：
- rbac权限全套（菜单、权限、角色、管理员、操作日志）
- 文件上传
- 表单异步导出excel（支持大量数据）
- echart数据
- 供外部调用的api请求、响应、签名处理

表结构参见：`console/controllers/InitController.php`里的SQL_TABLE_SCHEMA

#### 初始化步骤

1. 修改`common/config/main-local.php` `common/config/download_job_file_root`配置文件

2. 执行以下命令
```
composer install
php /your_project_path/console/yii init/run

# 需要异步导出excel时启动队列服务
crontab -e
* * * * * /usr/bin/php /your_project_path/console/yii queue/run
```

#### 升级步骤
1. 备份当前项目.git，common/config/main-local.php到temp_for_dev
2. 复制yii2-admin-api的.git到项目目录
```
git reset --hard xxxxx #（回退一个版本）
git pull
```
3. 删除yii2-admin-api的.git，还原当前项目.git和common/config/main-local.php
```
git pull
```
4. 缓存处理
```
# 清空表结构缓存，表结构发生变化时调用
php /your_project_path/console/yii cache/flush-schema
```

#### 接口地址
https://documenter.getpostman.com/view/3858621/SzfDvjN2?version=latest