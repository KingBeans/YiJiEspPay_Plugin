<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `cc_firstname` VARCHAR( 50 ) DEFAULT NULL COMMENT 'cc_firstname';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `cc_emali` VARCHAR( 50 ) DEFAULT NULL COMMENT 'cc_emali';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `phone_number` VARCHAR( 50 ) DEFAULT NULL COMMENT 'phone_number';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `cc_number` VARCHAR( 25 ) DEFAULT NULL COMMENT 'cc_number';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `cc_cid` VARCHAR( 10 ) DEFAULT NULL COMMENT 'cc_cid';


ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `cc_firstname` VARCHAR( 50 ) DEFAULT NULL COMMENT 'cc_firstname';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `cc_emali` VARCHAR( 50 ) DEFAULT NULL COMMENT 'cc_emali';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `phone_number` VARCHAR( 50 ) DEFAULT NULL COMMENT 'phone_number';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `cc_number` VARCHAR( 25 ) DEFAULT NULL COMMENT 'cc_number';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `cc_cid` VARCHAR( 10 ) DEFAULT NULL COMMENT 'cc_cid';

");
$installer->endSetup();
