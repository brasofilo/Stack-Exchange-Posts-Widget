<?php

class SEPW_Widget_Print 
{
	/**
	 * Build user widget
	 * 
	 */
	public static function print_user( $build, $get_se, $user, $before_title, $after_title, $title, $stack_site, $q_or_a, $subtitle, $se_sites )
	{ 
		if( $subtitle )
			$title .=  self::get_subtitle_html( 'user', $get_se['posts'][0]['owner'], $se_sites[ $stack_site ] );
		$build .= $before_title . $title . $after_title;
		foreach( $get_se['posts'] as $post )			
			$build .= self::print_post( $q_or_a, $post, $stack_site, $user, $se_sites );
		return $build;
	}
	
	
	/**
	 * Build site widget (answers or questions)
	 */
	public static function print_site( $build, $get_se, $before_title, $after_title, $title, $stack_site, $q_or_a, $subtitle, $se_sites )
	{
		if( $subtitle )
			$title .=  self::get_subtitle_html( 'site', '', $se_sites[ $stack_site ] );
		$build .= $before_title . $title . $after_title;
		foreach( $get_se['posts'] as $post )
			$build .= self::print_post( $q_or_a, $post, $stack_site, false, $se_sites );
		return $build;
	}

	
	private static function print_post( $q_or_a, $post, $stack_site, $user, $se_sites )
	{
		$tags = self::get_tags_html( $post, $stack_site, $se_sites );
		
		# USER BLOCK
		$user_meta = '';
		if( !$user )
		{
			if( isset( $post['owner']['link'] ) )
				$user_meta = "| <a href='{$post['owner']['link']}' class='author'>{$post['owner']['display_name']}</a>";
			else
				$user_meta = "| {$post['owner']['display_name']}";
		}
		$post_date = sprintf(
				'<span class="relativetime">%s</span>',
				date('d/m/y', $post['creation_date'] )
		);
		
		$l18n_votes = __( 'votes', 'sepw' );
		$l18n_answers = __( 'answers', 'sepw' );
		# OPEN html
		$html = "
			
<div class='se-post-summary'>";
		
		# QUESTIONS html
		if( 'questions' == $q_or_a )
		{
			$is_answered = ( isset( $post['is_answered'] ) && $post['is_answered'] ) 
				? 'answered-accepted'
				: '';
			$html .= "
	<div class='se-status'>
		<div class='se-votes'>
			<div class='mini-counts'>{$post['score']}</div>
			<div class='mini-text'>$l18n_votes</div>

			<div class='answers-count $is_answered'>
				<div class='mini-counts'>{$post['answer_count']}</div>
				<div class='mini-text'>$l18n_answers</div>
			</div>
		</div>
	</div>
";
		}
		
		# ANSWERS html
		else
		{
			$is_accepted = ( isset( $post['is_accepted'] ) && $post['is_accepted'] ) 
				? 'answered-accepted'
				: '';
			$html .= "
	<div class='se-status se-status-answer $is_accepted'>
		<div class='se-votes'>
			<div class='mini-counts'>{$post['score']}</div>
			<div class='mini-text'>$l18n_votes</div>

			<div class='answers-count'>
				<div class='mini-counts'>&nbsp;</div>
				<div>&nbsp;</div>
			</div>
		</div>
	</div>
";
		}
		
		# CLOSE html
		$html .=  "
	<div class='se-post-title'>
		<a href='{$post['link']}' class='se-hyperlink' target='_blank'>
			<h2>
				{$post['title']}
			</h2>
		</a>
		<div class='se-tags'>
		$tags
		</div>
		<div class='se-post-meta'>
			$post_date $user_meta 
		</div>
	</div>
</div><!-- end post-summary -->";
		return $html;
	}
	
	
	private static function get_subtitle_html( $user_or_site, $owner, $site_info )
	{
		if( 'user' == $user_or_site )
			return sprintf(
					'<div class="se-subtitle"><small>%1$s @ <a href="%2$s" title="%3$s">%4$s</a></small><div class="se-post-meta relativetime"><div class="site-logo"><img src="%5$s" /></div>%3$s</div></div>',
					$owner['display_name'],
					$site_info['site_url'],
					$site_info['audience'],
					$site_info['name'],
					$site_info['favicon_url']
				);
		else
			return sprintf(
					'<div class="se-subtitle"><small><a href="%1$s" title="%2$s">%3$s</a></small><div class="se-post-meta relativetime"><div class="site-logo"><img src="%4$s" /></div>%2$s</div></div>',
					$site_info['site_url'],
					$site_info['audience'],
					$site_info['name'],
					$site_info['favicon_url']
				);
	}
	

