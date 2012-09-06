<?php

/**
 * Autocompleting text field, using jQuery
 * @package forms
 * @subpackage fields-formattedinput
 */
class AutoCompleteField extends TextField {
	private $sourceClass;

	private $sourceField;

	private $sourceFilter;

	protected $suggestURL;

	protected $limit = 10;

	protected $minSearchLength = 2;

	/**
	 * Create a new AutocompleteField. 
	 * 
	 * @param string $name The name of the field.
	 * @param string $title [optional] The title to use in the form.
	 * @param string $value [optional] The initial value of this field.
	 * @param int $maxLength [optional] Maximum number of characters.
	 * @param string $sourceClass [optional] The suggestion source class.
	 * @param string $sourceField [optional] The suggestion source field.
	 * @param string $sourceFilter [optional] The suggestion source filter.
	 */
	function __construct($name, $title = null, $value = '', $maxLength = null, $form = null, $sourceClass = null, $sourceField = null, $sourceFilter = null) {
		// set source
		$this->sourceClass = $sourceClass;
		$this->sourceField = $sourceField;
		$this->sourceFilter = $sourceFilter;

		// construct the TextField
		parent::__construct($name, $title, $value, $maxLength, $form);
	}

	function getAttributes() {
		return array_merge(
			parent::getAttributes(), 
			array(
				'data-source' => $this->getSuggestURL(),
				'data-min-length' => $this->getMinSearchLength(),
				'autocomplete' => 'off'
			)
		);
	}

	function Type() {
		return 'autocomplete text';
	}

	function Field($properties = array()) {

		// jQuery Autocomplete Requirements
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.min.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.min.js');

		// init script for this field
		Requirements::javascript(AUTOCOMPLETEFIELD_DIR . '/javascript/AutocompleteField.js');

		return parent::Field($properties);
	}

	/**
	 * Set the class from which to get Autocomplete suggestions.
	 * 
	 * @param string $className The name of the source class.
	 */
	public function setSourceClass(string $className) {
		$this->sourceClass = $className;
	}

	/**
	 * Get the class which is used for Autocomplete suggestions.
	 * 
	 * @return The name of the source class. 
	 */
	public function getSourceClass() {
		return $this->sourceClass;
	}

	/**
	 * Set the field from which to get Autocomplete suggestions.
	 * 
	 * @param string $fieldName The name of the source field.
	 */
	public function setSourceField(string $fieldName) {
		$this->sourceField = $fieldName;
	}

	/**
	 * Get the field which is used for Autocomplete suggestions.
	 * 
	 * @return The name of the source field.
	 */
	public function getSourceField() {
		return $this->sourceField;
	}

	/**
	 * Set the filter used to get Autocomplete suggestions.
	 * 
	 * @param string $filter The source filter.
	 */
	public function setSourceFilter(string $filter) {
		$this->sourceFilter = $filter;
	}

	/**
	 * Get the filter used for Autocomplete suggestions.
	 * 
	 * @return The source filter.
	 */
	public function getSourceFilter() {
		return $this->sourceFilter;
	}

	/**
	 * Set the URL used to fetch Autocomplete suggestions.
	 * 
	 * @param string $URL The URL used for suggestions.
	 */
	public function setSuggestURL($URL) {
		$this->suggestURL = $url;
	}

	public function setLimit($limit) {
		$this->limit = $limit;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function setMinSearchLength($length) {
		$this->minSearchLength = $length;
	}

	public function getMinSearchLength() {
		return $this->minSearchLength;
	}

	/**
	 * Get the URL used to fetch Autocomplete suggestions. Returns null
	 * if the built-in mechanism is used.
	 *  
	 * @return The URL used for suggestions.
	 */
	public function getSuggestURL() {
		
		if(!empty($this->suggestURL)) return $this->suggestURL;
		
		// Attempt to link back to itself
		return parse_url($this->Link(), PHP_URL_PATH) . '/Suggest';
	}

	protected function determineSourceClass() {
		if ($sourceClass = $this->sourceClass)
			return $sourceClass;

		$form = $this->getForm();
		if (!$form) return null;

		$record = $form->getRecord();
		if (!$record) return null;

		return $record->ClassName;
	}

	/**
	 * Handle a request for an Autocomplete list.
	 * 
	 * @param HTTPRequest $request The request to handle.
	 * @return A list of items for Autocomplete.
	 */
	function Suggest(HTTPRequest $request) {
		// source
		$sourceClass = $this->determineSourceClass();
		if (!$sourceClass)
			return;
		$sourceDataClass = ClassInfo::baseDataClass($sourceClass);

		$sourceField = isset($this->sourceField)
				? $this->sourceField
				: $this->getName();

		// input
		$q = Convert::raw2sql($request->getVar('term'));
		$limit = $this->getLimit();

		// query
		$query = new SQLQuery();
		$query
				->setSelect($sourceField)
				->addFrom($sourceDataClass)
				->addWhere("\"{$sourceField}\" LIKE '%{$q}%'")
				->addOrderBy($sourceField)
				->setLimit($limit)
				->setDistinct(true);
		if (isset($this->sourceFilter))
			$query->addWhere($this->sourceFilter);

		// execute
		$result = $query->execute();

		// generate items from result
		$items = array();
		foreach ($result as $row)
			$items[] = $row[$sourceField];

		// the response body
		return json_encode($items);
	}

}