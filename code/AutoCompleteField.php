<?php

/**
 * Autocompleting text field, using jQuery
 * @package forms
 * @subpackage fields-formattedinput
 */
class AutoCompleteField extends TextField {

	private static $allowed_actions = array(
		'Suggest'
	);

	/**
	 * Name of the class this field searches
	 * @var string
	 */
	private $sourceClass;

	/**
	 * Name of the field to use as a filter for searches and results
	 * @var string
	 */
	private $sourceFields = array ();


	/**
	 * Constant SQL condition used to filter out search results
	 * @var string 
	 */
	private $sourceFilter;

	/**
	 * Constant SQL clause to sort results
	 * @var string
	 */
	private $sourceSort = "ID ASC";

	/**
	 * The url to use as the live search source
	 * @var string
	 */
	protected $suggestURL;

	/**
	 * Maximum numder of search results to display per search
	 * @var integer
	 */
	protected $limit = 10;

	/**
	 * Minimum number of characters that a search will act on
	 * @var integer
	 */
	protected $minSearchLength = 2;

	/**
	 * Flag indicating whether a selection must be made from the existing list.
	 * By default free text entry is allowed.
	 * @var boolean
	 */
	protected $requireSelection = false;

	/**
	 * The field or method used to identify the results
	 * @var string
	 */
	protected $displayField = "Title";

	/**
	 * The field to store in the database
	 * @var string
	 */
	protected $storedField = "ID";

	/**
	 * Create a new AutocompleteField. 
	 * 
	 * @param string $name The name of the field.
	 * @param string $title [optional] The title to use in the form.
	 * @param string $value [optional] The initial value of this field.
	 * @param int $maxLength [optional] Maximum number of characters.
	 * @param string $sourceClass [optional] The suggestion source class.
	 * @param string|array $sourceFields [optional] The suggestion source fields.
	 */
	function __construct($name, $title = null, $value = '', $maxLength = null, $form = null, $sourceClass = null, $sourceFields = null) {
		// set source
		$this->sourceClass = $sourceClass;
		$this->sourceFields = is_array($sourceFields) ? $sourceFields : array($sourceFields);

		// construct the TextField
		parent::__construct($name, $title, $value, $maxLength, $form);
	}

	function getAttributes() {
		return array_merge(
			parent::getAttributes(), array(
				'data-source' => $this->getSuggestURL(),
				'data-min-length' => $this->getMinSearchLength(),
				'data-require-selection' => $this->getRequireSelection(),
				'autocomplete' => 'off',
				'name' => $this->getName().'__autocomplete'
			)
		);
	}

	function Type() {
		return 'autocomplete text';
	}

	function Field($properties = array()) {

		// jQuery Autocomplete Requirements
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');

		// init script for this field
		Requirements::javascript(AUTOCOMPLETEFIELD_DIR . '/javascript/AutocompleteField.js');

		return parent::Field($properties);
	}

	/**
	 * Gets the readable value of the record, per $displayField
	 *
	 * @return  string
	 */
	public function Value() {
		$record = DataList::create($this->sourceClass)->filter(array(
			$this->storedField => $this->dataValue()
		))->first();

		return $record ? $record->{$this->displayField} : "";
	}

