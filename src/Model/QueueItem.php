<?php 

namespace ProductionPanic\BulkDelete\Model;

use CodesVault\Howdyqb\DB;
use DateTime;

class QueueItem extends Model {
    protected string $table = BDID_QUEUE_TABLE_POST;

    public int $id;    
    public int $queue_task_id;
    public int $post_id;
    public string $status;
    public DateTime $created_at;
    public DateTime $updated_at;

    public static function get_by_queue(int $queue_id) {
        $result = DB::select('id')
            ->from(BDID_QUEUE_TABLE_POST)
            ->where('queue_task_id','=', $queue_id)->get();
        
        $items = [];
        foreach ($result as $row) {
            $items[] =  QueueItem::find($row['id']);
        }
        return $items;
    }

    public static function get_next(int $queue_id) {
        $result = DB::select('id')
            ->from(BDID_QUEUE_TABLE_POST)
            ->where('queue_task_id', '=', $queue_id)
            ->where('status', '=', 'pending')
            ->get();

        if (count($result) === 0) {
            return null;
        }

        return new QueueItem($result[0]['id']);
    }

    public static function create_entries(int $Parent_id, array $post_ids) {
        $items = [];
        foreach ($post_ids as $post_id) {
            $items[] = [
                'queue_task_id' => $Parent_id,
                'post_id' => $post_id,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        DB::insert(BDID_QUEUE_TABLE_POST, $items);
    }
}