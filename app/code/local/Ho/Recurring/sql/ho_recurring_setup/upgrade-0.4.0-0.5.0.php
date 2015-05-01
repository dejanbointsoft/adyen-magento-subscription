<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile table

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        ADD COLUMN `status` smallint(2) unsigned DEFAULT NULL AFTER `entity_id`,
        ADD COLUMN `created_at` timestamp AFTER `store_id`,
        ADD COLUMN `ends_at` timestamp AFTER `created_at`;

");

$installer->endSetup();