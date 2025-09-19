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

// register lang
qa_register_plugin_phrases('qa-lang-misc.php', 'qa_misc_lang');

