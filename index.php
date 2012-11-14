<?php

define('INCLUDE_CHECK',true);

require 'connect.php';
require 'functions.php';
// Those two files can be included only if INCLUDE_CHECK is defined


session_name('tzLogin');
// Starting the session

session_set_cookie_params(2*7*24*60*60);
// Making the cookie live for 2 weeks

session_start();

if($_SESSION['id'] && !isset($_COOKIE['tzRemember']) && !$_SESSION['rememberMe'])
{
    // If you are logged in, but you don't have the tzRemember cookie (browser restart)
    // and you have not checked the rememberMe checkbox:

    $_SESSION = array();
    session_destroy();
    
    // Destroy the session
}


if(isset($_GET['logoff']))
{
    $_SESSION = array();
    session_destroy();
    
    header("Location: demo.php");
    exit;
}

if($_POST['submit']=='Login')
{
    // Checking whether the Login form has been submitted
    
    $err = array();
    // Will hold our errors
    
    
    if(!$_POST['username'] || !$_POST['password'])
        $err[] = 'All the fields must be filled in!';
    
    if(!count($err))
    {
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        $_POST['password'] = mysql_real_escape_string($_POST['password']);
        $_POST['rememberMe'] = (int)$_POST['rememberMe'];
        
        // Escaping all input data

        $row = mysql_fetch_assoc(mysql_query("SELECT id,usr FROM tz_members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'"));

        if($row['usr'])
        {
            // If everything is OK login
            
            $_SESSION['usr']=$row['usr'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['rememberMe'] = $_POST['rememberMe'];
            
            // Store some data in the session
            
            setcookie('tzRemember',$_POST['rememberMe']);
        }
        else $err[]='Wrong username and/or password!';
    }
    
    if($err)
    $_SESSION['msg']['login-err'] = implode('<br />',$err);
    // Save the error messages in the session

    header("Location: demo.php");
    exit;
}
else if($_POST['submit']=='Register')
{
    // If the Register form has been submitted
    
    $err = array();
    
    if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32)
    {
        $err[]='Your username must be between 3 and 32 characters!';
    }
    
    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
    {
        $err[]='Your username contains invalid characters!';
    }
    
    if(!checkEmail($_POST['email']))
    {
        $err[]='Your email is not valid!';
    }
    
    if(!count($err))
    {
        // If there are no errors
        
        $pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
        // Generate a random password
        
        $_POST['email'] = mysql_real_escape_string($_POST['email']);
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        // Escape the input data
        
        
        mysql_query("   INSERT INTO tz_members(usr,pass,email,regIP,dt)
                        VALUES(
                        
                            '".$_POST['username']."',
                            '".md5($pass)."',
                            '".$_POST['email']."',
                            '".$_SERVER['REMOTE_ADDR']."',
                            NOW()
                            
                        )");
        
        if(mysql_affected_rows($link)==1)
        {
            send_mail(  'demo-test@tutorialzine.com',
                        $_POST['email'],
                        'Registration System Demo - Your New Password',
                        'Your password is: '.$pass);

            $_SESSION['msg']['reg-success']='We sent you an email with your new password!';
        }
        else $err[]='This username is already taken!';
    }

    if(count($err))
    {
        $_SESSION['msg']['reg-err'] = implode('<br />',$err);
    }   
    
    header("Location: demo.php");
    exit;
}

$script = '';

if($_SESSION['msg'])
{
    // The script below shows the sliding panel on page load
    
    $script = '
    <script type="text/javascript">
    
        $(function(){
        
            $("div#panel").show();
            $("#toggle a").toggle();
        });
    
    </script>';
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="static/css/reset.css" type="text/css" media="screen">
    <link rel="stylesheet" href="static/css/style.css" type="text/css" media="screen">
    <link rel="stylesheet" href="static/css/grid.css" type="text/css" media="screen"> 
    <script src="static/js/jquery-1.6.2.min.js" type="text/javascript"></script>
    <script src="static/js/jquery.galleriffic.js" type="text/javascript"></script>
    <script src="static/js/jquery.opacityrollover.js" type="text/javascript"></script>      
	<!--[if lt IE 7]>
        <div style=' clear: both; text-align:center; position: relative;'>
            <a href="http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0"  alt="" /></a>
        </div>
	<![endif]-->
    <!--[if lt IE 9]>
   		<script type="text/javascript" src="static/js/html5.js"></script>
        <link rel="stylesheet" href="static/css/ie.css" type="text/css" media="screen">
	<![endif]-->

    <link rel="stylesheet" type="text/css" href="static/demo.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="static/login_panel/css/slide.css" media="screen" />
    
    
    <!-- PNG FIX for IE6 -->
    <!-- http://24ways.org/2007/supersleight-transparent-png-in-ie6 -->
    <!--[if lte IE 6]>
        <script type="text/javascript" src="static/login_panel/js/pngfix/supersleight-min.js"></script>
    <![endif]-->
    
    <script src="static/login_panel/js/slide.js" type="text/javascript"></script>
    
    <?php echo $script; ?>

</head>
<body id="page1">

<div id="toppan|el">
    <div id="panel">
        <div class="content clearfix">
            <div class="left">

            <!--<h1>The Sliding jQuery Panel</h1>
                <h2>A register/login solution</h2>      
                <p class="grey">You are free to use this login and registration system in you sites!</p>
                <h2>A Big Thanks</h2>
                <p class="grey">This tutorial was built on top of <a href="http://web-kreation.com/index.php/tutorials/nice-clean-sliding-login-panel-built-with-jquery" title="Go to site">Web-Kreation</a>'s amazing sliding panel.</p>
            -->
            </div>
            
            
            <?php
            
            if(!$_SESSION['id']):
            
            ?>
            
            <div class="left">
                <!-- Login Form -->
                <form class="clearfix" action="" method="post">
                    <h1>Iniciar Sección</h1>
                    
                    <?php
                        
                        if($_SESSION['msg']['login-err'])
                        {
                            echo '<div class="err">'.$_SESSION['msg']['login-err'].'</div>';
                            unset($_SESSION['msg']['login-err']);
                        }
                    ?>
                    
                    <label class="grey" for="username">Usuario:</label>
                    <input class="field" type="text" name="username" id="username" value="" size="23" />
                    <label class="grey" for="password">Clave:</label>
                    <input class="field" type="password" name="password" id="password" size="23" />
                    <label><input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" /> &nbsp;Recordarme</label>
                    <div class="clear"></div>
                    <input type="submit" name="submit" value="Entrar" class="bt_login" />
                </form>
            </div>
            <div class="left right">            
                <!-- Register Form -->
                <form action="" method="post">
                    <h1>¿No eres miembro todavía? ¡Regístrate!</h1>     
                    
                    <?php
                        
                        if($_SESSION['msg']['reg-err'])
                        {
                            echo '<div class="err">'.$_SESSION['msg']['reg-err'].'</div>';
                            unset($_SESSION['msg']['reg-err']);
                        }
                        
                        if($_SESSION['msg']['reg-success'])
                        {
                            echo '<div class="success">'.$_SESSION['msg']['reg-success'].'</div>';
                            unset($_SESSION['msg']['reg-success']);
                        }
                    ?>
                            
                <!--<label class="grey" for="username">Username:</label>
                    <input class="field" type="text" name="username" id="username" value="" size="23" />
                -->
                <table>
                    <tr>
                        <td>
                            <label class="grey" for="email">Email:</label>
                            <input class="field" type="text" name="email" id="email" size="23" />

                            <label class="grey" for="usuario">Usuario:</label>
                            <input class="field" type="text" name="usuario" id="usuario" value="" size="23" />

                            <label class="grey" for="password">Clave:</label>
                            <input class="field" type="password" name="password" id="password" value="" size="23" />

                            <label class="grey" for="razon">Nombre y Apellido:</label>
                            <input class="field" type="text" name="razon" id="razon" value="" size="23" />

                        </td>
                        <td>    

                            <label class="grey" for="telefono">Teléfono:</label>
                            <input class="field" type="text" name="telefono" id="telefono" value="" size="23" />                       

                            
                            <label class="grey" for="razon">Sexo:</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input class="field" type="radio" name="sexo" id="sexo" value="Masculino" />Masculino
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input class="field" type="radio" name="sexo" id="sexo" value="Femenino" />Femenino

                            <label class="grey" for="pais">País:</label>
                            <input class="field" type="text" name="pais" id="pais" value="" size="23" />

                            <input type="submit" name="submit" value="Regístrarse" class="bt_register" />
                        </td>
                        <td>
                            
                        </td>
                    </tr>
                </table>
                    
                </form>
            </div>
            
            <?php
            
            else:
            
            ?>
            
            <div class="left">
            <h1>Members panel</h1>
            
            <p>You can put member-only data here</p>
            <a href="registered.php">View a special member page</a>
            <p>- or -</p>
            <a href="?logoff">Log off</a>
            
            
            </div>
            
            <div class="left right">
            </div>
            
            <?php
            endif;
            ?>
        </div>
    </div> <!-- /login -->  

    <!-- The tab on top --> 
    <div class="tab">
        <ul class="login">
            <li class="left">&nbsp;</li>
            <li>Bienvenido <?php echo $_SESSION['usr'] ? $_SESSION['usr'] : '';?></li>
            <li class="sep">|</li>
            <li id="toggle">
                <a id="open" class="open" href="#"><?php echo $_SESSION['id']?'Open Panel':'Entrar| Regístrate';?></a>
                <a id="close" style="display: none;" class="close" href="#">Cerrar Panel</a>         
            </li>
            <li class="right">&nbsp;</li>
        </ul> 
    </div> <!-- / top -->
    
</div> <!--panel -->

	<!--==============================header=================================-->
    <header>
    	<div class="row-1">
        	<div class="main">
            	<div class="container_12">
                	<div class="grid_12">
                    	<nav>
                            <ul class="menu">
                                <li><a class="active" href="index.html">Inicio</a></li>
                                <li><a href="services.html">Quienes Somos</a></li>
                                <li><a href="catalogue.html">Productos</a></li>
                                <li><a href="pricing.html">Promociones</a></li>
                                <li><a href="contacts.html">Contactanos</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="row-2">
        	<div class="main">
            	<div class="container_12">
                	<div class="grid_9">
                    	<h1>
                            <a class="logo" href="index.html">PUNTO<strong>-ALTERSANAL</strong></a>
                            <span> Magdaleno</span>
                        </h1>
                    </div>
                    <div class="grid_3">
                    	<form id="search-form" method="post" enctype="multipart/form-data">
                            <fieldset>	
                                <div class="search-field">
                                    <input name="search" type="text" onfocus="if(this.value=='busqueda') this.value='';" onblur="if(this.value=='') this.value='busqueda';"/>
                                    <a class="search-button" href="#" onClick="document.getElementById('search-form').submit()"><span>search</span></a>	
                                </div>						
                            </fieldset>
                        </form>
                     </div>
                     <div class="clear"></div>
                </div>
            </div>
        </div>    	
    </header><div class="ic">More Website Templates  @ TemplateMonster.com - August22nd 2011!</div>
    
<!-- content -->
    <section id="content">
        <div class="bg-top">
        	<div class="bg-top-2">
                <div class="bg">
                    <div class="bg-top-shadow">
                        <div class="main">
                            <div class="gallery p3">
                            	<div class="wrapper indent-bot">
                                    <div id="gallery" class="content">
                                       <div class="wrapper">
                                           <div class="slideshow-container">
                                                <div id="slideshow" class="slideshow"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="thumbs" class="navigation">
                                        <ul class="thumbs noscript">
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img1.jpg" title=""> <img src="static/images/thumb-1.jpg" alt="" /><span></span> </a>
                                            </li> 
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img2.jpg" title=""> <img src="static/images/thumb-2.jpg" alt="" /> <span></span></a>
                                            </li> 
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img3.jpg" title=""> <img src="static/images/thumb-3.jpg" alt="" /> <span></span></a>
                                            </li> 
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img4.jpg" title=""> <img src="static/images/thumb-4.jpg" alt="" /> <span></span></a>
                                            </li> 
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img5.jpg" title=""> <img src="static/images/thumb-5.jpg" alt="" /> <span></span></a>
                                            </li> 
                                            <li>
                                                <a class="thumb" href="static/images/gallery-img6.jpg" title=""> <img src="static/images/thumb-6.jpg" alt="" /> <span></span></a>
                                            </li>           
                                        </ul>
                                    </div>
                                </div>
                                <div class="inner">
                                    <div class="wrapper">
                                        <span class="title img-indent3">HELLO!</span>
                                        <div class="extra-wrap indent-top2">
                                        	<strong>Interior Design</strong> is one of <a target="_blank" href="http://blog.templatemonster.com/free-website-templates/ ">free website templates</a> created by TemplateMonster.com team. This website template is opti mized for 1024X768 screen resolution. It is also XHTML &amp; CSS valid. It has several pages: <a class="color-3" href="index.html">About</a>, <a class="color-3" href="services.html">Services</a>, <a class="color-3" href="catalogue.html">Catalogue</a>, <a class="color-3" href="pricing.html">Pricing</a>, <a class="color-3" href="contacts.html">Contact Us</a> (note that contact us form – doesn’t work).
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="container_12">
                            	<div class="wrapper">
                                	<article class="grid_12">
                                    	<h3 class="color-1">Our Services List</h3>
                                        <div class="wrapper">
                                        	<article class="grid_6 alpha">
                                                <figure class="img-indent frame"><img src="static/images/page1-img1.jpg" alt="" /></figure>
                                                <div class="extra-wrap">
                                                    <div class="indent-top">
                                                        <ul class="list-1">
                                                             <li><a href="#">Interior Decorating Services</a></li>
                                                             <li class="last"><a href="#">Complete Color <br>Analysis</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="clear"></div>
                                            </article>
                                            <article class="grid_6 omega">
                                                <figure class="img-indent frame"><img src="static/images/page1-img2.jpg" alt="" /></figure>
                                                <div class="extra-wrap">
                                                    <div class="indent-top">
                                                        <ul class="list-1">
                                                             <li><a href="#">Design Services <br>for Home Construction</a></li>
                                                             <li class="last"><a href="#">Interior Design Remodeling</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="clear"></div>
                                            </article>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>	
        </div>
        <div class="bg-bot">
        	<div class="main">
            	<div class="container_12">
                	<div class="wrapper">
                    	<article class="grid_4">
                        	<h3 class="prev-indent-bot">About Us</h3>
                            <p class="prev-indent-bot">This <a target="_blank" href="http://blog.templatemonster.com/2011/08/22/free-website-template-clean-style-interior/ ">Interior Design Template</a> goes with two pack ages: with PSD source files and without them.</p>
                            PSD source files are available for free for the registered members of Templates.com. The basic package (without PSD source) is available for anyone without registration.
                        </article>
                        <article class="grid_4">
                        	<h3 class="prev-indent-bot">Testimonials</h3>
                            <div class="quote">
                            	<p class="prev-indent-bot">At vero eos et accusamus et iusto odio tium voluptatum deleniti atque corrupti quos<br> dolores et quas molestias excepturi sint occaecati cupiditate.</p>
                                <h5>James Reese</h5>
                                Managing Director
                            </div>
                        </article>
                        <article class="grid_4">
                        	<h3 class="prev-indent-bot">What’s New?</h3>
                            <time class="tdate-1" datetime="2011-08-15"><a class="link" href="#">15.08.2011</a></time>
                            <p class="prev-indent-bot">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.</p>
                            <time class="tdate-1" datetime="2011-08-11"><a class="link" href="#">11.08.2011</a></time>
                            Totam rem aperiam, eaque ipsa quae ab illo inven tore veritatis et quasi architecto.
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
	<!--==============================footer=================================-->
    <footer>
        <div class="main">
        	<div class="container_12">
            	<div class="wrapper">
                	<div class="grid_4">
                    	<div>Desarrollo Web  <a class="link color-3" href="#">Politicas de Privacidad</a></div>
                        <div><a rel="nofollow" target="_blank" href="#">Página Web</a> por J.E.D | <a rel="nofollow" target="_blank" href="#">WebSite-Magdaleno</a></div>
                        <!-- {%FOOTER_LINK} -->
                    </div>
                    <div class="grid_4">
                    	<span class="phone-numb"><span>+58</span> 0244-0000000</span>
                    </div>
                    <div class="grid_4">
                    	<ul class="list-services">
                        	<li><a href="#"></a></li>
                            <li><a class="item-2" href="#"></a></li>
                            <li><a class="item-3" href="#"></a></li>
                            <li><a class="item-4" href="#"></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script type="text/javascript">
			$(window).load(function() {
			// We only want these styles applied when javascript is enabled
			$('div.navigation').css({'width' : '320px', 'float' : 'right'});
			$('div.content').css('display', 'block');
	
			// Initially set opacity on thumbs and add
			// additional styling for hover effect on thumbs
			var onMouseOutOpacity = 0.5;
			$('#thumbs ul.thumbs li span').opacityrollover({
				mouseOutOpacity:   onMouseOutOpacity,
				mouseOverOpacity:  0.0,
				fadeSpeed:         'fast',
				exemptionSelector: '.selected'
			});
			
			// Initialize Advanced Galleriffic Gallery
			var gallery = $('#thumbs').galleriffic({
				delay:                     7000,
				numThumbs:                 12,
				preloadAhead:              6,
				enableTopPager:            false,
				enableBottomPager:         false,
				imageContainerSel:         '#slideshow',
				controlsContainerSel:      '',
				captionContainerSel:       '',
				loadingContainerSel:       '',
				renderSSControls:          true,
				renderNavControls:         true,
				playLinkText:              'Play Slideshow',
				pauseLinkText:             'Pause Slideshow',
				prevLinkText:              'Prev',
				nextLinkText:              'Next',
				nextPageLinkText:          'Next',
				prevPageLinkText:          'Prev',
				enableHistory:             true,
				autoStart:                 7000,
				syncTransitions:           true,
				defaultTransitionDuration: 900,
				onSlideChange:             function(prevIndex, nextIndex) {
					// 'this' refers to the gallery, which is an extension of $('#thumbs')
					this.find('ul.thumbs li span')
						.css({opacity:0.5})
				},
				onPageTransitionOut:       function(callback) {
					this.find('ul.thumbs li span').css({display:'block'});
				},
				onPageTransitionIn:        function() {
					this.find('ul.thumbs li span').css({display:'none'});
				}
			});
		});
	</script>
</body>
</html>
