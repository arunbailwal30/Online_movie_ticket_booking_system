<?php
include('config.php');
include_once('demo_config.php');
session_start();
date_default_timezone_set('Asia/Kathmandu');
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Demo allowlist guard for public site
if(defined('DEMO_MODE') && DEMO_MODE){
	$current = basename($_SERVER['PHP_SELF']);
	if(isset($DEMO_ALLOW_PUBLIC) && !in_array($current, $DEMO_ALLOW_PUBLIC)){
		header('Location: index.php');
		exit;
	}
}

// Handle POST actions
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  if($action === 'login'){
    $email = $_POST["Email"];
    $pass = $_POST["Password"];
    $qry=mysqli_query($con,"select * from tbl_login where username='$email' and password='$pass'");
    if(mysqli_num_rows($qry)){
      $usr=mysqli_fetch_array($qry);
      if($usr['user_type']==2){
        $_SESSION['user']=$usr['user_id'];
        if(isset($_SESSION['show'])){ header('location:index.php?action=booking'); }
        else { header('location:index.php'); }
      } else { $_SESSION['error']="Login Failed!"; header("location:index.php?action=login"); }
    } else { $_SESSION['error']="Login Failed!"; header("location:index.php?action=login"); }
    exit;
  }
  if($action === 'register'){
    extract($_POST);
    mysqli_query($con,"insert into  tbl_registration values(NULL,'$name','$email','$phone','$age','gender')");
    $id=mysqli_insert_id($con);
    mysqli_query($con,"insert into  tbl_login values(NULL,'$id','$email','$password','2')");
    $_SESSION['user']=$id;
    header('location:index.php');
    exit;
  }
  if($action === 'contact'){
    extract($_POST);
    mysqli_query($con,"insert into tbl_contact values(NULL,'$name','$email','$mobile','$subject')");
    $_SESSION['success'] = 'Thanks for contacting us!';
    header('location:index.php?action=contact');
    exit;
  }
}

