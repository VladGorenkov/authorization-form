<?php
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{    
  exit;    
}
if (!isset($_SESSION)) {
    session_start();
}

require_once('main.php');

class loginRequestHandler extends serverRequestHandler
{
    protected $login;
    protected $password;
    protected $name;
    protected $last_attempt;
    protected $db;

    protected $attempt_logs;
    protected $ban_logs;

    protected $current_time;
    protected $attempt_livetime;
    protected $ban_livetime;

    public function __construct($login, $password, $db)
    {
        $this->login = $login;
        $this->password = $password;
        $this->db = $db;
        
        $this->name = (array_key_exists( $this->login,$this->db))? $db[$login]['name'] : 'undefined';
        $this->last_attempt = [$this->login => $this->password];
       
        $this->attempt_logs = isset($_COOKIE['attempt_logs']) ? json_decode($_COOKIE['attempt_logs'], true) : [];
        $this->ban_logs = isset($_COOKIE['ban_logs']) ? json_decode($_COOKIE['ban_logs'], true) : [];

        $this->current_date = date('Y-m-d H:i:s');
        $this->attempt_livetime = 5 * 60;
        $this->ban_livetime = 5 * 60;
    }
    public function handle_request()
    {
        //create attempt
        $this->attempt_logs[$this->current_date] = $this->last_attempt;
        //clear old attempts
        $this->attempt_logs = $this->clear_old_logs($this->attempt_logs, $this->attempt_livetime, $this->current_date);
        //clear old bans
        $this->ban_logs = $this->clear_old_logs($this->ban_logs, $this->ban_livetime, $this->current_date);
        //generate_response
        $errors = $this->generate_response();
        //set cookie
        setcookie('attempt_logs', json_encode($this->attempt_logs));
        setcookie('ban_logs', json_encode($this->ban_logs));
        if (count($errors) == 0) {
            $_SESSION['user'] = [
                "login" => $this->login,
                "name" => $this->name
            ];
        }
        //send response
        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
    }
    public function generate_response()
    {
        $errors = [];

        if (!array_key_exists($this->login, $this->db)) {
            $errors['login-error'] = 'такого пользователя не существует';
        } else if ($this->password != $this->db[$this->login]['password']) {
            //encoded logs without banned attempts
            $encoded_attempt_logs = $this->encode_subarrays($this->exclude_banned_attempts($this->attempt_logs, $this->ban_logs, $this->ban_livetime));

            $count_encoded_attempts = array_count_values($encoded_attempt_logs);
            $encoded_last_attempt =  json_encode($this->last_attempt);

            if (in_array($this->last_attempt, $this->ban_logs)) {
                $count_encoded_attempts[$encoded_last_attempt] = 'banned';
            }

            switch ($count_encoded_attempts[$encoded_last_attempt]) {
                case 1:
                    $errors['password-error'] = 'неверный пароль';
                    break;
                case 2:
                    $errors['password-error'] = 'осталось ' . (5 - $count_encoded_attempts[$encoded_last_attempt]) . ' попытки';
                    break;
                case 3:
                    $errors['password-error'] = 'осталось ' . (5 - $count_encoded_attempts[$encoded_last_attempt]) . ' попытки';
                    break;
                case 4:
                    $errors['password-error'] = 'осталось ' . (5 - $count_encoded_attempts[$encoded_last_attempt]) . ' попытка';
                    break;
                case 'banned':
                    $ban_date = array_search($this->last_attempt, $this->ban_logs);
                    $ban_end_time = strtotime($ban_date) + $this->ban_livetime;
                    $ban_remaining_time =  ($ban_end_time - strtotime($this->current_date));
                    $errors['password-error'] = 'попробуйте через ' . date('i:s', $ban_remaining_time);
                    break;
                case $count_encoded_attempts[$encoded_last_attempt] >= 5:
                    //create ban
                    $this->ban_logs[$this->current_date] = $this->last_attempt;
                    $errors['password-error'] = 'попробуйте через ' . ($this->ban_livetime / 60) . ' минут';
                    break;
            }
        }
        return $errors;
    }
}

$login = $_POST['login'];
$password = $_POST['password'];

$password = md5($password);

$jsonConnector = new jsonConnector('usersDb.json');
$loginValidator = new loginRequestHandler($login, $password, $jsonConnector->db);

$loginValidator->handle_request();
