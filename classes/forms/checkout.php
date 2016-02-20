<?php
class CFM_Checkout_Form extends CFM_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'checkout';

	/** @var string Title of the form */
	public $title = 'Checkout';

	/** @var unknown Type of form: 'user', 'post', 'custom'. Dictates where the fields save their values. */
	public $type = 'post';

	/** @var string Version of form */
	public $version = '1.0.0';

	/** @var array Array of things it supports */
	public $supports = array(
		'formbuilder' => array(
			'fields' => array(
				'public' => true, // Show public toggle
				'export' => true, // Show export toggle
			),
		),
		'multiple' => false, // Whether or not multiples of a form type can be made
	);

	/** @var array Array of characteristics of the form that need to be stored in the database */
	public $characteristics = array( );

	public function set_title() {
		$title = _x( 'Checkout', 'CFM Form title translation', 'edd_cfm' );
		$title = apply_filters( 'cfm_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;		
	}
	
	public function extending_constructor(){
		add_action( 'cfm_render_' . $this->name() . '_form_frontend_before_fields', array( $this, 'run_edd_actions' ), 10, 3 );
	}
	
	public function run_edd_actions( $form_object, $user_id, $profile ){
		if ( ! $profile ){
			do_action( 'edd_purchase_form_user_info' ); 
			do_action( 'edd_purchase_form_user_info_fields' ); 
		}
	}
}