/*
 Navicat Premium Data Transfer

 Source Server         : 10.211.55.18
 Source Server Type    : MySQL
 Source Server Version : 50740
 Source Host           : 10.211.55.18:3306
 Source Schema         : mpay_mljf1_cn

 Target Server Type    : MySQL
 Target Server Version : 50740
 File Encoding         : 65001

 Date: 29/09/2023 12:21:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ymmzf_channel
-- ----------------------------
DROP TABLE IF EXISTS `ymmzf_channel`;
CREATE TABLE `ymmzf_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `type` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '0wx,1alipay,2qqpay,3pluginpay',
  `qrcode` text NOT NULL COMMENT 'qrcode本地路径',
  `collection_type` enum('0','1','2','3','4') NOT NULL COMMENT '4店员(站长)，0店员，1云端，2app，3pc',
  `addtime` varchar(10) NOT NULL COMMENT '添加时间',
  `lasttime` varchar(10) DEFAULT NULL COMMENT '心跳时间',
  `status` enum('0','1') NOT NULL COMMENT '0启用，1禁用',
  `software_id` varchar(32) DEFAULT NULL COMMENT '挂机软件的账号ID，举例微信：微信id',
  `nickname` text COMMENT '挂机软件的账号ID，举例微信：微信店长id',
  `avatar_address` text COMMENT '店长头像地址',
  `software_main_id` int(11) DEFAULT NULL COMMENT '如果是店员模式，则需要指定店长微信id。这里指定数据库id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ymmzf_channel
-- ----------------------------
BEGIN;
INSERT INTO `ymmzf_channel` (`id`, `userid`, `type`, `qrcode`, `collection_type`, `addtime`, `lasttime`, `status`, `software_id`, `nickname`, `avatar_address`, `software_main_id`) VALUES (1, 0, '0', 'https://u.wechat.com/MKORz9fAVrtwVmaABb5MQ7w', '4', '1695735144', '1695960171', '0', 'wxid_a2s9o5uizh9n12', 'GG爆', 'https://wx.qlogo.cn/mmhead/ver_1/FekU4ZaFzRfyuDReiauRZjia0rfvMqXo2dJkR2h7YkIBQHfgKaPVhiaNx1lF3TCeoic0OMxpB0H1OBMdkzQ5u1b47fkEicgBoLUBRAl0RIzxdjBI/132', NULL);
INSERT INTO `ymmzf_channel` (`id`, `userid`, `type`, `qrcode`, `collection_type`, `addtime`, `lasttime`, `status`, `software_id`, `nickname`, `avatar_address`, `software_main_id`) VALUES (27, 1000, '0', 'wxp://f2f0UO0D9hJv7ibONNqdpw6jj4X_SpIMywbFaVVWgpa-jrM', '0', '1695620549', '1695960171', '0', NULL, 'BLUE', NULL, 1);
COMMIT;

-- ----------------------------
-- Table structure for ymmzf_config
-- ----------------------------
DROP TABLE IF EXISTS `ymmzf_config`;
CREATE TABLE `ymmzf_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(20) DEFAULT NULL,
  `value` text,
  `comment` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ymmzf_config
-- ----------------------------
BEGIN;
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (1, 'app_public_key', '-----BEGIN PUBLIC KEY-----\nMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAy5nV/txaHnLRpcC6TTzI\nREVwjlG5YchaRqPJzFNwpIVUO4l+S5+bF2v4yEfIZeBvmB/vVZy96nQ9SSfSnSNc\nw25jyWZ8gvtSUhaamng8Rtg1dqhal4Xbyg3Sm/6gYNaDKAH4AuRD8dGPUZBjavBQ\nR46uFUQ5WFdanAXt4lArltieuCNqNHnPJAuFrEn18HmpsBKdGsHtgV5/NO1VGfAj\n7gJ3k56BzwDEljVfE5pcyNkhVIf4al5nLldr9w14MuXTEGfK8QAf0Lo54iisR0Aw\ns+KLrwnGLW7lMNVv1zN4yNSQ8iw/WgG64nFy9B5LdsUSbpZ41gOojpG7cQo/cm5x\ndAI+T+1FwtfmRRobzzPq5TuxqMW5iABWbMrq5APx69Z3n+KY74XUrujSA4sF3Qlp\nuEWk/vz+v2mfXi7cn93eRL+updaFh38dSHe0CpDufyp8sGsqtRtcCCW7bxKVLICW\n922ZVEma95mVjs56HnPEl6iUkHCItyB69nLbsgmJ7mg6sm/DUz9XTvqx2j4xLlqN\nUaykJRaA9njNnc3l+2Ts9lkdjXw9wkwV1Hg0uH/dAho70NwVDxqbm9oomDGThqsD\nfO2WiwDiY/T03zSAi4dxP855IWDZGah+NdH7QWFYKfRxqNE5245awtwMF79p2KaH\nrXH2vdHO7ACwJHEvtVzKeuMCAwEAAQ==\n-----END PUBLIC KEY-----\n', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (2, 'app_private_key', '-----BEGIN PRIVATE KEY-----\n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\n2tMIGYNjAHa/tdZmGjDsml/7AWmgzs0=\n-----END PRIVATE KEY-----\n', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (3, 'app_aes_key', 'ymmzf', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (4, 'app_aes_iv', 'IRoHyEg8DNC8VhKk', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (5, 'pay_heartbeat', '30', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (6, 'key', 'p4xwubJIF4hM626', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (7, 'geetest_id', 'f394286b9b054589164a1d294bf70498', NULL);
INSERT INTO `ymmzf_config` (`id`, `key`, `value`, `comment`) VALUES (8, 'geetest_key', '69ffac3e84b949a655873960930b46d7', NULL);
COMMIT;

-- ----------------------------
-- Table structure for ymmzf_order
-- ----------------------------
DROP TABLE IF EXISTS `ymmzf_order`;
CREATE TABLE `ymmzf_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tdid` int(11) NOT NULL COMMENT '通道id',
  `userid` int(11) NOT NULL COMMENT '用户id',
  `pluginid` int(11) DEFAULT '0' COMMENT '后期如果有插件，插件id，没有0',
  `wxid` varchar(32) DEFAULT NULL COMMENT '付款方扫描方的微信id，如果没有可不传，但这样不知道是否已扫描，支持传统码支付（订单加额模式）',
  `username` text COMMENT '微信支付者扫码者的昵称',
  `robotid` varchar(32) DEFAULT NULL COMMENT '微信机器人的机器id，也是店长/店员，店长模式只需要判断wxid，但如果是店员模式，就是wxid+nickname',
  `dz_nickname` text COMMENT '店长昵称',
  `trade_no` varchar(150) DEFAULT NULL COMMENT '订单id',
  `transid` varchar(150) DEFAULT NULL COMMENT '微信transid',
  `out_trade_no` varchar(150) DEFAULT NULL COMMENT '对方订单ID',
  `api_trade_no` varchar(150) DEFAULT NULL COMMENT '接口订单id，如果是微信，则是订单号',
  `type` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '0wx,1alipay,2qqpay,3pluginpay',
  `money` decimal(65,2) NOT NULL COMMENT '通道货币，比如美元啥的',
  `realmoney` decimal(65,2) NOT NULL COMMENT '此订单对应人民币',
  `getmoney` decimal(65,2) DEFAULT NULL COMMENT '付款的通道货币',
  `getrealmoney` varchar(65) DEFAULT NULL COMMENT '付款的人民币',
  `status` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '0待扫描，1扫描完成(废弃)，2支付成功，3支付失败或超时',
  `notify_url` text COMMENT '异步通知地址',
  `return_url` text COMMENT '同步通知地址',
  `param` longtext COMMENT '其他扩展参数，推荐传入json，方便编程语言调用，也可xml',
  `addtime` varchar(10) NOT NULL COMMENT '订单创建时间',
  `endtime` varchar(10) NOT NULL COMMENT '订单过期时间',
  `scantime` varchar(10) DEFAULT NULL COMMENT '二维码扫描时间',
  `successtime` varchar(10) DEFAULT NULL COMMENT '订单完成时间',
  `addtime_time` datetime NOT NULL,
  `endtime_time` datetime NOT NULL,
  `scantime_time` datetime DEFAULT NULL,
  `successtime_time` datetime DEFAULT NULL,
  `domain` varchar(64) DEFAULT NULL COMMENT '订单创建的域名',
  `ip` varchar(128) DEFAULT NULL COMMENT '订单创建的ip，可ipv4，v6',
  `payurl` text COMMENT '订单创建的完成url',
  `name` text COMMENT '购买物品',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_no` (`trade_no`),
  UNIQUE KEY `out_trade_no` (`out_trade_no`),
  UNIQUE KEY `api_trade_no` (`api_trade_no`),
  KEY `userid` (`userid`),
  KEY `tdid` (`tdid`),
  KEY `robotid` (`robotid`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `pluginid` (`pluginid`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ymmzf_order
-- ----------------------------
BEGIN;
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (24, 27, 1000, 0, NULL, NULL, 'wxid_a2s9o5uizh9n12', 'BLUE', '20230925135055825575108', NULL, '2023092513505588079', NULL, '0', 0.01, 0.01, 0.01, '0.01', '2', 'http://ypay.mljf1.cn/pay/notify/2023092513505588079/?out_trade_no=2023092513505588079&trade_no=20230925135055825575108&trade_status=TRADE_SUCCESS&money=0.01&sign=fe83f6c4614c988da55c332637ae42d8&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092513505588079/?out_trade_no=2023092513505588079&trade_no=20230925135055825575108&trade_status=TRADE_SUCCESS&money=0.01&sign=fe83f6c4614c988da55c332637ae42d8&sign_type=MD5', NULL, '1695621055', '1695621355', NULL, '1695621169', '2023-09-25 13:50:55', '2023-09-25 13:55:55', NULL, '2023-09-25 13:52:49', 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (25, 27, 1000, 0, NULL, NULL, 'wxid_a2s9o5uizh9n12', 'BLUE', '20230925135131016895567', NULL, '2023092513513047900', NULL, '0', 0.02, 0.01, 0.02, '0.02', '2', 'http://ypay.mljf1.cn/pay/notify/2023092513513047900/?out_trade_no=2023092513513047900&trade_no=20230925135131016895567&trade_status=TRADE_SUCCESS&money=0.01&sign=4375a4e9fc8cd75ce34c6c5da1b550a3&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092513513047900/?out_trade_no=2023092513513047900&trade_no=20230925135131016895567&trade_status=TRADE_SUCCESS&money=0.01&sign=4375a4e9fc8cd75ce34c6c5da1b550a3&sign_type=MD5', NULL, '1695621091', '1695621391', NULL, '1695621139', '2023-09-25 13:51:31', '2023-09-25 13:56:31', NULL, '2023-09-25 13:52:19', 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (26, 27, 1000, 0, NULL, NULL, 'wxid_a2s9o5uizh9n12', 'BLUE', '20230925135235501228169', NULL, '2023092513523436069', NULL, '0', 0.02, 0.01, NULL, NULL, '0', 'http://ypay.mljf1.cn/pay/notify/2023092513523436069/?out_trade_no=2023092513523436069&trade_no=20230925135235501228169&trade_status=TRADE_SUCCESS&money=0.01&sign=6b959cf59bde88623994a1e074df0e5a&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092513523436069/?out_trade_no=2023092513523436069&trade_no=20230925135235501228169&trade_status=TRADE_SUCCESS&money=0.01&sign=6b959cf59bde88623994a1e074df0e5a&sign_type=MD5', NULL, '1695621155', '1695621455', NULL, NULL, '2023-09-25 13:52:35', '2023-09-25 13:57:35', NULL, NULL, 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (27, 27, 1000, 0, NULL, NULL, 'wxid_a2s9o5uizh9n12', 'BLUE', '20230925141522571231400', NULL, '2023092514152181077', NULL, '0', 1.01, 1.00, NULL, NULL, '3', 'http://ypay.mljf1.cn/pay/notify/2023092514152181077/?out_trade_no=2023092514152181077&trade_no=20230925141522571231400&trade_status=TRADE_SUCCESS&money=1&sign=c7189ca80aa3552aa6c2e4509571869a&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092514152181077/?out_trade_no=2023092514152181077&trade_no=20230925141522571231400&trade_status=TRADE_SUCCESS&money=1&sign=c7189ca80aa3552aa6c2e4509571869a&sign_type=MD5', NULL, '1695622522', '1695622822', NULL, NULL, '2023-09-25 14:15:22', '2023-09-25 14:20:22', NULL, NULL, 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (28, 27, 1000, 0, NULL, NULL, 'wxid_a2s9o5uizh9n12', 'BLUE', '20230925142358438414569', NULL, '2023092514232673086', NULL, '0', 1.01, 1.00, 1.01, '1.01', '2', 'http://ypay.mljf1.cn/pay/notify/2023092514232673086/?out_trade_no=2023092514232673086&trade_no=20230925142358438414569&trade_status=TRADE_SUCCESS&money=1&sign=f4a3db60345d6bb6b533f008558c7353&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092514232673086/?out_trade_no=2023092514232673086&trade_no=20230925142358438414569&trade_status=TRADE_SUCCESS&money=1&sign=f4a3db60345d6bb6b533f008558c7353&sign_type=MD5', NULL, '1695623038', '1695623338', NULL, '1695959770', '2023-09-25 14:23:58', '2023-09-25 14:28:58', NULL, '2023-09-29 11:56:10', 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (29, 27, 1000, 0, NULL, NULL, NULL, 'BLUE', '20230925142558355939334', NULL, '2023092514255726513', NULL, '0', 1.02, 1.00, NULL, NULL, '0', 'http://ypay.mljf1.cn/pay/notify/2023092514255726513/?out_trade_no=2023092514255726513&trade_no=20230925142558355939334&trade_status=TRADE_SUCCESS&money=1&sign=de0c5b664d25fcd7185120e9ea5eb467&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092514255726513/?out_trade_no=2023092514255726513&trade_no=20230925142558355939334&trade_status=TRADE_SUCCESS&money=1&sign=de0c5b664d25fcd7185120e9ea5eb467&sign_type=MD5', NULL, '1695623158', '1695623458', NULL, NULL, '2023-09-25 14:25:58', '2023-09-25 14:30:58', NULL, NULL, 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (30, 27, 1000, 0, NULL, NULL, NULL, 'BLUE', '20230925142718086663246', NULL, '2023092514271740628', NULL, '0', 1.03, 1.00, NULL, NULL, '0', 'http://ypay.mljf1.cn/pay/notify/2023092514271740628/?out_trade_no=2023092514271740628&trade_no=20230925142718086663246&trade_status=TRADE_SUCCESS&money=1&sign=880206d290b7bb4fe934d3635d3717b3&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092514271740628/?out_trade_no=2023092514271740628&trade_no=20230925142718086663246&trade_status=TRADE_SUCCESS&money=1&sign=880206d290b7bb4fe934d3635d3717b3&sign_type=MD5', NULL, '1695623238', '1695623538', NULL, NULL, '2023-09-25 14:27:18', '2023-09-25 14:32:18', NULL, NULL, 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
INSERT INTO `ymmzf_order` (`id`, `tdid`, `userid`, `pluginid`, `wxid`, `username`, `robotid`, `dz_nickname`, `trade_no`, `transid`, `out_trade_no`, `api_trade_no`, `type`, `money`, `realmoney`, `getmoney`, `getrealmoney`, `status`, `notify_url`, `return_url`, `param`, `addtime`, `endtime`, `scantime`, `successtime`, `addtime_time`, `endtime_time`, `scantime_time`, `successtime_time`, `domain`, `ip`, `payurl`, `name`) VALUES (31, 27, 1000, 0, NULL, NULL, NULL, 'BLUE', '20230929103621555673681', NULL, '2023092910362063197', NULL, '0', 1.01, 1.00, NULL, NULL, '0', 'http://ypay.mljf1.cn/pay/notify/2023092910362063197/?out_trade_no=2023092910362063197&trade_no=20230929103621555673681&trade_status=TRADE_SUCCESS&money=1&sign=7ab5649c3d35254c8ee675adeaa6382e&sign_type=MD5', 'http://ypay.mljf1.cn/pay/return/2023092910362063197/?out_trade_no=2023092910362063197&trade_no=20230929103621555673681&trade_status=TRADE_SUCCESS&money=1&sign=7ab5649c3d35254c8ee675adeaa6382e&sign_type=MD5', NULL, '1695954981', '1695955281', NULL, NULL, '2023-09-29 10:36:21', '2023-09-29 10:41:21', NULL, NULL, 'ypay.mljf1.cn', '10.211.55.2', NULL, '支付测试');
COMMIT;

-- ----------------------------
-- Table structure for ymmzf_user
-- ----------------------------
DROP TABLE IF EXISTS `ymmzf_user`;
CREATE TABLE `ymmzf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) DEFAULT NULL COMMENT '用户名',
  `account` varchar(128) DEFAULT NULL COMMENT '账号',
  `pwd` varchar(32) DEFAULT NULL COMMENT '密码',
  `key` varchar(32) DEFAULT NULL COMMENT '密钥',
  `money` decimal(65,2) DEFAULT NULL COMMENT '余额',
  `addtime` varchar(10) DEFAULT NULL COMMENT '注册时间',
  `lasttime` varchar(10) DEFAULT NULL COMMENT '最后登录时间',
  `endtime` varchar(10) DEFAULT NULL COMMENT '封禁时间',
  `level` tinyint(1) DEFAULT NULL COMMENT '等级',
  `status` enum('0','1') DEFAULT '0' COMMENT '0未禁用，1禁用',
  `comment` longtext COMMENT '封禁理由',
  `pay` enum('0','1') DEFAULT NULL COMMENT '支付功能是否启用，0启用，1禁用',
  `pay_time` int(5) DEFAULT '300' COMMENT '支付超时时间，秒',
  `ordinary_token` varchar(32) DEFAULT NULL,
  `ip` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ymmzf_user
-- ----------------------------
BEGIN;
INSERT INTO `ymmzf_user` (`id`, `username`, `account`, `pwd`, `key`, `money`, `addtime`, `lasttime`, `endtime`, `level`, `status`, `comment`, `pay`, `pay_time`, `ordinary_token`, `ip`) VALUES (1, NULL, 'admin', '7a92e9c9d4a41842aee464898bc6d3cf', NULL, NULL, NULL, '1695959686', NULL, NULL, '0', NULL, NULL, 300, '6945c2115ead6e7a7e5c99bcaf047c91', '10.211.55.2');
INSERT INTO `ymmzf_user` (`id`, `username`, `account`, `pwd`, `key`, `money`, `addtime`, `lasttime`, `endtime`, `level`, `status`, `comment`, `pay`, `pay_time`, `ordinary_token`, `ip`) VALUES (1000, NULL, 'amen', 'e656e6dcf63f706c7b45f667d72a21f6', '8a50bca7326a2b06bf42a55d3c90bc15', NULL, NULL, '1695898250', NULL, NULL, '0', NULL, NULL, 300, 'dc7b79c9a703935c0461cf0548a93b02', '10.211.55.2');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
