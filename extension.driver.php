<?php
	/*
	Copyrights: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	require_once(EXTENSIONS . '/google_analytics_dashboard/vendor/autoload.php');
	
	/**
	 *
	 * @author Deux Huit Huit
	 * https://deuxhuithuit.com/
	 *
	 */
	class extension_google_analytics_dashboard extends Extension {

		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Google Analytics Dashboard';
		
		/**
		 * Name of the extension
		 * @var string
		 */
		const PANEL_NAME = 'Google Analytics';

		const URL = '/extension/google_analytics_dashboard/';

		/* ********* DELEGATES ******* */

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelRender',
					'callback'  => 'dashboard_render_panel'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelTypes',
					'callback'  => 'dashboard_panel_types'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelOptions',
					'callback'  => 'dashboard_panel_options'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelValidate',
					'callback'  => 'dashboard_panel_validate'
				),
			);
		}

		public function dashboard_render_panel($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			$config = $context['config'];
			$height = isset($config['height']) ? $config['height'] : '500px';
			$i = new XMLElement('iframe', null, array(
				'src' => APPLICATION_URL . self::URL .'?p=' . $context['id'],
				'style' => "width:100%;height:$height;",
				'frameborder' => 'no',
				'scrolling' => 'no',
			));

			$context['panel']->appendChild($i);
		}

		public function dashboard_panel_types($context) {
			$context['types'][self::PANEL_NAME] = self::PANEL_NAME;
		}

		public function dashboard_panel_options($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			$config = $context['existing_config'];
			if (empty($config)) {
				$handle = General::createHandle(self::EXT_NAME);
				$settings = Symphony::Configuration()->get($handle);
				if (!empty($settings)) {
					$config = $settings;
				}
			}

			$fieldset = new XMLElement('fieldset', NULL, array('class' => 'settings two cols'));
			$fieldset->appendChild(new XMLElement('legend', 'Google Analytics Options'));

			$label = Widget::Label('Google Analytics Client ID', Widget::Input('config[cid]', $config['cid']));
			$fieldset->appendChild($label);
			
			$label = Widget::Label('Google Analytics Client Secret', Widget::Input('config[csec]', $config['csec']));
			$fieldset->appendChild($label);
			
			$client = static::createClient($config, $context['id']);
			$auth = Widget::Anchor('Get a token', $client->createAuthUrl());
			$label = Widget::Label('Google Access Token ' . $auth->generate(), Widget::Input('config[at]', $config['at'], null, array('disabled' => 'disabled')));
			$fieldset->appendChild($label);
			
			$label = Widget::Label('Height (include units)', Widget::Input('config[height]', $config['height']));
			$fieldset->appendChild($label);

			$label = Widget::Label('Save as default', Widget::Input('default', 'on', 'checkbox'));
			$fieldset->appendChild($label);

			$context['form'] = $fieldset;
		}

		public function dashboard_panel_validate($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			if (isset($_POST['default']) && $_POST['default'] == 'on') {
				$config = $context['existing_config'];
				$handle = General::createHandle(self::EXT_NAME);
				Symphony::Configuration()->set($handle, $config);
				Symphony::Configuration()->write();
			}
		}

		/* ********* GOOGLE CLIENT ******* */

		public static function createClient(array $config, $panelId) {
			$client = new Google_Client();
			$client->setClientId($config['cid']);
			//$client->setClientSecret($config['csec']);
			$client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
			$client->setAccessType('offline');
			$client->setRedirectUri(APPLICATION_URL . self::URL . 'oauth/?p=' . $panelId);
			return $client;
		}

		/* ********* INSTALL/UPDATE/UNINSTALL ******* */

		/**
		 * Creates the table needed for the settings of the field
		 */
		public function install() {
			return true;
		}

		/**
		 * This method will update the extension according to the
		 * previous and current version parameters.
		 * @param string $previousVersion
		 */
		public function update($previousVersion = false) {
			return true;
		}

		public function uninstall() {
			return true;
		}

	}