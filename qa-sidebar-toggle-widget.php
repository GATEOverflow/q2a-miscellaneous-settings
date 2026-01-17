<?php

class qa_sidebar_toggle_widget
{
    public function allow_template($template)
    {
        return true;
    }

    public function allow_region($region)
    {
        return ($region === 'side');
    }

    public function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
        // If "Hide sidepanel" toggle already enabled for User Navigation in Plugin Options -> return
        // No point in outputting 2 buttons that do the same.
        if (qa_opt('misc_enable_hide_sidepanel')) return;
        
        $themeobject->output('
            <div id="stw-widget-wrap">
                <button id="stw-hide-btn" class="stw-btn" type="button">
                    <span class="stw-icon">←</span> Hide side panel
                </button>
            </div>

            <style>
                /* Widget Container */
                #stw-widget-wrap { margin-bottom: 15px; }

                /* Shared Button Styles */
                .stw-btn {
                    border: 1px solid #ccc;
                    background: var(--stw-bg, #fff);
                    color: var(--stw-text, #333);
                    font-size: 13px;
                    padding: 8px 16px;
                    border-radius: 20px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .stw-btn:hover {
                    background: #f8f8f8;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                    transform: translateY(-1px);
                }

                /* Floating Show Button */
                #stw-show-btn {
                    position: fixed;
                    z-index: 10001;
                    touch-action: none; /* Prevents scrolling while dragging */
                    user-select: none;
                    white-space: nowrap;
                    font-weight: bold;
                }

                /* Layout Transitions */
                .qa-main, .qa-sidepanel, .qa-footer {
                    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                                opacity 0.3s ease, 
                                transform 0.3s ease;
                }

                /* State: Sidebar Hidden */
                body.stw-hidden-side .qa-sidepanel {
                    display: none !important;
                }
                body.stw-hidden-side .qa-main {
                    width: 100% !important;
                    max-width: 100% !important;
                    float: none !important;
                }
                
                /* Dark Mode Compatibility (Auto) */
                @media (prefers-color-scheme: dark) {
                    .stw-btn { --stw-bg: #222; --stw-text: #eee; border-color: #444; }
                    .stw-btn:hover { background: #333; }
                }

                @media (max-width: 768px) {
                    #stw-hide-btn { width: 100%; justify-content: center; }
                }
            </style>

            <script>
            (function() {
                const STORAGE_KEY = "stw-sidebar-hidden";
                const POS_KEY = "stw-button-pos";

                function init() {
                    if (window.__qaSTWInit) return;
                    window.__qaSTWInit = true;

                    const $ = window.jQuery;
                    const $body = $("body");
                    const $sidebar = $(".qa-sidepanel, .qa-rightside").first();
                    
                    if (!$sidebar.length) return;

                    // Create Floating Button
                    const $showBtn = $("<button>", {
                        id: "stw-show-btn",
                        class: "stw-btn",
                        html: "<span class=\"stw-icon\">→</span> Show side panel"
                    }).appendTo("body").hide();

                    // --- State Management ---
                    const isHidden = localStorage.getItem(STORAGE_KEY) === "true";
                    if (isHidden) {
                        $body.addClass("stw-hidden-side");
                        $showBtn.show();
                    }

                    // --- Position Management ---
                    function applyPosition(pos) {
                        if (pos && pos.top) {
                            $showBtn.css({
                                top: pos.top + "px",
                                left: pos.left !== undefined ? pos.left + "px" : "auto",
                                right: pos.right !== undefined ? pos.right + "px" : "1rem"
                            });
                        }
                    }
                    applyPosition(JSON.parse(localStorage.getItem(POS_KEY)));

                    // --- Actions ---
                    $("#stw-hide-btn").on("click", function() {
                        $body.addClass("stw-hidden-side");
                        $showBtn.fadeIn(200);
                        localStorage.setItem(STORAGE_KEY, "true");
                    });

                    $showBtn.on("click", function(e) {
                        if ($showBtn.data("isDragging")) return;
                        $body.removeClass("stw-hidden-side");
                        $showBtn.fadeOut(200);
                        localStorage.setItem(STORAGE_KEY, "false");
                    });

                    // --- Modern Draggable Logic ---
                    let active = false, currentX, currentY, initialX, initialY, xOffset = 0, yOffset = 0;

                    $showBtn[0].addEventListener("pointerdown", dragStart, false);
                    document.addEventListener("pointerup", dragEnd, false);
                    document.addEventListener("pointermove", drag, false);

                    function dragStart(e) {
                        initialX = e.clientX - xOffset;
                        initialY = e.clientY - yOffset;
                        if (e.target === $showBtn[0]) {
                            active = true;
                            $showBtn.data("isDragging", false);
                        }
                    }

                    function dragEnd() {
                        if (!active) return;
                        active = false;
                        
                        // Snap to nearest side
                        const winW = window.innerWidth;
                        const rect = $showBtn[0].getBoundingClientRect();
                        const midX = rect.left + rect.width / 2;
                        
                        let finalPos;
                        if (midX < winW / 2) {
                            finalPos = { left: 8, top: rect.top, right: undefined };
                        } else {
                            finalPos = { left: undefined, top: rect.top, right: 8 };
                        }
                        
                        $showBtn.animate({ left: finalPos.left, right: finalPos.right, top: finalPos.top }, 200);
                        localStorage.setItem(POS_KEY, JSON.stringify(finalPos));
                        
                        // Prevent accidental click if moved significantly
                        setTimeout(() => $showBtn.data("isDragging", false), 50);
                    }

                    function drag(e) {
                        if (!active) return;
                        e.preventDefault();
                        
                        $showBtn.data("isDragging", true);
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;

                        $showBtn.css({
                            transform: `translate3d(${currentX}px, ${currentY}px, 0)`,
                            right: "auto"
                        });
                    }
                }

                if (window.jQuery) init();
                else document.addEventListener("DOMContentLoaded", init);
            })();
            </script>
        ');
    }
}
