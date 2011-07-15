<?php
/*
Plugin Name: ActivityStream
Plugin URI: http://christopherreding.com
Description: Grabs posts and comments and arranges them in an activity stream
Version: 1.0
Author URI: http://christopherreding.com
Copyright 2011 Christopher Reding
*/

class ActivityStream {

	var $activity_steam;

	function __construct(){
	
		$this->get_app_vars();
		$this->activity_stream = array();
		
		add_action('init', array($this, 'action_register_activity_scripts') );
		add_action('wp_ajax_nopriv_load_activity_feed', array( $this, 'ajax_load_activity_feed' ) );
	    add_action('wp_ajax_load_activity_feed', array( $this, 'ajax_load_activity_feed' ) );
		add_action('wp_print_scripts', array($this, 'action_enqueue_activity_scripts' ) );

	}
	
	private function get_app_vars(){
		$this->plugin    = plugin_basename(__FILE__);
		$this->basepath  = plugin_dir_path(__FILE__);
		$this->url       = plugin_dir_url(__FILE__);
		$this->js		 = $this->url.'js/';
		$this->css		 = $this->url.'css';
		$this->ext 		 = '.php';
		$this->apppath   = $this->basepath .'app' . DIRECTORY_SEPARATOR;
	}
	
	public function action_register_activity_scripts(){
		wp_register_script('activity-feed', $this->js.'scripts.js', 'jquery', '1.0', TRUE);
	}
	
	public function action_enqueue_activity_scripts(){
		wp_enqueue_script( array( 'activity-feed' ) );
	}
	

	
	private function get_activity_class($post_type){
	
		switch( $post_type ){
			case 'heroes':
				return 'main_heroes';
				break;
			case 'discussions':
				return 'main_disc';
				break;
			case 'polls':
				return 'main_polls';
				break;
			case 'interest-groups':
				return 'main_int';
				break;
			case 'post':
				return 'main_blog';
				break;
			case 'news':
				return 'main_news';
				break;
			default:
				return false;
		}
	}
	
	public function the_activity_class($post_type){
		return $this->get_activity_class($post_type);
	}
	
	private function get_activity_name($post_type){
	
		$find = array('post', 'interest-groups', 'polls', 'discussions', 'news', 'heroes' );
		$replace = array('Blog', 'Interest Groups', 'Polls', 'Discussions', 'News', 'Heroes' );
		return str_replace( $find, $replace ,$post_type );
	}
	
	public function the_activity_name($post_type){
		echo $this->get_activity_name($post_type);
	}
	
    private function comment_count($id) {

			$comments_by_type = &separate_comments(get_comments('status=approve&post_id=' . $id));
			echo count($comments_by_type['comment']);
	}
	
	
	
	private function limit_words($string, $word_limit){
	    $words = explode(" ",$string);
	    $num = count($words);	
	    $string = implode(" ",array_splice($words,0,$word_limit));
	    if($num > $word_limit)
	    	$string .= '...';

	    return $string;
	}
	
	public function relativeTime($time)
	{   
		//date_default_timezone_set('PDT');
		
		$second =  1;
		$minute = 60 * $second;
		$hour = 60 * $minute;
		$day = 24 * $hour;
		$month = 30 * $day;
		
	    $delta = (time() - $time);
	
	    if ($delta < 1 * $minute)
	    {
	        return $delta == 1 ? "one second ago" : $delta . " seconds ago";
	    }
	    if ($delta < 2 * $minute)
	    {
	      return "a minute ago";
	    }
	    if ($delta < 45 * $minute)
	    {
	        return floor($delta / $minute) . " minutes ago";
	    }
	    if ($delta < 90 * $minute)
	    {
	      return "an hour ago";
	    }
	    if ($delta < 24 * $hour)
	    {
	      return floor($delta / $hour) . " hours ago";
	    }
	    if ($delta < 48 * $hour)
	    {
	      return "yesterday";
	    }
	    if ($delta < 5 * $day)
	    {
	        return floor($delta / $day) . " days ago";
	    }
	   return date("F j, Y, g:i a",$time);
	   	}

	
	public function get_activity_stream($opts = array()){
	
		$activities = $this->get_the_activities($opts);
		
		
		return $activities;
	}
	
