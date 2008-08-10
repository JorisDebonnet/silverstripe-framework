<?php
/**
 * Represents a field in a form.  
 * A FieldSet contains a number of FormField objects which make up the whole of a form.
 * In addition to single fields, FormField objects can be "composite", for example, the {@link TabSet}
 * field.  Composite fields let us define complex forms without having to resort to custom HTML.
 * @package forms
 * @subpackage core
 */
class FormField extends RequestHandlingData {
	protected $form;
	protected $name, $title, $value ,$message, $messageType, $extraClass;
	
	/**
	 * @var $description string Adds a "title"-attribute to the markup.
	 * @todo Implement in all subclasses
	 */
	protected $description;
	
	/**
	 * @var $extraClasses array Extra CSS-classes for the formfield-container
	 */
	protected $extraClasses;
	
	public $dontEscape;
	
	/**
	 * @var $rightTitle string Used in SmallFieldHolder() to force a right-aligned label.
	 */
	protected $rightTitle;
	
	/**
	 * @var $leftTitle string Used in SmallFieldHolder() to force a left-aligned label with correct spacing.
	 * Please use $title for FormFields rendered with DefaultFieldHolder.
	 */
	protected $leftTitle;
	
	/**
	 * Set the "tabindex" HTML attribute on the field.
	 *
	 * @var int
	 */
	protected $tabIndex;
	
	/**
	 * Create a new field.
	 * @param name The internal field name, passed to forms.
	 * @param title The field label.
	 * @param value The value of the field.
	 * @param form Reference to the container form
	 * @param maxLength The Maximum length of the attribute
	 */
	function __construct($name, $title = null, $value = null, $form = null, $rightTitle = null) {
		$this->name = $name;
		$this->title = ($title === null) ? $name : $title;

		if(isset($value)) $this->value = $value;
		if($form) $this->setForm($form);

		parent::__construct();
	}
	
	/**
	 * Return a Link to this field
	 */
	function Link() {
		return $this->form->FormAction() . '/field/' . $this->name;
	}
	
	/**
	 * Returns the HTML ID of the field - used in the template by label tags.
	 * The ID is generated as FormName_FieldName.  All Field functions should ensure
	 * that this ID is included in the field.
	 */
	function id() { 
		$name = ereg_replace('(^-)|(-$)','',ereg_replace('[^A-Za-z0-9_-]+','-',$this->name));
		if($this->form) return $this->form->FormName() . '_' . $name;
		else return $name;
	}
	
	/**
	 * Returns the field name - used by templates.
	 */
	function Name() {
		return $this->name;
	}
	
	function attrName() {
		return $this->name;
	}
	
	/** 
	 * Returns the field message, used by form validation
	 */
	function Message(){
		return $this->message;
	} 
	
	/** 
	 * Returns the field message type, used by form validation
	 */
	function MessageType(){
		return $this->messageType;
	} 
	
	/**
	 * Returns the field value - used by templates.
	 */
	function Value() {
		return $this->value;
	}
	
	/**
	 * Method to save this form field into the given data object.
	 * By default, makes use of $this->dataValue()
	 */
	function saveInto(DataObjectInterface $record) {
		if($this->name) {
			$record->setCastedField($this->name, $this->dataValue());
		}
	}
	
	/**
	 * Returns the field value suitable for insertion into the data object
	 */
	function dataValue() { 
		return $this->value;
	}
	
	/**
	 * Returns the field label - used by templates.
	 */
	function Title() { 
		return $this->title;
	}
	
	function setTitle($val) { 
		$this->title = $val;
	}
	
	function RightTitle() {
		return $this->rightTitle;
	}
	
	function setRightTitle($val) { 
		$this->rightTitle = $val;
	}

	function LeftTitle() {
		return $this->leftTitle;
	}
	
	function setLeftTitle($val) { 
		$this->leftTitle = $val;
	}
	
	/**
	 * Set tabindex HTML attribute
	 * (defaults to none).
	 *
	 * @param int $index
	 */
	public function setTabIndex($index) {
		$this->tabIndex = $index;
	}
	
	/**
	 * Get tabindex (if previously set)
	 *
	 * @return int
	 */
	public function getTabIndex() {
		return $this->tabIndex;
	}

	/**
	 * Get tabindex HTML string
	 *
	 * @param int $increment Increase current tabindex by this value
	 * @return string
	 */
	protected function getTabIndexHTML($increment = 0) {
		$tabIndex = (int)$this->getTabIndex() + (int)$increment;
		return (is_numeric($tabIndex)) ? ' tabindex = "' . $tabIndex . '"' : '';
	}
	
