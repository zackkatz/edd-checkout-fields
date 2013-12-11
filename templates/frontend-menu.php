<?php
$task = ! empty( $_GET['task'] ) ? $_GET['task'] : '';
?>
<div id="fes-wraper" class="fes">
<div class='fes-menu'>
	<nav>
		<ul>
			<li class="<?php if( $task == '' || $task == 'dashboard' ) echo "active"; ?>">
				<a href='<?php echo get_permalink();?>'>
					<i class="icon icon-home icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'Dashboard', 'edd_fes' ); ?></span>
				</a>
			</li>
			<li class="<?php if( $task == 'products' ) echo "active"; ?>" >
				<a href='<?php echo add_query_arg( 'task', 'products', get_permalink() ); ?>'>
					<i class="icon icon-list icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'My Products', 'edd_fes' ); ?></span>
				</a>
			</li>
			<?php if (EDD_FES()->vendor_permissions->vendor_can_create_product()) : ?>
			<li class="<?php if( $task == 'new' ) echo "active"; ?>" >
				<a href='<?php echo add_query_arg( 'task', 'new', get_permalink() ); ?>'>
					<i class="icon icon-pencil icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'Add New Product', 'edd_fes' ); ?></span>
				</a>
			</li>
			<?php endif; ?>
			<?php if(EDD_FES()->vendors->is_commissions_active()) : ?>
			<li class="<?php if( $task == 'earnings' ) echo "active"; ?>" >
				<a href="<?php echo add_query_arg( 'task', 'earnings', get_permalink() ); ?>">
					<i class="icon icon-shopping-cart icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'Earnings', 'edd_fes' ); ?></span>
				</a>
			</li> 
			<?php endif; ?>
			<li class="<?php if( $task == 'profile' ) echo "active"; ?>">
				<a href="<?php echo add_query_arg( 'task', 'profile', get_permalink() ); ?>">
					<i class="icon icon-user icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'Profile', 'edd_fes' ); ?></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo add_query_arg( 'task', 'logout', get_permalink() ); ?>">
					<i class="icon icon-off icon-white"></i> <span class="hidden-phone hidden-tablet"><?php _e( 'Logout', 'edd_fes' ); ?></span>
				</a>
			</li> 
		</ul>  
	</nav>
</div>
</div>