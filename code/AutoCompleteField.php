<?php

/**
 * Autocompleting text field, using jQuery.
 *
 * @package    forms
 * @subpackage fields-formattedinput
 */
class AutoCompleteField extends TextField {
	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'Suggest'
	);

	/**
	 * Name of the class this field searches.
	 *
	 * @var string
	 */
	private $sourceClass;

	/**
	 * Name of the field to use as a filter for searches and results.
	 *
	 * @var string
	 */
	private $sourceFields = array();


	/**
	 * Constant SQL condition used to filter out search results.
	 *
	 * @var string
	 */
	private $sourceFilter;

	/**
	 * Constant SQL clause to sort results.
	 *
	 * @var string
	 */
	private $sourceSort = 'ID ASC';

	/**
	 * The url to use as the live search source.
	 *
	 * @var string
	 */
	protected $suggestURL;

	/**
	 * Maximum number of search results to display per search.
	 *
	 * @var int
	 */
	protected $limit = 10;

	/**
	 * Minimum number of characters that a search will act on.
	 *
	 * @var int
	 */
	protected $minSearchLength = 2;

	/**
	 * Flag indicating whether a selection must be made from the existing list.
	 *
	 * By default this is true to ensure ID value is saved.
	 *
	 * @var bool
	 */
	protected $requireSelection = true;

	/**
	 * The field or method used to identify the results.
	 *
	 * @var string
	 */
	protected $displayField = 'Title';

	/**
	 * The field or method used for the display of the result in the listing
	 *
	 * @var string
	 */
	protected $labelField = 'Title';

	/**
	 * The field to store in the database.
	 *
	 * @var string
	 */
	protected $storedField = 'ID';

	/**
	 * @param string      $name         The name of the field.
	 * @param null|string $title        The title to use in the form.
	 * @param string      $value        The initial value of this field.
	 * @param null|int    $maxLength    Maximum number of characters.
	 * @param null|string $form
	 * @param null|string $sourceClass  The suggestion source class.
	 * @param mixed       $sourceFields The suggestion source fields.
	 */
	function __construct($name, $title = null, $value = '', $maxLength = null, $form = null, $sourceClass = null, $sourceFields = null) {
		// set source
		$this->sourceClass = $sourceClass;
		$this->sourceFields = is_array($sourceFields) ? $sourceFields : array($sourceFields);

		// construct the TextField
		parent::__construct($name, $title, $value, $maxLength, $form);
	}

	/**
	 * @return array
	 */
	function getAttributes() {
		return array_merge(
			parent::getAttributes(), array(
				'data-source' => $this->getSuggestURL(),
				'data-min-length' => $this->getMinSearchLength(),
				'data-require-selection' => $this->getRequireSelection(),
				'autocomplete' => 'off',
				'name' => $this->getName() . '__autocomplete'
			)
		);
	}

	/**
	 * @return string
	 */
	function Type() {
		return 'autocomplete text';
	}

	/**
	 * @param array $properties
	 *
	 * @return string
	 */
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
	 * Gets the readable value of the record, per $displayField.
	 *
	 * @return string
	 */
	public function Value() {
		$record = DataList::create($this->sourceClass)
			->filter(array(
				$this->storedField => $this->dataValue()
			))
			->first();

		return $record ? $record->{$this->displayField} : '';
	}

	/**
	 * Set the class from which to get Autocomplete suggestions.
	 *
	 * @param string $className The name of the source class.
	 *
	 * @return static
	 */
	public function setSourceClass($className) {
		$this->sourceClass = $className;

		return $this;
	}

	/**
	 * Get the class which is used for Autocomplete suggestions.
	 *
	 * @return string The name of the source class.
	 */
	public function getSourceClass() {
		return $this->sourceClass;
	}

	/**
	 * Set the field from which to get Autocomplete suggestions.
	 *
	 * @param array|string $fields The name of the source field.
	 *
	 * @return static
	 */
	public function setSourceFields($fields) {
		$this->sourceFields = is_array($fields) ? $fields : array($fields);

		return $this;
	}

	/**
	 * Get the field which is used for Autocomplete suggestions.
	 *
	 * @return array|string
	 */
	public function getSourceFields() {
		if(isset($this->sourceFields)) {
			return $this->sourceFields;
		}

		return array($this->getName());
	}


	/**
	 * Set the field or method that should label the results.
	 *
	 * @param string $field
	 *
	 * @return static
	 */
	public function setDisplayField($field) {
		$this->displayField = $field;

		return $this;
	}

	/**
	 * Get the field or method that should label the results.
	 *
	 * @return string The name of the field.
	 */
	public function getDisplayField() {
		return $this->displayField;
	}

	/**
	 * Set the field or method that should label the results.
	 *
	 * @param string $field
	 *
	 * @return static
	 */
	public function setLabelField($field) {
		$this->labelField = $field;

		return $this;
	}

	/**
	 * Get the field or method that should label the results.
	 *
	 * @return string The name of the field.
	 */
	public function getLabelField() {
		return $this->labelField;
	}


	/**
	 * Set the field that should store in the database.
	 *
	 * @param string $field
	 *
	 * @return static
	 */
	public function setStoredField($field) {
		$this->storedField = $field;

		return $this;
	}


	/**
	 * Get the field that should store in the database
	 *
	 * @return string The name of the field.
	 */
	public function getStoredField() {
		return $this->storedField;
	}


	/**
	 * Set the filter used to get Autocomplete suggestions.
	 *
	 * @param string $filter The source filter.
	 *
	 * @return static
	 */
	public function setSourceFilter($filter) {
		$this->sourceFilter = $filter;

		return $this;
	}

	/**
	 * Get the filter used for Autocomplete suggestions.
	 *
	 * @return string
	 */
	public function getSourceFilter() {
		return $this->sourceFilter;
	}


	/**
	 * Set the sort used to get Autocomplete suggestions.
	 *
	 * @param string $sort The source sort.
	 *
	 * @return static
	 */
	public function setSourceSort($sort) {
		$this->sourceSort = $sort;

		return $this;
	}


	/**
	 * Get the sort used for Autocomplete suggestions.
	 *
	 * @return string
	 */
	public function getSourceSort() {
		return $this->sourceSort;
	}

	/**
	 * Set the URL used to fetch Autocomplete suggestions.
	 *
	 * @param string $url The URL used for suggestions.
	 *
	 * @return static
	 */
	public function setSuggestURL($url) {
		$this->suggestURL = $url;

		return $this;
	}

	/**
	 * @param int $limit
	 *
	 * @return static
	 */
	public function setLimit($limit) {
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @param int $length
	 *
	 * @return static
	 */
	public function setMinSearchLength($length) {
		$this->minSearchLength = $length;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMinSearchLength() {
		return $this->minSearchLength;
	}

	/**
	 * @param bool $requireSelection
	 *
	 * @return static
	 */
	public function setRequireSelection($requireSelection) {
		$this->requireSelection = $requireSelection;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getRequireSelection() {
		return $this->requireSelection;
	}

	/**
	 * Get the URL used to fetch Autocomplete suggestions.
	 *
	 * Returns null if the built-in mechanism is used.
	 *
	 * @return string The URL used for suggestions.
	 */
	public function getSuggestURL() {
		if(!empty($this->suggestURL)) {
			return $this->suggestURL;
		}

		// Attempt to link back to itself
		return parse_url($this->Link(), PHP_URL_PATH) . '/Suggest';
	}

	/**
	 * @return null|string
	 */
	protected function determineSourceClass() {
		if($sourceClass = $this->sourceClass) {
			return $sourceClass;
		}

		$form = $this->getForm();

		if(!$form) {
			return null;
		}

		$record = $form->getRecord();

		if(!$record) {
			return null;
		}

		return $record->ClassName;
	}

	/**
	 * Handle a request for an Autocomplete list.
	 *
	 * @param SS_HTTPRequest $request The request to handle.
	 *
	 * @return string A JSON list of items for Autocomplete.
	 */
	public function Suggest(SS_HTTPRequest $request) {
		// Find class to search within
		$sourceClass = $this->determineSourceClass();

		if(!$sourceClass) {
			return;
		}

		// Find field to search within
		$sourceFields = $this->getSourceFields();

		// input
		$q = $request->getVar('term');
		$limit = $this->getLimit();

		$filters = array();

		foreach(preg_split('/[\s,]+/', $q) as $keyword) {
			foreach($sourceFields as $sourceField) {
				$filters["{$sourceField}:PartialMatch"] = $keyword;
			}
		}

		// Generate query
		$query = DataList::create($sourceClass)
			->filterAny($filters)
			->sort($this->sourceSort)
			->limit($limit);

		if($this->sourceFilter) {
			$query = $query->where($this->sourceFilter);
		}

		// generate items from result
		$items = array();

		foreach($query as $record) {
			$items[$record->{$this->storedField}] = array(
				'label' => $record->{$this->labelField},
				'value' => $record->{$this->displayField},
				'stored' => $record->{$this->storedField}
			);
		}

		$items = array_values($items);

		// the response body
		return json_encode($items);
	}
}
