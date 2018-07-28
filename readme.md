# WordPress FileMaker Data API integration 
A WordPress plugin to integrate with the FileMaker Data API to allow data to be pulled from FileMaker and displayed easily in your WordPress site.

At this early stage of development only 'pull' is possible. Over time this plugin will be expanded to allow for data to be updated in FileMaker as well.

The primary means of 'pulling' data is through the use of two WordPress shortcodes

## Installation
Download, copy to your plugins directory, enable, configure and you're ready to go.

## Shortcodes
The two shortcodes listed below provide access to your FileMaker data.

### [FM-DATA-TABLE]
Pull all records from the specified layout and generate a table of records.

| Parameter | Description | Required
|---|---|---|
|layout|Specify the layout which data should be pulled from|true|
|fields|A list of the fields which should be included in the table. \| separated  (see example below) |true|
|labels|The labels to use for the column headers. If ommited then the field names (as above) are used instead. \| separated|false|
|types|The type of each field. Currently supported are <ul><li>Currency - displays a number in the selected currency (see the settings screen for locale selection)</li><li>Image, which can optionally be follwed by a hypehen and an integer value (e.g. Image-100) which will set the width to 100px (defaults to full size)</li><li>Thumbnail - as for image, however defaults to 50px</li><li>`null` - outputs the content of the field</li></ul>| false| 
|id-field|Which field on the layout acts as a primary key for the given layout|false|
|detail-url|If both this and the id-field are set then the content of the cells is converted to a link to the URL. You must provide the location in the URL which the value of id-field will be embded in using `*id*` e.g. `detail-url="/product/?id=*id*"` |false|

Example
```
 [FM-DATA-TABLE layout="Product Details" fields="Image|Part Number|Name|Unit Cost|Category|Availability" types="Thumbnail-20|||Currency||" id-field="Part Number" detail-url="/product/?id=*id*" ]
```

### [FM-DATA-FIELD]
Display a single field value.

| Parameter | Description | Required
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

### TODO
<ul>
<li>More output types</li>
<li>Ability to query for table rows</li>
<li>Write data back to FM</li>
