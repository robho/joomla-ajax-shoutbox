<script type="text/javascript">
	var jal_org_timeout = <?php echo intval($params->get( 'refresh', 4 )) * 1000; ?>;
	var jal_timeout = jal_org_timeout;
	var guestName = '<?php echo JText::_( 'GUEST'); ?>';
	var fadefrom = '<?php echo $params->get("fadefrom"); ?>';
	var fadeto = '<?php echo $params->get("fadeto"); ?>';
	var GetChaturl = "<?php echo $getshouts ?>";
	var SendChaturl = "<?php echo $addshout ?>";
</script>

<div id="shoutbox">
	<div id="chatoutput">
		<?php $first_time = true; ?>
		<?php foreach ($list as $item) : ?>
			<?php if ($first_time == true):
			$lastID = $item->id; ?>
				<div id="lastMessage"><span><?php echo JText::_( 'LAST_MESSAGE'); ?>:</span> <em id="responseTime"><?php echo modShoutboxHelper::time_since($item->time); ?> <?php echo JText::_( 'AGO'); ?></em></div><ul id="outputList">
			<?php endif; ?>
			<?php if ($maydelete): ?>
			<li><span title="<?php echo $item->ip; ?>"><?php echo $item->url; ?> : </span><?php echo $item->text; ?> <a href="<?php echo $delshout; ?>&amp;shoutid=<?php echo $item->id; ?>" title="Delete">x</a></li>
			<?php else : ?>
			<li><span title="<?php echo modShoutboxHelper::time_since($item->time); ?>"><?php echo $item->url; ?> : </span><?php echo $item->text; ?></li>
			<?php endif; ?>
			<?php $first_time = false; ?>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php if ($params->get('tag')) : ?>
	<p><?php echo JText::_( 'GUESTTAG');?></p>
	<?php endif; ?>
	<?php if ($params->get('post_guest') || $loggedin != 'guest') : ?>
	<form id="chatForm" name="chatForm" method="post" action="index.php">
		<p>
			<?php $name = ($params->get("name")) ? $user->get('name') : $user->get('username'); ?>
			<?php if($loggedin != 'guest') :  /* If they are logged in, then print their nickname */ ?>
			<label><?php echo JText::_( 'NAME'); ?> <em><?php echo $name; ?></em></label>
			<input type="hidden" name="shoutboxname" id="shoutboxname" class="inputbox" value="<?php echo $name; ?>" />
			<?php else:  /* Otherwise allow the user to pick their own name */ ?>
			<label for="shoutboxname"><?php echo JText::_( 'NAME'); ?></label>
			<input type="text" name="shoutboxname" id="shoutboxname" class="inputbox" value="<?php if (isset($_COOKIE['jalUserName'])) { echo $_COOKIE['jalUserName']; } ?>" />
			<?php endif; ?>
			<?php if (!$params->get('url')) : ?>
			<span style="display: none">
			<?php endif; ?>
			<label for="shoutboxurl">Url:</label>
			<input type="text" name="shoutboxurl" id="shoutboxurl" class="inputbox" value="<?php if (isset($_COOKIE['jalUrl'])) { echo $_COOKIE['jalUrl']; } else { echo 'http://'; } ?>" />
			<?php if (!$params->get('url')) : ?>
			</span>
			<?php endif; ?>
			
			<label for="chatbarText"><?php echo JText::_( 'MESSAGE'); ?></label>
			<?php if ($params->get('textarea')) : ?>
			<?php
			$Form = '';
			$mainframe = JFactory::getApplication();
			$mainframe->triggerEvent('onBBCode_RenderForm', array('document.forms.chatForm.chatbarText', &$Form) );
			echo $Form;
			?>
			<textarea rows="4" cols="16" name="chatbarText" id="chatbarText" class="inputbox" onkeypress="return pressedEnter(this,event);"></textarea>
			<?php else: ?>
			<input type="text" name="chatbarText" id="chatbarText" class="inputbox" onkeypress="return pressedEnter(this,event);"/>
			<?php endif; ?>
			<input type="text" name="website" id="website" class="website" />
		</p>
		<?php if(JPluginHelper::isEnabled('system', 'yvsmiley')): ?>
		<a id="toggle" href="#" name="toggle"><?php echo JText::_( 'SMILEYS'); ?></a>
		<?php 
		$smilies = '';
		$mainframe->triggerEvent('onSmiley_RenderForm', array('document.forms.chatForm.chatbarText', &$smilies, 'sbsmile') );
		echo $smilies;
		?>
		<?php endif; ?>
		<input type="hidden" id="jal_lastID" value="<?php echo $lastID + 1; ?>" name="jal_lastID" />
		<input type="hidden" name="shout_no_js" value="true" />
		<?php if ($params->get('submit')): ?>
		<input type="submit" id="submitchat" name="submit" class="button" value="<?php echo JText::_( 'SEND'); ?>" />
		<?php endif; ?>
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php else: ?>
	<p><?php echo JText::_( 'REGISTER_ONLY'); ?></p>
	<?php endif; ?>
</div>
