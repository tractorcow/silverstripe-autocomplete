<?php

/**
 * Autocompleting text field, using jQuery
 * @package forms
 * @subpackage fields-formattedinput
 */
class AutoCompleteField extends TextField
{

    private $sourceClass;
    private $sourceField;
    private $sourceFilter;
    protected $suggestURL;

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
    function __construct($name, $title = null, $value = '', $maxLength = null, $sourceClass = null, $sourceField = null, $sourceFilter = null)
    {
        // set source
        $this->sourceClass = $sourceClass;
        $this->sourceField = $sourceField;

        // construct the TextField
        parent::__construct($name, $title, $value, $maxLength);
    }

    /**
     * Return the field.
     * 
     * @return The field tag.
     */
    function Field()
    {
        Requirements::javascript(THIRDPARTY_DIR . "/prototype.js");
        Requirements::javascript(THIRDPARTY_DIR . "/behaviour.js");

        // jQuery Autocomplete Requirements
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery_improvements.js');
        Requirements::javascript('formfields/javascript/jquery-autocomplete/jquery.autocomplete.js');
        Requirements::css('formfields/javascript/jquery-autocomplete/jquery.autocomplete.css');

        // init script for this field
        Requirements::javascript('formfields/javascript/AutocompleteField.js');

        // the suggestions source
        $src = $this->suggestURL;
        if (!$src)
            $src = parse_url($this->Link(), PHP_URL_PATH) . '/Suggest';

        // field attributes, notice the src attribute
        $attributes = array(
            'type' => 'text',
            'class' => 'text AutocompleteField' . ($this->extraClass()
                    ? $this->extraClass()
                    : ''),
            'id' => $this->id(),
            'name' => $this->Name(),
            'value' => $this->Value(),
            'tabindex' => $this->getTabIndex(),
            'maxlength' => ($this->maxLength)
                    ? $this->maxLength
                    : null,
            'size' => ($this->maxLength)
                    ? min($this->maxLength, 30)
                    : null,
            'autocomplete' => 'off',
            'src' => $src
        );

        if ($this->disabled)
            $attributes['disabled'] = 'disabled';

        // create tag
        return $this->createTag('input', $attributes);
    }

    /**
     * Set the class from which to get Autocomplete suggestions.
     * 
     * @param string $className The name of the source class.
     */
    public function setSourceClass(string $className)
    {
        $this->sourceClass = $className;
    }

    /**
     * Get the class which is used for Autocomplete suggestions.
     * 
     * @return The name of the source class. 
     */
    public function getSourceClass()
    {
        return $this->sourceClass;
    }

    /**
     * Set the field from which to get Autocomplete suggestions.
     * 
     * @param string $fieldName The name of the source field.
     */
    public function setSourceField(string $fieldName)
    {
        $this->sourceField = $fieldName;
    }

    /**
     * Get the field which is used for Autocomplete suggestions.
     * 
     * @return The name of the source field.
     */
    public function getSourceField()
    {
        return $this->sourceField;
    }

    /**
     * Set the filter used to get Autocomplete suggestions.
     * 
     * @param string $filter The source filter.
     */
    public function setSourceFilter(string $filter)
    {
        $this->sourceFilter = $filter;
    }

    /**
     * Get the filter used for Autocomplete suggestions.
     * 
     * @return The source filter.
     */
    public function getSourceFilter()
    {
        return $this->sourceFilter;
    }

    /**
     * Set the URL used to fetch Autocomplete suggestions.
     * 
     * @param string $URL The URL used for suggestions.
     */
    public function setSuggestURL(string $URL)
    {
        $this->suggestURL = $url;
    }

    /**
     * Get the URL used to fetch Autocomplete suggestions. Returns null
     * if the built-in mechanism is used.
     *  
     * @return The URL used for suggestions.
     */
    public function getSuggestURL()
    {
        return $this->suggestURL;
    }

    /**
     * Handle a request for an Autocomplete list.
     * 
     * @param HTTPRequest $request The request to handle.
     * @return A list of items for Autocomplete.
     */
    function Suggest(HTTPRequest $request)
    {
        // source
        $sourceClass = $this->sourceClass;
        if (!$sourceClass)
        {
            $form = $this->getForm();
            if (!$form)
                return;
            $record = $form->getRecord();
            if (!$record)
                return;
            $sourceClass = $record->ClassName;
            if (!$sourceClass)
                return;
        }
        $sourceDataClass = ClassInfo::baseDataClass($sourceClass);
        $sourceField = isset($this->sourceField)
                ? $this->sourceField
                : $this->Name();
        $sourceFilter = isset($this->sourceFilter)
                ? $this->sourceFilter
                : '1';

        // input
        $q = Convert::raw2sql($request->getVar('q'));
        $limit = Convert::raw2sql($request->getVar('limit'));

        // query
        $query = new SQLQuery();
        $query->select = array(
            "`{$sourceField}`"
        );
        $query->from = array(
            "`{$sourceDataClass}`"
        );
        $query->where = array(
            "`{$sourceField}` LIKE '%{$q}%'",
            $sourceFilter
        );
        $query->orderby = "`{$sourceField}` ASC";
        $query->limit = "{$limit}";
        $query->distinct = true;
        
        // execute
        $result = $query->execute();

        // generate items from result
        $items = array();
        foreach ($result as $row)
            $items[] = $row[$sourceField];

        // the response body
        $body = join("\n", $items);

        // build response

        // return set
        return $body;
    }

}