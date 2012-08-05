<?php

/*
Clicky Plugin for MyBB
Copyright (C) 2012 Gabriel Topala

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined('IN_MYBB'))
{
	$info = clicky_info();
	die("This is MyBB plugin '{$info['name']}'");
}

$plugins->add_hook('pre_output_page','clicky');

function clicky_info()
{
	return array
	(
		'name'=>'Clicky',
		'description'=>'Adding Clicky Analytics tracking code to every page.',
		'website'=>'http://mods.mybb.com/',
		'author'=>'Gabriel Topala',
		'authorsite' => 'http://www.gtopala.com/',
		'version'=>'1.0',
		'guid'=>'04472bacaa2e891573e4bc5c5d680e43',
		'compatibility'=>'16*',
		'codename'=>'clicky'
	);
}

function clicky_activate()
{
	global $db,$mybb;
	$info=clicky_info();
	$setting_group_array=array
	(
		'name'=>$info['codename'],
		'title'=>$info['name'],
		'description'=>'Here you can edit '.$info['name'].' settings.',
		'disporder'=>1,
		'isdefault'=>0
	);
	$db->insert_query('settinggroups',$setting_group_array);
	$group=$db->insert_id();
	$settings=array
	(
		'clicky_webpropertyid'=>array
		(
			'Clicky Site ID',
			'Enter here your Clicky Site ID (for example "111111").',
			'text',
			''
		)
	);
	$i=1;
	foreach($settings as $name=>$sinfo)
	{
		$insert_array=array
		(
			'name'=>$name,
			'title'=>$db->escape_string($sinfo[0]),
			'description'=>$db->escape_string($sinfo[1]),
			'optionscode'=>$db->escape_string($sinfo[2]),
			'value'=>$db->escape_string($sinfo[3]),
			'gid'=>$group,
			'disporder'=>$i,
			'isdefault'=>0
		);
		$db->insert_query('settings',$insert_array);
		$i++;
	}
	rebuild_settings();
}

function clicky_deactivate()
{
	global $db;
	$info=clicky_info();
	$result=$db->simple_select('settinggroups','gid','name="'.$info['codename'].'"',array('limit'=>1));
	$group=$db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups','gid="'.$group['gid'].'"');
		$db->delete_query('settings','gid="'.$group['gid'].'"');
		rebuild_settings();
	}
}

function clicky($page)
{
	global $mybb;
	if($mybb->settings['clicky_webpropertyid'])
	{
		$page=str_replace('</body>', '
<script src="//static.getclicky.com/js" type="text/javascript"></script>
<script type="text/javascript">try{ clicky.init('.$mybb->settings['clicky_webpropertyid'].'); }catch(e){}</script>
<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/'.$mybb->settings['clicky_webpropertyid'].'ns.gif" /></p></noscript></body>',$page);
	}
	return $page;
}

?>