<?php

$host = '54.179.36.5';
$dbuser = 'admin';
$dbpass = 'admin5BrET+VY/JpJIYP2MXuaNLfFDHRGKW6a';
$dbname = 'projectfingerprint';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if($conn->connect_error){
    echo'<script>
        alert("There was an error connecting to database, Error : '.$conn->connect_error.'");
        window.location.href="";
    </script>';
    exit();
}
?>