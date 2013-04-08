<?php
/***************************************************************************
 *                              admin_db_generator.php
 *                            --------------------
 *
 * copyright	: ©2003 Freakin' Booty ;-P & Antony Bailey
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_PHPBB', true);

if(!empty ($setmodules))
{
	$filename = basename(__FILE__);
	$module['General']['db_update generator'] = $filename;
	return;
}

//
// Set the phpBB path and all that junk. ;)
//
$no_page_header = true;
$phpbb_root_path = './../';
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);


//
// The user has submitted some sql
//
if( $HTTP_POST_VARS['submit'] || $HTTP_POST_VARS['download'] )
{
	if( isset($HTTP_POST_VARS['sql']) )
	{
		$sql = stripslashes($HTTP_POST_VARS['sql']);
	}
	else
	{
		message_die(GENERAL_MESSAGE, 'You have not entered any SQL');
	}

	//
	// Start the program
	//
	include($phpbb_root_path . 'includes/sql_parse.'.$phpEx);

	$original_sql = $sql;
	$sql = str_replace('phpbb_', '" . $table_prefix . "', $sql);
	$sql = remove_remarks($sql);
	$sql = split_sql_file($sql, ';');

	$sql_output = '';
	for( $i = 0; $i < count($sql); $i++ )
	{
		$sql_output .= ( ( $sql_output != '' ) ? "\n" : '' ) . '$sql[] = "' . trim($sql[$i]) . '";';
	}

	$output_file = $phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/db_generator.tpl';
	if( !$file_handle = @fopen($output_file, 'rb') )
	{
		message_die(GENERAL_MESSAGE, 'Could not open ' . $output_file);
	}
	if( !$output = @fread($file_handle, filesize($output_file)))
	{
		message_die(GENERAL_MESSAGE, 'Could not read ' . $output_file);
	}
	fclose($file_handle);

	$output = preg_replace("#\{SQL_OUTPUT\}#", $sql_output, $output);
}


if( $HTTP_POST_VARS['submit'] )
{
	$template->assign_block_vars('show_output', array(
		'OUTPUT' => htmlspecialchars($output)
		)
	);
}
else if( $HTTP_POST_VARS['download'] )
{
	//
	// Hack for IE - stupid browser!
	//
	if( strstr($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE') )
	{
		$attachment = '';
	}
	else
	{
		$attachment = ' attachment;';
	}

	header("Content-Type: application/x-forced-download; name=\"db_update.php\"");
	header("Content-disposition: $attachment; filename=db_update.php");
	echo $output;
	exit;
}


//
// Output page to template
//
include('./page_header_admin.'.$phpEx);

$template->set_filenames(array(
	'body' => 'admin/admin_db_generator_body.tpl'
	)
);

$template->assign_vars(array(
	'L_INSTRUCTIONS' => $lang['Instructions'],
	'L_SQL_INSTRUCTIONS' => $lang['SQL_instructions'],
	'L_ENTER_SQL' => $lang['Enter_SQL'],
	'L_ENTER_SQL_EXPLAIN' => $lang['Enter_SQL_explain'],
	'L_OUTPUT_SQL' => $lang['Output_SQL'],
	'L_OUTPUT_SQL_EXPLAIN_ADMIN' => $lang['Output_SQL_explain'],

	'L_SUBMIT' => $lang['Submit'],
	'L_RESET' => $lang['Reset'],
	'L_DOWNLOAD' => $lang['Download'],

	'ORIGINAL_SQL' => $original_sql,

	'S_FORM_ACTION' => append_sid("admin_db_generator.$phpEx"),
	'S_HIDDEN_FIELDS' => $s_hidden_fields
	)
);

$template->pparse('body');

include('./page_footer_admin.'.$phpEx);

?>
