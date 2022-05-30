<?php
class createConnection  { // make connection
    var $host = 'localhost';
    var $user = 'root';
    var $pass = '';
    var $db = 'flowspot_db';
    var $myconn;

    function connectToDatabase() {
        $con = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$con) {
            die('Could not connect to database!');
        } else {
            $this->myconn = $con;
//            echo 'Connection established!';
        }
        return $this->myconn;
    }

    function close() {
        mysqli_close($this->myconn);
//        echo 'Connection closed!';
    }

}
?>