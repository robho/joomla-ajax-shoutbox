<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

$shouts 	= intval($params->get( 'shouts', 10 ));
$refresh 	= intval($params->get( 'refresh', 4 ));
$post_guest = $params->get( 'post_guest' );
$tag	 	= $params->get( 'tag' );
$soundopt	= $params->get( 'sound' );
$loggedin 	= modShoutboxHelper::getType();
$user 		=& JFactory::getUser();
$jal_lastID    = isset($_GET['jal_lastID']) ? $_GET['jal_lastID'] : "";
$jalGetChat    = isset($_GET['jalGetChat']) ? $_GET['jalGetChat'] : "";
$jalSendChat   = isset($_GET['jalSendChat']) ? $_GET['jalSendChat'] : "";

//Make the urls to get the shouts
$uri =& JURI::getInstance(modShoutboxHelper::curPageURL());
//getshouts
$uri->delVar('mode');
$param = $uri->getQuery(true);
$query = array_merge($param, array('mode' => 'getshouts'));
$uri->setQuery($query);
$getshouts = $uri->toString();
//addshouts
$uri->delVar('mode');
$param = $uri->getQuery(true);
$query = array_merge($param, array('mode' => 'addshout'));
$uri->setQuery($query);
$addshout = $uri->toString();
$uri->delVar('mode');
//delshouts
$param = $uri->getQuery(true);
$query = array_merge($param, array('mode' => 'delshout'));
$uri->setQuery($query);
$delshout = $uri->toString();
$uri->delVar('mode');


$name = JRequest::getVar( 'n',			'',			'post' ); 
$url  = JRequest::getVar( 'u',			'',			'post' );
$text = JRequest::getVar( 'c',			'',			'post' );
$homepage = JRequest::getVar( 'h',			'',			'post' );
$shoutid = JRequest::getInt( 'shoutid',			'',			'get' ); 

$maydelete = $user->authorize('com_content', 'edit', 'content', 'all');

$mode = JRequest::getCmd('mode');
//$ajaxcall = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") : false;

switch ($mode) {
case 'addshout':
	if(empty($homepage)) {
		modShoutboxHelper::addShout($name, $url, $text, $tag, $params);
	}
	break;
case 'delshout':
	if($maydelete) {
		modShoutboxHelper::delShout($shoutid);
	}
	break;
}

//getList

if($mode == 'getshouts') {
	header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
	header( "Last-Modified: ".gmdate( "D, d M Y H:i:s" )."GMT" ); 
	header( "Cache-Control: no-cache, must-revalidate" ); 
	header( "Pragma: no-cache" );
	header( "Content-Type: text/html; charset=utf-8" );
	$loop = '';
	$ajaxshouts = modShoutBoxHelper::getAjaxShouts($shouts);
	foreach ( $ajaxshouts as $shout ) {
		$loop = $shout->id."###".stripslashes($shout->name)."###".stripslashes($shout->text)."###0 minutes ago###".stripslashes($shout->url)."###" . $loop; 
		// ### is being used to separate the fields in the output
	}
	if (empty($loop)) { 
	 	$loop = "0"; 
	}
	ob_clean();
	echo $loop;
	exit;
}

$list = modShoutboxHelper::getShouts($shouts);



if (isset($_POST['shout_no_js'])) {
	JRequest::checkToken() or jexit( 'Invalid Token' );
	if ($_POST['shoutboxname'] != '' && $_POST['chatbarText'] != '' && empty($homepage)) {
		$name = $_POST['shoutboxname'];
		($tag) ? $name = '['.$name.']' : $name;
		modShoutboxHelper::jal_addData($name, $_POST['shoutboxurl'], $_POST['chatbarText'], $params);
		header('location: '.$_SERVER['HTTP_REFERER']);
	} else {
		echo "You must have a name and a comment";
	}
}


JHTML::_('behavior.mootools');
$module_base     = JURI::base() . 'modules/mod_shoutbox/';
$document =& JFactory::getDocument();
$document->addScript($module_base . 'js/fatAjax.js');
if(JPluginHelper::isEnabled('system', 'yvsmiley')) {
	if($params->get('post_guest') || $loggedin != 'guest') {
		$document->addScript($module_base . 'js/sbsmile.js');
	}
}
$document->addStyleSheet($module_base . 'css/mod_shoutbox.css');

require(JModuleHelper::getLayoutPath('mod_shoutbox'));
?>