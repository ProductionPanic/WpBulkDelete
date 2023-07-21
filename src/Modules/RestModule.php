<?php 

namespace ProductionPanic\BulkDelete\Modules;

use ProductionPanic\BulkDelete\Common\Singleton;
use ProductionPanic\BulkDelete\Model\Queue;
use ProductionPanic\BulkDelete\Modules\StartTaskModule;
use WP_REST_Request;

class RestModule extends Singleton {
    protected function onInit() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes() {
        // Route for starting a bulk delete task
        register_rest_route( 'bulk-delete/v1', '/start', array(
            'methods' => 'POST',
            'callback' => [$this, 'start'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ) );

        // Route for stopping a bulk delete task
        register_rest_route( 'bulk-delete/v1', '/stop', array(
            'methods' => 'POST',
            'callback' => [$this, 'stop'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ) );

        // Route for triggering a task until it is finished
        // it also retrieves the status of the task
        register_rest_route( 'bulk-delete/v1', '/trigger', array(
            'methods' => 'POST',
            'callback' => [$this, 'trigger'],
        ) );
    }


    public function start(WP_REST_Request $req) {
        // get and validate data
        // post type
        $post_type = $req->get_param('post_type');
        if (!$post_type) {
            wp_send_json_error('No post type provided');
            return;
        }

        // ids
        $ids = $req->get_param('ids');
        if (!$ids) {
            wp_send_json_error('No ids provided');
            return;
        }
        if(!is_array($ids)) {
            wp_send_json_error('Ids should be an array');
            return;
        }

        // start task
        $created = StartTaskModule::get()->start($post_type, $ids);

        // return result
        if ($created) {
            wp_send_json_success([
                'task_id' => $created->id,
            ]);
        } else {
            wp_send_json_error('Task could not be started');            
        }
    }

    public function stop(WP_REST_Request $req) {
        // get and validate data
        // task_id
        $task_id = $req->get_param('task_id');

        if (!$task_id) {
            wp_send_json_error('No task id provided');
            return;
        }

        // stop task
        $task = Queue::find($task_id);
        
        $stopped = $task->stop();

        $stopped =  $stopped->status = 'stopped';

        // return result
        if ($stopped) {
            wp_send_json_success('Task stopped');
        } else {
            wp_send_json_error('Task could not be stopped');
        }
    }

    public function trigger(WP_REST_Request $req) {
        // check nonce
        $nonce = $req->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        // get and validate data
        // task_id
        $task_id = $req->get_param('task_id');

        if (!$task_id) {
            wp_send_json_error('No task id provided');
            return;
        }

        // trigger task
        $task = Queue::find($task_id);

        $task->trigger();

        // query run details
        $details = $task->get_run_details();

        // return result
        wp_send_json($details);
    }
}
