<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
    function doctype()
    {
        // Only on the main favorites page
        if ($this->template == 'favorites' && qa_opt('add_list_link')){
			$userid = qa_get_logged_in_userid();
			if (!$userid) return;
			
			if($this->content['q_list']['title'] !== qa_lang_html('misc/no_favorite_qs'))
				$this->content['q_list']['title'] .= ': <a href="' . qa_path_html('userlists/' . qa_get_logged_in_handle(), ['listid' => 0]) . '">'.qa_lang_html('qa_misc_lang/redirection_fav_content').'</a>.';
		}
		
		// Call parent to populate $this->content
        parent::doctype();
	}
}