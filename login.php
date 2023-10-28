<?php
global $admin;
require_once 'system.php';
if($admin->islogin()) header("location:index.php?basarili");

if(isset($_POST['submit'])){
    if(hash_equals($_SESSION['token'],$_POST['token'])){
        $username=strip_tags(trim($_POST['username']));
        $password=strip_tags(trim($_POST['password']));
        $login=$admin->login($username,$password);
       if($login)
            header("location:login.php?s=basarili");
    }
            header("location:login.php?s=hata");
}



?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Apple devices fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- Apple devices fullscreen -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

    <title><?=$_ENV['title']?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Bootstrap responsive -->
    <link rel="stylesheet" href="assets/css/bootstrap-responsive.min.css">
    <!-- icheck -->
    <link rel="stylesheet" href="assets/css/plugins/icheck/all.css">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Color CSS -->
    <link rel="stylesheet" href="assets/css/themes.css">
    <!-- Notify -->
    <link rel="stylesheet" href="assets/css/plugins/gritter/jquery.gritter.css">

    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>

    <!-- Nice Scroll -->
    <script src="assets/js/plugins/nicescroll/jquery.nicescroll.min.js"></script>
    <!-- Validation -->
    <script src="assets/js/plugins/validation/jquery.validate.min.js"></script>
    <script src="assets/js/plugins/validation/additional-methods.min.js"></script>
    <!-- icheck -->
    <script src="assets/js/plugins/icheck/jquery.icheck.min.js"></script>,
    <!-- Notify -->
    <script src="assets/js/plugins/gritter/jquery.gritter.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/eakroko.js"></script>

    <!--[if lte IE 9]>
    <script src="assets/js/plugins/placeholder/jquery.placeholder.min.js"></script>
    <script>
        $(document).ready(function() {
            $('input, textarea').placeholder();
        });
    </script>
    <![endif]-->


    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" />
    <!-- Apple devices Homescreen icon -->
    <link rel="apple-touch-icon-precomposed" href="assets/img/apple-touch-icon-precomposed.png" />

</head>

<body class='login'>
<div class="wrapper">
    <h1><a href="#"><img src="assets/img/logo-big.png" alt="" class='retina-ready' width="59" height="49">Administrator</a></h1>
    <div class="login-body">
        <h2>SIGN IN</h2>
        <form  method='post' action="">
            <div class="control-group">
                <div class="email controls">
                    <input type="text" name='username' placeholder="Kullanıcı Adı"  class='input-block-level' data-rule-required="true" required>
                </div>
            </div>
            <div class="control-group">
                <div class="pw controls">
                    <input type="password" name="password" placeholder="Password" class='input-block-level' data-rule-required="true" required>
                </div>
            </div>
            <div class="submit">
                <div class="remember">
                    <input type="checkbox" name="remember" value="remember" class='icheck-me' data-skin="square" data-color="blue" id="remember"> <label for="remember">Beni Hatırla</label>
                </div>
                <input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
                <input type="submit" name="submit" value="Giriş Yap" class='btn btn-primary'>
            </div>
        </form>
        <div class="forget">
            <a target="_blank" href="https://about.me/mucahidbaris"><span>2023 Mucahid Baris </span></a>
        </div>
    </div>
</div>
</body>

</html>
<?php
if(isset($_GET['s'])){
    switch ($_GET['s']){
        case 'basarili':
            bildirim('İşlem Başarılı','Başarıyla tamamlandı.',);
            break;
        case 'hata':
            bildirim('HATA','Hay Aksi :( Birşeyler ters gitti.',);
            break;
    }
}
?>