	/**
	 * Build Tags html block
	 * 
	 * @param object $post
	 * @return string
	 */
	private static function get_tags_html( $post, $stack_site, $se_sites )
	{
		$tags = '';
		if( isset( $post['tags'] ) )
		{
			foreach( $post['tags'] as $tag )
			{
				$tags = sprintf(
						'<a href="%s" class="se-post-tag">%s</a>',
						$se_sites[ $stack_site ]['site_url'] . '/questions/tagged/' . $tag,
						htmlentities( $tag )
						);
			}
		}
		return $tags;
	}

	
	/**
	 * Print Dropdown
	 * 
	 * @param string $title
	 * @param string $slug
	 * @param array $instance
	 * @param string $items
	 * @param string $id
	 * @param string $name
	 */
	public static function the_simple_select( $title, $slug, $instance, $items, $id, $name )
	{
		echo "<!-- $title: Select Box -->";
		echo '<p>';
		printf(
			'<label for="%s">%s</label>',
			$id,
			$title
		);
		printf(
			'<select id="%s" name="%s" class="widefat">',
			$id,
			$name
		);
		foreach( $items as $key => $value )
		{
			printf(
		        '<option value="%s" %s> %s</option>',
				$key,
		        selected( $instance[$slug], $key, false),
				$value
		    );
	
		 }   
		echo '</select></p>';
	}
	
	
	/**
	 * Print Checkbox
	 * 
	 * @param array $instance
	 * @param string $key
	 * @param string $value
	 * @param string $id
	 * @param string $name
	 */
	public static function the_checkbox( $instance, $key, $value, $id, $name )
	{
		echo "<!-- $value Checkbox -->";
		echo '<p>';
		printf(
			'<input class="checkbox" type="checkbox" %s id="%s" name="%s" />',
			checked( $instance[ $key ], 'on', false ),
			$id,
			$name
		);
		printf(
			'<label for="%s"> %s</label>',
			$id,
			$value
		);
		echo '</p>';
		
	}
	
	
	/**
	 * Print Input Text
	 * @param string $key
	 * @param string $value
	 * @param array $instance
	 * @param string $id
	 * @param string $name
	 */
	public static function the_text( $key, $value, $instance, $id, $name )
	{
		echo "<!-- Widget $value Text Input -->";
		echo '<p>';
		printf(
				"<label for='%s'>%s</label>", 
				$id, 
				$value
		);
		printf(
				"<input id='%s' name='%s' value='%s' class='widefat' type='text' />", 
				$id, 
				$name, 
				$instance[$key]
		);
		echo '</p>';
	}
	
	
	/**
	 * Print Dropdown with list of sites
	 * 
	 * @param array $instance
	 * @param string $id
	 * @param string $name
	 * @param array $se_sites
	 */
	public static function the_sites( $instance, $id, $name, $se_sites )
	{
		$se_site_saved = 'stackoverflow';
		if( !empty( $instance['stack_site'] ) )
			$se_site_saved = $instance['stack_site'];
		
		// Print dropdown selection
		echo '<!-- Stack site: Select Box -->';
		echo '<p>';
		printf(
			'<label for="%s">%s</label><br />',
			$id,
			__('Target site', 'sepw')
		);
		printf(
			'<select id="%s" name="%s" class="widefat se-site-icons">',
			$id,
			$name
		);
		foreach( $se_sites as $slug => $site )
		{
			printf(
		        '<option value="%s" data-image="%s" %s> %s</option>',
				$site['api_site_parameter'],
				$site['favicon_url'],
		        selected( $instance['stack_site'], $slug, false),
				$site['name']
		    );
	
		 }   
		echo '</select></p>';
	}

	
	/**
	 * List of Sites
	 * Check if transient exist, otherwise create new one
	 * 
	 * @return array
	 */
	public static function get_sites()
	{
		$sites = get_transient( 'sepw_widget_sites' );
		if ( empty( $sites ) )
		{
			$response = API::Sites();
			$sites = array();
			while( $site = $response->Fetch(TRUE) )
			{
				$temp_data = $site->Data();
				$sites[$temp_data['api_site_parameter']] = $temp_data;
			}
			set_transient( 'sepw_widget_sites', $sites, 8 * WEEK_IN_SECONDS );
		} 
		return $sites;
	}

	
	/**
	 * Stack PHP - API Query
	 * 
	 * @param string $user_id
	 * @param string $p_size
	 * @param string $stack_site
	 * @param string $q_or_a
	 * @param string $sort
	 * @param string $order
	 * @return array
	 */
	public static function get_se( $user_id, $p_size, $stack_site, $q_or_a, $sort, $order )
	{
		### DEFINE VARS
		$sites = self::get_sites();
		$site_info = $sites[$stack_site];
		$posts = array();
		$filter = new Filter();
		### SITE
		if( empty( $user_id ) )
		{
			$site = API::Site($stack_site);

			## QUESTIONS
			if( 'questions' == $q_or_a )
			{
				# ASC
				if( 'asc' == $order )
					$query = $site->Questions()->SortBy( $sort )->Ascending()->Filter( '!n8.5kFmsos' )->Exec()->Page(1)->Pagesize($p_size+1);
				# DESC
				else
					$query = $site->Questions()->SortBy( $sort )->Descending()->Filter( '!n8.5kFmsos' )->Exec()->Page(1)->Pagesize($p_size+1);
			}
			## ANSWERS
			else
			{
				# ASC
				if( 'asc' == $order )
					$query = $site->Answers()->SortBy( $sort )->Ascending()->Filter( '!--btTLsEdbp(' )->Exec()->Page(1)->Pagesize($p_size+1);
				# DESC
				else
					$query = $site->Answers()->SortBy( $sort )->Descending()->Filter( '!--btTLsEdbp(' )->Exec()->Page(1)->Pagesize($p_size+1);
			}
		}
		### USER
		else
		{
			$user = API::Site( $stack_site )->Users( $user_id );
			$user_data = $user->Exec()->Fetch();
			$filter = new Filter();
			## QUESTIONS
			if( 'questions' == $q_or_a )
			{
				# ASC
				if( 'asc' == $order )
					$query = $user->Questions()->SortBy( $sort )->Ascending()->Filter('!gfG0_rPCgOGeBliTwxTD1pl6ZzcYbMMx2tk')->Exec()->Page(1)->Pagesize($p_size+1);
				# DESC
				else
					$query = $user->Questions()->SortBy( $sort )->Descending()->Filter('!gfG0_rPCgOGeBliTwxTD1pl6ZzcYbMMx2tk')->Exec()->Page(1)->Pagesize($p_size+1);
			}
			## ANSWERS
			else
			{
				# ASC
				if( 'asc' == $order )
					$query = $user->Answers()->SortBy( $sort )->Ascending()->Filter('!--btTLsEdbp(')->Exec()->Page(1)->Pagesize($p_size+1);
				# DESC
				else
					$query = $user->Answers()->SortBy( $sort )->Descending()->Filter('!--btTLsEdbp(')->Exec()->Page(1)->Pagesize($p_size+1);
			}	

		}
		
		### NO DATA RETRIEVED
		if( !$query->Fetch( false ) )
			return false; 
		### LOOP QUERY
		while( $post = $query->Fetch( false ) ){
			$posts[] = $post;
			// TODO: remove this, it's a debug check for INFINITE QUERY
			//ploga('fetch post: '.$post['title'] );
		}
		### RESULTS
		return array( 'posts' => $posts, 'site' => $site_info );
	}

}