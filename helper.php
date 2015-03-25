<?php
defined('_JEXEC') or die('Restricted access');

class modShoutboxHelper {
	
	function addShout($name, $url, $text, $tag, &$params)
	{		
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		header( "Last-Modified: ".gmdate( "D, d M Y H:i:s" )."GMT" ); 
		header( "Cache-Control: no-cache, must-revalidate" ); 
		header( "Pragma: no-cache" );
		header( "Content-Type: text/html; charset=utf-8" );
		$user		=& JFactory::getUser();
		$userid = $user->get('id');
		if ($name != '' && $text != '' ) {
			($tag && $userid == 0) ? $name = '['.$name.']' : $name;
			modShoutboxHelper::jal_addData($name, $url, $text, $params);
		}
		exit();
	}
	
	function delShout($id)
	{
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		header( "Last-Modified: ".gmdate( "D, d M Y H:i:s" )."GMT" ); 
		header( "Cache-Control: no-cache, must-revalidate" ); 
		header( "Pragma: no-cache" );
		header( "Content-Type: text/html; charset=utf-8" );
		
		$db = & JFactory::getDBO();
		$query = 'DELETE FROM #__shoutbox WHERE id='. (int) $id;
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseError( 500, $db->stderr() );
			return false;
		}
	}
	
	function jal_addData($name,$url,$text, &$params) {
		$db = & JFactory::getDBO();
		$user 		=& JFactory::getUser();
		
		if($user->get('guest') && !$params->get( 'post_guest' )) {
			return;
		}
		
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$name = strip_tags($name);
		$name = substr(trim($name), 0,12);
		
		$text = strip_tags($text);
		$text = substr($text,0,500);
		
		$text = htmlspecialchars(trim($text));
				
		$text = preg_replace('/#{3,}/', '##', $text); 
		
		$name = (empty($name)) ? "Anonymous" : htmlspecialchars($name);
		
		$url = ($url == "http://") ? "" : htmlspecialchars($url);
		if(strlen($text) > 0) {
			$query = 'INSERT INTO #__shoutbox' . ' (time,name,url,text,ip) VALUES ( '. time() .', '.$db->quote( $name ).', '.$db->quote( $url ).', '.$db->quote( $text ).', '.$db->quote( $ip ).' )';
			$db->setQuery($query);
			if (!$db->query()) {
				JError::raiseError( 500, $db->stderr() );
				return false;
			}
		}
		return false;
	} 
	
	function deleteOld() {
		
	}
	
	function getShouts($shouts) {
		$mainframe = JFactory::getApplication();
		
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__shoutbox ORDER BY id DESC';
		$db->setQuery( $query , 0 , $shouts);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum()) {
			modShoutboxHelper::install();
		}
		$i		= 0;
		$shouts	= array();
		foreach ( $rows as $row ) {
			$shouts[$i]->name = $row->name;
			$shouts[$i]->text = $row->text;
			$shouts[$i]->text = preg_replace( "`(http|ftp)+(s)?:(//)((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"\\0\">&laquo;link&raquo;</a>", $shouts[$i]->text);
			$shouts[$i]->text = preg_replace("`([-_a-z0-9]+(\.[-_a-z0-9]+)*@[-a-z0-9]+(\.[-a-z0-9]+)*\.[a-z]{2,6})`i","<a href=\"mailto:\\1\">&laquo;email&raquo;</a>", $shouts[$i]->text); 
			$shouts[$i]->url = $row->url;
			$shouts[$i]->url = (empty($shouts[$i]->url) && $shouts[$i]->url = "http://") ? $shouts[$i]->name : '<a href="'.$shouts[$i]->url.'">'.$shouts[$i]->name.'</a>';
			$mainframe->triggerEvent('onBBCode_RenderText', array (& $shouts[$i]->text) ); 
			$mainframe->triggerEvent('onSmiley_RenderText', array (& $shouts[$i]->text) );
			$shouts[$i]->id = $row->id;
			$shouts[$i]->ip = $row->ip;
			$shouts[$i]->time = $row->time;
			$i++;
		}
		return $shouts;
	}
	
	function getAjaxShouts($shouts) {
		$mainframe = JFactory::getApplication();
		
		$db =& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$maydelete = $user->authorize('com_content', 'edit', 'content', 'all');
		
		$jal_lastID = JRequest::getInt( 'jal_lastID',			0		 );

		$query = 'SELECT * FROM #__shoutbox WHERE id > '. (int) $jal_lastID.' ORDER BY id DESC';
		$db->setQuery( $query , 0 , $shouts);
		$rows = $db->loadObjectList();
		$i		= 0;
		$shouts	= array();
		foreach ( $rows as $row ) {
			$shouts[$i]->id = $row->id;
			$shouts[$i]->name = $row->name;
			$shouts[$i]->text = $row->text;
			$shouts[$i]->text = preg_replace( "`(http|ftp)+(s)?:(//)((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"\\0\">&laquo;link&raquo;</a>", $shouts[$i]->text);
			$shouts[$i]->text = preg_replace("`([-_a-z0-9]+(\.[-_a-z0-9]+)*@[-a-z0-9]+(\.[-a-z0-9]+)*\.[a-z]{2,6})`i","<a href=\"mailto:\\1\">&laquo;email&raquo;</a>", $shouts[$i]->text); 
			$mainframe->triggerEvent('onBBCode_RenderText', array (& $shouts[$i]->text) ); 
			$mainframe->triggerEvent('onSmiley_RenderText', array (& $shouts[$i]->text) );
			if($maydelete)
			$shouts[$i]->text =  $shouts[$i]->text.' <a href="'.JURI::current().'?mode=delshout&amp;shoutid='.$shouts[$i]->id.'" title="Delete">x</a>';
			$shouts[$i]->url = $row->url;
			$shouts[$i]->id = $row->id;
			$shouts[$i]->time = $row->time;
			$i++;
		}
		return $shouts;
	}
	
	function install() {
		$db		=& JFactory::getDBO();
		$query = "CREATE TABLE IF NOT EXISTS #__shoutbox (
	  		`id` int(11) NOT NULL auto_increment,
	  		`time` int(11) DEFAULT '0' NOT NULL,
	  		`name` varchar(25) NOT NULL,
	  		`text` text NOT NULL,
	  		`url` varchar(225) NOT NULL,
	  		`ip` varchar(255) NOT NULL,
	  		PRIMARY KEY  (`id`)
		) ; ";
		$db->setQuery($query);
		$db->query();
		$query = "INSERT INTO `#__shoutbox` (`time`, `name`, `text`) VALUES
			('".time()."', 'Risp', 'Welcome to the shoutbox');";

		$db->setQuery($query);
		$db->query();
	}
	
	function getType()
	{
		$user = & JFactory::getUser();
	    return (!$user->get('guest')) ? 'user' : 'guest';
	}
	
	function time_since($original) {
	    // array of time period chunks
	    $chunks = array(
	        array(60 * 60 * 24 * 365 , JText::_( 'YEAR'), JText::_( 'YEARS')),
	        array(60 * 60 * 24 * 30 , JText::_( 'MONTH') , JText::_( 'MONTHS')),
	        array(60 * 60 * 24 * 7, JText::_( 'WEEK') , JText::_( 'WEEKS')),
	        array(60 * 60 * 24 , JText::_( 'DAY') , JText::_( 'DAYS')),
	        array(60 * 60 , JText::_( 'HOUR') , JText::_( 'HOURS')),
	        array(60 , JText::_( 'MINUTE') , JText::_( 'MINUTES')),
	    );
	    $original = $original - 10; // Shaves a second, eliminates a bug where $time and $original match.
	    $today = time(); /* Current unix time  */
	    $since = $today - $original;

	    // $j saves performing the count function each time around the loop
	    for ($i = 0, $j = count($chunks); $i < $j; $i++) {

	        $seconds = $chunks[$i][0];
	        $name = $chunks[$i][1];
			$names = $chunks[$i][2];

	        // finding the biggest chunk (if the chunk fits, break)
	        if (($count = floor($since / $seconds)) != 0) {
	            break;
	        }
	    }

	    $print = ($count == 1) ? '1 '.$name : "$count {$names}";

	    if ($i + 1 < $j) {
	        // now getting the second item
	        $seconds2 = $chunks[$i + 1][0];
	        $name2 = $chunks[$i + 1][1];
			$names2 = $chunks[$i + 1][2];

	        // add second item if it's greater than 0
	        if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
	            $print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$names2}";
	        }
	    }
	return $print;
	}
	function curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
}