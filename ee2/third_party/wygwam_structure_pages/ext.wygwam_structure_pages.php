<?php if ( ! defined('APP_VER')) exit('No direct script access allowed');


/**
 * Wygwam Structure Pages
 * 
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2010 Pixel & Tonic, Inc
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Wygwam_structure_pages_ext {

	var $name           = 'Wygwam Structure Pages';
	var $version        = '1.0';
	var $description    = 'Adds a “Structure Pages” Link Type to Wygwam’s Link dialog';
	var $settings_exist = 'n';
	var $docs_url       = 'http://github.com/brandonkelly/wygwam_structure_pages';

	/**
	 * Class Constructor
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		// add the row to exp_extensions
		$this->EE->db->insert('extensions', array(
			'class'    => get_class($this),
			'method'   => 'wygwam_config',
			'hook'     => 'wygwam_config',
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{
		// Nothing to change...
		return FALSE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		// Remove all Wygwam_structure_pages_ext rows from exp_extensions
		$this->EE->db->where('class', get_class($this))
		             ->delete('extensions');
	}

	// --------------------------------------------------------------------

	/**
	 * wygwam_config hook
	 */
	function wygwam_config($config, $settings)
	{
		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$config = $this->EE->extensions->last_call;
		}

		// is Structure installed?
		if ($this->EE->db->where('module_name', 'Structure')->get('modules')->num_rows())
		{
			// load the Sql_structure class
			if (! class_exists('Sql_structure'))
			{
				require_once PATH_THIRD.'structure/sql.structure.php';
			}

			$obj = new Sql_structure();

			// are there any Structure pages?
			if ($structure_pages = $obj->get_data())
			{
				// get EE's record of site pages
				$site_pages = $this->EE->config->item('site_pages');
				$site_id = $this->EE->config->item('site_id');

				foreach ($structure_pages as $entry_id => $page_data)
				{
					// ignore if EE doesn't have a record of this page
					if (! isset($site_pages[$site_id]['uris'][$entry_id])) continue;

					$label = $page_data['title'];

					// add this page to the config
					$config['link_types']['Structure'][] = array(
						'label' => $label,
						'url' => $this->EE->functions->create_page_url($site_pages[$site_id]['url'], $site_pages[$site_id]['uris'][$entry_id]),
						'label_depth' => $page_data['depth']
					);
				}
			}
		}

		return $config;
	}
}

// End of file ext.wygwam_structure_pages.php */
// Location: ./system/expressionengine/third_party/wygwam_structure_pages/ext.wygwam_structure_pages.php