include('header.php');
?>
<div class="content">
	<div class="wrap">
		<?php if($action === 'movies'){ ?>
		<div class="content-top">
			<center><h1 style="color:#555;">(NOW SHOWING)</h1></center>
			<?php $qry2=mysqli_query($con,"select * from  tbl_movie "); while($m=mysqli_fetch_array($qry2)){ ?>
			<div class="col_1_of_4 span_1_of_4">
				<div class="imageRow">
					<div class="single">
						<a href="about.php?id=<?php echo $m['movie_id'];?>"><img src="<?php echo $m['image'];?>" alt="" /></a>
					</div>
					<div class="movie-text">
						<h4 class="h-text"><a href="about.php?id=<?php echo $m['movie_id']; ?>" style="text-decoration:none;">&nbsp;<?php echo $m['movie_name'];?></a></h4>
						Cast: <Span class="color2" style="text-decoration:none;">&nbsp;<?php echo $m['cast'];?></span><br>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<?php } else if($action === 'login'){ ?>
		<div class="content-top" style="min-height:300px;padding:50px">
			<div class="col-md-4 col-md-offset-4">
				<div class="panel panel-default">
					<div class="panel-heading">Login</div>
					<div class="panel-body">
						<?php include('msgbox.php');?>
						<p class="login-box-msg">Sign in to start your session</p>
						<form action="index.php?action=login" method="post">
							<div class="form-group has-feedback">
								<input name="Email" type="text" size="25" placeholder="Email" class="form-control"/>
								<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="Password" type="password" size="25" placeholder="Password" class="form-control"/>
								<span class="glyphicon glyphicon-lock form-control-feedback"></span>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary">Login</button>
								<p class="login-box-msg" style="padding-top:20px">New Here? <a href="index.php?action=register">Register</a></p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php } else if($action === 'register'){ ?>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css"/>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
		<?php include('form.php'); $frm=new formBuilder; ?>
		<div class="content-top" style="min-height:300px;padding:50px">
			<div class="col-md-4 col-md-offset-4">
				<div class="panel panel-default">
					<div class="panel-heading">Register</div>
					<div class="panel-body">
						<form action="index.php?action=register" method="post" id="form1">
							<div class="form-group has-feedback">
								<input name="name" type="text" size="25" placeholder="Name" class="form-control"/>
								<?php $frm->validate("name",array("required","label"=>"Name","regexp"=>"name")); ?>
								<span class="glyphicon glyphicon-user form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="age" type="text" size="25" placeholder="Age" class="form-control"/>
								<?php $frm->validate("age",array("required","label"=>"Age","regexp"=>"age")); ?>
								<span class="glyphicon glyphicon-user form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<select name="gender" class="form-control">
									<option value>Select Gender</option>
									<option>Male</option>
									<option>Female</option>
								</select>
								<?php $frm->validate("gender",array("required","label"=>"Gender")); ?>
								<span class="glyphicon glyphicon-user form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="phone" type="text" size="25" placeholder="Mobile Number" class="form-control"/>
								<?php $frm->validate("phone",array("required","label"=>"Mobile Number","regexp"=>"mobile")); ?>
								<span class="glyphicon glyphicon-phone form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="email" type="text" size="25" placeholder="Email" class="form-control"/>
								<?php $frm->validate("email",array("required","label"=>"Email","email")); ?>
								<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="password" type="password" size="25" placeholder="Password" class="form-control"/>
								<?php $frm->validate("password",array("required","label"=>"Password","min"=>"7")); ?>
								<span class="glyphicon glyphicon-lock form-control-feedback"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="cpassword" type="password" size="25" placeholder="Password" class="form-control"/>
								<?php $frm->validate("cpassword",array("required","label"=>"Confirm Password","min"=>"7","identical"=>"password Password")); ?>
								<span class="glyphicon glyphicon-lock form-control-feedback"></span>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary">Continue</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<script>
		<?php $frm->applyvalidations("form1");?>
		</script>
		<?php } else if($action === 'contact'){ ?>
		<div class="content-top">
			<div class="section group">
				<div class="col span_2_of_3">
					<div class="contact-form">
						<h3>Contact Us</h3>
						<?php include('msgbox.php');?>
						<form action="index.php?action=contact" method="post" name="form11">
							<div><span><label>NAME</label></span><span><input type="text" required name="name"></span></div>
							<div><span><label>E-MAIL</label></span><span><input type="text" required name="email"></span></div>
							<div><span><label>MOBILE.NO</label></span><span><input type="number" required name="mobile"></span></div>
							<div><span><label>SUBJECT</label></span><span><textarea required name="subject"></textarea></span></div>
							<div><span><input type="submit" value="Submit"></span></div>
						</form>
					</div>
				</div>
				<div class="col span_1_of_3">
					<div class="contact_info"><h3>Find Us Here</h3>
						<div class="map"><iframe width="100%" height="175" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.co.in/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=Lighthouse+Point,+FL,+United+States&amp;aq=4&amp;oq=light&amp;sll=26.275636,-80.087265&amp;sspn=0.04941,0.104628&amp;ie=UTF8&amp;hq=&amp;hnear=Lighthouse+Point,+Broward,+Florida,+United+States&amp;t=m&amp;z=14&amp;ll=26.275636,-80.087265&amp;output=embed"></iframe></div>
					</div>
				</div>
			</div>
		</div>
		<?php } else { ?>
		<div class="content-top">
			<div class="listview_1_of_3 images_1_of_3">
				<h2 style="color:#555;">Upcoming Movies</h2>
				<?php $qry3=mysqli_query($con,"SELECT * FROM tbl_news LIMIT 5"); while($n=mysqli_fetch_array($qry3)){ ?>
				<div class="content-left">
					<div class="listimg listimg_1_of_2"><img src="admin/<?php echo $n['attachment']; ?>" alt="" /></div>
					<div class="text list_1_of_2">
						<div class="extra-wrap">
							<span style="text-color:#000" class="data"><strong><?php echo $n['name'];?></strong><br>
							<span style="text-color:#000" class="data"><strong>Cast :<?php echo $n['cast'];?></strong><br>
							<div class="data">Release Date :<?php echo $n['news_date'];?></div>
							<span class="text-top"><?php echo $n['description'];?></span>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<?php } ?>
			</div>
			<div class="listview_1_of_3 images_1_of_3">
				<h2 style="color:#555;">Movie Trailers</h2>
				<div class="middle-list">
					<?php $qry4=mysqli_query($con,"SELECT * FROM tbl_movie ORDER BY rand() LIMIT 6"); while($nm=mysqli_fetch_array($qry4)){ ?>
					<div class="listimg1">
						<a target="_blank" href="<?php echo $nm['video_url'];?>"><img src="<?php echo $nm['image'];?>" alt=""/></a>
						<a target="_blank" href="<?php echo $nm['video_url'];?>" class="link" style="text-decoration:none; font-size:14px;">&nbsp;<?php echo $nm['movie_name'];?></a>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php include('movie_sidebar.php');?>
		</div>
		<?php } ?>
</div>
<?php include('footer.php');?>
<?php include('searchbar.php');?>