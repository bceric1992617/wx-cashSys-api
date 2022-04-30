疑问
是否要用HTML5+CSS3来设置
redis配合餐单排名和盈利数据


主要功能：
	开台下单页面
		显示菜单功能
		菜式添加
		下单时间
		结算时间
		
	餐桌（限20）
		添加，修改和删除餐桌按钮
		全部餐桌页面

	菜单（限40）
		添加，修改和删除菜单按钮
		菜式分类
		菜单页面
		
	账号
		创建和删除子账号按钮
		修改密码
		充值连接
		用户信息
		店铺名字

		

	记录营业利润（月和年）
		记录每月的盈利

	VIP

	小程序分享朋友

账号权限
	主账号
		可以修改菜单，餐桌，创建子账号，下单，结算
	子账号（限3）
		可以下单，结算

web支持设备
	常规电脑，平板，小程序


页面：
	注册
	登录
	首页：左边显示全部餐桌（正方形），右边显示餐单
	餐桌：显示全部餐桌（条形），每条餐桌信息有删除按钮，可以多选删除，右上角有添加餐桌按钮(页面)
	菜单：显示全部菜单（条形），每条菜单信息有删除按钮，可以多选删除，右上角有添加菜单按钮(页面)
	我的：显示账号信息，修改密码，修改店铺名字，退出登录，创建子账号(页面)，充值窗口(页面)
	盈利：今天，12个月份，今年盈利 导出excel功能，每年自动清空
	菜单销量：菜单销量排行，一周，每月，今年
	

数据库创建：
用户登录信息表
	create table userLoginInfo(
		userLoginId int unsigned auto_increment primary key,
		userId int unsigned not null,
		deviceInfo varchar(50) not null,
		userIp varchar(20) not null,
		code char(255) not null,
		loginTime int not null
	); 


用户表
	create table users(
		userId int unsigned auto_increment primary key,
		userName varchar(50) not null,
		storeName varchar(50),
		passwd char(255) not null,
		VIP enum('0','1') default '0',
		email varchar(50),
		phone varchar(11) UNIQUE KEY not null,
		createTime int not null,
		updateTime int not null,
		isDel enum('0','1') default '0'
	); 

用户收款码 

	create table receiveCode(
		receiveCodeId int unsigned auto_increment primary key,
		userId int unsigned not null,
		imgName varchar(50) not null,
		imgUrl varchar(255) not null,
		saveDir varchar(255) not null,
		isMain tinyint default 0 comment '0第二张 1第一张'
	); 

子账号
	create table cUsers(
		cUserId int unsigned auto_increment primary key,
		userId int unsigned not null,
		cUserName varchar(50) not null,
		passwd char(255) not null,
		createTime int not null,
		updateTime int not null,
		isDel enum('0','1') default '0'
	); 
	
用户的菜单类型
	create table menuType(
		menuTypeId int unsigned unsigned auto_increment primary key,
		userId int not null,
		menuType varchar(255) not null
	); 
用户的菜单
	create table menu(
		menuId int unsigned auto_increment primary key,
		userId int not null,
		menuName varchar(30) not null,
		menuTypeId int not null,
		price float(5,1) unsigned not null,
		daySaleNum varchar(255) default 0,
		monthSaleNum varchar(255) default 0,
		tags varchar(30) default '0',
		inventory enum('0','1') default '1' comment '0售空 1有货',
	); 

	
用户的餐桌
	create table diningTable(
		tableId int unsigned auto_increment primary key,
		userId int not null,
		tableNum int not null,
		stats enum('0','1') default '0' comment '0空闲 1不空闲',
		bill text
	); 
用户的每月的盈利
	create table interest(
		interestId int unsigned auto_increment primary key,
		userId int not null,
		today smallint unsigned default 0,
        	1m  MEDIUMINT unsigned default 0,
		2m  MEDIUMINT unsigned default 0,
		3m  MEDIUMINT unsigned default 0,
		4m  MEDIUMINT unsigned default 0,
		5m  MEDIUMINT unsigned default 0,
		6m  MEDIUMINT unsigned default 0,
		7m  MEDIUMINT unsigned default 0,
		8m  MEDIUMINT unsigned default 0,
		9m  MEDIUMINT unsigned default 0,
		10m  MEDIUMINT unsigned default 0,
		11m  MEDIUMINT unsigned default 0,
		12m  MEDIUMINT unsigned default 0
	); 
	
导航条
	create table navBar(
		navId int auto_increment primary key,
		navName varchar(20) not null,
		sort tinyint not null default 0,
		isDel enum('0','1') not null default '0'
	); 

打单机信息
	create table printer(
		printerId int auto_increment primary key,
		userId int not null,
		printerSN varchar(20) not null,
		printerKey varchar(20) not null,
		printerName varchar(60)	
	); 


审核信息
	create table reviewOrderInfo(
		reviewId int auto_increment primary key,
		userId int not null,
		tableNum int not null,
		bill text not null
	); 

alter table menu add column imgAddr varchar(50);
alter table diningTable add column orderCodeAddr varchar(50);


删除表信息
truncate table cUsers;
truncate table diningTable;
truncate table interest;
truncate table menu;
truncate table menuType;
truncate table navBar;
truncate table printer;
truncate table receiveCode;
truncate table reviewOrderInfo;
truncate table userLoginInfo;
truncate table users;

13760714161


www.zxcvbnm.xyz


navicat
secureCRT
fileZilla
VM 
	centos
phpstudy
sublimetext
python
vscode
	remote-ssh
	php intellisense
	eslint



socket作业
reload封装，task，消息队列，自动加载
内存的存储和销毁，start,receive,workerstart

第5课作业。。







	
	
