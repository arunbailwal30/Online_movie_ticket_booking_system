<?php
session_start();
if(!isset($_SESSION['theatre']))
{
  header('location:../index.php');
}
date_default_timezone_set('Asia/Kolkata');
include('../../config.php');
include_once('../../demo_config.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Demo guard
if(defined('DEMO_MODE') && DEMO_MODE){
    $current = basename($_SERVER['PHP_SELF']);
    if(isset($DEMO_ALLOW_THEATRE) && !in_array($current, $DEMO_ALLOW_THEATRE)){
        header('Location: index.php');
        exit;
    }
}

// Fetch theatre
$th = mysqli_query($con, "select * from tbl_theatre where id='".$_SESSION['theatre']."'");
$theatre = mysqli_fetch_array($th);

// Inline process handlers
if($action === 'add_movie' && $_SERVER['REQUEST_METHOD'] === 'POST'){
  extract($_POST);
  $target_dir = "../../images/";
  $target_file = $target_dir . basename($_FILES["image"]["name"]);
  $flname = "images/".basename($_FILES["image"]["name"]);
  mysqli_query($con,"insert into  tbl_movie values(NULL,'".$_SESSION['theatre']."','$name','$cast','$desc','$rdate','$flname','$video','0')");
  @move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
  $_SESSION['success']="Movie Added";
  header('location:index.php?action=add_movie');
  exit;
}

if($action === 'add_show' && $_SERVER['REQUEST_METHOD'] === 'POST'){
  extract($_POST);
  if(isset($stime) && is_array($stime)){
    foreach($stime as $time){
      mysqli_query($con,"insert into  tbl_shows values(NULL,'$time','".$_SESSION['theatre']."','$movie','$sdate','1','0')");
    }
  }
  $_SESSION['success']="Show Added";
  header('location:index.php?action=add_show');
  exit;
}

if($action === 'delete_movie' && isset($_GET['mid'])){
  $mid = $_GET['mid'];
  mysqli_query($con,"DELETE FROM tbl_movie WHERE movie_id='$mid'");
  $_SESSION['success']="Movie Deleted";
  header('location:index.php?action=view_movie');
  exit;
}

if($action === 'change_running' && isset($_GET['id'], $_GET['status'])){
  $id = $_GET['id'];
  $status = $_GET['status'];
  mysqli_query($con,"update tbl_shows set r_status='$status' where s_id='$id'");
  $_SESSION['success']="Running Status Updated";
  header('location:index.php?action=view_shows');
  exit;
}

if($action === 'stop_running' && isset($_GET['id'])){
  $id = $_GET['id'];
  mysqli_query($con,"update tbl_shows set status='0' where s_id='$id'");
  $_SESSION['success']="Show Deleted";
  header('location:index.php?action=view_shows');
  exit;
}

if($action === 'get_showtime' && $_SERVER['REQUEST_METHOD'] === 'POST'){
  $screen = isset($_POST['screen']) ? $_POST['screen'] : '';
  $html = '<option value="0">Select Show Times</option>';
  if($screen !== ''){
    $st = mysqli_query($con, "select * from tbl_show_time where screen_id='".mysqli_real_escape_string($con,$screen)."'");
    while($row = mysqli_fetch_array($st)){
      $html .= '<option value="'.$row['st_id'].'">'.date('h:i A',strtotime($row['start_time'])).' ( '.$row['name'].' Show )</option>';
    }
  }
  echo $html;
  exit;
}

if($action === 'get_show' && $_SERVER['REQUEST_METHOD'] === 'POST'){
  $id = isset($_POST['id']) ? $_POST['id'] : '';
  echo '<option value="0">Select Show</option>';
  if($id !== ''){
    $w=mysqli_query($con,"select * from tbl_show_time where screen_id='".mysqli_real_escape_string($con,$id)."'");
    while($sh=mysqli_fetch_array($w)){
      echo '<option value="'.$sh['st_id'].'">'.$sh['name'].'</option>';
    }
  }
  exit;
}

if($action === 'get_tickets' && $_SERVER['REQUEST_METHOD'] === 'POST'){
  $id = isset($_POST['id']) ? $_POST['id'] : '';
  ob_start();
  ?>
<div class="panel panel-default">
  <div class="panel-body" id="disp"><?php
    $w=mysqli_query($con,"select * from tbl_shows where st_id='".mysqli_real_escape_string($con,$id)."' and r_status='1'");
    $swt=mysqli_fetch_array($w);
    $qq=mysqli_query($con,"select * from tbl_bookings where show_id='".$swt['s_id']."' and date=CURDATE()");
    if(mysqli_num_rows($qq)){
      $m=mysqli_query($con,"select * from tbl_movie where movie_id='".$swt['movie_id']."'");
      $movie=mysqli_fetch_array($m);
      ?>
      <h3><small>Movie : </small><?php echo $movie['movie_name'];?></h3>
      <table class="table">
        <th>Slno</th>
        <th>Ticket id</th>
        <th>Viewer Name</th>
        <th>Phone</th>
        <th>Number of Tickets</th>
        <?php $sl=1; while($sh=mysqli_fetch_array($qq)){ $us=mysqli_query($con,"select * from tbl_registration where user_id='".$sh['user_id']."'"); $user=mysqli_fetch_array($us); ?>
        <tr>
          <td><?php echo $sl; $sl++;?></td>
          <td><?php echo $sh['ticket_id'];?></td>
          <td><?php echo $user['name'];?></td>
          <td><?php echo $user['phone'];?></td>
          <td><?php echo $sh['no_seats'];?></td>
        </tr>
        <?php } ?>
      </table>
      <?php
    } else { echo '<h3>No Show</h3>'; }
  ?></div>
</div>
  <?php
  echo ob_get_clean();
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Theatre Assistance</title>
  <script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@2.3.11/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@2.3.11/dist/css/skins/_all-skins.min.css">
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <header class="main-header">
    <a href="index.php" class="logo">
      <span class="logo-mini"><b>T</b>A</span>
      <span class="logo-lg"><b>Theatre</b> Assistant</span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../admin/dist/img/avatar.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $theatre['name'];?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="../../admin/dist/img/avatar.png" class="img-circle" alt="User Image">
                <p> Theatre Assistance </p>
              </li>
              <li class="user-footer">
                <div class="pull-right">
                  <a href="../../shared/api/logout.php" class="btn btn-default btn-flat">Logout</a>
                </div>
              </li>
            </ul>
          </li>
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <aside class="main-sidebar">
    <section class="sidebar">
      <div class="user-panel">
        <div class="pull-left image">
          <img src="../../admin/dist/img/avatar.png" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo $theatre['name'];?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <ul class="sidebar-menu">
        <li class="treeview"><a href="index.php"><i class="fa fa-home"></i> <span>Home</span></a></li>
        <?php if(!(defined('DEMO_MODE') && DEMO_MODE)){ ?>
        <li class="treeview"><a href="index.php?action=add_movie"><i class="fa fa-plus"></i> <span>Add Movie</span></a></li>
        <li class="treeview"><a href="index.php?action=view_movie"><i class="fa fa-list-alt"></i> <span>View Movies</span></a></li>
        <li class="treeview"><a href="index.php?action=add_show"><i class="fa fa-ticket"></i> <span>Add Show</span></a></li>
        <li class="treeview"><a href="index.php?action=todays_shows"><i class="fa fa-calendar"></i> <span>Todays Shows</span></a></li>
        <li class="treeview"><a href="index.php?action=view_shows"><i class="fa fa-eye"></i> <span>View Show</span></a></li>
        <li class="treeview"><a href="index.php?action=tickets"><i class="fa fa-film"></i> <span>Todays Bookings</span></a></li>
        <li class="treeview"><a href="add_theatre_2.php"><i class="fa fa-film"></i> <span>Theatre Details</span></a></li>
        <?php } ?>
      </ul>
    </section>
  </aside>

  <div class="content-wrapper">
    <?php if($action === 'add_movie'){ ?>
    <section class="content-header"><h1>Add Movie</h1></section>
    <section class="content">
      <div class="box"><div class="box-body">
        <?php include('../../msgbox.php');?>
        <?php include('../../form.php'); $frm=new formBuilder; ?>
        <form action="index.php?action=add_movie" method="post" enctype="multipart/form-data" id="form1">
          <div class="form-group">
            <label class="control-label">Movie Name</label>
            <input type="text" name="name" class="form-control"/>
            <?php $frm->validate("name",array("required","label"=>"Movie Name")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Cast</label>
            <input type="text" name="cast" class="form-control"/>
            <?php $frm->validate("cast",array("required","label"=>"Cast","regexp"=>"text")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea name="desc" class="form-control"></textarea>
            <?php $frm->validate("desc",array("required","label"=>"Description")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Release Date</label>
            <input type="date" name="rdate" class="form-control"/>
            <?php $frm->validate("rdate",array("required","label"=>"Release Date")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Image</label>
            <input type="file" name="image" class="form-control"/>
            <?php $frm->validate("image",array("required","label"=>"Image")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Trailer Youtube Link</label>
            <input type="text" name="video" class="form-control"/>
            <?php $frm->validate("video",array("label"=>"Image","max"=>"500")); ?>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-success">Add Movie</button>
          </div>
        </form>
      </div></div>
    </section>
    <?php } else if($action === 'add_show'){ ?>
    <section class="content-header"><h1>Add Show</h1></section>
    <section class="content">
      <div class="box"><div class="box-body">
        <?php include('../../msgbox.php');?>
        <?php include('../../form.php'); $frm=new formBuilder; ?>
        <form action="index.php?action=add_show" method="post" id="form1">
          <div class="form-group">
            <label class="control-label">Select Movie</label>
            <select name="movie" class="form-control">
              <option value>Select Movie</option>
              <?php $mv=mysqli_query($con,"select * from tbl_movie where status='0'"); while($movie=mysqli_fetch_array($mv)){ ?>
                <option value="<?php echo $movie['movie_id'];?>"><?php echo $movie['movie_name']; ?></option>
              <?php } ?>
            </select>
            <?php $frm->validate("movie",array("required","label"=>"Movie")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Select Screen</label>
            <select name="screen" class="form-control" id="screen">
              <option value>Select Screen</option>
              <?php $sc=mysqli_query($con,"select * from tbl_screens where t_id='".$_SESSION['theatre']."'"); while($screen=mysqli_fetch_array($sc)){ ?>
                <option value="<?php echo $screen['screen_id']; ?>"><?php echo $screen['screen_name']; ?></option>
              <?php } ?>
            </select>
            <?php $frm->validate("screen",array("required","label"=>"Screen")); ?>
          </div>
          <div class="form-group">
            <label class="control-label">Select Show Times</label>
            <select name="stime[]" class="form-control" id="stime" multiple>
              <option value="0">Select Show Times</option>
            </select>
          </div>
          <div class="form-group">
            <label class="control-label">Start Date</label>
            <input type="date" name="sdate" class="form-control"/>
            <?php $frm->validate("sdate",array("required","label"=>"Start Date")); ?>
          </div>
          <div class="form-group">
            <button class="btn btn-success">Add Show</button>
          </div>
        </form>
      </div></div>
    </section>
    <?php } else if($action === 'view_movie'){ ?>
    <section class="content-header"><h1>Movies List</h1></section>
    <section class="content">
      <div class="box"><div class="box-body"><div class="box box-primary"><div class="box-body">
        <?php include('../../msgbox.php');?>
        <ul class="todo-list">
          <?php $qry7=mysqli_query($con,"select * from tbl_movie"); if(mysqli_num_rows($qry7)){ while($c=mysqli_fetch_array($qry7)){ ?>
          <li>
            <span class="handle"><i class="fa fa-film"></i></span>
            <span class="text"><?php echo $c['movie_name'];?></span>
            <div class="tools">
              <button class="fa fa-trash-o" onclick="delMovie(<?php echo $c['movie_id'];?>)"></button>
            </div>
          </li>
          <?php } } ?>
        </ul>
      </div></div></div></div>
    </section>
    <?php } else if($action === 'view_shows'){ ?>
    <section class="content-header"><h1>View Shows</h1></section>
    <section class="content">
      <div class="box"><div class="box-body">
        <?php include('../../msgbox.php');?>
        <?php $sw=mysqli_query($con,"select * from tbl_shows where st_id in(select st_id from tbl_show_time where screen_id in(select screen_id from  tbl_screens where t_id='".$_SESSION['theatre']."')) and status='1'"); if(mysqli_num_rows($sw)){ ?>
        <table class="table">
          <th class="col-md-1">Sl.no</th>
          <th class="col-md-2">Screen</th>
          <th class="col-md-3">Show Time</th>
          <th class="col-md-3">Movie</th>
          <th class="col-md-3">Options</th>
          <?php $sl=1; while($shows=mysqli_fetch_array($sw)){ $st=mysqli_query($con,"select * from tbl_show_time where st_id='".$shows['st_id']."'"); $show_time=mysqli_fetch_array($st); $sr=mysqli_query($con,"select * from tbl_screens where screen_id='".$show_time['screen_id']."'"); $screen=mysqli_fetch_array($sr); $mv=mysqli_query($con,"select * from tbl_movie where movie_id='".$shows['movie_id']."'"); $movie=mysqli_fetch_array($mv); ?>
          <tr>
            <td><?php echo $sl; $sl++;?></td>
            <td><?php echo $screen['screen_name'];?></td>
            <td><?php echo date('h:i A',strtotime($show_time['start_time']))." ( ".$show_time['name']." Show )";?></td>
            <td><?php echo $movie['movie_name'];?></td>
            <td>
              <?php if($shows['r_status']==1){ ?>
                <a href="index.php?action=change_running&id=<?php echo $shows['s_id'];?>&status=0"><button class="btn btn-danger">Stop Running</button></a>
              <?php } else { ?>
                <a href="index.php?action=change_running&id=<?php echo $shows['s_id'];?>&status=1"><button class="btn btn-success">Start Running</button></a>
              <?php } ?>
              <a href="index.php?action=stop_running&id=<?php echo $shows['s_id'];?>"><button class="btn btn-warning">Stop Show</button></a>
            </td>
          </tr>
          <?php } ?>
        </table>
        <?php } else { ?><h3>No Shows Added</h3><?php } ?>
      </div></div>
    </section>
    <?php } else if($action === 'todays_shows'){ ?>
    <section class="content-header"><h1>Todays Shows</h1></section>
    <section class="content">
      <div class="box"><div class="box-body">
        <?php $sw=mysqli_query($con,"select * from tbl_shows where st_id in(select st_id from tbl_show_time where screen_id in(select screen_id from  tbl_screens where t_id='".$_SESSION['theatre']."')) and status='1' and r_status='1'"); if(mysqli_num_rows($sw)){ ?>
        <table class="table">
          <th class="col-md-1">Sl.no</th>
          <th class="col-md-2">Screen</th>
          <th class="col-md-3">Show Time</th>
          <th class="col-md-3">Movie</th>
          <?php $sl=1; while($shows=mysqli_fetch_array($sw)){ $st=mysqli_query($con,"select * from tbl_show_time where st_id='".$shows['st_id']."'"); $show_time=mysqli_fetch_array($st); $sr=mysqli_query($con,"select * from tbl_screens where screen_id='".$show_time['screen_id']."'"); $screen=mysqli_fetch_array($sr); $mv=mysqli_query($con,"select * from tbl_movie where movie_id='".$shows['movie_id']."'"); $movie=mysqli_fetch_array($mv); ?>
          <tr>
            <td><?php echo $sl; $sl++;?></td>
            <td><?php echo $screen['screen_name'];?></td>
            <td><?php echo date('h:i A',strtotime($show_time['start_time']))." ( ".$show_time['name']." Show )";?></td>
            <td><?php echo $movie['movie_name'];?></td>
          </tr>
          <?php } ?>
        </table>
        <?php } else { ?><h3>No Shows Added</h3><?php } ?>
      </div></div>
    </section>
    <?php } else if($action === 'tickets'){ ?>
    <section class="content-header">
      <h1>Todays Bookings</h1>
      <ol class="breadcrumb"><li><a href="index.php"><i class="fa fa-home"></i> Home</a></li><li class="active">Todays Bookings</li></ol>
    </section>
    <section class="content">
      <div class="box"><div class="box-body">
        <div class="panel panel-default"><div class="panel-body">
          <div class="form-group col-md-6">
            <label class="control-label">Select Screen</label>
            <select class="form-control" id="screen">
              <option value="0">Select Screen</option>
              <?php $q=mysqli_query($con,"select  * from tbl_screens where t_id='".$_SESSION['theatre']."'"); while($th=mysqli_fetch_array($q)){ ?>
                <option value="<?php echo $th['screen_id'];?>"><?php echo $th['screen_name'];?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group col-md-6">
            <label class="control-label">Select Show</label>
            <select class="form-control" id="show"><option value="0">Select Screen</option></select>
            </div>
        </div></div>
        <div id="disp"></div>
      </div></div>
    </section>
    <?php } else { ?>
    <section class="content-header"><h1>Theatre Assistance</h1></section>
    <section class="content">
      <div class="box"><div class="box-body">
        <div class="box"><div class="box-header"><h3 class="box-title">Running Movies</h3></div>
            <div class="box-body no-padding">
              <table class="table table-condensed">
              <tr><th class="col-md-1">No</th><th class="col-md-3">Show Time</th><th class="col-md-4">Screen</th><th class="col-md-4">Movie</th></tr>
              <?php $qry8=mysqli_query($con,"select * from tbl_shows where r_status=1 and theatre_id='".$_SESSION['theatre']."'"); $no=1; while($mn=mysqli_fetch_array($qry8)){ $qry9=mysqli_query($con,"select * from tbl_movie where movie_id='".$mn['movie_id']."'"); $mov=mysqli_fetch_array($qry9); $qry10=mysqli_query($con,"select * from tbl_show_time where st_id='".$mn['st_id']."'"); $scr=mysqli_fetch_array($qry10); $qry11=mysqli_query($con,"select * from tbl_screens where screen_id='".$scr['screen_id']."'"); $scn=mysqli_fetch_array($qry11); ?>
              <tr>
                <td><?php echo $no; ?></td>
                  <td><span class="badge bg-green"><?php echo $scn['screen_name'];?></span></td>
                  <td><span class="badge bg-light-blue"><?php echo $scr['start_time'];?>(<?php echo $scr['name'];?>)</span></td>
                  <td><?php echo $mov['movie_name'];?></td>
                  </tr>
              <?php $no=$no+1; } ?>
              </table>
          </div>
        </div> 
      </div></div>
    </section>
    <?php } ?>
  </div>

  <footer class="main-footer">
    <div class="pull-right hidden-xs"><b>Version</b> 2.3.8</div>
    <strong>&copy; <?php echo date("Y"); ?> <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights reserved.
  </footer>

  <aside class="control-sidebar control-sidebar-dark">
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane" id="control-sidebar-home-tab"></div>
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <div class="tab-pane" id="control-sidebar-settings-tab"><form method="post"><h3 class="control-sidebar-heading">General Settings</h3></form></div>
    </div>
  </aside>
  <div class="control-sidebar-bg"></div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fastclick/1.0.6/fastclick.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@2.3.11/dist/js/app.min.js"></script>
<script>
function delMovie(m){ if(confirm('Are you want to delete this movie')){ window.location = 'index.php?action=delete_movie&mid='+m; } }
$('#screen').change(function(){ var screen=$(this).val(); $.ajax({ url: 'index.php?action=get_show', type: 'POST', data: 'id='+screen, dataType: 'html' }).done(function(data){ $('#show').html(data); }).fail(function(){ $('#screendtls').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...'); }); });
$('#show').change(function(){ var show=$(this).val(); $.ajax({ url: 'index.php?action=get_tickets', type: 'POST', data: 'id='+show, dataType: 'html' }).done(function(data){ $('#disp').html(data); }).fail(function(){ $('#screendtls').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...'); }); });
</script>
<?php if($action === 'add_movie' || $action === 'add_show'){ ?>
<script>
  <?php if(file_exists('../../form.php')){ include('../../form.php'); $frm=new formBuilder; echo $frm->applyvalidations("form1"); } ?>
</script>
<?php } ?>
</body>
</html>