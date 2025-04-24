<?php

namespace TractorCow\AutoComplete;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Autocompleting text field, using jQuery.
 */
class AutoCompleteField extends TextField
{
    /**
     * The url to use as the live search source.
     */
    protected string $suggestURL = '';

    /**
     * Maximum number of search results to display per search.
     */
    protected int $limit = 10;

    /**
     * Minimum number of characters that a search will act on.
     */
    protected int $minSearchLength = 2;

    /**
     * Flag indicating whether a selection must be made from the existing list.
     *
     * By default this is true to ensure ID value is saved.
     */
    protected bool $requireSelection = true;

    /**
     * The field or method used to identify the results.
     */
    protected string $displayField = 'Title';

    /**
     * The field or method used for the display of the result in the listing.
     */
    protected string $labelField = 'Title';

    /**
     * The field to store in the database.
     */
    protected string $storedField = 'ID';

    /**
     * Indicate if results (when selected) should be populated underneath the text field instead of inside of the text field.
     */
    protected bool $populateSeparately = false;

    /**
     * Clears the search input field field when a selection has been made.
     *
     * NOTE: Only applies to when populating separately.
     *
     */
    protected bool $clearInput = true;

    private static array $allowed_actions = [
        'Suggest',
    ];

    /**
     * Name of the class this field searches.
     */
    private string $sourceClass;

    /**
     * Name of the field to use as a filter for searches and results.
     */
    private array $sourceFields;

    /**
     * Constant SQL condition used to filter out search results.
     */
    protected string $sourceFilter = '';

    /**
     * Constant SQL clause to sort results.
     */
    private string $sourceSort = 'ID ASC';

    /**
     * @param string $name the name of the field
     * @param null|string $title the title to use in the form
     * @param string $value the initial value of this field
     * @param null|string $sourceClass the suggestion source class
     * @param mixed $sourceFields the suggestion source fields
     */
    public function __construct($name, $title = null, $value = '', $sourceClass = null, $sourceFields = null)
    {
        // set source
        $this->sourceClass = $sourceClass;
        $this->sourceFields = is_array($sourceFields) ? $sourceFields : [$sourceFields];

        // construct the TextField
        parent::__construct($name, $title, $value);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $atts = array_merge(
            [
                'data-source' => $this->getSuggestURL(),
                'data-min-length' => $this->getMinSearchLength(),
                'data-require-selection' => $this->getRequireSelection(),
                'data-pop-separate' => $this->getPopulateSeparately(),
                'data-clear-input' => $this->getClearInput(),
                'autocomplete' => 'off',
                'name' => $this->getName() . '__autocomplete',
                'placeholder' => 'Search on ' . implode(' or ', $this->getSourceFields()),
            ],
            parent::getAttributes()
        );

        // Override the value so we start with a clear search form (depending on configuration).
        $atts['value'] = ($this->getPopulateSeparately() ? null : $this->getValue());

        return $atts;
    }

    /**
     * @return string
     */
    public function Type()
    {
        return 'autocomplete text';
    }

    /**
     * @param array $properties
     * @inheritDoc
     * @return string|DBHTMLText
     */
    public function Field($properties = [])
    {
        // jQuery Autocomplete Requirements
        // Requirements::css('silverstripe/admin:thirdparty/jquery-ui-themes/smoothness/jquery-ui.css');
        if (is_a(Controller::curr(), 'SilverStripe\\CMS\\Controllers\\ContentController')) {
            Requirements::javascript('silverstripe/admin:thirdparty/jquery-query/jquery.query.js');
            Requirements::javascript('silverstripe/admin:thirdparty/jquery-ui/jquery-ui.js');

            // Entwine requirements
            Requirements::javascript('silverstripe/admin:thirdparty/jquery-entwine/jquery.entwine.js');
        }

        // init script for this field
        Requirements::javascript('tractorcow/silverstripe-autocomplete:javascript/AutocompleteField.js');

        // styles for this field
        Requirements::css('tractorcow/silverstripe-autocomplete:css/AutocompleteField.css');

        return parent::Field($properties);
    }

    /**
     * Gets the readable value of the record, per $displayField.
     */
    public function getValue(): string
    {
        // try to fetch value from selected record
        $record = DataList::create($this->sourceClass)
            ->filter([
                $this->storedField => $this->dataValue(),
            ])
            ->first();

        if ($record) {
            return $record->{$this->displayField};
        }

        // if selection is not required, display the user provided value
        if (!$this->requireSelection && !empty($this->value)) {
            return $this->value;
        }

        return '';
    }

    /**
     * Set the class from which to get Autocomplete suggestions.
     *
     * @param string $className the name of the source class
     */
    public function setSourceClass(string $className): self
    {
        $this->sourceClass = $className;

        return $this;
    }

