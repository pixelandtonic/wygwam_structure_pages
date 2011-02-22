<?php

if ( ! defined('EXT')) exit('Invalid file request');
/**
 * Wygwam Structure Pages
 *
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2010 Pixel & Tonic, Inc
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Wygwam_structure_pages {

	var $name           = 'Wygwam Structure Pages';
	var $version        = '1.0';
	var $description    = 'Adds a "Structure Pages" Link Type to Wygwam\'s Link dialog';
	var $settings_exist = 'n';
	var $docs_url       = 'http://github.com/brandonkelly/wygwam_structure_pages';

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		global $DB;

		// add the row to exp_extensions
		$DB->query($DB->insert_string('exp_extensions', array(
			'class'    => get_class($this),
			'method'   => 'wygwam_config',
			'hook'     => 'wygwam_config',
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		)));
	}

	/**
	 * Update Extension
	 *
	 * @param string  $current  Previous installed version of the extension
	 */
	function update_extension($current='')
	{
		// Nothing to change...
		return FALSE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		global $DB;
		$DB->query($DB->update_string('exp_extensions', array('enabled' => 'n'), 'class = "'.get_class($this).'"'));
	}

	// --------------------------------------------------------------------

	/**
	 * wygwam_config hook
	 */
	function wygwam_config($config, $settings)
	{
		global $EXT, $FNS, $DB, $PREFS;

		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($EXT->last_call !== FALSE)
		{
			$config = $EXT->last_call;
		}

		// is Structure installed?
		if ($DB->query('SELECT COUNT(*) AS count FROM exp_modules WHERE module_name = "Structure"')->row['count'])
		{
			// Load the Structure class
			if (! class_exists('Structure'))
			{
				require PATH_MOD.'structure/mod.structure.php';
			}

			$obj = new Structure();

			// are there any Structure pages?
			if ($structure_pages = $obj->get_data())
			{
				// get EE's record of site pages
				$site_pages = $PREFS->ini('site_pages');
				$site_id = $PREFS->ini('site_id');

				foreach ($structure_pages as $entry_id => $page_data)
				{
					// ignore if EE doesn't have a record of this page
					if (! isset($site_pages[$site_id]['uris'][$entry_id])) continue;

					$label = $page_data['title'];

					// add this page to the config
					$config['link_types']['Structure'][] = array(
						'label'       => $label,
						'label_depth' => $page_data['depth'],
						'url'         => $FNS->create_page_url($site_pages[$site_id]['url'], $site_pages[$site_id]['uris'][$entry_id])
					);
				}
			}
		}

		return $config;
	}
}