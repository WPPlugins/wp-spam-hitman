<?php
/*
Plugin Name: WP-Spam-Hitman
Plugin URI: http://shwsite.org/wp-spam-hitman/
Description: Small, fast and simple plugin which allows user to fight with SPAM by defining list of words/regular expressions and hitpoint to get rid of SPAM once and for all!
Version: 0.7
Author: Jaroslaw Pendowski
Author URI: http://shwsite.org
*/

$default_wpsh_rules = '';
$default_wpsh_message = 'Spam Hitman thinks that your comment is spam. As all bots - he can be wrong so - just contact owner of this blog and tell him or her about it!';
$WPSH_VERSION = '0.7';

add_filter('preprocess_comment', 'wpspamhitman_process', 1);

add_action('comment_post', 'wpspamhitman_process_act', 1);
add_action('trackback_post', 'wpspamhitman_process_act', 1);
add_action('pingback_post', 'wpspamhitman_process_act', 1);

add_action('admin_menu', 'wpspamhitman_addoptions');


//Wordpress Version 1.2 / 1.3+ compatibility, add this to every plugin you write :)
if(!isset($wpdb->posts)) {
	foreach (array ("posts", "users", "categories", "post2cat", "comments",
		"links", "linkcategories", "options", "optiontypes", "optionvalues",
		"optiongroups", "optiongroup_options", "postmeta") as $table) {
			$wpdb->$table = ${"table".$table};
	}
}

function get_opt($key, $default)
{
	$opt = get_option($key);			// PHP - WTF? why I have to set it to variable before checking if it's empty?!
	if(!empty($opt)) { return $opt; }
	return $default;
}

function wpspamhitman_process($comment)
{
	/// this function reacts only if action == 'delete'
	if(get_opt('wpsh_action', 'moderation') != 'delete') { return $comment; }
	
	global $default_wpsh_message;
	$message = get_opt('wpsh_message', $default_wpsh_message);
	
	$original = $comment;
	$comment = wpsh__process($comment);
	if($comment == null && !empty($message))
	{
		$message = str_replace(
			array('%comment%', '%author%', '%url%', '%email%'),
			array($original['comment_content'], $original['comment_author'], $original['comment_author_url'], $original['comment_author_email']), 
		$message);
		// prevent comment to be saved!
		die($message);
	}
	
	return $comment;
}

function wpspamhitman_process_act($id)
{
	/// this function reacts only if action == 'spam'
	$action = get_opt('wpsh_action', 'moderation');
	if($action != 'delete') 
	{
		global $wpdb;
		global $default_wpsh_message;
		$message = get_opt('wpsh_message', $default_wpsh_message);
	
		$comment = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_ID = {$id}", ARRAY_A);
		$original = $comment[0];
		$comment = wpsh__process($comment[0]);
		
		if($comment == null)
		{
			$mark = '0';
			if($action == 'spam') { $mark = 'spam'; }
			$wpdb->query("UPDATE {$wpdb->comments} SET comment_approved = '{$mark}' WHERE comment_ID = {$id}");
			
			if(!empty($message))
			{
				$message = str_replace(
					array('%comment%', '%author%', '%url%', '%email%'),
					array($original['comment_content'], $original['comment_author'], $original['comment_author_url'], $original['comment_author_email']), 
				$message);
				die($message);
			}
		}
	}
	
	return $id;
}

function wpsh__process($comment)
{
	global $wpdb;
	if(!($comment['user_ID'] > 0))
	{
		$skip_aproved = get_opt('wpsh_skip_aproved', 'true');
		
		if($skip_aproved == 'true')
		{
			$aproved = 0;
			$email = $wpdb->escape($comment['comment_author_email']);
			$aproved = $wpdb->get_var("SELECT COUNT(comment_post_ID) FROM {$wpdb->comments} WHERE comment_author_email='{$email}' AND comment_approved = '1'");
			
			/// user is aproved
			if($aproved > 0)
			{
				return $comment;
			}
		}
	
		$opt_hits = get_opt('wpsh_number_of_hits', 5);
		$rules = get_opt('wpsh_rules', '');
		
		// no rules to check
		if(empty($rules))
		{
			return $comment;
		} else {
		
			$rules = explode("\n", $rules);
			$keys = array('comment_author', 'comment_author_email', 'comment_author_url', 'comment_content');
			
			$hits = 0;
			$count = count($rules);
			
			$data = ''; // glue all of them together - it doesn't matter and it's faster to run
			foreach($keys as $key)
			{
				$data .= $comment[$key];
			}
			
			for($i = 0; $i < $count; $i++)
			{
				$func = 0; // simple string to match
				if($rules[$i]{0} == '/' || $rules[$i]{0} == '#') { $func = 1; } // preg-syntax?
				
				$rules[$i] = trim($rules[$i]); // make sure that you don't have any "junk" in your rule
				
				$m = 0;
				switch($func)
				{
					case 0:
						$m = substr_count(strtolower($data), strtolower($rules[$i]));
						break;
					case 1:
						$m = preg_match_all($rules[$i], $comment[$key], $empty);
						unset($empty); // clear memmory - maybe it will help the performance
						break;
				}
				
				if(is_numeric($m)) { $hits += $m; }
			}
			
			// it is it!
			if($hits >= $opt_hits)
			{
				return null;
			}
			
			// it's clean!
			return $comment;
		}
	}
	
	return $comment;
}

