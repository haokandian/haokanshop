# 佣金明细
CREATE TABLE `{PREFIX}plugins_distribution_profit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单id',
  `order_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单用户id',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退回金额（一般订单发生退款）',
  `profit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '收益金额（已扣除退款金额，最终实际收益金额）',
  `rate` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '返佣比例 0~100 的数字（创建时写入，防止发送退款重新计算时用户等级变更）',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '当前级别（1~3）',
  `is_refund` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有退款（0否, 1是）',
  `msg` char(255) NOT NULL DEFAULT '' COMMENT '描述（一般用于退款描述）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销佣金明细 - 应用';