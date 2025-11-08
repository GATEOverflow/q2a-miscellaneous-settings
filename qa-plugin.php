<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

// register admin page
qa_register_plugin_module('module', 'qa-misc-admin.php', 'qa_misc_admin', 'Misc Tweaks Settings');

//register ask page layer
qa_register_plugin_layer('qa-ask-reorder-layer.php', 'ASK page reorder Layer');

//register user profile hide and edit page layer
qa_register_plugin_layer('qa-user-profile-hide-layer.php', 'Hiding spam users profile from normal users, Restricting users for updating their own profiles');

//register favorites page layer
qa_register_plugin_layer('qa-favorite-list-link-layer.php', 'Adding lists link to the title of questions division in the favorites page Layer');


//register print page layer
qa_register_plugin_layer('qa-print-layer.php', 'Adding buttons to questions');

//Registering print page
qa_register_plugin_module('page', 'qa-print.php', 'qa_print_page', 'Page for printing questions/blogs');

//Registering a widget
qa_register_plugin_module('widget', 'qa-sidebar-toggle-widget.php', 'qa_sidebar_toggle_widget', 'Sidebar Toggle Widget');

//register account page layer for username change
qa_register_plugin_layer('qa-username-change-layer.php', 'Username Change Layer');

//Registering event for catching the username change event
qa_register_plugin_module('event', 'qa-username-change-event.php', 'qa_username_change_event', 'Username Change Event');

// register lang
qa_register_plugin_phrases('qa-lang-misc.php', 'qa_misc_lang');

