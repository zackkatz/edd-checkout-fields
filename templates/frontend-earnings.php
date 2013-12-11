<?php
if (EDD_FES()->vendors->is_commissions_active()){  ?>
<h2><?php _e( 'Commissions Overview', 'edd_fes' ); ?></h2>
<?php if(eddc_user_has_commissions()){
echo do_shortcode('[edd_commissions]'); 
}
else{
	echo 'You haven\'t gotten any sales yet!';
}
}
else {
	echo 'Error 4908';
}
?>