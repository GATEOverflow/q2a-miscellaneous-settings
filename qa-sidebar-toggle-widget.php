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
        $themeobject->output('
            <div id="stw-widget-wrap">
                <div id="sidebar-toggle-widget">
                    <button id="stw-hide-btn" type="button" aria-label="Hide side panel">
                        Hide side panel
                    </button>
                </div>
            </div>

            <style>
                #stw-hide-btn, #stw-show-btn {
                    border: 1px solid #ccc;
                    background: #fff;
                    font-size: 14px;
                    padding: 6px 12px;
                    border-radius: 999px;
                    cursor: pointer;
                    transition: box-shadow 0.2s ease, background 0.2s ease;
                    user-select: none;
                }
                #stw-hide-btn:hover, #stw-show-btn:hover {
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }

                #stw-show-btn {
                    position: fixed;
                    bottom: 1rem;
                    right: 1rem;
                    z-index: 9999;
                    display: none;
                    background: #fff;
                    color: #333;
                    cursor: grab;
                }
                #stw-show-btn:active { cursor: grabbing; }

                body.stw-expanded .qa-main {
                    width: 100% !important;
                    max-width: 100% !important;
                    float: none !important;
                    transition: width 250ms ease;
                }
                body.stw-expanded .qa-footer {
                    width: 100% !important;
                    max-width: 100% !important;
                    float: none !important;
                }

                @media (max-width: 768px) {
                    #stw-show-btn { padding: 8px 16px; font-size: 16px; }
                }
            </style>

            <script>
            (function() {
              function initSTW($) {
                $(function() {
                  if (window.__qaSTWInit) return;
                  window.__qaSTWInit = true;

                  const $sidebar = $(".qa-sidepanel, .qa-rightside").first();
                  if (!$sidebar.length) return;

                  const $body = $("body");
                  const $showBtn = $("<button>", {
                      id: "stw-show-btn",
                      text: "Show side panel",
                      "aria-label": "Show side panel"
                  }).appendTo("body").hide();

                  // --- Restore saved state ---
                  const savedState = localStorage.getItem("stw-sidebar-hidden");
                  const savedPos = JSON.parse(localStorage.getItem("stw-button-pos") || "{}");

                  if (savedState === "true") {
                      $sidebar.hide();
                      $body.addClass("stw-expanded");
                      $showBtn.show();
                  }

                  if (savedPos.left || savedPos.top) {
                      $showBtn.css({
                          left: savedPos.left + "px",
                          top: savedPos.top + "px",
                          right: "auto",
                          bottom: "auto"
                      });
                  }

				// --- Hide ---
				$("#stw-hide-btn").on("click", function() {
					$sidebar.hide();
					$body.addClass("stw-expanded");

					// Restore last saved vertical position (top)
					const savedPos = JSON.parse(localStorage.getItem("stw-button-pos") || "{}");
					let top = typeof savedPos.top === "number" ? savedPos.top : 100; // fallback if not set

					// Reset left position to near right edge
					const winW = window.innerWidth;
					const rect = $showBtn[0].getBoundingClientRect();
					const btnW = rect.width;
					const left = winW - btnW - 8 - 100; // your chosen offset

					$showBtn.css({ left, top }).fadeIn(150);
					localStorage.setItem("stw-button-pos", JSON.stringify({ left, top }));
					localStorage.setItem("stw-sidebar-hidden", "true");
				});

				// --- Adjust position on window resize ---
				$(window).on("resize", function() {
					if (!$showBtn.is(":visible")) return;

					const rect = $showBtn[0].getBoundingClientRect();
					const winW = window.innerWidth;
					const winH = window.innerHeight;
					const btnW = rect.width;
					const btnH = rect.height;

					let left = rect.left;
					let top = rect.top;

					// Keep within bounds
					if (left + btnW > winW - 8) left = winW - btnW - 8;
					if (top + btnH > winH - 8) top = winH - btnH - 8;
					if (left < 8) left = 8;
					if (top < 8) top = 8;

					$showBtn.css({ left, top });

					// Save corrected position
					localStorage.setItem("stw-button-pos", JSON.stringify({ left, top }));
				});


                  // --- Show ---
                  $showBtn.on("click", function() {
                      $sidebar.show();
                      $body.removeClass("stw-expanded");
                      $showBtn.fadeOut(150);
                      localStorage.setItem("stw-sidebar-hidden", "false");
                      //$("#stw-hide-btn").trigger("focus");
                  });

				// --- Draggable + Edge-Snap ---
				let dragging = false, moved = false, offsetX = 0, offsetY = 0;
				let dragStartedAt = 0;

				$showBtn.on("mousedown touchstart", function(e) {
					dragging = true;
					moved = false;
					dragStartedAt = Date.now();

					const evt = e.type === "touchstart" ? e.touches[0] : e;
					offsetX = evt.clientX - this.getBoundingClientRect().left;
					offsetY = evt.clientY - this.getBoundingClientRect().top;
					$(this).css("cursor", "grabbing");
				});

				$(document).on("mousemove touchmove", function(e) {
					if (!dragging) return;
					const evt = e.type === "touchmove" ? e.touches[0] : e;
					e.preventDefault();

					const x = evt.clientX - offsetX;
					const y = evt.clientY - offsetY;
					$showBtn.css({
						left: x + "px",
						top: y + "px",
						right: "auto",
						bottom: "auto"
					});

					moved = true;
				});

				$(document).on("mouseup touchend", function(e) {
					if (!dragging) return;
					dragging = false;
					$showBtn.css("cursor", "grab");

					const rect = $showBtn[0].getBoundingClientRect();
					const winW = window.innerWidth;
					const winH = window.innerHeight;
					const btnW = rect.width;
					const btnH = rect.height;

					// Snap to right edge (as your code did)
					const left = winW - btnW - 8;
					const top = Math.min(Math.max(rect.top, 8), winH - btnH - 8);

					$showBtn.animate({
						left: left + "px",
						top: top + "px"
					}, 200);

					// Save position
					localStorage.setItem("stw-button-pos", JSON.stringify({ left, top }));

					// If user was dragging (moved or held long), block click
					const heldTooLong = Date.now() - dragStartedAt > 150;
					if (moved || heldTooLong) {
						$showBtn.data("skipClick", true);
						setTimeout(() => $showBtn.data("skipClick", false), 250);
					}
				});

				// --- Show sidebar (with click suppression) ---
				$showBtn.off("click").on("click", function(e) {
					if ($showBtn.data("skipClick")) {
						e.stopImmediatePropagation();
						e.preventDefault();
						return;
					}

					$sidebar.show();
					$body.removeClass("stw-expanded");
					$showBtn.fadeOut(150);
					localStorage.setItem("stw-sidebar-hidden", "false");
				});


                });
              }

              // Safe jQuery loader (for Polaris list pages etc.)
              if (window.jQuery) {
                initSTW(window.jQuery);
              } else {
                const s = document.createElement("script");
                s.src = "https://code.jquery.com/jquery-3.6.0.min.js";
                s.onload = function() { initSTW(window.jQuery); };
                document.head.appendChild(s);
              }
            })();
            </script>
        ');
    }
}
