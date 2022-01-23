# WordPress FileMaker Data API integration 
A WordPress plugin to integrate with the FileMaker Data API to allow data to be pulled from FileMaker and displayed easily in your WordPress site.

The primary means of 'pulling' data is through the use of two WordPress shortcodes. It's also possible to use a filter to call a script.

## Installation
Download, copy to your plugins directory, enable, configure and you're ready to go.

## Shortcodes
The two shortcodes listed below provide access to your FileMaker data.

### [FM-DATA-TABLE]
Pull all records from the specified layout and generate a table of records.

| Parameter  | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | Required |
|------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|
| layout     | Specify the layout which data should be pulled from                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |true|
| fields     | A list of the fields which should be included in the table.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | separated  (see example below) |true|
| labels     | The labels to use for the column headers. If omitted then the field names (as above) are used instead.                                                                                                                                                                                                                                                                                                                                                                                                                                                                     | separated|false|
| types      | The type of each field. Currently supported are <ul><li>Currency - displays a number in the selected currency (see the settings screen for locale selection)</li><li>Image, which can optionally be follwed by a hypehen and an integer value (e.g. Image-100) which will set the width to 100px (defaults to full size)</li><li>Thumbnail - as for image, however defaults to 50px</li><li>WebLink - the content of the field will be used as the hyperlink, the name of the field as the text.</li><li>`null` - outputs the content of the field</li></ul>               | false| 
| class      | A CSS class to be added to the markup of the generated table to help you with styling your table.                                                                                                                                                                                                                                                                                                                                                                                                                                                                          | false |
| id-field   | Which field on the layout acts as a primary key for the given layout                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |false|
| detail-url | If both this and the id-field are set then the content of the cells is converted to a link to the URL. You must provide the location in the URL which the value of id-field will be embded in using `*id*` e.g. `detail-url="/product/?id=*id*"`                                                                                                                                                                                                                                                                                                                           |false|
| query      | The JSON encoded query to apply to the records selected in the form `'field': '{ operator } value'`.<br><br>It is simplest to use single quotes for the JSON object, which will be transposed prior to submission to FM. e.g. `query="{'Unit Price': '&lt;500', 'Availability': 'In stock'}"`.<br><br>Note that depending on the exact Wordpress editor you're using then less than, and greater than signs may be html encoded. Again, the parser will cope with that. Also be aware that you're performing an `AND` query if you specify more than one key / value pair. |false|
| sort       | A JSON encoded set of fields and orders to sort by in the form `'field': 'order'`.<br><br>As with the query above it's simplest to use single quotes for the JSON object, which will be transposed prior to submission to FM. e.g. `sort="{'Unit Price': 'ascend', 'Availability': 'descend'}"`.<br><br>As in the example above use the keywords `ascend` and `descend` to specify order.                                                                                                                                                                                  |false|

Example
```
 [FM-DATA-TABLE layout="Product Details" fields="Image|Part Number|Name|Unit Price|Category|Availability" types="Thumbnail-50|||Currency||" id-field="Part Number" detail-url="/product/?id=*id*" query="{'Unit Price': '&lt;500', 'Availability': 'In stock'}"]
```

### [FM-DATA-FIELD]
Display a single field value.

| Parameter | Description | Required |
|---|---|---|
|layout|Specify the layout which data should be pulled from|true|
|id-field|The field which contains the UUID to locate the correct record|true|
|id|The ID to search for in the above field. Either a static value, or this can be a special value `URL-xxx` in which case the query parameter `xxx` will be used. In the detail-url example above this would correspond to `id`, so it would be `URL-id`|true|
|field|The field to display|true|
|type|The type of output - options are the same as for the 'types' above.|false|

Examples
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="URL-id" field="Image" type="Image-200"]
```
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="23456" field="Price" type="Currency"]
```
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="URL-id" field="Stock Level"]
```

## Filter
If you need to run a FileMaker script from within your WordPress site a filter is provided to support this.

```
$param = new \FMDataAPI\ScriptParameter('My Layout', 'My Script', json_encode(['foo' => 'bah']));
$result = apply_filters('fm-call-script', $param);
```
The `ScriptParameter` object takes three parameters:

| Parameter | Description | Required |
|---|---|---|
| layout | The base layout to being from. Clearly your script is then able to change layouts, however this will be the initial context. | true | 
| script | The name of the script to call. | true |
| parameter | Any parameters to pass to the script. This must be a string, so if you need to pass complex data make sure that you JSON encode it first. Note that because the script parameter is passed to the FileMaker Data API as a query parameter this is a limit to how much data you can include. This is approx. 2,400 characters in total. | false |

In the example above `$result` will contain the result from the script. The exact format will depend on what the script returns. If you want to pass data make sure you use `Exit Script` with the desired data.


<table>
<tr>
<th>Response</th>
<th>Details</th>
</tr>

<tr>
<td><code>null</code></td>
<td>An error occurred in calling the script. This may be caused by permissions errors, connectivity errors, or an error in the script itself.</td>
</tr>

<tr>
<td><pre>
Array
(
    [result] => 
)
</pre></td>
<td>If the script does not end with <code>Exit Script</code>, or <code>Exit Script</code> is called with no value.</td>
</tr>

<tr>
<td><pre>
Array
(
    [result] => foo
)
</pre></td>
<td>When <code>Exit Script</code>is called with a string.</td>
</tr>

<tr>
<td><pre>
Array
(
    [foo] => bah
    [who] => hah
)
</pre>
</td>
<td>When <code>Exit Script</code> is called with a JSON object.</td>
</tr>

</table>

### TODO
<ul>
<li>Call scripts in tables / fields</li>
<li>More output types</li>
<li>Write data back to FM</li>
</ul>

### Contact Details
Steve Winter  
[Matatiro Solutions](https://msdev.nz)  
[steve@msdev.nz](mailto:steve@msdev.nz)