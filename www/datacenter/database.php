<?php 

    $host = 'localhost';

    $user = 'root';

    $password = '';

    $database = 'ipnz_db';

    ini_set('mysql.connect_timeout',300);

    ini_set('default_socket_timeout',300);

    

  

    // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

    $connection = new mysqli($host, $user, $password, $database);





    if ($connection->connect_error) {

        echo "Unable to connect to server <br/>";

        echo "Message: ".$connection->connect_error;

    } else {

          //echo "Connected";

    }



?>