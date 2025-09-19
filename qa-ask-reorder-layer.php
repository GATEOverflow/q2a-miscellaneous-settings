<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base {
    
	function doctype() {
        if ($this->template == 'ask' && qa_opt('misc_tweaks_ask_reorder')) {
            $fields = &$this->content['form']['fields'];

            if (isset($fields['content'], $fields['title'], $fields['similar'])) {

                // store and remove the title and similar fields from the form.
                $title   = $fields['title'];
                $similar = $fields['similar'];
                unset($fields['title'], $fields['similar']);
				
                // rebuild the form with title+similar after content
                $newfields = [];
                foreach ($fields as $key => $field) {
                    $newfields[$key] = $field;

                    if ($key === 'content') {
                        // insert similar then title right after content
                        $newfields['similar'] = $similar;
                        $newfields['title']   = $title;
                    }
                }
                $fields = $newfields;
            }
        }
        qa_html_theme_base::doctype();
    }
}