	/**
	 * Compiles all CSS-classes. Optionally includes a "nolabel"-class
	 * if no title was set on the formfield.
	 * 
	 * @return String CSS-classnames
	 */
	function extraClass() {
		$output = "";
		if(is_array($this->extraClasses)) {
			$output = " " . implode($this->extraClasses, " ");
		}
		if(!$this->Title()) $output .= " nolabel";
		
		return $output;
	}
	
	/**
	 * Add a CSS-class to the formfield-container.
	 * 
	 * @param $class String
	 */
	function addExtraClass($class) {
		$this->extraClasses[$class] = $class;
	}

	/**
	 * Remove a CSS-class from the formfield-container.
	 * 
	 * @param $class String
	 */
	function removeExtraClass($class) {
		if(array_key_exists($class, $this->extraClasses)) unset($this->extraClasses[$class]);
	}

	/**
	 * Returns a version of a title suitable for insertion into an HTML attribute
	 */
	function attrTitle() {
		return Convert::raw2att($this->title);
	}
	/**
	 * Returns a version of a title suitable for insertion into an HTML attribute
	 */
	function attrValue() {
		return Convert::raw2att($this->value);
	}
	
	/**
	 * Set the field value.
	 * Returns $this.
	 */
	function setValue($value) { $this->value = $value; return $this; }
	
	/**
	 * Set the field name
	 */
	function setName($name) { $this->name = $name; }
	
	
	/**
	 * Set the container form.
	 * This is called whenever you create a new form and put fields inside it, so that you don't
	 * have to worry about linking the two.
	 */
	function setForm($form) {
		$this->form = $form; 
	}
	
	/**
	 * Get the currently used form.
	 *
	 * @return Form
	 */
	function getForm() {
		return $this->form; 
	}
	
	/**
	 * Sets the error message to be displayed on the form field
	 * Set by php validation of the form
	 */
	function setError($message,$messageType){
		$this->message = $message; 
		$this->messageType = $messageType; 
	}
	
	/**
	 * Returns the form field - used by templates.
	 * Although FieldHolder is generally what is inserted into templates, all of the field holder
	 * templates make use of $Field.  It's expected that FieldHolder will give you the "complete"
	 * representation of the field on the form, whereas Field will give you the core editing widget,
	 * such as an input tag.
	 * 
	 * Our base FormField class just returns a span containing the value.  This should be overridden!
	 */
	function Field() {
		if($this->value) $val = $this->dontEscape ? ($this->reserveNL?Convert::raw2xml($this->value):$this->value) : Convert::raw2xml($this->value);
		else $val = '<i>('._t('FormField.NONE', 'none').')</i>';
		$valforInput = $this->value ? Convert::raw2att($val) : "";
		return "<span class=\"readonly\" id=\"" . $this->id() . "\">$val</span>\n<input type=\"hidden\" name=\"".$this->name."\" value=\"".$valforInput."\"" . $this->getTabIndexHTML() . " />";
	}
	/**
	 * Returns a "Field Holder" for this field - used by templates.
	 * Forms are constructed from by concatenating a number of these field holders.  The default
	 * field holder is a label and form field inside a paragraph tag.
	 * 
	 * Composite fields can override FieldHolder to create whatever visual effects you like.  It's
	 * a good idea to put the actual HTML for field holders into templates.  The default field holder
	 * is the DefaultFieldHolder template.  This lets you override the HTML for specific sites, if it's
	 * necessary.
	 * 
	 * @todo Add "validationError" if needed.
	 */
	function FieldHolder() {
		$Title = $this->XML_val('Title');
		$Message = $this->XML_val('Message');
		$MessageType = $this->XML_val('MessageType');
		$RightTitle = $this->XML_val('RightTitle');
		$Type = $this->XML_val('Type');
		$extraClass = $this->XML_val('extraClass');
		$Name = $this->XML_val('Name');
		$Field = $this->XML_val('Field');
		
		$titleBlock = (!empty($Title)) ? "<label class=\"left\" for=\"{$this->id()}\">$Title</label>" : "";
		$messageBlock = (!empty($Message)) ? "<span class=\"message $MessageType\">$Message</span>" : "";
		$rightTitleBlock = (!empty($RightTitle)) ? "<label class=\"right\" for=\"{$this->id()}\">$RightTitle</label>" : "";

		return <<<HTML
<div id="$Name" class="field $Type $extraClass">$titleBlock<div class="middleColumn">$Field</div>$rightTitleBlock$messageBlock</div>
HTML;
	}