    /**
     * Get the class which is used for Autocomplete suggestions.
     *
     * @return string the name of the source class
     */
    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    /**
     * Set the field from which to get Autocomplete suggestions.
     *
     * @param array|string $fields the name of the source field
     *
     * @return static
     */
    public function setSourceFields(string|array $fields): self
    {
        $this->sourceFields = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * Get the field which is used for Autocomplete suggestions.
     */
    public function getSourceFields(): array|string
    {
        if (isset($this->sourceFields)) {
            return $this->sourceFields;
        }

        return [$this->getName()];
    }

    /**
     * Set the field or method that should label the results.
     */
    public function setDisplayField(string $field): self
    {
        $this->displayField = $field;

        return $this;
    }

    /**
     * Get the field or method that should label the results.
     *
     * @return string the name of the field
     */
    public function getDisplayField(): string
    {
        return $this->displayField;
    }

    /**
     * Set the field or method that should label the results.
     */
    public function setLabelField(string $field): self
    {
        $this->labelField = $field;

        return $this;
    }

    /**
     * Get the field or method that should label the results.
     *
     * @return string the name of the field
     */
    public function getLabelField(): string
    {
        return $this->labelField;
    }

    /**
     * Set the field that should store in the database.
     */
    public function setStoredField(string $field): self
    {
        $this->storedField = $field;

        return $this;
    }

    /**
     * Get the field that should store in the database.
     *
     * @return string the name of the field
     */
    public function getStoredField(): string
    {
        return $this->storedField;
    }

    /**
     * Set the filter used to get Autocomplete suggestions.
     *
     * @param string $filter the source filter
     */
    public function setSourceFilter(string $filter): self
    {
        $this->sourceFilter = $filter;

        return $this;
    }

    /**
     * Get the filter used for Autocomplete suggestions.
     */
    public function getSourceFilter(): string
    {
        return $this->sourceFilter;
    }

    /**
     * Set the sort used to get Autocomplete suggestions.
     *
     * @param string $sort the source sort
     */
    public function setSourceSort(string $sort): self
    {
        $this->sourceSort = $sort;

        return $this;
    }

    /**
     * Get the sort used for Autocomplete suggestions.
     */
    public function getSourceSort(): string
    {
        return $this->sourceSort;
    }

    /**
     * Set the URL used to fetch Autocomplete suggestions.
     *
     * @param string $url the URL used for suggestions
     */
    public function setSuggestURL(string $url): self
    {
        $this->suggestURL = $url;

        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setMinSearchLength(int $length): self
    {
        $this->minSearchLength = $length;

        return $this;
    }

    public function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }

    public function setRequireSelection(bool $requireSelection): self
    {
        $this->requireSelection = $requireSelection;

        return $this;
    }

    public function getRequireSelection(): bool
    {
        return $this->requireSelection;
    }

    /**
     * Get the URL used to fetch Autocomplete suggestions.
     *
     * Returns null if the built-in mechanism is used.
     *
     * @return string the URL used for suggestions
     */
    public function getSuggestURL(): string
    {
        if (!empty($this->suggestURL)) {
            return $this->suggestURL;
        }

        // Attempt to link back to itself
        return parse_url($this->Link(), PHP_URL_PATH) . '/Suggest';
    }

    public function setPopulateSeparately(bool $populateSeparately): self
    {
        $this->populateSeparately = $populateSeparately;

        return $this;
    }

    public function getPopulateSeparately(): bool
    {
        return $this->populateSeparately;
    }

    public function setClearInput(bool $clearInput): self
    {
        $this->clearInput = $clearInput;

        return $this;
    }

    public function getClearInput(): bool
    {
        return $this->clearInput;
    }

    /**
     * Handle a request for an Autocomplete list.
     *
     * @param HTTPRequest $request the request to handle
     *
     * @return string a JSON list of items for Autocomplete
     * @throws \JsonException
     */
    public function Suggest(HTTPRequest $request): string
    {
        // Find class to search within
        $sourceClass = $this->determineSourceClass();

        if (!$sourceClass) {
            return json_encode([], JSON_THROW_ON_ERROR);
        }

        // Find fields to search within
        $sourceFields = $this->getSourceFields();

        // input
        $q = $request->getVar('term');
        $limit = $this->getLimit();

        // Generate query
        $query = DataList::create($sourceClass)
            ->sort($this->sourceSort)
            ->limit($limit);

        // Fetch results that match all of the keywords across any of the source fields
        $keywords = preg_split('/[\s,]+/', $q);
        foreach ($keywords as $keyword) {
            $filters = [];
            foreach ($sourceFields as $sourceField) {
                $filters["{$sourceField}:PartialMatch"] = $keyword;
            }
            $query = $query->filterAny($filters);
        }

        if ($this->sourceFilter) {
            $query = $query->where($this->sourceFilter);
        }

        // generate items from result
        $items = [];

        foreach ($query as $record) {
            $items[$record->{$this->storedField}] = [
                'label' => $record->{$this->labelField},
                'value' => $record->{$this->displayField},
                'stored' => $record->{$this->storedField},
            ];
        }

        $items = array_values($items);

        // the response body
        return json_encode($items, JSON_THROW_ON_ERROR);
    }

    protected function determineSourceClass(): ?string
    {
        if ($sourceClass = $this->sourceClass) {
            return $sourceClass;
        }

        $form = $this->getForm();

        return $form?->getRecord()?->ClassName;
    }
}