	/**
	 * Set the class from which to get Autocomplete suggestions.
	 * 
	 * @param string $className The name of the source class.
	 */
	public function setSourceClass(string $className) {
		$this->sourceClass = $className;

		return $this;
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
	public function setSourceFields($fields) {
		$this->sourceFields = is_array($fields) ? $fields : array($fields);

		return $this;
	}

	/**
	 * Get the field which is used for Autocomplete suggestions.
	 * 
	 * @return The name of the source field.
	 */
	public function getSourceFields() {
		if (isset($this->sourceFields))
			return $this->sourceFields;
		return array($this->getName());
	}


	/**
	 * Set the field or method that should label the results
	 * 
	 * @param string $field
	 */
	public function setDisplayField($field) {
		$this->displayField = $field;

		return $this;
	}

	/**
	 * Get the field or method that should label the results
	 * 
	 * @return The name of the field.
	 */
	public function getDisplayField() {
		return $this->displayField;
	}


	/**
	 * Set the field that should store in the database
	 * 
	 * @param string $field
	 */
	public function setStoredField($field) {
		$this->storedField = $field;

		return $this;
	}


	/**
	 * Get the field that should store in the database
	 * 
	 * @return The name of the field.
	 */
	public function getStoredField() {
		return $this->storedField;
	}


	/**
	 * Set the filter used to get Autocomplete suggestions.
	 * 
	 * @param string $filter The source filter.
	 */
	public function setSourceFilter(string $filter) {
		$this->sourceFilter = $filter;

		return $this;
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
	 * Set the sort used to get Autocomplete suggestions.
	 * 
	 * @param string $sort The source sort.
	 */
	public function setSourceSort(string $sort) {
		$this->sourceSort = $sort;

		return $this;
	}


	/**
	 * Get the sort used for Autocomplete suggestions.
	 * 
	 * @return The source sort.
	 */
	public function getSourceSort() {
		return $this->sourceSort;
	}

	/**
	 * Set the URL used to fetch Autocomplete suggestions.
	 * 
	 * @param string $url The URL used for suggestions.
	 */
	public function setSuggestURL($url) {
		$this->suggestURL = $url;

		return $this;
	}

	public function setLimit($limit) {
		$this->limit = $limit;

		return $this;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function setMinSearchLength($length) {
		$this->minSearchLength = $length;

		return $this;
	}

	public function getMinSearchLength() {
		return $this->minSearchLength;
	}

	public function setRequireSelection($requireSelection) {
		$this->requireSelection = $requireSelection;

		return $this;
	}

	public function getRequireSelection() {
		return $this->requireSelection;
	}

	/**
	 * Get the URL used to fetch Autocomplete suggestions. Returns null
	 * if the built-in mechanism is used.
	 *  
	 * @return The URL used for suggestions.
	 */
	public function getSuggestURL() {

		if (!empty($this->suggestURL))
			return $this->suggestURL;

		// Attempt to link back to itself
		return parse_url($this->Link(), PHP_URL_PATH) . '/Suggest';
	}

	protected function determineSourceClass() {
		if ($sourceClass = $this->sourceClass)
			return $sourceClass;

		$form = $this->getForm();
		if (!$form)
			return null;

		$record = $form->getRecord();
		if (!$record)
			return null;

		return $record->ClassName;
	}

	/**
	 * Handle a request for an Autocomplete list.
	 * 
	 * @param HTTPRequest $request The request to handle.
	 * @return A list of items for Autocomplete.
	 */
	function Suggest(HTTPRequest $request) {
		// Find class to search within
		$sourceClass = $this->determineSourceClass();
		if (!$sourceClass)
			return;

		// Find field to search within
		$sourceFields = $this->getSourceFields();

		// input
		$q = $request->getVar('term');
		$limit = $this->getLimit();

		$filters = array ();
		foreach(preg_split('/[\s,]+/', $q) as $keyword) {
			foreach($sourceFields as $sourceField) {
				$filters["$sourceField:PartialMatch"] = $keyword;
			}
		}

		// Generate query
		$query = DataList::create($sourceClass)
				->filterAny($filters)
				->sort($this->sourceSort)
				->limit($limit);

		if ($this->sourceFilter) {
			$query = $query->where($this->sourceFilter);
		}

		// generate items from result
		$items = array ();
		foreach($query as $record) {
			$items[] = array (
				'label' => $record->{$this->displayField},
				'value' => $record->{$this->displayField},
				'stored' => $record->{$this->storedField}
			);
		}
		// the response body
		return json_encode($items);
	}

}
