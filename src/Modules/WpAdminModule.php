<?php

namespace ProductionPanic\BulkDelete\Modules;

use ProductionPanic\BulkDelete\Common\Singleton;

class WpAdminModule extends Singleton {
    protected function onInit() {
        $this->register_bulk_options();

        //enqueue scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('pp-bulk-delete', BDID_PUBLIC_URL . 'bulk_delete.js', []);
        wp_enqueue_style('pp-bulk-delete', BDID_PUBLIC_URL . 'main.css');

        wp_localize_script('pp-bulk-delete', 'ppBulkDelete', [
            'ajaxUrl' => rest_url('bulk-delete/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    private function register_bulk_options() {
        $posttypes = get_post_types(['public' => true], 'names');
        foreach ($posttypes as $posttype) {
            $this->register_bulk_options_for_posttype($posttype);
        }
    }

    private function register_bulk_options_for_posttype(string $posttype) {
        add_filter('bulk_actions-edit-' . $posttype, [$this, 'register_bulk_action']);
        add_filter('handle_bulk_actions-edit-' . $posttype, [$this, 'handle_bulk_action'], 10, 3);
    }

    public function register_bulk_action($bulk_actions) {
        $bulk_actions['bulk_delete'] = 'Bulk Delete';
        return $bulk_actions;
    }

    public function handle_bulk_action($redirect_to, $action, $post_ids) {
        if ($action !== 'bulk_delete') {
            return $redirect_to;
        }

        $post_type = $_REQUEST['post_type'] ?? '';

        $created = StartTaskModule::get()->start($post_type, $post_ids);

        if ($created) {
            $redirect_to = add_query_arg([
                'bulk_delete' => 'success',
                'task_id' => $created->id,
            ], $redirect_to);
        } else {
            $redirect_to = add_query_arg([
                'bulk_delete' => 'error',
            ], $redirect_to);
        }

        return $redirect_to;
    }
}