<?php

defined('COT_CODE') or die('Wrong URL');

$ifupload_errors = array(); // using isolated errors for if you didn't want to have cot_error halt core based actions

define('IFU_ECODE_FILE_NAME', 1);
define('IFU_ECODE_FILE_EXT', 2);
define('IFU_ECODE_FILE_CHECK', 3);
define('IFU_ECODE_FILE_EXISTS', 4);
define('IFU_ECODE_FILE_ERROR', 5);
define('IFU_ECODE_FILE_SIZE', 6);

require_once cot_langfile('ifupload', 'plug');

/**
* 
*
* @param string $name Input name
* @param array $options Options for the individual file
*/
function ifupload_file_handle($name, $options = array())
{
	global $cfg;

	$default_options = array(
		'use_cot_error' => true,
		'use_safename' => true, 
		'use_file_check' => true,
		'upload_path' => $cfg['plugin']['ifupload']['path'],
		'custom_validator' => false, 
		'check_file_exists' => true, 
		'special' => '',
		'max_size' => '',
		'valid_extensions' => explode(',', $cfg['plugin']['ifupload']['exts'])
	);
	$options = array_merge($default_options, $options);
	$options['upload_path'] = str_replace('\\', '/', $options['upload_path']);
	$options['upload_path'] = mb_substr($options['upload_path'], -1) != '/' ? $options['upload_path'].'/' : $options['upload_path'];
	$found_error = FALSE;
	$file = array();

	$file = ifupload_file_sift($name, $options);

	if($options['use_safename'])
	{
		require_once cot_incfile('uploads');
		$file['name'] = cot_safename($file['name'], false);
	}

	$validator = ($options['custom_validator'] && function_exists($options['custom_validator'])) ? $options['custom_validator'] : 'ifupload_file_validate';

	if($validator($file, $options))
	{
		// passed validation

		if(@move_uploaded_file($file['tmp_name'], $file['upload_path']))
		{
			return array(
				'success' => true,
				'file' => $file,
			);
		}
		else
		{
			ifupload_error(IFU_ECODE_FILE_NAME);
		}
	}

	$errors_found = ifupload_list_errors();

	if($options['use_cot_error'])
	{
		if(is_array($errors_found))
		{
			foreach($errors_found as $error_found)
			{
				cot_error($error_found);
			}
		}
	}
	return array(
		'success' => false,
		'errors' => $errors_found,
		'error_codes' => array_keys($errors_found),
		'error_messages' => array_values($errors_found), 
	);
}

function ifupload_file_remove($path)
{
	if(file_exists($path))
	{
		return @unlink($path);
	}
}

function ifupload_file_sift($name, array $options)
{
	$file = array();
    if(isset($_FILES[$name]) && is_array($_FILES[$name]))
    {
    	$file_data = $_FILES[$name];
    	$file = array(
    		'name' => trim(ifupload_mb_basename(stripslashes($file_data['name']))),
    		'size' => intval($file_data['size']),
    		'path' => $options['upload_path'],
    		'extension' => ifupload_get_ext($file_data['name']),
    		'tmp_name' => $file_data['tmp_name'],
    		'error' => $file_data['error'],
    	);
    	$file['upload_path'] = $options['upload_path'].$file['name'];
    	if($options['special'])
    	{
    		$file['name'] = $options['special'].$file['name'];
    	}
    }

    return $file;
}

function ifupload_list_errors()
{
	global $ifupload_errors;
	return count($ifupload_errors) > 0 ? $ifupload_errors : array();
}

function ifupload_error_found()
{
	global $inload_errors;
	return count($inload_errors) > 0;
}

function ifupload_file_validate(array $file, array $options)
{
	$valid = TRUE;
	$filesize_check = @filesize($file['tmp_name']);
	if(empty($file) || !$file['name'] || $filesize_check !== $file['size'] || !is_uploaded_file($file['tmp_name']) || $filesize_check === 0)
	{
		return FALSE;
	}
	if(!in_array($file['extension'], $options['valid_extensions']))
	{
		$valid = FALSE;
		ifupload_error(IFU_ECODE_FILE_EXT);
	}

	if($options['check_file_exists'] && @file_exists($file['upload_path']))
	{
		$valid = FALSE;
		ifupload_error(IFU_ECODE_FILE_EXISTS);
	}

	if($file['error'] !== 0)
	{
		$valid = FALSE;
		ifupload_error(IFU_ECODE_FILE_ERROR);
	}

	if($options['use_file_check'])
	{
		global $cfg;
		$cfg['pfsfilecheck'] = TRUE;
		require_once cot_incfile('uploads');
		if(!cot_file_check($file['tmp_name'], $file['name'], $file['extension']))
		{
			$valid = FALSE;
			ifupload_error(IFU_ECODE_FILE_CHECK);
		}
	}

	if($options['max_size'] != '' && $filesize_check > $options['max_size'])
	{
		$valid = FALSE;
		ifupload_error(IFU_ECODE_FILE_SIZE);
	}

	return $valid;
}

function ifupload_error($code)
{
	global $ifupload_errors, $L;
	$ifupload_errors[$code] = $L['ifupload_ecode_'.$code];
}

// Functions from Trustmaster's attach2 -- renamed to avoid collisions ( a check isn't enough because of possible ordering )

// workaround for splitting basename whith beginning utf8 multibyte char
function ifupload_mb_basename($filepath, $suffix = NULL)
{
	$splited = preg_split('/\//', rtrim($filepath, '/ '));
	return substr(basename('X' . $splited[count($splited) - 1], $suffix), 1);
}

function ifupload_get_ext($filename)
{
	if (preg_match('#((\.tar)?\.\w+)$#', $filename, $m))
	{
		return mb_strtolower(mb_substr($m[1], 1));
	}
	else
	{
		return false;
	}
}

