<?php

$server = "localhost:3307";
$user = "root";
$pass = "";
$bd = "educamente";

if($con = mysqli_connect($server, $user, $pass, $bd)){
   //echo "Conectado";
}
else{
   echo "Erro!";
}


?>