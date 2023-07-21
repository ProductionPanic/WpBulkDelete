<?php

namespace ProductionPanic\BulkDelete\Model;

use DateTime;

class Queue extends Model
{
    protected string $table = BDID_QUEUE_TABLE;
    private int $max_execution_time = 20;
    public int $id;
    public string $post_type;
    public string $status;
    public DateTime $created_at;
    public DateTime $updated_at;
    public string $details;

    public function stop()
    {
        // delete items still in queue
        $items = QueueItem::get_by_queue($this->id);
        foreach ($items as $item) {
            $item->delete();
        }

        $this->status = 'stopped';
        $this->save();

        return $this;
    }

    public function start()
    {
        $this->status = 'running';
        $this->save();

        return $this;
    }

    public function trigger()
    {
        $start_execution_time = time();

        // get items
        $items = QueueItem::get_by_queue($this->id);
        foreach ($items as $item) {
            wp_delete_post($item->post_id, true);
            $item->delete();
            if (time() - $start_execution_time > $this->max_execution_time) {
                break;
            }
        }

        // check if there are still items in the queue
        $items = QueueItem::get_by_queue($this->id);
        if (count($items) === 0) {
            $this->status = 'finished';
            $this->save();
        }

        return $this;
    }

    public function get_run_details()
    {
        // start amount of items
        $details = json_decode($this->details, true);
        // current amount of items
        $current_amount = QueueItem::get_by_queue($this->id);
        // percentage
        $percentage = round((1 - (count($current_amount) / max(count($details['ids']), 1))) * 100, 2);
        // time since start
        $time_since_start = time() - strtotime($this->created_at->format('Y-m-d H:i:s'));
        // time left
        $time_left = round($time_since_start / $percentage * (100 - $percentage));

        return [
            'details' => $details,
            'current_amount' => count($current_amount),
            'percentage' => $percentage,
            'time_since_start' => $time_since_start,
            'time_left' => $time_left
        ];
    }
}
