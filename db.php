<?php 
    $serverName = "localhost";
    $username = "root";
    $pass = "";
    $databaseName = "cafateriaDB";


  $connection =   mysqli_connect($serverName, $username, $pass,$databaseName);

//   if($connection){
//       echo "connected";
//   }else{
//       echo "not connected";
//   }
