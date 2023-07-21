<?php 

namespace ProductionPanic\BulkDelete\Modules;

use CodesVault\Howdyqb\DB;
use ProductionPanic\BulkDelete\Common\Singleton;

class DbInitModule extends Singleton {
    protected function onInit() {
        // require needed wp functions
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // create tables
        $this->create_queue_table();
        $this->create_log_table();
    }

    private function create_queue_table() {
        DB::create(BDID_QUEUE_TABLE)
            ->column('id')->bigInt()->unsigned()->autoIncrement()->primary()->required()
            ->column('post_type')->text()->required()
            ->column('status')->text()->required()
            ->column('created_at')->datetime()->required()
            ->column('updated_at')->datetime()->required()
            ->column('details')->text()->required()
            ->index(['id'])
            ->execute();
    
        DB::create(BDID_QUEUE_TABLE_POST)
            ->column('id')->bigInt()->unsigned()->autoIncrement()->primary()->required()
            ->column('queue_task_id')->bigInt()->required()
            ->column('post_id')->bigInt()->required()
            ->column('status')->text()->required()
            ->column('created_at')->datetime()->required()
            ->column('updated_at')->datetime()->required()
            ->index(['id'])
            ->execute();
     }
    
    // log table
    private function create_log_table() {
        DB::create(BDID_LOG_TABLE)
            ->column('id')->bigInt()->unsigned()->autoIncrement()->primary()->required()
            ->column('queue_task_id')->bigInt()->required()
            ->column('created_at')->datetime()->required()
            ->column('message')->text()->required()
            ->column('details')->text()->required()
            ->column('level')->int()->unsigned()->required()
            ->index(['id'])
            ->execute();       
    }
    
}