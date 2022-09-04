<?php
abstract class dbConnector
{
    abstract protected function connect();
    abstract protected function create($value, $key);
    abstract protected function read($keys_selection);
    abstract protected function update($key, $value);
    abstract protected function delete($keys_selection);
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}
class jsonConnector extends dbConnector
{
    protected $dbPath;
    protected $db;
    public function __construct($dbPath)
    {
        $this->dbPath = $dbPath;
        $this->db = $this->connect();
    }
    protected function connect()
    {
        $db = json_decode(file_get_contents($this->dbPath), true);
        if ($db == NULL) {
            return [];
        } else {
            return $db;
        }
    }
    protected function overrwiteDb()
    {
        file_put_contents($this->dbPath, json_encode($this->db));
    }
    function create($values, $key = null)
    {
        if (array_key_exists($key, $this->db)) {
            echo 'key is already exists';
        } else if ($key == null) {
            array_push($this->db, $values);
        } else {
            $this->db[$key] = $values;
        }
        $this->overrwiteDb();
    }
    function read($keys_selection)
    {
        $selection = array_intersect_key($this->db, $keys_selection);
        return $selection;
    }
    function update($key, $values)
    {
        if (!array_key_exists($key, $this->db)) {
            echo "there's no such key";
        } else if (is_array($values)) {
            foreach ($values as $k => $v) {
                $this->db[$key][$k] = $v;
            }
        } else {
            $this->db[$key] = $values;
        }
        $this->overrwiteDb();
    }
    function delete($keys_selection)
    {
        $selection = array_diff_key($this->db, $keys_selection);
        return $selection;
    }
}
abstract class serverRequestHandler
{
    abstract public function handle_request();
    abstract public function generate_response();
    public function encode_subarrays($array)
    {
        foreach ($array as &$subarray) {
            $subarray = json_encode($subarray);
        }
        return $array;
    }
    public function decode_subarrays($array)
    {
        foreach ($array as &$subarray) {
            $subarray = json_decode($subarray, true);
        }
        return $array;
    }
    public function clear_old_logs($logs, $max_livetime, $current_date)
    {
        foreach ($logs as $date => $attempt) {
            $attempt_livetime = strtotime($current_date) - strtotime($date);
            if ($attempt_livetime > $max_livetime) {
                unset($logs[$date]);
            }
        }
        return $logs;
    }
    public function exclude_banned_attempts($logs, $ban_logs, $ban_livetime)
    {
        foreach ($logs as $attempt_date => $attempt) {
            if (in_array($attempt, $ban_logs)) {
                $ban_date = array_search($attempt, $ban_logs);
                $ban_end_time = strtotime($ban_date) + $ban_livetime;
                $attempt_time = strtotime($attempt_date);
                if ($attempt_time < $ban_end_time) {
                    unset($logs[$attempt_date]);
                }
            }
        }
        return $logs;
    }
}
