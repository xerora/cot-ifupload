# About this plugin

This plugin simply allows you to handle the POST upload process for a file input. It will upload files and place them in a path you specify, but any tracking of the files after it is uploaded is your responsibility. This plugin is just meant to provide a somewhat flexible way to validate and upload a file.

# Installation

#### As a plugin

1. Download, unzip and move 'ifupload' folder into your Cotonti plugin directory
2. Install plugin through the administration panel and make sure configurations are to your preference

#### As a standalone

1. Download and move and or rename inc/ifupload.functions.php and place into any folder
2. Add the language strings from lang/ into your extension language files
3. Include from your extension

# Example Usage

This is an example for adding an image to a page with a custom page database column.

## Step 1
Add any amount of file input fields into page.add.tpl. Currently file array inputs are __NOT__ supported (e.g. test_upload[]). You can have multiple file inputs with different names.

```html
<input type="file" name="test_upload" />
```

## Step 2

Add a file handler where ever you complete a POST operation. 

In this example, it is in a hook into `page.add.add.error`. This hook is just before adding the page into the database, but after page data validation.

##### Example 1

This example has the `ifupload_file_handle` function send errors through cot_error. In most circumstances, this is a tolerable method. In some circumstances, calling cot_error, depending where you are hooking at, will halt a process from continuing. To handle errors on your own, see example 2.

```php

if(!cot_error_found())
{
	require_once cot_incfile('ifupload', 'plug');

	$rtestupload = ifupload_file_handle('test_upload');
	if($rtestupload['success'])
	{
		// The file was successfully uploaded
		// Where this hook is at in this example, this will add the column to the page insert query
		$rpage['page_custom_image'] = $rtestupload['file']['upload_path'];
	}
	else
	{
		// Add this if you are requiring the upload
		if(empty($rtestupload['errors']))
		{
			cot_error('You must select an upload');
		}
	}
}
```

#### Example 2

This example shows how to handle your own errors. In some cases you won't want to send some error messages to the user or effect your POST processing by triggering `cot_error_found` with `cot_error`.

```php

if(!cot_error_found())
{
	require_once cot_incfile('ifupload', 'plug');

	$rtestupload = ifupload_file_handle('test_upload',
		array(
			'use_cot_error' => false,
		)
	);

	if($rtestupload['success'])
	{
		$rpage['page_custom_page_image'] = $rtestupload['file']['upload_path'];
	}
	else
	{
		// handle what errors to send to the user based on the error codes returned
		if(!empty($rtestupload['error_codes']))
		{
			foreach($rtestupload['error_codes'] as $error_code)
			{
				// Only send error messages with the code 1 or 2 to the user. The others will be suppressed.
				switch($error_code)
				{
					case 1:
					case 2:
						// Of course you would avoid cot_error() if you didn't want to trigger cot_found_error()
						cot_error($rtestupload['errors'][$error_code]);
					break;
				}
			}
		}
	}
}
```

# Available Functions

#### ifupload_file_handle( $name, [array $options] )

Handles the POST upload process for a file input.

