<?php

class qa_html_theme_layer extends qa_html_theme_base
{
	public function head_css()
	{
		qa_html_theme_base::head_css();

		// Return if option disabled
		if (!qa_opt('misc_enable_hide_sidepanel')) return;
		
		$styles = '
			<style>
				.qa-nav-user-hide-sidepanel .qa-nav-user-nolink {
					display: flex;
					padding: 1rem 0;
				}
				.qa-nav-user-hide-sidepanel .flex-item {
					display: flex;
					justify-content: flex-start;
					align-items: center;
					flex-wrap: nowrap;
				}
				.qa-nav-user-hide-sidepanel .flex-end-toggle {
					justify-content: flex-end;
					flex-grow: 1;
					display: flex;
				}
				
				.sp-toggle-container {
					color: #ebebeb;
					position: relative;
					width: 36px;
					height: 14px;
					margin: 4px 1px;
					cursor: pointer;
				}
				.sp-toggle-wrapper {
					position: absolute;
					-webkit-overflow-scrolling: touch;
					height: 100%;
					width: 100%;
					border-radius: 8px;
					pointer-events: none;
					opacity: 0.4;
					transition: background-color linear .08s;
					background-color: #929292;
				}
				[data-theme="dark"] .sp-toggle-wrapper {
					background-color: #ebebeb;
				}

				.sp-toggle-button {
					position: absolute;
					top: -3px;
					left: 0;
					right: auto;
					-webkit-overflow-scrolling: touch;
					transition: transform linear .08s, background-color linear .08s;
					will-change: transform;
					height: 20px;
					width: 20px;
					border-radius: 50%;
					box-shadow: 0 1px 5px 0 rgba(0, 0, 0, 0.6);
					background-color: rgb(95, 99, 104);
					cursor: pointer;
				}
				.toggle-active .sp-toggle-button, .toggle-active[checked] .sp-toggle-button {
					transform: translateX(16px) translateZ(0);
					background-color: #3ea6ff;
				}
				.qa-sidepanel.display-none {
					display: none;
				}
				@media (max-width:575px) {
					.qa-nav-user-hide-sidepanel {
						display: none;
					}
				}
			</style>
		';
		
		if (qa_opt('site_theme') == 'Polaris' && method_exists($this, 'minify_code')) {
			$this->output($this->minify_code($styles));
		} else {
			$this->output($styles);
		}
	}
	
	public function prepend_toggle_user_nav()
	{
		// Append it to the User Nav
		if (
			qa_is_logged_in() &&
			isset($this->content['navigation']['user'])
		) {
			// Only add 'hide-sidepanel' if the 'profile' link exists in the user nav - (POLARIS THEME)
			if (isset($this->content['navigation']['user']['profile'])) {
				// Get the 'user' navigation array
				$userNav = $this->content['navigation']['user'];

				// Split the array before and after 'profile'
				$beforeProfile = array_slice($userNav, 0, array_search('profile', array_keys($userNav)) + 0);
				$afterProfile = array_slice($userNav, array_search('profile', array_keys($userNav)) + 0);

				// Add 'hide-sidepanel' before 'profile'
				$this->content['navigation']['user'] = array_merge(
					$beforeProfile,
					['hide-sidepanel' => [
						'label' => '
							<div class="flex-item">Hide Sidepanel</div>
							<div class="flex-end-toggle">
								<div class="sp-toggle-container sidepanel-trigger">
									<div class="sp-toggle-wrapper"></div>
									<div class="sp-toggle-button"></div>
								</div>
							</div>
							<div class="mf-divider"></div>
						',
					]],
					$afterProfile
				);
			} else {
				// Other themes
				
				// Get the 'user' navigation array
				$userNav = $this->content['navigation']['user'];

				// Split the array before and after 'updates'
				$beforeProfile = array_slice($userNav, 0, array_search('updates', array_keys($userNav)) + 0);
				$afterProfile = array_slice($userNav, array_search('updates', array_keys($userNav)) + 0);

				// Add 'hide-sidepanel' before 'updates'
				$this->content['navigation']['user'] = array_merge(
					$beforeProfile,
					['hide-sidepanel' => [
						'label' => '
							<div class="no-select flex-item">Hide Sidepanel</div>
							<div class="flex-end-toggle">
								<div class="sp-toggle-container sidepanel-trigger">
									<div class="sp-toggle-wrapper"></div>
									<div class="sp-toggle-button"></div>
								</div>
							</div>
							<div class="mf-divider"></div>
						',
					]],
					$afterProfile
				);
			}
		}
	}
	
	function doctype()
	{
		parent::doctype();
		
		if (!qa_opt('misc_enable_hide_sidepanel')) return;
		
		// Add sidebar toggle to User Navigation
		$this->prepend_toggle_user_nav();
	}
	
	public function body_hidden()
	{
		qa_html_theme_base::body_hidden();
		// $patchNumber = self::PATCH_NUMBER;
		
		// Return if option disabled
		if (!qa_opt('misc_enable_hide_sidepanel')) return;
		
		$javascript = '
		<script>
			(() => {
				// Sidepanel elements
				const sidepanelTrigger = document.querySelector(".sidepanel-trigger");
				const sidepanel = document.querySelector(".qa-sidepanel");
				
				// Exit if required elements are missing
				if (!sidepanel || !sidepanelTrigger) {
					return;
				}
				
				// Get stored option (default: false)
				const getSidebarHidden = () => {
					return localStorage.getItem("stw-sidebar-hidden") === "true";
				};

				// Save option
				const setSidebarHidden = (isHidden) => {
					localStorage.setItem("stw-sidebar-hidden", String(isHidden));
				};

				// Apply UI state
				const applySidebarState = (isHidden) => {
					sidepanel.classList.toggle("display-none", isHidden);
					sidepanelTrigger.classList.toggle("toggle-active", isHidden);
				};

				// Toggle handler
				const toggleSidepanel = () => {
					const isHidden = getSidebarHidden();
					const newState = !isHidden;

					setSidebarHidden(newState);
					applySidebarState(newState);
				};

				// Initial state on page load
				const initialSidebarHidden = getSidebarHidden();
				applySidebarState(initialSidebarHidden);

				// Click listener
				sidepanelTrigger.addEventListener("click", () => {
					toggleSidepanel();
				});
			})();
		</script>';
		
		if (qa_opt('site_theme') == 'Polaris' && method_exists($this, 'minify_code')) {
			$this->output($this->minify_code($javascript));
		} else {
			$this->output($javascript);
		}
	}
	
}
