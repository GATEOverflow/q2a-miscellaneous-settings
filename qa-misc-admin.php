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
            case 'misc_min_active_days_profile_visible':
            case 'misc_min_active_days_profile_editable':
                return 0;
            case 'enable_print_button':
                return 1; // enabled by default
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
            qa_opt('misc_tweaks_ask_reorder', (int)qa_post_text('misc_tweaks_ask_reorder'));
			qa_opt('add_list_link', (int)qa_post_text('add_list_link'));
			qa_opt('misc_min_active_days_profile_visible', (int)qa_post_text('misc_min_active_days_profile_visible'));
			qa_opt('misc_min_active_days_profile_editable', (int)qa_post_text('misc_min_active_days_profile_editable'));
            qa_opt('enable_print_button', (int)qa_post_text('enable_print_button'));
            qa_opt('custom_print_css', qa_post_text('custom_print_css'));
            $saved = true;
        }

        return array(
            'ok' => $saved ? 'Settings saved' : null,

            'fields' => array(
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
                    'label' => qa_lang_html('qa_misc_lang/print_enable'),
                    'type'  => 'checkbox',
                    'value' => qa_opt('enable_print_button'),
                    'tags'  => 'name="enable_print_button"',
                ),
                array(
                    'label' => qa_lang_html('qa_misc_lang/print_css'),
                    'type'  => 'textarea',
                    'rows'  => 25,
                    'value' => qa_opt('custom_print_css'),
                    'tags'  => 'name="custom_print_css" style="width:100%; font-family:monospace;"',
                    'note'  => 'Full print stylesheet. Modify to change layout, fonts, etc.',
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
}
