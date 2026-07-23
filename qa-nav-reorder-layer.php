<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base {

	function nav_main_sub() {
		$savedOrder = qa_opt('misc_nav_main_order');
		$savedHidden = qa_opt('misc_nav_main_hidden');

		if (isset($this->content['navigation']['main'])) {
			// Remove hidden items
			if (!empty($savedHidden)) {
				$hiddenKeys = json_decode($savedHidden, true);
				if (is_array($hiddenKeys)) {
					foreach ($hiddenKeys as $key) {
						unset($this->content['navigation']['main'][$key]);
					}
				}
			}

			// Apply saved order
			if (!empty($savedOrder)) {
				$orderKeys = json_decode($savedOrder, true);

				if (is_array($orderKeys)) {
					$navigation = $this->content['navigation']['main'];
					$reordered = array();

					foreach ($orderKeys as $key) {
						if (isset($navigation[$key])) {
							$reordered[$key] = $navigation[$key];
						}
					}

					foreach ($navigation as $key => $navlink) {
						if (!isset($reordered[$key])) {
							$reordered[$key] = $navlink;
						}
					}

					$this->content['navigation']['main'] = $reordered;
				}
			}
		}

		qa_html_theme_base::nav_main_sub();
	}
}
