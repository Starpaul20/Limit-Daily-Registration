<?php
/**
 * Limit Daily Registration
 * Copyright 2014 Starpaul20
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Tell MyBB when to run the hooks
$plugins->add_hook("member_do_register_start", "limitregistration_run");
$plugins->add_hook("member_register_start", "limitregistration_run");
$plugins->add_hook("member_register_agreement", "limitregistration_run");
$plugins->add_hook("member_register_coppa", "limitregistration_run");

// The information that shows up on the plugin manager
function limitregistration_info()
{
	global $lang;
	$lang->load("limitregistration", true);

	return array(
		"name"				=> $lang->limitregistration_info_name,
		"description"		=> $lang->limitregistration_info_desc,
		"website"			=> "http://galaxiesrealm.com/index.php",
		"author"			=> "Starpaul20",
		"authorsite"		=> "http://galaxiesrealm.com/index.php",
		"version"			=> "1.1",
		"codename"			=> "limitregistration",
		"compatibility"		=> "18*"
	);
}

// This function runs when the plugin is activated.
function limitregistration_activate()
{
	global $db;
	$query = $db->simple_select("settinggroups", "gid", "name='member'");
	$gid = $db->fetch_field($query, "gid");

	$insertarray = array(
		'name' => 'maxregday',
		'title' => 'Maximum Registrations allowed Per Day',
		'description' => 'Maximum number of registrations that can be made per day. 0 for unlimited.',
		'optionscode' => 'numeric
min=0',
		'value' => 10,
		'disporder' => 25,
		'gid' => (int)$gid
	);
	$db->insert_query("settings", $insertarray);

	rebuild_settings();
}

// This function runs when the plugin is deactivated.
function limitregistration_deactivate()
{
	global $db;
	$db->delete_query("settings", "name IN('maxregday')");

	rebuild_settings();
}

// Limit Registrations per day
function limitregistration_run()
{
	global $mybb, $db, $lang;
	$lang->load("limitregistration");

	// Check limits
	if($mybb->settings['maxregday'] > 0)
	{
		$query = $db->simple_select("users", "COUNT(*) AS user_count", "regdate >= '".(TIME_NOW - (60*60*24))."'");
		$user_count = $db->fetch_field($query, "user_count");
		if($user_count >= $mybb->settings['maxregday'])
		{
			$lang->error_max_registration_day = $lang->sprintf($lang->error_max_registration_day, $mybb->settings['maxregday']);
			error($lang->error_max_registration_day);
		}
	}
}
