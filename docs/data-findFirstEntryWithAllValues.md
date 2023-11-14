---
layout: default
permalink: developers/API/classes/data_handler/findFirstEntryWithAllValues/
---

# findFirstEntryWithAllValues( <span style='font-size: 14pt;'>(array) $elementsAndValues, (string) $operator = "="</span> )

## Description

Gets the first entry id which matches all of the values specified for the corresponding elements specified. Values must match the raw value stored in the database, and won't necessarily be human readable (could be an id number, etc).

## Parameters

__$elementsAndValues__ - an array of key=>value pairs, where the keys are the element identifiers and the values are the values to look for. Only entries that match every pair will be returned.<br>
__$operator__ -  Optional.  the operator to use in when querying for the values. The same operator is used for all key=>value pairs. Defaults to equals. Any valid SQL operator can be used. If LIKE is used, _then the values will be automatically wrapped in % signs_ to support pattern matching.

## Return Values

Returns the __first (earliest) entry id found__.

Returns __false__ if the query fails, or if the query finds no entries that match the values.

## Example

~~~
// find the first entry created that has 'blue' as the value for the 'colour' element,
// and 'hot' as the value for the 'temperature' element, in form 6
$form_id = 6;
$dataHandler = new formulizeDataHandler($form_id);
$entry_id = $dataHandler->findFirstEntryWithAllValues(array(
    'colour'=>'blue',
    'temperature'=>'hot'
));
~~~

~~~
// find the first entry created where the value for element 33 contains 'foo'
// and the value for element 99 contains 'bar', in form 6
$form_id = 6;
$dataHandler = new formulizeDataHandler($form_id);
$values = array(
    33=>'foo',
    99=>'bar'
);
$entry_id = $dataHandler->findFirstEntryWithAllValues($values, "LIKE");
~~~