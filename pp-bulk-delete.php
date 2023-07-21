<?php

/**
 * Plugin Name: Bulk Delete
 * Plugin URI: https://github.com/ProductionPanic/WpBulkDelete.git
 * Description: Bulk delete posts, pages, users, attachments and other custom post types and related items
 * Version: 1.0.1
 * Author: ProductionPanic
 * Author URI: https://github.com/ProductionPanic
 * License: GPL3
 * update URI: https://github.com/ProductionPanic/WpBulkDelete.git
 * Requires at least: 5.2
 * Tested up to: 6.2.2
 * Requires PHP: 8.2
 */


// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

const BDID_QUEUE_TABLE = 'bulk_delete_queue_task';
const BDID_QUEUE_TABLE_POST = 'bulk_delete_queue_task_post';
const BDID_LOG_TABLE = 'bulk_delete_logging';
define('BDID_PUBLIC_URL', plugin_dir_url(__FILE__) . '/public/dist/');

// load plugin
require_once __DIR__ . '/vendor/autoload.php';


// create tables
register_activation_hook(__FILE__, function () {
    ProductionPanic\BulkDelete\Modules\DbInitModule::get();
});


// start rest api
ProductionPanic\BulkDelete\Modules\RestModule::get();

// start wp admin module
ProductionPanic\BulkDelete\Modules\WpAdminModule::get();
