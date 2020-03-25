<?php


namespace console\controllers;


use backend\models\Admin;
use backend\models\AuthItem;
use backend\models\AuthMenu;
use backend\service\AuthService;
use yii\console\Controller;

class InitController extends Controller
{
    const SQL_TABLE_SCHEMA = "
drop table if exists `admin`;
drop table if exists `admin_log`;
drop table if exists `auth_menu`;
drop table if exists `auth_assignment`;
drop table if exists `auth_item_child`;
drop table if exists `auth_item`;
drop table if exists `auth_rule`;

set foreign_key_checks=0;

create table `admin`  (
  `id` int(10) UNSIGNED not null auto_increment comment '管理员id',
  `username` varchar(50)  not null comment '用户名',
  `phone` char(11) comment '手机号码',
  `email` varchar(255) comment '电子邮箱',
  `password` varchar(255)  not null comment '密码',
  `password_default` varchar(32) comment '默认密码',
  `status` tinyint(4) null default 1 comment '1可用 0禁用',
  `create_time` timestamp(0) null default null comment '创建时间',
  `update_time` timestamp(0) null default null comment '修改时间',
  primary key (`id`),
  unique index `username_unique`(`username`),
  unique index `email_unique`(`email`)
) engine = InnoDB;

create table `admin_log`  (
  `id` int(10) UNSIGNED not null auto_increment,
  `admin_id` int(10) UNSIGNED not null,
  `api` varchar(100)  not null comment '访问的api路径',
  `content` varchar(255)  not null comment '内容',
  `ip` varchar(20)  not null comment 'ip',
  `create_time` timestamp(0) null default null comment '创建时间',
  primary key (`id`)
) engine = InnoDB;

create table `auth_menu`  (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(128) not null,
  `parent` int(10) unsigned,
  `route` varchar(256),
  `order` int(11),
  `icon` varchar(100),
  primary key (`id`),
  index `parent`(`parent`),
  foreign key (`parent`) references `auth_menu` (`id`) on delete set null on update cascade
) engine = InnoDB;

create table `auth_rule`
(
   `name`                 varchar(64) not null,
   `data`                 blob,
   `created_at`           integer,
   `updated_at`           integer,
    primary key (`name`)
) engine InnoDB;

create table `auth_item`
(
   `name`                 varchar(64) not null,
   `type`                 smallint not null,
   `description`          text,
   `rule_name`            varchar(64),
   `data`                 blob,
   `menu_id` int(10) unsigned,
   `created_at`           integer,
   `updated_at`           integer,
   primary key (`name`),
   foreign key (`rule_name`) references `auth_rule` (`name`) on delete set null on update cascade,
   foreign key (`menu_id`) references `auth_menu` (`id`) on delete set null on update cascade,
   key `type` (`type`)
) engine InnoDB;

create table `auth_item_child`
(
   `parent`               varchar(64) not null,
   `child`                varchar(64) not null,
   primary key (`parent`, `child`),
   foreign key (`parent`) references `auth_item` (`name`) on delete cascade on update cascade,
   foreign key (`child`) references `auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB;

create table `auth_assignment`
(
   `item_name`            varchar(64) not null,
   `user_id`              varchar(64) not null,
   `created_at`           integer,
   primary key (`item_name`, `user_id`),
   foreign key (`item_name`) references `auth_item` (`name`) on delete cascade on update cascade,
   key `auth_assignment_user_id_idx` (`user_id`)
) engine InnoDB;

set foreign_key_checks=1;
    ";

    /**
     * 初始化系统业务数据
     */
    public function actionRun(){
        $this->initMysqlData();
    }

    protected function initMysqlData(){
        echo "初始化mysql数据...\n";
        $datetime = date('Y-m-d H:i:s');

        $transaction = \Yii::$app->db->beginTransaction();
        try{
            \Yii::$app->db->createCommand(self::SQL_TABLE_SCHEMA)->execute();

            (new Admin([
                'id'=>1,
                'username'=>'admin',
                'password'=>'$2y$13$lL72Ol6uHb.uAgvkG8v9QuCX83D75tECReieIwR1Z5t16mn2Hg/Vu',
                'status'=>1,
                'create_time'=>$datetime,
                'update_time'=>$datetime
            ]))->save(false);

            (new AuthMenu([
                'id'=>1,
                'name'=>'权限管理',
                'route'=>'/'
            ]))->save(false);

            $authService = new AuthService();

            $authService->addRole(new AuthItem([
                'name'=>AuthService::SUPER_ADMIN,
                'description'=>'拥有所有权限',
            ]));

            $authService->addItem(new AuthItem([
                'name'=>'admin/logout',
                'description'=>'拥有所有权限',
                'menu_id'=>1
            ]));

            $role = new \stdClass();
            $role->name = AuthService::SUPER_ADMIN;
            \Yii::$app->authManager->assign($role, 1);

            echo "SUCCESS\n";
            $transaction->commit();
        }catch (\Throwable $e){
            $transaction->rollBack();
            $message = 'mysql数据初始化失败：'. $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage();
            \Yii::error($message);
            echo $message;
        }
    }
}