function wpspamhitman_options()
{
	global $default_wpsh_rules;
	global $default_wpsh_message;
	global $WPSH_VERSION;
	
	$opt_hits = get_opt('wpsh_number_of_hits', 5);
	$rules = get_opt('wpsh_rules', $default_wpsh_rules);
	$message = get_opt('wpsh_message', $default_wpsh_message);
	$skip_aproved = get_opt('wpsh_skip_aproved', 'true');
	$wpsh_action = get_opt('wpsh_action', 'moderation');
	
	?>
	<div class="wrap">
		<h2>WP Spam Hitman</h2>
		<div style="margin-top: -30px; margin-left: 230px; margin-bottom: 10px">
			<small>v<?php echo $WPSH_VERSION ?></small>
		</div>
		<?php 
			$version_check = @file('http://shwsite.org/wp-spam-hitman/version', 'r');
			if($version_check != null)
			{
				if($WPSH_VERSION != trim($version_check[0]))
				{
					echo '<div style="padding: 5px; text-align: center">';
					echo '<a href="http://shwsite.org/wp-spam-hitman">Check for new version ('. $version_check[0] .')</a>';
					echo '</div>';
				}
			}
		?>
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options') ?>
			<fieldset class="options">
				<legend>Minimum hits</legend>
				<p>
					Set number of hits the comment have to get to be marked as spam and not saved in the database. Every rule adds a number of hits.
					Be aware that for instance rule 'p0rn' adds a hit every time it finds a word 'p0rn' in a comment (<strong>not</strong> just 1 hit for finding at least one word!)
				</p>
				<label for="wpsh_number_of_hits"><?php echo _e('Number of hits to spam') ?></label>
				<input type="text" id="wpsh_number_of_hits" name="wpsh_number_of_hits" value="<?php echo $opt_hits; ?>" size="3" />
			</fieldset>
			<fieldset class="options">
				<legend>Rules</legend>
				<p>
					Set of rules to be executed on every comment (except from registered users).
				</p>
				<strong>RULES of rules</strong>
				<ol>
					<li>Every rule in a seperate line</li>
					<li>PREG rules has to start with / or # character</li>
					<li>non-PREG rules are case-insensitive</li>
				</ol>
				<label for="wpsh_rules"><?php echo _e('SPAM rules') ?></label>
				<textarea id="wpsh_rules" rows="4" cols="40" style="width: 70%" name="wpsh_rules"><?php echo $rules; ?></textarea>
			</fieldset>
			<fieldset class="options">
				<legend>Actions</legend>
				<p>
					Choose what type of actions should be performed on a comment after wanted count of hits it gets from Spam Hitman.
				</p>
				<label for="wpsh_action">Status</label>
				<select id="wpsh_action" name="wpsh_action">
					<option value="delete" <?php if($wpsh_action == 'delete') { echo 'selected="selected"'; } ?>>Delete comment</option>
					<option value="spam" <?php if($wpsh_action == 'spam') { echo 'selected="selected"'; } ?>>Mark it as spam</option>
					<option value="moderation" <?php if($wpsh_action == 'moderation') { echo 'selected="selected"'; } ?>>Mark it for moderation</option>
				</select>
			</fieldset>
			<fieldset class="options">
				<legend>Message for spammers</legend>
				<p>
					Here you can set a message to spammers which will apear to them if their comment will get enough hits from Spam Hitman.
				</p>
				<ul>
					<li>HTML is allowed!</li>
					<li>Use <strong>%comment%</strong> for place you wish to place comment's content.</li>
					<li>Use <strong>%author%</strong> for place you wish to place comment's author.</li>
					<li>Use <strong>%url%</strong> for place you wish to place comment's URL.</li>
					<li>Use <strong>%email%</strong> for place you wish to place comment's email.</li>
				</ul>
				<label for="wpsh_message"><?php echo _e('Message') ?></label>
				<textarea id="wpsh_message" rows="4" cols="40" style="width: 70%" name="wpsh_message"><?php echo $message; ?></textarea>
			</fieldset>
			<fieldset class="options">
				<legend>Aproved visitors</legend>
				<p>
					Choose an option how to treat aproved users.<br />
					Option about aproving users is available in [Options] -> [Discussion] -> [Before a comment appears]
				</p>
				<label for="wpsh_skip_aproved">Action</label>
				<select id="wpsh_skip_aproved" name="wpsh_skip_aproved">
					<option value="false" <?php if($skip_aproved != 'true') { echo 'selected="selected"'; } ?>>Use Spam Hitman on everybody (except regitered users)</option>
					<option value="true" <?php if($skip_aproved == 'true') { echo 'selected="selected"'; } ?>>Skip checking for aproved visitors</option>
				</select>
			</fieldset>
			<fieldset class="options">
				<p class="submit">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="wpsh_number_of_hits,wpsh_rules,wpsh_message,wpsh_skip_aproved,wpsh_action" />
					<input type="submit" name="Submit" value="<?php _e('Update Options »') ?>" />
				</p>
			</fieldset>
		</form>
	</div>
	<?php
}

function wpspamhitman_addoptions()
{
	add_options_page('WP Spam Hitman', 'WP Spam Hitman', 10, 'wp-spam-hitman.php', 'wpspamhitman_options');
}

?>