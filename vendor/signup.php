<?php
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{    
  exit;    
}
if (!isset($_SESSION)) {
    session_start();
}

require_once('main.php');

class registerRequestHandler extends serverRequestHandler
{
    protected $login;
    protected $password;
    protected $email;
    protected $name;

    protected $last_attempt;
    protected $db;

    protected $current_time;


    public function __construct($login, $password, $email, $name, $dbConnector)
    {
        $this->login = $login;
        $this->password = $password;
        $this->email = $email;
        $this->name = $name;
        $this->dbConnector = $dbConnector;
        $this->db = $dbConnector->db;
        $this->current_date = date('Y-m-d H:i:s');
    }
    public function handle_request()
    {
        //generate_response
        $errors = $this->generate_response();
        if (count($errors) == 0) {
            $this->dbConnector->create(['password' => $this->password, 'email' => $this->email, 'name' => $this->name], $this->login);
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
        if (array_key_exists($this->login, $this->db)) {
            $errors['login-error'] = 'пользователь уже зарегистрирован';
        }
        if (in_array($this->email, array_column($this->db, 'email'))) {
            $errors['email-error'] = 'пользователь с таким адресом уже зарегистрирован';
        }
        if (in_array($this->name, array_column($this->db, 'name'))) {
            $errors['name-error'] = 'этот никнейм занят';
        }
        return $errors;
    }
}

$login = $_POST['login'];
$password = md5($_POST['password']);
$email = $_POST['email'];
$name = $_POST['name'];

$jsonConnector = new jsonConnector('usersDb.json');
$loginValidator = new registerRequestHandler($login, $password, $email, $name, $jsonConnector);

$loginValidator->handle_request();