- `$name`: (string) The input name for the file to handle
- `$options`: (array) `optional`, Overwrite the default options
	
	__Options__
	
	<table>
		<tr>
			<td>Property</td>
			<td>Default</td>
			<td>Description</td>
		</tr>
		<tr>
			<td>use_cot_error</td>
			<td>true</td>
			<td>Have any file upload errors sent to cot_error.</td>
		</tr>
		<tr>
			<td>use_safename</td>
			<td>true</td>
			<td>Use `cot_safename` from the upload API</td>
		</tr>
		<tr>
			<td>file_name</td>
			<td></td>
			<td>Rename the file to the value set here if not empty</td>
		</tr>
		<tr>
			<td>use_file_check</td>
			<td>true</td>
			<td>Use `cot_file_check` from the upload API</td>
		</tr>
		<tr>
			<td>upload_path</td>
			<td>Value of `$cfg['plugin']['ifupload']['path']`</td>
			<td>The path to move the uploaded file to.</td>
		</tr>
		<tr>
			<td>custom_validator</td>
			<td>false</td>
			<td>Put the name of the callback you wish to use instead of the default file validator.</td>
		</tr>
		<tr>
			<td>check_file_exists</td>
			<td>true</td>
			<td>Check to see if the file name already exists. If the file does exist and this is property is true, the file will return a 'File already exists' error.</td>
		</tr>
		<tr>
			<td>special</td>
			<td></td>
			<td>Place a indentifier string you wish to append to a file. Nothing will be added if empty.</td>
		</tr>
		<tr>
			<td>valid_extensions</td>
			<td>Value of $cfg['plugin']['ifupload']['exts']</td>
			<td>An array of valid extensions (without ".") to accept. If a file's extension isn't in this list, it will return a 'Invalid file extension error'</td>
		</tr>
		<tr>
			<td>max_size</td>
			<td></td>
			<td>Set in bytes. If set other than empty, send an error on upload if the file size is too big. Empty is no limit.</td>
		</tr>
	</table> 
- `return`: (array) See "Return examples" for further details

#### ifupload_file_remove($path)

Delete file from the upload path

- `$path`: (string) The file path
- `return`: (boolean) Removal completed

# Return examples

Example of what `ifupload_file_handle` would return when successful or not

### Successful

```PHP
$rtestupload = ifupload_file_handle('test_upload');

// The above would return this if successful
return array(
	'success' => true,
	'file' => 
		array(
			'name' => 'uploadedfile.jpg',
			'size' => 648775,
			'tmp_name' => '/tmp/php8B9E.tmp',
			'error' => 0,
			'path' => 'static/',
			'upload_path' => 'static/uploadedfile.jpg'
		)
);
```

### Not Successful

```PHP
$rtestupload = ifupload_file_handle('test_upload');

// The above would return something like this if not successful
return array(
	'success' => false,
	'errors' => array(
		1 => 'Invalid file input',
		2 => 'Invalid file extension'
	),
	'error_codes' => array(
		0 => 1,
		1 => 2
	)
	'error_messages' => array(
		0 => 'Invalid file input',
		1 => 'Invalid file extension'
	)
);
```

# Errors

## Error codes and messages

<table>
	<tr>
		<td>Code</td>
		<td>Constant</td>
		<td>Message</td>
		<td>Explanation</td>
	</tr>
	<tr>
		<td>1</td>
		<td>IFU_ECODE_FILE_NAME</td>
		<td>Invalid file input</td>
		<td>The file failed validation because it was empty, sizes didn't match or wasn't actually an uploaded file.</td>
	</tr>
	<tr>
		<td>2</td>
		<td>IFU_ECODE_FILE_EXT</td>
		<td>Invalid file extension</td>
		<td>The file failed validation because the file extension was not in the valid_extensions list</td>
	</tr>
	<tr>
		<td>3</td>
		<td>IFU_ECODE_FILE_CHECK</td>
		<td>Invalid file</td>
		<td>File failed validation because the file failed cot_check_file() if it was set in options</td>
	</tr>
	<tr>
		<td>4</td>
		<td>IFU_ECODE_FILE_EXISTS</td>
		<td>File name already exists</td>
		<td>File failed validation because the file name already exists</td>
	</tr>
	<tr>
		<td>5</td>
		<td>IFU_ECODE_FILE_ERROR</td>
		<td>File failed to upload</td>
		<td>The file failed to upload and this was trigger by $_FILES[$name]['error'] !== 0</td>
	</tr>
	<tr>
		<td>5</td>
		<td>IFU_ECODE_FILE_SIZE</td>
		<td>File size is too big</td>
		<td>File failed validation because the size of the file was being that max_size option</td>
	</tr>
</table>




