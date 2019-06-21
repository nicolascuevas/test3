<?php
$cdn = "https://addshop-storage.s3.amazonaws.com/";
$domain = explode(':', $_SERVER['HTTP_HOST'])[0];
$uri = explode('/', $_SERVER['REQUEST_URI']);
$lang = json_decode("TRANSLATION");
// LOGIN
function tryAccess($data) {
    GLOBAL $domain;
    $connection = @file_get_contents("http://back-end.ddns.net:3030/web-access?domain={$domain}&".$data, false);
    return $connection;
}
if ($uri[1]=='sso') {
    $sso = $uri[2];
    setcookie('token', '', 0, '/');
    $app = json_decode(tryAccess("sso={$sso}"));
    setcookie('token', $app->token, 0, '/');
    if($app->token) die("<script>location.replace('/')</script>");
}
//REGISTRO DE USUARIO (LOGEA EN CUENTA INTERNA QUE SOLO TIENE PANTALLA DE REGISTRO)
if ($uri[1]=='signup') {
    setcookie('token', '', 0, '/');
    setcookie('socialSignup', '', 0, '/');
    $app = json_decode(tryAccess("begin-signup=1"));
    setcookie('token', $app->token, 0, '/');
    if($app->token) die("<script>location.replace('/')</script>");
    else { $socialNonexistent=true; }
}
//LOGIN SOCIAL: FACEBOOK
if ($uri[1]=='login-facebook') {
    $sso = $uri[2];
    setcookie('token', '', 0, '/');
    $app = json_decode(tryAccess("login-facebook={$sso}"));
    setcookie('token', $app->token, 0, '/');
    if($app->socialSignup) setcookie('socialSignup', json_encode($app->socialSignup), 0, '/');
    if($app->token) die("<script>location.replace('/')</script>");
    else { $socialNonexistent=true; }
}
//LOGIN SOCIAL: GOOGLE
if ($uri[1]=='login-google') {
    $sso = $uri[2];
    setcookie('token', '', 0, '/');
    $app = json_decode(tryAccess("login-google={$sso}"));
    setcookie('token', $app->token, 0, '/');
    if($app->socialSignup) setcookie('socialSignup', json_encode($app->socialSignup), 0, '/');
    if($app->token) die("<script>location.replace('/')</script>");
    else { $socialNonexistent=true; }
}
// print_r($_POST);
if ($_POST['email'] && !strstr($_POST['email'], 'etpsuperadmin')) {
    setcookie('token', '');
    $app = json_decode(tryAccess("email={$_POST['email']}&password={$_POST['password']}"));
    setcookie('token', $app->token);
    // se hizo login? limpiar POST
    if ($app->token) {
        $_POST = array();
    }
    // se esta haciendo logout? DIE
    if (!$_POST['email']) {
        die("<script>location.replace('/login')</script>"); // causa de porque el correo no persiste al equivocarse en login - pero es necesario para cerrar sesion
    }
}
elseif ($_COOKIE['token']) {
    $app = json_decode(tryAccess("token={$_COOKIE['token']}"));
}

else {
    $app = json_decode(@file_get_contents("http://back-end.ddns.net:3030:3030/web-access?domain={$domain}", false));
}

