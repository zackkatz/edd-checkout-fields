<?php
if (EDD_FES()->vendor_permissions->vendor_can_create_product()){
	echo do_shortcode('[fes-form]');	
}
