<?php
$connection = mysqli_connect('localhost','root','','kuliah');

if (!$connection){
    echo "connection failed" . mysqli_connect_error();
}else{
    echo "connection susccesfully";
}   
?>