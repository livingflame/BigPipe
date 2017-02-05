<?php 
// Autoload classes
function __autoload($class_name) {
    if (file_exists($class_name . '.php')) { 
        require_once $class_name . '.php'; 
        return true; 
    }
    return false; 
}
$view = new BigPipe();

$view->registerScript("jquery","static/js/jquery-3.1.0.js");
$view->registerScript("delayJSBlue","static/js/delayJSBlue.php",array('jquery'));
$view->registerScript("delayJSGreen","static/js/delayJSGreen.php");
$view->registerScript("delayJSRed","static/js/delayJSRed.php");

$view->registerStyle('red-style',"static/css/red.php");
$view->registerStyle('blue-style',"static/css/blue.php");
$view->registerStyle('green-style',"static/css/green.php");

$view->addPagelet("red",'Just a simple example HTML test',"red-style","delayJSRed",array(
    'priority' => 2,
    'enabled' => FALSE
));
$view->addPagelet("blue",'Another sample HTML',array("red-style","blue-style"),array("delayJSBlue","delayJSGreen"),array(
    'priority' => 3
));
$view->addPagelet("green",'The third sample HTML',"green-style","delayJSGreen");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
        <title>BigPipe</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="robots" content="index,follow" />				
        <meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate" />
        <link type="text/css" href="static/css/test.css" rel="stylesheet" />
        <script src="static/js/BigPipe.js"></script>
	</head>
    <body>
        <h1> BigPipe example</h1>
        <div id="container">
            <?php echo $view->getPagelet('red');?>
            <?php echo $view->getPagelet('blue');?>
            <?php echo $view->getPagelet('green');?>
        </div>  
        <?php echo $view->render(); ?>
    </body>
</html>