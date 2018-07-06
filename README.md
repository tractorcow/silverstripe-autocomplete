silverstripe-autocomplete
=========================

Autocomplete text field for Silverstripe

usage
=====

A field can be created as follows...
```php
\TractorCow\AutoComplete\AutoCompleteField::create('MyTextField','My Text Field','',null,null,'LookupDataObject','LookupFieldName')
```
where it will accept values from the following dataobject field...

```php
class LookupDataObject extends DataObject {
	static $db = array(
		'LookupFieldName'				=> 'Varchar',
	);
}
```
