<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_print_page
{
    public function match_request($request)
    {
        $parts = qa_request_parts();
        return ($parts[0] === 'print' && ($parts[1] === 'Q' || $parts[1] === 'B') && is_numeric(@$parts[2]));
    }

    public function process_request($request)
    {
        $parts = qa_request_parts();
        $questionid = (int)$parts[2];

        require_once QA_INCLUDE_DIR . 'db/selects.php';
        require_once QA_INCLUDE_DIR . 'app/format.php';
        require_once QA_INCLUDE_DIR . 'app/users.php';
        require_once QA_INCLUDE_DIR . 'app/options.php';

        $userid = qa_get_logged_in_userid();

        // Fetch question, answers, comments, related questions
        list($question, $childposts, $achildposts) = qa_db_select_with_pending(
            $parts[1] === 'Q' ? qa_db_full_post_selectspec($userid, $questionid):qas_blog_db_full_post_selectspec($userid, $questionid),
           $parts[1] === 'Q' ?  qa_db_full_child_posts_selectspec($userid, $questionid):qas_blog_db_full_child_posts_selectspec($userid, $questionid),
            qa_db_full_a_child_posts_selectspec($userid, $questionid)
        );

        if (!$question || ($question['type']!='B' && $question['type']!='Q' )) {
            echo '<h1>'.($parts[1] === 'Q' ? "No such Question found":"No such Blog found").'</h1>';
            return;
        }

        // --- Categorize posts ---
        $answers = [];
        $comments = [];

        foreach ($childposts as $postid => $post) {
            if ($post['type'] == 'A') {
                $answers[$postid] = $post;
            } elseif ($post['type'] == 'C') {
                $comments[$post['parentid']][] = $post;
            }
        }

        foreach ($achildposts as $postid => $post) {
            if ($post['type'] == 'C') {
                $comments[$post['parentid']][] = $post;
            }
        }

        // --- User note ---
        $usernote = null;
        if ($userid) {
            $usernote = $parts[1] === 'Q' ? qa_db_read_one_value(
                qa_db_query_sub("SELECT note FROM ^usernote WHERE postid=# AND userid=#", $questionid, $userid),
                true
            ):null;
        }

        // --- Config options ---
        $logo_url = qa_opt('logo_url');
        $site_title = qa_opt('site_title');
        $custom_css = qa_opt('custom_print_css');

        // --- HTML output ---
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . qa_html($question['title']) . ' - ' . qa_html($site_title) . '</title>
            <style>' . $custom_css . '</style>
            <script>
                window.onload = function() {
                    setTimeout(function(){ window.print(); }, 800);
                };
            </script>
        </head>
        <body>
        <div class="print-container">';

        // --- Header with logo and site title ---
        echo '<div class="print-header">';
        if (!empty($logo_url)) {
            echo '<div class="logo"><img src="' . qa_html($logo_url) . '" alt="' . qa_html($site_title) . ' Logo"></div>';
        }
        echo '<h1 class="site-title">' . qa_html($site_title) . '</h1>';
        echo '</div><hr>';

        // --- Question section ---
		$ask_label = $parts[1] === "Q"? "Asked by:":"Posted by:";
        echo '<div class="print-question">';
        echo '<h2 class="print-title">' . qa_html($question['title']) . '</h2>';
        echo '<div class="print-meta">
                <strong>'.$ask_label.'</strong> ' . qa_get_one_user_html($question['handle'], false) . '<br>
                <strong>Date:</strong> ' . date('F j, Y, g:i a', $question['created']) . '
              </div>';
        echo '<div class="print-body">' . qa_viewer_html($question['content'], $question['format']) . '</div>';

        // --- Related questions ---
		$sync_html = $parts[1] === 'Q' ? $this->get_synced_questions_html($questionid):"";
		if ($sync_html) {
			echo '<div class="print-synced-questions">' . nl2br($sync_html) . '</div>';
			echo '<hr class="print-divider">';
		}

        // --- User note ---
        if (!empty($usernote)) {
            echo '<div class="print-note">
                    <h3>Your Note</h3>
                    <div class="note-text">' . nl2br(qa_html($usernote)) . '</div>
                  </div>';
			echo '<hr class="print-divider">';
        }

        // --- Question comments ---
        if (!empty($comments[$questionid])) {
			if($parts[1] === 'Q')
				echo '<div class="print-comments"><h3>Comments on Question:</h3>';
			else
				echo '<div class="print-comments"><h3>Comments on Blog:</h3>';
            foreach ($comments[$questionid] as $c) {
				$time = !empty($c['updated']) ? $c['updated'] : $c['created'];
                echo '<div class="comment">
                        <strong>' . qa_get_one_user_html($c['handle'], false) . '</strong>
                        <small>(' . date('F j, Y, g:i a', $time) . ')</small><br>
                        ' . qa_viewer_html($c['content'], $c['format']) . '
                      </div>';
            }
            echo '</div>';
        }

        echo '</div>'; // end question



        // --- Answers section ---
        if (!empty($answers)) {
            echo '<h2>Answers:</h2>';
            foreach ($answers as $a) {
				$time = !empty($a['updated']) ? $a['updated'] : $a['created'];
                echo '<div class="print-answer">
                        <div class="print-answer-meta">
                            <strong>Answered by:</strong> ' . qa_get_one_user_html($a['handle'], false) . '
                            &nbsp;<small>(' . date('F j, Y, g:i a', $time) . ')</small>
                        </div>
                        <div class="print-answer-body">' . qa_viewer_html($a['content'], $a['format']) . '</div>';

                // Comments under this answer
                if (!empty($comments[$a['postid']])) {
                    echo '<div class="print-comments">';
                    foreach ($comments[$a['postid']] as $c) {
						$time = !empty($c['updated']) ? $c['updated'] : $c['created'];
                        echo '<div class="comment">
                                <strong>' . qa_get_one_user_html($c['handle'], false) . '</strong>
                                <small>(' . date('F j, Y, g:i a', $time) . ')</small><br>
                                ' . qa_viewer_html($c['content'], $c['format']) . '
                              </div>';
                    }
                    echo '</div>';
                }

                echo '</div>';
            }
        } else {
            echo $parts[1] === 'Q' ? '<p><em>No answers yet.</em></p>' : "";
        }

        // --- Footer section ---
        echo '<div class="print-footer">';
        echo '<div class="footer-content">&copy; ' . date('Y') . ' ' . qa_html($site_title) . '. All rights reserved.</div>';
        echo '</div>';

        echo '</div></body></html>';
        exit;
    }
	
	private function get_synced_questions_html($postid)
	{
		$relateds = qa_db_read_all_assoc(qa_db_query_sub(
			'SELECT related_postid, related_postid_prefix FROM ^synced_questions WHERE postid=#',
			$postid
		));

		if (empty($relateds)) return '';

		$html = '<div class="qa-sync-note-wrapper"><hr class="qa-sync-note-divider">
				 <div class="qa-sync-note-block">';
		$html .= qa_lang('qa_sync_lang/related_questions_title') . '<div class="qa-sync-content"><ol>';

		foreach ($relateds as $r) {
			$table = $r['related_postid_prefix'].'posts';
			$title = qa_db_read_one_value(qa_db_query_sub(
				'SELECT title FROM '.$table.' WHERE postid=# and type=\'Q\'', $r['related_postid']
			), true);

			if ($title) {
				$pre_url = qa_network_site_url_sync_plugin($r['related_postid_prefix']);
				$html .= '<li><a href="' . $pre_url . '/' . $r['related_postid'] . '/' . qa_html($title) . '">' . qa_html($title) . '</a></li>';
			}
		}

		$html .= '</ol></div></div></div>';
		return $html;
	}
}
