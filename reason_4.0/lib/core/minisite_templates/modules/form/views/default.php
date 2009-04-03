<?php
/**
 * @package reason
 * @subpackage minisite_modules
 */

/**
 * Include dependencies
 */
include_once('reason_header.php');
include_once(DISCO_INC . 'disco.php');

/**
 * Register form with Reason
 */
$GLOBALS[ '_form_view_class_names' ][ basename( __FILE__, '.php') ] = 'DefaultForm';

/**
 * DefaultForm is an extension of Disco used to work with database backed forms.
 *
 * Prior to init, this class should be passed a reference to a model via the set_model method. Using the model and parameters set in the model 
 * by the controller, the forms sets itself up during a private _init method. All the core disco methods are available for use in extending the
 * form, including:
 *
 * 1. on_every_time
 * 2. run_error_checks
 * 3. pre_error_check_actions
 * 4. post_error_check_actions
 * 5. process
 *
 * A couple new methods are available - notably:
 *
 * 1. custom_init
 *
 * Note that the process method is invoked just prior to the _process method, and is intended for custom process actions.
 *
 * To create universal views that work with thor forms, use these methods to get the name of elements:
 * 
 * 1. get_element_name_from_label - returns the name of the disco element that correspons to a label
 * 2. get_value_from_label - returns the value of the disco element that corresponds to a label
 *
 * Class variables you may want to overload:
 *
 * 1. custom_magic_transform_attributes - extra fields to grab from the directory service for the logged in user
 * 2. custom_magic_transform_methods - custom mappings from normalized elements labels to methods that return an autofill value
 * 
 * Class variables which replace defaults set by the model - overloading these should rarely be necessary:
 *
 * 1. magic_transform_attributes - fields to grab from the directory service used by magic transform methods
 * 2. magic_transform_methods - mappings of normalized elements to methods that return an autofill value
 * 3. submitted_data_hidden_fields - fields to hide when showing submitted data to e-mail recipients and those with viewing privs for form data
 * 4. submitted_data_hidden_fields_submitter_view - same as above, but when the viewer is the submitter
 *
 * MODEL REQUIREMENTS
 *
 * The DefaultForm view assumes the availability of these model methods
 *
 * - transform_form()
 * - get_disco_field_name($label)
 * - get_redirect_url()
 * 
 * In addition, each method in the array process_actions should be represented with two methods in the model, which define default behavior:
 *
 * - should_$process_action AND
 * - $process_action
 *
 * If, however, the above methods are defined in the view, the view methods will be preferred.
 *
 * @author Nathan White
 */

class DefaultForm extends Disco
{
	var $_model; // thor model

	/**
	 * @var array extra directory service attributes - merged with magic transform attribues
	 */
	var $custom_magic_transform_attributes;
	
	/**
	 * @var array extra magic transform methods - merged with magic transform methods array
	 */
	var $custom_magic_transform_methods;

	/**
	 * @var array directory service attributes needed by magic transform methods - if defined, replaces model defaults
	 */
	var $magic_transform_attributes; // in most cases do not define this - the model has defaults

	/**
	 * @var array maps normalized element names (lower case, spaces replaced with _) to magic transform methods - if defined, replaces model defaults
	 */
	var $magic_transform_methods; // in most cases do not define this - the model has defaults
	
	/**
	 * @var array fields that should be hidden from the submitter in screen and e-mail views of data - if defined, replaces model defaults
	 */
	var $submitted_data_hidden_fields_submitter_view; // in most cases do not define this - the model has defaults

	/**
	 * @var array fields that should be hidden from everyone in screen and e-mail views of data - if defined, replaces model defaults
	 */
	var $submitted_data_hidden_fields; // in most cases do not define this - the model has defaults
	
	/**
	 * @var boolean whether or not the clear button should be shown
	 */
	var $show_clear_button; // set to true if you want the clear button to be available
	
	/**
	 * @var array of process actions
	 */
	var $process_actions = array('save_form_data', 'email_form_data_to_submitter', 'email_form_data', 'save_submitted_data_to_session');
	
