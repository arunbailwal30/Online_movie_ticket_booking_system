<?php
    $host = "127.0.0.1";
    $user = "root";                     
    $pass = "";                                  
    $db = "movietheatredb";
    $port = 3306;
     $con = mysqli_connect($host, $user, $pass, $db, $port)or die(mysql_error());

    // Central demo guard: restrict direct access when DEMO_MODE is enabled
    // Applies to any script that includes this config
    @include_once(__DIR__ . '/demo_config.php');
    if(defined('DEMO_MODE') && DEMO_MODE){
        $current = basename($_SERVER['PHP_SELF']);
        $path = str_replace('\\', '/', $_SERVER['PHP_SELF']);
        if(strpos($path, '/admin/') !== false){
            if(isset($DEMO_ALLOW_ADMIN) && !in_array($current, $DEMO_ALLOW_ADMIN)){
                header('Location: index.php');
                exit;
            }
        } else if(strpos($path, '/theatre/') !== false){
            if(isset($DEMO_ALLOW_THEATRE) && !in_array($current, $DEMO_ALLOW_THEATRE)){
                header('Location: index.php');
                exit;
            }
        } else {
            if(isset($DEMO_ALLOW_PUBLIC) && !in_array($current, $DEMO_ALLOW_PUBLIC)){
                header('Location: index.php');
                exit;
            }
        }
    }
?>