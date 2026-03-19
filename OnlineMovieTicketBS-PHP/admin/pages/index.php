<?php
session_start();
if(!isset($_SESSION['admin']))
{
  header('location:../index.php');
}
date_default_timezone_set('Asia/Kolkata');
include('../../config.php');
include_once('../../demo_config.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if(defined('DEMO_MODE') && DEMO_MODE){
    $current = basename($_SERVER['PHP_SELF']);
    if(isset($DEMO_ALLOW_ADMIN) && !in_array($current, $DEMO_ALLOW_ADMIN)){
        header('Location: index.php');
        exit;
    }
}

// Inline process handlers (replaces separate process_*.php files)
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  if($action === 'add_theater'){
    extract($_POST);
    mysqli_query($con,"insert into  tbl_theatre values(NULL,'$name','$address','$place','$state','$pin')");
    $id=mysqli_insert_id($con);
    mysqli_query($con,"insert into  tbl_login values(NULL,'$id','$username','$password','1')");
    header('location:add_theatre_2.php?id='.$id);
    exit;
  }
  if($action === 'add_news'){
    extract($_POST);
    $uploaddir = '../news_images/';
    $uploadfile = $uploaddir . basename($_FILES['attachment']['name']);
    @move_uploaded_file($_FILES['attachment']['tmp_name'],$uploadfile);
    $flname = "news_images/".basename($_FILES["attachment"]["name"]);
    mysqli_query($con,"insert into  tbl_news values(NULL,'$name','$cast','$date','$description','$flname')");
    $_SESSION['add']=1;
    header('location:index.php?action=add_movie_news');
    exit;
  }
}