	/**
	 * Inits the Disco Form
	 */
	function init( $externally_set_up = false )
	{
		$this->_init();
		parent::init();
	}
	
	/**
	 * Runs the model method transform_form.
	 */
	function _init()
	{		
		if (!isset($this->__inited)) // only run once
		{
			if (method_exists($this, 'custom_init')) $this->custom_init();
			$model =& $this->get_model();
			$model->transform_form($this);
			$this->__inited = true;
		}
	}
	
	function run_load_phase()
	{
		parent::run_load_phase();
		$this->add_required_field_comment();
	}
	
	// check for required elements at end of load phase and add the * = required field comment
	function add_required_field_comment()
	{
		if (!empty($this->required))
		{
			$order = $this->get_order();
			$this->add_element('_required_text', 'comment', array('text' => '<p class="required_indicator">* = required field</p>'));
			$this->set_order(array('_required_text' => '_required_text') + $this->get_order());
		}
	}
	
	/**
	 * Default process actions
	 */
	function _process()
	{
		if ($actions = $this->get_process_actions())
		{
			foreach ($actions as $action)
			{
				if (!empty($action))
				{
					if ($this->check_and_invoke_view_or_model_method('should_'.$action)) $this->check_and_invoke_view_or_model_method($action);
				}
			}
		}
	}
		
	/**
	 * We use (abuse) the disco finish method to do our processing, leaving the process method for custom views
	 */
	function finish()
	{
		$this->_process();
	}
	
	function has_content()
	{
		return true;
	}
	
	function where_to()
	{
		$model =& $this->get_model();
		return $model->get_redirect_url();
	}

	/**
	 * Checks if the view or model has a method with name method - if so, run that method, otherwise trigger an error
	 * @param string method name to invoke
	 */
	function check_and_invoke_view_or_model_method($method)
	{
		$model =& $this->get_model();
		if (method_exists($this, $method)) return $this->$method();
		elseif (method_exists($model, $method)) return $model->$method();
		else
		{
			trigger_error('The form view or model needs to have the method ' . $method . ' defined to support all the process actions defined for the form.');
		}
		return false;
	}
		
	function get_element_name_from_label($label)
	{
		$model =& $this->get_model();
		return $model->get_disco_field_name($label);
	}
		
	function get_value_from_label( $element_label ) // {{{
	{
		if($element_name = $this->get_element_name_from_label($element_label))
		{
			return $this->get_value($element_name);
		}
		else return false;
	}
	
	/**
	 * DefaultForm must be provided with a model
	 */
	function set_model(&$model)
	{
		$this->_model =& $model;
	}
	
	function &get_model()
	{
		return $this->_model;
	}
	
	/**
	 * Allow the view process actions to be set - this could potentially be called by a controller
	 */
	function set_process_actions($process_actions)
	{
		$this->process_actions = $process_actions;
	}
	
	function get_process_actions()
	{
		return (isset($this->process_actions)) ? $this->process_actions: false;
	}
	
	function get_custom_magic_transform_attributes()
	{
		return (isset($this->custom_magic_transform_attributes)) ? $this->custom_magic_transform_attributes : false;
	}
	
	function get_custom_magic_transform_methods()
	{
		return (isset($this->custom_magic_transform_methods)) ? $this->custom_magic_transform_methods : false;
	}
	
	function get_magic_transform_attributes()
	{
		return (isset($this->magic_transform_attributes)) ? $this->magic_transform_attributes : false;
	}
	
	function get_magic_transform_methods()
	{
		return (isset($this->magic_transform_methods)) ? $this->magic_transform_methods : false;
	}
	
	function get_submitted_data_hidden_fields_submitter_view()
	{
		return (isset($this->submitted_data_hidden_fields_submitter_view)) ? $this->submitted_data_hidden_fields_submitter_view : false;
	}
	
	function get_submitted_data_hidden_fields()
	{
		return (isset($this->submitted_data_hidden_fields)) ? $this->submitted_data_hidden_fields : false;
	}
	
	function get_show_clear_button()
	{
		return (isset($this->show_clear_button)) ? $this->show_clear_button : false;
	}
}
?>
