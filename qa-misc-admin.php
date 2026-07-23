<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_misc_admin {

	function option_default($option) {
		switch ($option) {
			case 'misc_tweaks_ask_reorder':
			case 'add_list_link':
				return 0;
			case 'misc_enable_logout_all':
				return 3599;
			case 'misc_min_active_days_profile_visible':
			case 'misc_min_active_days_profile_editable':
				return 0;
			case 'allowed_username_changes':
			case 'enable_print_button':
				return 1; // enabled by default
			case 'misc_nav_main_order':
				return '';
			case 'misc_nav_main_hidden':
				return '';
			case 'custom_print_css':
				return <<<CSS
							body { background:#fff; color:#000; font-family:"Segoe UI", Arial, sans-serif; margin:30px; }
							.print-container { max-width:900px; margin:auto; }
							.print-meta { font-size:14px; color:#555; margin-bottom:20px; }
							.print-body, .print-answer-body, .comment { font-size:16px; line-height:1.6; margin-bottom:15px; }
							.print-answer { margin-bottom:25px; }
							.print-answer-meta { font-size:13px; color:#333; margin-bottom:8px; }
							.print-comments { border-left:3px solid #ddd; margin:10px 0 20px 20px; padding-left:10px; }
							.comment { margin-bottom:10px; }
							.print-note { background:#f4f9ff; border-left:4px solid #3399ff; padding:10px; margin-bottom:20px; border-radius:4px; }
							.note-text { white-space:pre-wrap; }
							@media print {
								a[href]:after { content:""; }
								.qa-nav-main, .qa-sidepanel, .qa-footer, .qa-header, .qa-q-view-buttons { display:none; }
								body { margin:0; }
							}
							.print-divider {border: 0;border-top: 1px solid #ccc;margin: 20px 0;}
							.print-footer {
								text-align: center;
								margin-top: 40px;
								padding-top: 15px;
								border-top: 1px solid #ccc;
								font-size: 12px;
								color: #555;
								font-style: italic;
							}

							/* Add subtle fade or watermark effect for footer logo (optional) */
							.print-footer img {
								height: 30px;
								opacity: 0.7;
								vertical-align: middle;
								margin-right: 8px;
							}

							/* Prevent page break before footer in print */
							@media print {
								.print-footer {
									page-break-inside: avoid;
									position: relative;
									bottom: 0;
									width: 100%;
								}
							}

							CSS;
		}
	}

	function admin_form(&$qa_content) {

		$saved = false;
		if (qa_clicked('reset_print_css')) {
			qa_opt('custom_print_css', $this->option_default('custom_print_css'));
			$saved = true;
		}
		if (qa_clicked('misc_tweaks_save')) {
			qa_opt('misc_enable_logout_all', (int)qa_post_text('misc_enable_logout_all'));
			qa_opt('misc_enable_hide_sidepanel', (int)qa_post_text('misc_enable_hide_sidepanel'));
			qa_opt('misc_tweaks_ask_reorder', (int)qa_post_text('misc_tweaks_ask_reorder'));
			qa_opt('add_list_link', (int)qa_post_text('add_list_link'));
			qa_opt('misc_min_active_days_profile_visible', (int)qa_post_text('misc_min_active_days_profile_visible'));
			qa_opt('misc_min_active_days_profile_editable', (int)qa_post_text('misc_min_active_days_profile_editable'));
			qa_opt('enable_print_button', (int)qa_post_text('enable_print_button'));
			qa_opt('custom_print_css', qa_post_text('custom_print_css'));
			qa_opt('allowed_username_changes', (int)qa_post_text('allowed_username_changes'));

			// Save nav reorder
			$navOrder = qa_post_text('misc_nav_main_order');
			if ($navOrder !== null) {
				$decoded = json_decode($navOrder, true);
				if (is_array($decoded)) {
					$clean = array_filter($decoded, function($k) {
						return preg_match('/^[a-zA-Z0-9$\/_-]+$/', $k);
					});
					qa_opt('misc_nav_main_order', json_encode(array_values($clean)));
				}
			}

			// Save hidden nav items
			$navHidden = qa_post_text('misc_nav_main_hidden');
			if ($navHidden !== null) {
				$decoded = json_decode($navHidden, true);
				if (is_array($decoded)) {
					$clean = array_filter($decoded, function($k) {
						return preg_match('/^[a-zA-Z0-9$\/_-]+$/', $k);
					});
					qa_opt('misc_nav_main_hidden', json_encode(array_values($clean)));
				} else {
					qa_opt('misc_nav_main_hidden', '[]');
				}
			}

			$saved = true;
		}
		
		qa_set_display_rules($qa_content, array(
			'custom_print_css' => 'enable_print_button',
		));

		return array(
			'ok' => $saved ? 'Settings saved' : null,

			'fields' => array(
				//Logout from all devices
				array(
					'type' => 'custom',
					'html' => '<strong>'.qa_lang('qa_misc_lang/admin_section_title_logout').'</strong>',
				),
				array(
					'type'  => 'custom',
					'html'  => '<label>'
						. qa_lang_html('qa_misc_lang/logout_all_on_each_request') . ' '
						. '<input type="number" name="misc_enable_logout_all" min="0" value="' . (int)qa_opt('misc_enable_logout_all') . '" />'
						. '</label>',
				),
				array(
					'type' => 'blank',
				),
				// Sidepanel
				array(
					'type' => 'custom',
					'html' => '<strong>'.qa_lang('qa_misc_lang/admin_section_title_sidepanel').'</strong>',
				),
				array(
					'label' => qa_lang_html('qa_misc_lang/opt_hide_sidepanel'),
					'type' => 'checkbox',
					'value' => qa_opt('misc_enable_hide_sidepanel'),
					'tags'  => 'name="misc_enable_hide_sidepanel"',
				),
				array(
					'type' => 'blank',
				),
				
				// Profile
				array(
					'type' => 'custom',
					'html' => '<strong>'.qa_lang('qa_misc_lang/admin_section_title_profile').'</strong>',
				),
				array(
					'type'  => 'custom',
					'html'  => '<label>'
						. qa_lang_html('qa_misc_lang/minimum_active_profile_visible') . ' '
						. '<input type="number" name="misc_min_active_days_profile_visible" min="0" value="' . (int)qa_opt('misc_min_active_days_profile_visible') . '" />'
						. '</label>',
				),
				array(
					'type'  => 'custom', 
					'html'  => '<label>'
						. qa_lang_html('qa_misc_lang/minimum_active_profile_editable') . ' '
						. '<input type="number" name="misc_min_active_days_profile_editable" min="0" value="' . (int)qa_opt('misc_min_active_days_profile_editable') . '" />'
						. '</label>',
				),
				array(
					'label' => qa_lang_html('qa_misc_lang/opt_username_change'),
					'tags' => 'name="allowed_username_changes"',
					'value' => qa_opt('allowed_username_changes'),
					'type' => 'number',
					'note' => qa_lang_html('qa_misc_lang/opt_username_change_description'),
				),
				array(
					'type' => 'blank',
				),
				
				// Other
				array(
					'type' => 'custom',
					'html' => '<strong>'.qa_lang('qa_misc_lang/admin_section_title_other').'</strong>',
				),
				array(
					'label' => qa_lang_html('qa_misc_lang/reorder_ask'),
					'type' => 'checkbox',
					'value' => qa_opt('misc_tweaks_ask_reorder'),
					'tags'  => 'name="misc_tweaks_ask_reorder"',
				),
				array(
					'label' => qa_lang_html('qa_misc_lang/redirection_fav'),
					'type' => 'checkbox',
					'value' => qa_opt('add_list_link'),
					'tags'  => 'name="add_list_link"',
				),
				array(
					'label' => qa_lang_html('qa_misc_lang/print_enable'),
					'type'  => 'checkbox',
					'value' => qa_opt('enable_print_button'),
					'tags'  => 'name="enable_print_button" ID="enable_print_button"',
				),
				array(
					'id' => 'custom_print_css', // This will be a show/hide section, based on the previous checkbox's state
					'label' => qa_lang_html('qa_misc_lang/print_css'),
					'type'  => 'textarea',
					'rows'  => 25,
					'value' => qa_opt('custom_print_css'),
					'tags'  => 'name="custom_print_css" style="width:100%; font-family:monospace;"',
					'note' => qa_lang_html('qa_misc_lang/print_css_description'),
				),
				array(
					'type' => 'blank',
				),

				// Navigation reorder
				array(
					'type' => 'custom',
					'html' => '<strong>' . qa_lang('qa_misc_lang/admin_section_title_nav_reorder') . '</strong>',
				),
				array(
					'type' => 'custom',
					'html' => $this->nav_reorder_html(),
				),
				array(
					'type' => 'blank',
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save',
					'tags' => 'name="misc_tweaks_save"',
				),
				array(
					'label' => 'Reset CSS to Default',
					'tags' => 'name="reset_print_css" onclick="return confirm(\'Are you sure you want to reset the print CSS to default?\')"',
				),
			),
		);
	}

	function nav_reorder_html() {
		// Build the current main navigation items in their current order
		// We simulate the same logic Q2A uses in qa_content_prepare()
		$navItems = array();

		if (qa_opt('nav_home') && qa_opt('show_custom_home'))
			$navItems['$'] = qa_lang('main/nav_home');

		if (qa_opt('nav_activity'))
			$navItems['activity'] = qa_lang('main/nav_activity');

		$hascustomhome = qa_has_custom_home();
		if (qa_opt($hascustomhome ? 'nav_qa_not_home' : 'nav_qa_is_home'))
			$navItems[$hascustomhome ? 'qa' : '$'] = qa_lang('main/nav_qa');

		if (qa_opt('nav_questions'))
			$navItems['questions'] = qa_lang('main/nav_qs');

		if (qa_opt('nav_hot'))
			$navItems['hot'] = qa_lang('main/nav_hot');

		if (qa_opt('nav_unanswered'))
			$navItems['unanswered'] = qa_lang('main/nav_unanswered');

		if (qa_using_tags() && qa_opt('nav_tags'))
			$navItems['tag'] = qa_lang('main/nav_tags');

		if (qa_using_categories() && qa_opt('nav_categories'))
			$navItems['categories'] = qa_lang('main/nav_categories');

		if (qa_opt('nav_users'))
			$navItems['user'] = qa_lang('main/nav_users');

		if (qa_opt('nav_ask'))
			$navItems['ask'] = qa_lang('main/nav_ask');

		// Add custom pages that appear in main nav
		$navPages = qa_db_read_all_assoc(
			qa_db_query_sub('SELECT pageid, title, tags, nav, flags FROM ^pages WHERE nav IN ($, $) ORDER BY position', 'B', 'M')
		);
		foreach ($navPages as $page) {
			$key = ($page['flags'] & QA_PAGE_FLAGS_EXTERNAL) ? ('custom-' . $page['pageid']) : ($page['tags'] . '$');
			$navItems[$key] = qa_html($page['title']);
		}

		// Admin link
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)
			$navItems['admin'] = qa_lang('main/nav_admin');

		// Apply saved order
		$savedOrder = qa_opt('misc_nav_main_order');
		$orderedItems = array();
		if (!empty($savedOrder)) {
			$orderKeys = json_decode($savedOrder, true);
			if (is_array($orderKeys)) {
				foreach ($orderKeys as $key) {
					if (isset($navItems[$key])) {
						$orderedItems[$key] = $navItems[$key];
					}
				}
				// Append any items not in saved order
				foreach ($navItems as $key => $label) {
					if (!isset($orderedItems[$key])) {
						$orderedItems[$key] = $label;
					}
				}
				$navItems = $orderedItems;
			}
		}

		$savedOrderJson = qa_html(!empty($savedOrder) ? $savedOrder : json_encode(array_keys($navItems)));

		// Get hidden items
		$savedHidden = qa_opt('misc_nav_main_hidden');
		$hiddenKeys = array();
		if (!empty($savedHidden)) {
			$decoded = json_decode($savedHidden, true);
			if (is_array($decoded)) $hiddenKeys = $decoded;
		}
		$savedHiddenJson = qa_html(!empty($hiddenKeys) ? json_encode($hiddenKeys) : '[]');

		$html = '<p style="margin:4px 0 8px; color:#666;">' . qa_lang_html('qa_misc_lang/nav_reorder_description') . '</p>';
		$html .= '<input type="hidden" name="misc_nav_main_order" id="misc_nav_main_order" value="' . $savedOrderJson . '" />';
		$html .= '<input type="hidden" name="misc_nav_main_hidden" id="misc_nav_main_hidden" value="' . $savedHiddenJson . '" />';
		$html .= '<ul id="misc-nav-sortable" style="list-style:none; padding:0; margin:0; max-width:400px;">';

		foreach ($navItems as $key => $label) {
			if (in_array($key, $hiddenKeys)) continue;
			$html .= '<li data-key="' . qa_html($key) . '" style="'
				. 'padding:8px 12px; margin:4px 0; background:#f7f7f7; border:1px solid #ddd;'
				. 'border-radius:4px; cursor:grab; user-select:none; display:flex;'
				. 'align-items:center; gap:8px; font-size:14px;'
				. '">' 
				. '<span style="color:#999; font-size:16px;">&#9776;</span> '
				. '<span style="flex:1;">' . qa_html($label) . '</span>'
				. '<button type="button" class="nav-remove-btn" style="background:none;border:none;color:#d32f2f;cursor:pointer;font-size:18px;line-height:1;padding:0 4px;" title="Remove">&times;</button>'
				. '</li>';
		}

		$html .= '</ul>';

		// Removed items section
		$html .= '<div id="misc-nav-removed" style="margin-top:12px;' . (empty($hiddenKeys) ? 'display:none;' : '') . '">';
		$html .= '<p style="margin:4px 0 6px; color:#999; font-size:13px;"><strong>' . qa_lang_html('qa_misc_lang/nav_removed_title') . '</strong></p>';
		$html .= '<ul id="misc-nav-removed-list" style="list-style:none; padding:0; margin:0; max-width:400px;">';
		foreach ($navItems as $key => $label) {
			if (!in_array($key, $hiddenKeys)) continue;
			$html .= '<li data-key="' . qa_html($key) . '" style="'
				. 'padding:8px 12px; margin:4px 0; background:#fafafa; border:1px dashed #ccc;'
				. 'border-radius:4px; display:flex; align-items:center; gap:8px; font-size:14px; color:#999;'
				. '">' 
				. '<span style="flex:1; text-decoration:line-through;">' . qa_html($label) . '</span>'
				. '<button type="button" class="nav-restore-btn" style="background:none;border:none;color:#388e3c;cursor:pointer;font-size:13px;padding:2px 8px;" title="Restore">&#8629; Restore</button>'
				. '</li>';
		}
		$html .= '</ul>';
		$html .= '</div>';
		$html .= <<<'JSBLOCK'
<script>
(function() {
	var list = document.getElementById('misc-nav-sortable');
	var hiddenOrder = document.getElementById('misc_nav_main_order');
	var hiddenRemoved = document.getElementById('misc_nav_main_hidden');
	var removedSection = document.getElementById('misc-nav-removed');
	var removedList = document.getElementById('misc-nav-removed-list');
	var dragEl = null;
	var placeholder = document.createElement('li');
	placeholder.style.cssText = 'padding:8px 12px;margin:4px 0;background:#e3f2fd;border:2px dashed #90caf9;border-radius:4px;min-height:20px;';

	function getItems() {
		return Array.prototype.slice.call(list.querySelectorAll('li[data-key]'));
	}

	function updateInputs() {
		var keys = getItems().map(function(li) { return li.getAttribute('data-key'); });
		hiddenOrder.value = JSON.stringify(keys);
		var removedKeys = Array.prototype.slice.call(removedList.querySelectorAll('li[data-key]')).map(function(li) { return li.getAttribute('data-key'); });
		hiddenRemoved.value = JSON.stringify(removedKeys);
		removedSection.style.display = removedKeys.length ? '' : 'none';
	}

	function removeItem(li) {
		li.querySelector('.nav-remove-btn').remove();
		var label = li.querySelector('span[style*="flex:1"]');
		if (label) label.style.textDecoration = 'line-through';
		li.style.background = '#fafafa';
		li.style.border = '1px dashed #ccc';
		li.style.color = '#999';
		li.style.cursor = 'default';
		li.removeAttribute('draggable');
		var restoreBtn = document.createElement('button');
		restoreBtn.type = 'button';
		restoreBtn.className = 'nav-restore-btn';
		restoreBtn.style.cssText = 'background:none;border:none;color:#388e3c;cursor:pointer;font-size:13px;padding:2px 8px;';
		restoreBtn.title = 'Restore';
		restoreBtn.innerHTML = '&#8629; Restore';
		restoreBtn.addEventListener('click', function() { restoreItem(li); });
		li.appendChild(restoreBtn);
		removedList.appendChild(li);
		updateInputs();
	}

	function restoreItem(li) {
		li.querySelector('.nav-restore-btn').remove();
		var label = li.querySelector('span[style*="flex:1"]');
		if (label) label.style.textDecoration = '';
		li.style.background = '#f7f7f7';
		li.style.border = '1px solid #ddd';
		li.style.color = '';
		li.style.cursor = 'grab';
		li.setAttribute('draggable', 'true');
		var removeBtn = document.createElement('button');
		removeBtn.type = 'button';
		removeBtn.className = 'nav-remove-btn';
		removeBtn.style.cssText = 'background:none;border:none;color:#d32f2f;cursor:pointer;font-size:18px;line-height:1;padding:0 4px;';
		removeBtn.title = 'Remove';
		removeBtn.innerHTML = '&times;';
		removeBtn.addEventListener('click', function() { removeItem(li); });
		li.appendChild(removeBtn);
		li.addEventListener('dragstart', onDragStart);
		li.addEventListener('dragend', onDragEnd);
		list.appendChild(li);
		updateInputs();
	}

	// Bind remove buttons
	list.addEventListener('click', function(e) {
		var btn = e.target.closest('.nav-remove-btn');
		if (btn) removeItem(btn.closest('li[data-key]'));
	});
	removedList.addEventListener('click', function(e) {
		var btn = e.target.closest('.nav-restore-btn');
		if (btn) restoreItem(btn.closest('li[data-key]'));
	});

	function onDragStart(e) {
		dragEl = this;
		e.dataTransfer.effectAllowed = 'move';
		e.dataTransfer.setData('text/plain', '');
		this.style.opacity = '0.4';
	}

	function onDragEnd(e) {
		this.style.opacity = '1';
		if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
		dragEl = null;
		updateInputs();
	}

	function onDragOver(e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'move';
		var target = e.target;
		while (target && target !== list && !target.getAttribute('data-key')) {
			target = target.parentNode;
		}
		if (!target || target === list || target === dragEl || target === placeholder) return;

		var rect = target.getBoundingClientRect();
		var midY = rect.top + rect.height / 2;
		if (e.clientY < midY) {
			list.insertBefore(placeholder, target);
		} else {
			list.insertBefore(placeholder, target.nextSibling);
		}
	}

	function onDrop(e) {
		e.preventDefault();
		if (dragEl && placeholder.parentNode) {
			list.insertBefore(dragEl, placeholder);
			placeholder.parentNode.removeChild(placeholder);
		}
	}

	// Touch support for mobile
	var touchDragEl = null;
	var touchClone = null;
	var touchStartY = 0;

	function onTouchStart(e) {
		var li = e.target.closest('li[data-key]');
		if (!li) return;
		touchDragEl = li;
		touchStartY = e.touches[0].clientY;
		touchClone = li.cloneNode(true);
		touchClone.style.cssText = li.style.cssText + 'position:fixed;z-index:9999;pointer-events:none;opacity:0.8;width:' + li.offsetWidth + 'px;box-shadow:0 2px 8px rgba(0,0,0,0.2);';
		touchClone.style.top = li.getBoundingClientRect().top + 'px';
		touchClone.style.left = li.getBoundingClientRect().left + 'px';
		document.body.appendChild(touchClone);
		li.style.opacity = '0.4';
	}

	function onTouchMove(e) {
		if (!touchDragEl) return;
		e.preventDefault();
		var touch = e.touches[0];
		touchClone.style.top = touch.clientY - 20 + 'px';

		var items = getItems();
		for (var i = 0; i < items.length; i++) {
			var item = items[i];
			if (item === touchDragEl) continue;
			var rect = item.getBoundingClientRect();
			var midY = rect.top + rect.height / 2;
			if (touch.clientY > rect.top && touch.clientY < rect.bottom) {
				if (touch.clientY < midY) {
					list.insertBefore(touchDragEl, item);
				} else if (item.nextSibling) {
					list.insertBefore(touchDragEl, item.nextSibling);
				} else {
					list.appendChild(touchDragEl);
				}
				break;
			}
		}
	}

	function onTouchEnd(e) {
		if (!touchDragEl) return;
		touchDragEl.style.opacity = '1';
		if (touchClone && touchClone.parentNode) touchClone.parentNode.removeChild(touchClone);
		touchClone = null;
		touchDragEl = null;
		updateInputs();
	}

	// Bind events
	getItems().forEach(function(li) {
		li.setAttribute('draggable', 'true');
		li.addEventListener('dragstart', onDragStart);
		li.addEventListener('dragend', onDragEnd);
	});

	list.addEventListener('dragover', onDragOver);
	list.addEventListener('drop', onDrop);
	list.addEventListener('touchstart', onTouchStart, {passive: false});
	list.addEventListener('touchmove', onTouchMove, {passive: false});
	list.addEventListener('touchend', onTouchEnd);
})();
</script>
JSBLOCK;

		return $html;
	}
}
