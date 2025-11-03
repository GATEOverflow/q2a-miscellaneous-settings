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

        list($question, $childposts, $achildposts) = qa_db_select_with_pending(
            $parts[1] === 'Q'
                ? qa_db_full_post_selectspec($userid, $questionid)
                : qas_blog_db_full_post_selectspec($userid, $questionid),
            $parts[1] === 'Q'
                ? qa_db_full_child_posts_selectspec($userid, $questionid)
                : qas_blog_db_full_child_posts_selectspec($userid, $questionid),
            qa_db_full_a_child_posts_selectspec($userid, $questionid)
        );

        if (!$question || ($question['type'] != 'B' && $question['type'] != 'Q')) {
            echo '<h1>' . ($parts[1] === 'Q' ? "No such Question found" : "No such Blog found") . '</h1>';
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
            $usernote = $parts[1] === 'Q'
                ? qa_db_read_one_value(
                    qa_db_query_sub("SELECT note FROM ^usernote WHERE postid=# AND userid=#", $questionid, $userid),
                    true
                )
                : null;
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
        </head>
        <body>
        <div class="print-container">';

        // MathJax support
        echo '<script>
            MathJax = {
              tex: {
                inlineMath: [ [\'$\',\'$\'], ["\\(","\\)"] ],
                processEscapes: true
              }
            };
			window.print();
            </script>
            <script id="MathJax-script" type="text/javascript" async
             src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>';

        // --- Header ---
        echo '<div class="print-header">';
        if (!empty($logo_url)) {
            echo '<div class="logo"><img src="' . qa_html($logo_url) . '" alt="' . qa_html($site_title) . ' Logo"></div>';
        }
        echo '<h1 class="site-title">' . qa_html($site_title) . '</h1>';
        echo '</div><hr>';

        // --- Question section ---
        $ask_label = $parts[1] === "Q" ? "Asked by:" : "Posted by:";
        $time = !empty($question['updated']) ? $question['updated'] : $question['created'];

        echo '<div class="print-question">';
        echo '<h2 class="print-title">' . qa_html($question['title']) . '</h2>';
        echo '<div class="print-meta">
                <strong>' . $ask_label . '</strong> ' . qa_get_one_user_html($question['handle'], false) . '<br>
                <strong>Date:</strong> <span class="local-time" data-timestamp="' . intval($time) . '">Loading...</span>
              </div>';
        echo '<div class="print-body">' . qa_viewer_html($question['content'], $question['format']) . '</div>';

        // Related questions
        $sync_html = $parts[1] === 'Q' ? $this->get_synced_questions_html($questionid) : "";
        if ($sync_html) {
            echo '<div class="print-synced-questions">' . nl2br($sync_html) . '</div>';
            echo '<hr class="print-divider">';
        }

        // User note
        if (!empty($usernote)) {
            echo '<div class="print-note">
                    <h3>Your Note</h3>
                    <div class="note-text">' . nl2br(qa_html($usernote)) . '</div>
                  </div><hr class="print-divider">';
        }

        // Comments on Question/Blog
        if (!empty($comments[$questionid])) {
            echo '<div class="print-comments"><h3>' . ($parts[1] === 'Q' ? 'Comments on Question:' : 'Comments on Blog:') . '</h3>';
            foreach ($comments[$questionid] as $c) {
                $time = !empty($c['updated']) ? $c['updated'] : $c['created'];
                echo '<div class="comment">
                        <strong>' . qa_get_one_user_html($c['handle'], false) . '</strong>
                        <small>(<span class="local-time" data-timestamp="' . intval($time) . '">Loading...</span>)</small><br>
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
                            &nbsp;<small>(<span class="local-time" data-timestamp="' . intval($time) . '">Loading...</span>)</small>
                        </div>
                        <div class="print-answer-body">' . qa_viewer_html($a['content'], $a['format']) . '</div>';

                // Comments under this answer
                if (!empty($comments[$a['postid']])) {
                    echo '<div class="print-comments">';
                    foreach ($comments[$a['postid']] as $c) {
                        $time = !empty($c['updated']) ? $c['updated'] : $c['created'];
                        echo '<div class="comment">
                                <strong>' . qa_get_one_user_html($c['handle'], false) . '</strong>
                                <small>(<span class="local-time" data-timestamp="' . intval($time) . '">Loading...</span>)</small><br>
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

        // --- Footer ---
        echo '<div class="print-footer">
                <div class="footer-content">&copy; ' . date('Y') . ' ' . qa_html($site_title) . '. All rights reserved.</div>
              </div>';

        // --- JavaScript for local timezone conversion ---
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".local-time").forEach(function(span) {
                const ts = parseInt(span.dataset.timestamp, 10) * 1000; // PHP gives seconds
                const date = new Date(ts);
                const options = {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    hour: "numeric",
                    minute: "2-digit",
                    hour12: true,
                    timeZoneName: "short"
                };
                span.textContent = new Intl.DateTimeFormat([], options).format(date);
            });
        });
        </script>';

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
            $table = $r['related_postid_prefix'] . 'posts';
            $title = qa_db_read_one_value(qa_db_query_sub(
                'SELECT title FROM ' . $table . ' WHERE postid=# and type=\'Q\'',
                $r['related_postid']
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
