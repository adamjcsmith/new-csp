<!--
<footer class="c p2">
	<h6 class="thin">&copy 2015 CSP Detailing</h6>
</footer>
-->

<!-- JS Includes -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="<?php echo bloginfo('template_directory'); ?>/files/parallax.min.js"></script>

	
	<script>
		
		$( document ).ready(function() {
			featuretteVerticalCentering();
		});
		
		$( window ).resize(function() {
			featuretteVerticalCentering();
		});
		
		function featuretteVerticalCentering() {
			$('.featurette').each(function(){
				var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
				
				if(w > 980) {
					$(this).css("display", "none");
					$(this).css("height", parseInt($(this).parent().css("height")) - (parseInt($(this).parent().css("padding-top")) * 2));	
					$(this).css("display", "");
				}
				else $(this).css("height", "");

			});				
		}
	
	</script>


</body>
</html>