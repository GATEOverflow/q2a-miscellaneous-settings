<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	
	public function q_view_buttons($q_view)
    {
        if (qa_opt('enable_print_button') &&
			($this->template === 'question' || $this->template === 'blog') &&
            isset($q_view['form']['buttons'])
        ) {
            $postid = (int)$q_view['raw']['postid'];
			$print_element = $this->template === 'question' ? 'Q': 'B';
			$print_url = qa_path_html('print/' . $print_element.'/'.$postid);
			$print_button = array(
				'printnote' => array(
					'tags' => 'onclick="window.open(\'' . $print_url . '\', \'_blank\');"',
					'label' => qa_lang_html('qa_misc_lang/print_label'),
					'popup' =>qa_lang_html('qa_misc_lang/print_popup'),
					),
			);
			qa_array_insert($q_view['form']['buttons'], null, $print_button);
        }

        parent::q_view_buttons($q_view);
    }
}