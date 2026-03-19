<?php
include('../../config.php');
session_start();

$email = isset($_POST['Email']) ? $_POST['Email'] : '';
$pass = isset($_POST['Password']) ? $_POST['Password'] : '';
$role = isset($_POST['role']) ? $_POST['role'] : ''; // 'admin' or 'theatre'

$qry=mysqli_query($con,"select * from tbl_login where username='$email' and password='$pass'");
if(mysqli_num_rows($qry))
{
    $usr=mysqli_fetch_array($qry);
    if($role==='admin' && $usr['user_type']==0)
    {
        $_SESSION['admin']=$usr['user_id'];
        header('location: ../../admin/pages/index.php');
        exit;
    }
    if($role==='theatre' && $usr['user_type']==1)
    {
        $_SESSION['theatre']=$usr['user_id'];
        header('location: ../../theatre/pages/index.php');
        exit;
    }
    $_SESSION['error']="Login Failed!";
    header('location: ../../'.($role==='admin'?'admin':'theatre').'/index.php');
    exit;
}
else
{
    $_SESSION['error']="Login Failed!";
    header('location: ../../'.($role==='admin'?'admin':'theatre').'/index.php');
    exit;
}
?>