	public function the_activity_stream( $args = array() ){
		   
		   $activities = $this->get_activity_stream( $args );
		   		   
		   if ($activities) : foreach($activities as $activity): 
		   if( $post_author ):
		  
		    $class = $this->the_activity_class( $activity->post_type );
			$permalink = get_permalink($activity->post_id);
			$permalink = ($class == 'Comment') ? $permalink.'#coment-'.$activity->id : $permalink; ?>
				<tr class="activity">
					<td>
						<h4>
							<?php $this->the_activity_name($activity->post_type); ?> 
							<a href="<?php echo $permalink; ?>" title="<?php echo $activity->title; ?>">
							<?php echo $activity->title; ?></a>
						</h4>
					</td>
					
					<td>
						<p><?php echo $this->relativeTime($activity->date); ?></p>
					</td>
				</tr>
			
		   <?php else: ?>
		   <?php $activity = $activity; ?>
		   <article>
		   		<?php 
		   			$class = $this->get_activity_class( $activity->post_type );
					$permalink = get_permalink($activity->post_id);
					$permalink = ($class == 'Comment') ? $permalink.'#coment-'.$activity->id : $permalink;
					?>
				<h2 class="<?php echo $class; ?>">
				<a href="<?php echo $permalink; ?>" title="<?php echo $activity->title; ?>">
				<?php echo $activity->title; ?></a></h2>
				<p class="meta">Date posted: <?php echo $this->relativeTime($activity->date); ?></p>
				
				<div class="thumb">
					<?php if( $activity->type == 'heroes' ): ?>	
					 	<a href="<?php echo $activity->author_url; ?>">
					<?php echo get_the_post_thumbnail($activity->id, 'small-post-thumb'); ?>
					<?php else: ?>
						<a href="<?php echo  get_permalink($activity->post_id); ?>">
					<?php echo get_avatar($activity->user_id, 52); ?>
					<?php endif; ?>
					</a>
				</div>
				
				<div class="copy">
					<?php echo strip_tags($activity->content); ?>
				</div><br clear="all" />
				
				<div class="meta">
					<p>Posted By: <a href="<?php echo $activity->author_url; ?>"><?php echo $activity->author; ?></a> 
					<img src="<?php bloginfo('template_url'); ?>/images/meta_seperate.png" alt="meta_seperate" width="26" height="7" /> 
					Category: <a href="<?php  echo get_bloginfo('url').'/'.$activity->type; ?>"> 
					<?php 
					$find = array('post', 'interest-groups', 'polls', 'discussions', 'news', 'heroes' );
					$replace = array('Blog', 'Interest Groups', 'Polls', 'Discussions', 'News', 'Heroes' ); ?>
					<?php echo str_replace( $find, $replace ,$activity->post_type ); ?>
					</a> <span><a href="<?php echo $permalink; ?>#comments"><?php $this->comment_count($activity->id) ?> Comments</a></span></p>
				</div><!-- #meta -->
			</article>
			<?php endif; ?>
			<?php endforeach; endif; 
	}
	
	private function get_the_activities( $args = array() ){
		global $wpdb;
		
		//defaults
		$start = 0;
		$limit = 5;
		$post_author = FALSE;
		
		if(is_array($args)){
			extract( $args, EXTR_OVERWRITE );
		}


		if($post_author){
			$post_author = get_userdata($post_author);
		}
	 	$url = get_bloginfo('url') . "/profile/";
	 	$query = "(SELECT 
				UNIX_TIMESTAMP(post_date) as date,  
				wp_posts.ID as id,
				wp_posts.ID as post_id,
				post_title as title, 
				wp_users.display_name as author,
				CONCAT('".get_bloginfo('url')."', '/profile/', wp_users.user_nicename) as author_url,
				wp_users.user_email as avatar,
				SUBSTRING_INDEX(wp_posts.post_content,' ',35) AS content,
				wp_posts.post_author as user_id,
				('category') as category,
				wp_posts.post_parent as parent,
				wp_posts.post_type as type,
				wp_posts.post_type as post_type
			FROM wp_posts INNER JOIN wp_users ON wp_posts.post_author = wp_users.ID
			WHERE wp_posts.post_status = 'publish' ";
			if($post_author):
			$query .= "AND wp_posts.post_author = $post_author->ID ";
			endif;
			$query .= "AND wp_posts.post_type IN ('post', 'heroes', 'discussions', 'interest-groups' ) ) 
			UNION
			(SELECT 
				UNIX_TIMESTAMP(wp_comments.comment_date) as date,
				comment_ID as id,
				wp_posts.ID as post_id,
				CONCAT('Re: ', wp_posts.post_title) as title,  
				wp_users.display_name as author,
				CONCAT('".get_bloginfo('url')."', '/profile/', wp_users.user_nicename) as author_url,
				wp_users.user_email as avatar,
				SUBSTRING_INDEX(wp_comments.comment_content,' ',35) AS content,
				wp_users.ID as user_id,
				('category') as category,
				comment_post_ID as parent,
				('Comment') as type,
				wp_posts.post_type as post_type
			FROM (wp_comments INNER JOIN wp_posts ON wp_comments.comment_post_ID = wp_posts.ID 
			AND wp_posts.post_type IN ('post', 'heroes', 'discussions', 'interest-groups', 'polls' )";
			
		if($post_author):
		$query .= "AND wp_comments.user_id = '{$post_author->ID}' ";
		endif;
		$query .= "AND wp_comments.comment_approved = 1 )
		    INNER JOIN wp_users ON wp_comments.comment_author_email = wp_users.user_email )
			
			ORDER BY date DESC
			LIMIT " . $start . " , ". $limit;
			
		$results = $wpdb->get_results($query);
		return $results;
	}
	
	public function ajax_load_activity_feed(){
		$nonce = $_POST['nonce'];
		if (! wp_verify_nonce($nonce, 'load_activity_feed') ) die(-1);
		
		$start = $_POST['start'];
		$limit = $_POST['limit'];
		
		die($this->the_activity_stream(array('start' => $start,'limit' => $limit ) ) );
	
	}
}

$activitystream = new ActivityStream();
