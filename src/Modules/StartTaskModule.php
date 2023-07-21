<?php 

namespace ProductionPanic\BulkDelete\Modules;
use CodesVault\Howdyqb\DB;
use ProductionPanic\BulkDelete\Common\Singleton;
use ProductionPanic\BulkDelete\Model\Queue;
use ProductionPanic\BulkDelete\Model\QueueItem;

class StartTaskModule extends Singleton {
    public function start($post_type, $ids) {
            DB::insert(BDID_QUEUE_TABLE, [[
                'post_type' => $post_type,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'details' => json_encode([
                    'ids' => $ids,
                ]),            
            ]]);

            $item = Queue::latest();

            if(!$item) {
                throw new \Exception('Could not create queue item');
            }

            QueueItem::create_entries($item->id, $ids);
            
            return $item;
    }
}