//DESACTIVAR REGISTRO
if ($app->company->signupDisabled && ($uri[1]=="signup" || $uri[2]=="signup" || strpos($app->account->meta->role, "signup"))) {
    die("<meta name=viewport content=width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0>
         <center><h1 style='font-family:verdana;font-size: 2em;margin-top: 100px;'>Registro temporalmente deshabilitado");
}
//RESTRINGIR LOGIN A ROLES DETERMINADOS
if ($app->account && ($app->company->loginRestricted &&
    count(array_intersect(explode(',',$app->account->meta->role), explode(',', $app->company->loginRestricted)))<1) || $app->account->banned) {
    die("<meta name=viewport content=width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0>
         <center><h1 style='font-family:verdana;font-size: 2em;margin-top: 100px;'>Acceso temporalmente restringido");
}

//TRANSLATIONS
if ($app->lang) {
    $codeTranslations = Array();
    $langugae = Array();
    foreach ($lang as $o) {
        $codeTranslations[$o->string] = $o;
    }
    foreach ($app->lang as $o) {
        if ($codeTranslations[$o->string]) {
            $codeTranslations[$o->string] = $o;
        }
    }
    $app->lang = $codeTranslations;
}
//BOOT UP
if (!$app->company && !$app->error) {
    ?> <!-- no-connection --> <?
}
elseif (!$app->company->name) {
    ?> <!-- no-company --> <?
}
elseif (!$app->company->options->active) {
    ?> <!-- inactive-company --> <?
}
elseif (!$app->account->name) {
    ?> <!-- login --> <?
}
else { ?>
    <!doctype html>
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="HandheldFriendly" content="true"/>
    <link rel="apple-touch-icon" sizes="57x57" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://addshop-static.s3.amazonaws.com/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="https://addshop-static.s3.amazonaws.com/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://addshop-static.s3.amazonaws.com/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="https://addshop-static.s3.amazonaws.com/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://addshop-static.s3.amazonaws.com/icons/favicon-16x16.png">
    <link rel="manifest" href="https://addshop-static.s3.amazonaws.com/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <html ng-app="elder"><meta charset="utf-8"><base href=/><body>
    <link rel="stylesheet" href="https://addshop-apps.s3-sa-east-1.amazonaws.com/QO1gzNzMDM3IzNxcTN3gDM0UjMyMzM1QTM0kjN1QTNzUjMxMDOwgTO3ITOzcTM0MzMykjN.css">
    <link rel="stylesheet" href="https://addshop-apps.s3-sa-east-1.amazonaws.com/QOyQDO5kzMyIDNxITO0UTO1QTMzEjN5gDOzgTOycTNycTOzkzMwkTMwAzN3gDO5EzNxETN.css">
    <script>
        __e__=<? echo json_encode($app); ?>;
        localStorage.setItem('$LoopBack$accessTokenId', '<? echo $app->token; ?>');
        localStorage.setItem('$LoopBack$currentUserId', '<? echo $app->account->id; ?>');
    </script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/10jquery.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/11jquery-color.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/20angular.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/30moment.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/40es.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/50angular-resource.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/60angular-route.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/61angular-sanitize.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/70angular-moment.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/80tether.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/90bootstrap.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/100bootstrap-extension.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/101bootstrap-notify.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/102angular-bootstrap-toggle.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/110sidebar-nav.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/120jquery.waypoints.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/130jquery.counterup.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/140raphael-min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/150morris.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/151chart.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/152peity.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/160jquery.sparkline.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/170jquery.charts-sparkline.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/180jquery.toast.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/190tablesaw.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/200richAutocomplete.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/210waves.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/220socket.io.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/230lodash.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/240switchery.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/250dropzone.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/260Sortable.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/270popup.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/290selectize.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/291selectize-dropdown-plugin.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/300jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/302quagga.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/310cropper.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/320cleave.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/330hammer.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/340angular.hammer.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/350dragscroll.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/360bootstrap-lightbox.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/370masonry.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/js/380apexcharts.min.js"></script>
    <script src="https://addshop-apps.s3-sa-east-1.amazonaws.com/QOxYzN5MzN3QDO1QTMwQTN4YTNxQDOyYjN2UDNyEzMzIDOwMzN5kTO4MTNxUzMxkTM3IzN.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-Bx4pytHkyTDy3aJKjGkGoHPt3tvv6zlwwjc3iqN7ktaiEMLDPqLSZYts2OjKcBx1" crossorigin="anonymous">

<div id="wrapper" class="elder-wrapper" ng-class="{'with-sidebar': sidebar}">
    <div class="wrapper-nav-top position-absolute" ng-include="'core/html/views/header.html'"></div>
    <div class="lowerside" ng-if="Account.username !== 'signup'" ng-include="'core/html/views/lowerside.html'" ng-controller="lowerside"></div>
    <div class="main-container">
        <div ng-if="viewAsType=='store'" class="wrap-sidebar" ng-include="'core/html/views/sidebar.html'"></div>
        <div ng-if="viewAsType=='admin'" class="wrap-admin-sidebar" ng-include="'core/html/views/admin-sidebar.html'"></div>
        <div class="wrap-container" ng-class="{'w-100': viewAsType!='store'&&viewAsType!='admin', 'dashboard-container': viewAsType=='store'||viewAsType=='admin'}">
            <div class="container-fluid elder-view-container" ng-show="!showError" ng-class="{'with-sidebar': viewAsType=='store'||viewAsType=='admin'}">
                <div ng-view class="h-100"></div>
            </div>
        </div>
    </div>
</div>
<? }
?>