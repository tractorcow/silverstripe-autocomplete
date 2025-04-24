<?php

namespace TractorCow\AutoComplete\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use TractorCow\AutoComplete\AutoCompleteField;

class AutoCompleteFieldTest extends SapphireTest
{
    public function testType(): void
    {
        $field = AutoCompleteField::create('Name', 'Title', null, File::class);

        $this->assertSame('autocomplete text', $field->Type());
    }

    public function testSettersAndGetters(): void
    {
        $field = AutoCompleteField::create('Name', 'Title', null, File::class, ['Name']);

        $this->assertSame(File::class, $field->getSourceClass());
        $field->setSourceClass('ChangeSourceClass');
        $this->assertSame('ChangeSourceClass', $field->getSourceClass());

        $this->assertSame(['Name'], $field->getSourceFields());
        $field->setSourceFields('ChangeSourceField');
        $this->assertSame(['ChangeSourceField'], $field->getSourceFields());

        $field->setDisplayField('Display');
        $this->assertSame('Display', $field->getDisplayField());

        $field->setLabelField('label');
        $this->assertSame('label', $field->getLabelField());

        $field->setStoredField('stored field');
        $this->assertSame('stored field', $field->getStoredField());

        $field->setSourceFilter('source filter');
        $this->assertSame('source filter', $field->getSourceFilter());

        $field->setSourceSort('source sort');
        $this->assertSame('source sort', $field->getSourceSort());

        $field->setLimit(3);
        $this->assertSame(3, $field->getLimit());

        $field->setMinSearchLength(5);
        $this->assertSame(5, $field->getMinSearchLength());

        $field->setRequireSelection(true);
        $this->assertTrue($field->getRequireSelection());

        $field->setSuggestURL('URL/suggestion');
        $this->assertSame('URL/suggestion', $field->getSuggestURL());

        $field->setPopulateSeparately(true);
        $this->assertTrue($field->getPopulateSeparately());

        $field->setClearInput(false);
        $this->assertFalse($field->getClearInput());
    }
}
