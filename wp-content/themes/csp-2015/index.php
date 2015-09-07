<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<?php $image = get_field('page_banner_image'); 
		echo "<div class='page-banner cf' style='background-image:url(" . $image['url'] . ")' >";
			
		$title =  $post->post_title;	
		$subtitle = $post->subtitle;
		$description = $post->page_description;
		
		echo "<div class='page-title'><h1>" . $title . "</h1><h3 class='m-hide'>". $subtitle ."</h3></div>";
	
		
		echo "<div class='social-box p1'>
		
		<a target='_blank' href='http://twitter.com/home/?status= " .$title. " - " .wp_get_shortlink(). " via @cspdetailing 'title='Tweet this!'>
			<i class='fa fa-twitter fa-lg fa-fw'></i>
		</a>
		
		<a target='_blank' href='http://facebook.com/sharer.php?u=" .wp_get_shortlink(). "&title=" .$title. "'>
			<i class='fa fa-facebook fa-lg fa-fw'></i>
		</a>

		
		</div>";
			
		echo "</div>";
		
		if(isset($post->page_description)) {
			echo "<div class='panel c'><p>". $description ."</p></div>";
		}
	?>
	
	<?php the_content(__('(more...)')); ?>
	
	<div style="height:2000px; width: 100%; color: #f1f1f1; ">
	
	</div>
	
	<!--
	<h1><?php the_title(); ?></h1>
	<h4>Posted on <?php the_time('F jS, Y') ?></h4>
	<p><?php the_content(__('(more...)')); ?></p>
	<hr> <?php endwhile; else: ?>
	<p><?php _e('Sorry, no posts matched your criteria.'); ?></p><?php endif; ?>
	<?php get_sidebar(); ?>
-->
	
	
<?php get_footer(); ?>