if($action === 'delete_movie' && isset($_GET['mid'])){
  $mid = $_GET['mid'];
  mysqli_query($con,"delete from tbl_movie where movie_id='$mid'");
  $_SESSION['success']="Movie deleted  successfully";
  header("location:index.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Admin | Admin</title>
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
      <span class="logo-mini"><b>O</b>BS</span>
      <span class="logo-lg"><b>OMTBS</b></span>
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
              <img src="admin-icn.png" class="user-image" alt="User Image">
              <span class="hidden-xs">Admin</span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="admin-icn.png" class="img-circle" alt="User Image">
                <p>
                  Theatre Assistant
                </p>
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
          <img src="admin-icn.png" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Administrator</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <ul class="sidebar-menu">
        <li class="treeview">
          <a href="index.php">
            <i class="fa fa-home"></i> <span>Home</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <?php if(!(defined('DEMO_MODE') && DEMO_MODE)){ ?>
        <li class="treeview">
          <a href="index.php?action=add_theatre">
            <i class="fa fa-film"></i> <span>Add Theatre</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="index.php?action=add_movie_news">
            <i class="fa fa-plus"></i> <span>Upcoming Movie News</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <?php } ?>
      </ul>
    </section>
  </aside>

  <div class="content-wrapper">
    <?php if($action === 'add_theatre'){ ?>
    <section class="content-header">
      <h1>
        Add Theatre
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Add Theatre</li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-body">
          <?php include('../../form.php'); $frm=new formBuilder; ?>
          <form action="index.php?action=add_theater" method="post" id="form1">
            <div class="form-group">
              <label class="control-label">Theatre Name</label>
              <input type="text" name="name" class="form-control"/>
              <?php $frm->validate("name",array("required","label"=>"Theatre Name")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Theatre Address</label>
              <input type="text" name="address" class="form-control"/>
              <?php $frm->validate("address",array("required","label"=>"Theatre Address")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Place</label>
              <input type="text" name="place" class="form-control">
              <?php $frm->validate("place",array("required","label"=>"Place")); ?>
            </div>
            <div class="form-group">
               <label class="control-label">State</label>
              <input type="text" name="state" id="administrative_area_level_1" s placeholder="State" class="form-control">
              <?php $frm->validate("state",array("required","label"=>"State")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Pin Code</label>
               <input type="text" name="pin" id="postal_code"s placeholder="Zip code" class="form-control">
               <?php $frm->validate("pin",array("required","label"=>"Pin Code","regexp"=>"pin")); ?>
            </div>
            <?php
              start:
              $username="THR".rand(123456,999999);
              $u=mysqli_query($con,"select * from tbl_login where username='$username'");
              if(mysqli_num_rows($u))
              {
                goto start;
              }
            ?>
            <div class="form-group">
              <label class="control-label">Username</label>
              <input type="text" name="username" class="form-control" value="<?php echo $username ?>">
              <?php $frm->validate("username",array("required","label"=>"Username")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Password</label>
              <input type="text" name="password" class="form-control" value="<?php echo "PWD".rand(123456,999999);?>">
              <?php $frm->validate("password",array("required","label"=>"Password")); ?>
            </div>
            <div class="form-group">
              <button class="btn btn-success">Add Theatre</button>
            </div>
            <input type="hidden" name="country" class="form-control" id="country">
            <input type="hidden" class="field" id="route" disabled="true">
            <input type="hidden" class="field" id="street_number" disabled="true">
            <input type="hidden" class="field" id="locality"disabled="true">
          </form>
        </div>
      </div>
    </section>
    <?php } else if($action === 'add_movie_news'){ ?>
    <section class="content-header">
      <h1>
        Add Upcoming Movie News
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Add Movies News</li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-body">
          <?php include('../../form.php'); $frm=new formBuilder; ?>
          <form action="index.php?action=add_news" method="post" enctype="multipart/form-data" id="form1">
            <div class="form-group">
              <label class="control-label">Movie name</label>
              <input type="text" name="name" class="form-control"/>
              <?php $frm->validate("name",array("required","label"=>"Movie Name")); ?>
            </div>
            <div class="form-group">
               <label class="control-label">Cast</label>
              <input type="text" name="cast" class="form-control">
              <?php $frm->validate("cast",array("required","label"=>"Cast","regexp"=>"text")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Release Date</label>
              <input type="date" name="date" class="form-control"/>
              <?php $frm->validate("date",array("required","label"=>"Release Date")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Description</label>
              <input type="text" name="description" class="form-control">
              <?php $frm->validate("description",array("required","label"=>"Description")); ?>
            </div>
            <div class="form-group">
              <label class="control-label">Images</label>
              <input type="file"  name="attachment" class="form-control" placeholder="Images">
              <?php $frm->validate("attachment",array("required","label"=>"Image")); ?>
            </div>
            <div class="form-group">
              <button class="btn btn-success">Add News</button>
            </div>
          </form>
        </div>
      </div>
    </section>
    <?php } else { ?>
    <section class="content-header">
      <h1>
        Movies List
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Movies List</li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-body">
          <div class="box box-primary">
            <div class="box-body">
              <?php include('../../msgbox.php');?>
              <ul class="todo-list">
                <?php 
                  $qry7=mysqli_query($con,"select * from tbl_movie");
                  if(mysqli_num_rows($qry7))
                  {
                    while($c=mysqli_fetch_array($qry7))
                    {
                ?>
                <li>
                  <span class="handle">
                    <i class="fa fa-film"></i>
                  </span>
                  <span class="text"><?php echo $c['movie_name'];?></span>
                  <div class="tools">
                    <button class="fa fa-trash-o" onclick="del(<?php echo $c['movie_id'];?>)"></button>
                  </div>
                </li>
                <?php
                    }
                  }
                ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php } ?>
  </div>

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.3.8
    </div>
    <strong>&copy; <?php echo date("Y"); ?> <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights
    reserved
  </footer>

  <aside class="control-sidebar control-sidebar-dark">
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane" id="control-sidebar-home-tab"></div>
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>
        </form>
      </div>
    </div>
  </aside>
  <div class="control-sidebar-bg"></div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fastclick/1.0.6/fastclick.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@2.3.11/dist/js/app.min.js"></script>
<script>
function del(m){
  if (confirm("Are you want to delete this movie") == true){
    window.location = "index.php?action=delete_movie&mid=" + m;
  }
}
</script>
<?php if($action === 'add_theatre'){ ?>
<script>
  var placeSearch, autocomplete;
  var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'long_name',
    country: 'long_name',
    postal_code: 'short_name'
  };
  function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
        (document.getElementById('autocomplete')),
        {types: ['geocode']});
    autocomplete.addListener('place_changed', fillInAddress);
  }
  function fillInAddress() {
    var place = autocomplete.getPlace();
    for (var component in componentForm) {
      var el = document.getElementById(component);
      if(!el) continue;
      el.value = '';
      el.disabled = false;
    }
    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) {
        var val = place.address_components[i][componentForm[addressType]];
        var target = document.getElementById(addressType);
        if(target) target.value = val;
      }
    }
  }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDfO40iueprTDv0WCf0BCIlbj56JO-HylA&libraries=places&callback=initAutocomplete" async defer></script>
<script>
  <?php if(file_exists('../../form.php')){ include('../../form.php'); $frm=new formBuilder; echo $frm->applyvalidations("form1"); } ?>
</script>
<?php } else if($action === 'add_movie_news'){ ?>
<script>
  <?php if(file_exists('../../form.php')){ include('../../form.php'); $frm=new formBuilder; echo $frm->applyvalidations("form1"); } ?>
</script>
<?php } ?>
</body>
</html>