	/**
	 * Returns a restricted field holder used within things like FieldGroups.
	 */
	function SmallFieldHolder() {
		$result = '';
		// set label
		if($title = $this->RightTitle()){
			$result .= "<label class=\"right\" for=\"" . $this->id() . "\">{$title}</label>\n";
		} elseif($title = $this->LeftTitle()) {
			$result .= "<label class=\"left\" for=\"" . $this->id() . "\">{$title}</label>\n";
		} elseif($title = $this->Title()) {
			$result .= "<label for=\"" . $this->id() . "\">{$title}</label>\n";
		}
		
		$result .= $this->Field();
		
		return $result;
	}

	
	/**
	 * Returns true if this field is a composite field.
	 * To create composite field types, you should subclass {@link CompositeField}.
	 */
	function isComposite() { return false; }
	
	/**
	 * Returns true if this field has its own data.
	 * Some fields, such as titles and composite fields, don't actually have any data.  It doesn't
	 * make sense for data-focused methods to look at them.  By overloading hasData() to return false,
	 * you can prevent any data-focused methods from looking at it.
	 *
	 * @see FieldSet::collateDataFields()
	 */
	function hasData() { return true; }

	function isReadonly() { 
		return !in_array($this->class, array("ReadonlyField","FormField","LookupField")); 
	}
	
	/**
	 * Returns a readonly version of this field
	 */
	function performReadonlyTransformation() {
		$field = new ReadonlyField($this->name, $this->title, $this->value);
		$field->setForm($this->form);
		return $field;
	}
	
	/**
	 * Return a disabled version of this field
	 */
	function performDisabledTransformation() {
		$disabledClassName = $this->class . '_Disabled';
		if( ClassInfo::exists( $disabledClassName ) )
			return new $disabledClassName( $this->name, $this->title, $this->value );
		elseif($this->hasMethod('setDisabled')){
			$this->setDisabled(true);
			return $this;
		}else{
			return $this->performReadonlyTransformation();
		}
	}
	
	function transform(FormTransformation $trans) {
		return $trans->transform($this);
	}
	
	function hasClass($class){
		$patten = '/'.strtolower($class).'/i';
		$subject = strtolower($this->class." ".$this->extraClass());
		return preg_match($patten, $subject);
	}
	
	/**
	 * Returns the field type - used by templates.
	 * The field type is the class name with the word Field dropped off the end, all lowercase.
	 * It's handy for assigning HTML classes.
	 */
	function Type() {return strtolower(ereg_replace('Field$','',$this->class)); }
	
	/**
	 * Construct and return HTML tag
	 */
	function createTag($tag, $attributes, $content = null) {
		$preparedAttributes = '';
		foreach($attributes as $k => $v) {
			if(!empty($v)) $preparedAttributes .= " $k=\"" . Convert::raw2att($v) . "\"";
		}

		if($content) return "<$tag$preparedAttributes>$content</$tag>";
		else return "<$tag$preparedAttributes />";
	}
	
	/**
	 * javascript handler Functions for each field type by default
	 * formfield doesnt have a validation function
	 * 
	 * @todo shouldn't this be an abstract method?
	 */
	function jsValidation() {}
	
	/**
	 * Validation Functions for each field type by default
	 * formfield doesnt have a validation function
	 * 
	 * @todo shouldn't this be an abstract method?
	 */
	function validate(){return true;}

	/**
	 * Describe this field, provide help text for it.
	 * The function returns this so it can be used like this:
	 * $action = FormAction::create('submit', 'Submit')->describe("Send your changes to be approved")
	 */
	function describe($description) {
		$this->description = $description;
		return $this;
	}
	
	function debug() {
		return "$this->class ($this->name: $this->title : <font style='color:red;'>$this->message</font>) = $this->value";
	}
	
	/**
	 * This function is used by the template processor.  If you refer to a field as a $ variable, it
	 * will return the $Field value.
	 */
	function forTemplate() {
		return $this->Field();
	}
	
	function Required() {
		if($this->form && ($validator = $this->form->Validator)) {
			return $validator->fieldIsRequired($this->name);
		}
	}
	
	// ###################
	// DEPRECATED
	// ###################
	
	/**
	 * @deprecated please use addExtraClass
	 */
	function setExtraClass($extraClass) {
		user_error('FormField::setExtraClass() is deprecated. Use FormField::addExtraClass() instead.', E_USER_NOTICE);
		$this->extraClasses[] = $extraClass;
	}
}

?>
