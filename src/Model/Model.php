<?php

namespace ProductionPanic\BulkDelete\Model;
use CodesVault\Howdyqb\DB;

abstract class Model {
    protected string $table = "";

    public int $id;

    final protected function __construct(int $id) {
        if (!$this->table) {
            $clasname = get_class($this);
            $this->table = 'BDID_' . strtolower($clasname);
        }
        $query = DB::select('id')->from($this->table);
        if ($id > 0) {
            $query->where('id', '=', $id);
        } else {
            $query
            ->orderBy('id', 'desc');
        }
        $result = $query->get();

        if(!is_array($result)) {
            throw new \Exception('No record found');
        }

        if (count($result) === 0) {
            throw new \Exception('No record found');
        }

        $data = reset($result); // get first item (should be only one
        $this->id = $data['id'];
        $this->update_from_db();
    }

    private function update_from_db() {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = DB::select('*')->from($this->table)->where('id', '=', $this->id)->get()[0];
        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            // check if property is a datetime
            if($type->getName() === 'DateTime') {
                $this->$name = \DateTime::createFromFormat('Y-m-d H:i:s', $data[$name]);
            } else {
                $this->$name = $data[$name];
            }
        }
    }

    public static function find($id): static {
        return new static($id);
    }

    public static function latest(): static {
        $result = new static(-1);
        return $result;
    }
    
    protected function delete() {
        DB::delete($this->table)->where('id','=', $this->id)->execute();
    }

    public function save() {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            if($type->getName() === 'object') {
                // datetime
                $data[$name] = $this->$name->format('Y-m-d H:i:s');
            } else {
                $data[$name] = $this->$name;
            }
        }

        if ($this->id > 0) {
            DB::update($this->table, self::parse_data($data))->where('id', '=', $this->id)->execute();
        } else {
            DB::insert($this->table, [self::parse_data($data)]);
            $this->id = DB::select('id')->from($this->table)->orderBy('id', 'desc')->get()[0]['id'];
            $this->update_from_db();
        }

        return $this;
    }

    private static function parse_data(array $data) {
        $result = [];
        foreach ($data as $key => $value) {
            // if datetime convert to string
            if (is_object($value) && get_class($value) === 'DateTime') {
                $result[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}