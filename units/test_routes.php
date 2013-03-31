<?php
namespace Habari;

class RoutesTest extends UnitTestCase
{
	/** @var RewriteRules $rules */
	protected $rules;

	public function setup()
	{
		$this->rules = RewriteRules::get_active();
	}

	/*
			// Display content
			array( 'name' => 'display_home', 'parse_regex' => '#^(?:page/(?P<page>0|1))?/?$#', 'build_str' => '(page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_home', 'priority' => 1000, 'description' => 'Homepage (index) display' ),
			array( 'name' => 'display_entries', 'parse_regex' => '#^(?:page/(?P<page>[2-9]|[1-9][0-9]+))/?$#', 'build_str' => '(page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_entries', 'priority' => 999, 'description' => 'Display multiple entries' ),
			array( 'name' => 'display_entries_by_date', 'parse_regex' => '#^(?P<year>[1,2]{1}[\d]{3})(?:/(?P<month>[\d]{2}))?(?:/(?P<day>[\d]{2}))?(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$year}(/{$month})(/{$day})(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_date', 'priority' => 2, 'description' => 'Displays posts for a specific date.' ),
			array( 'name' => 'display_entries_by_tag', 'parse_regex' => '#^tag/(?P<tag>[^/]*)(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => 'tag/{$tag}(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_tag', 'priority' => 5, 'description' => 'Return posts matching specified tag.', 'parameters' => serialize( array( 'require_match' => array('Tag', 'rewrite_tag_exists') ) ) ),
			array( 'name' => 'display_entry', 'parse_regex' => '#^(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$slug}(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_post', 'priority' => 100, 'description' => 'Return entry matching specified slug', 'parameters' => serialize( array( 'require_match' => array('Posts', 'rewrite_match_type'), 'content_type'=>'entry', 'request_types' => array('display_post') ) ) ),
			array( 'name' => 'display_page', 'parse_regex' => '#^(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$slug}(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_post', 'priority' => 100, 'description' => 'Return page matching specified slug', 'parameters' => serialize( array( 'require_match' => array('Posts', 'rewrite_match_type'), 'content_type'=>'page', 'request_types' => array('display_post') ) ) ),
			array( 'name' => 'display_search', 'parse_regex' => '#^search(?:/(?P<criteria>[^/]+))?(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => 'search(/{$criteria})(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'search', 'priority' => 8, 'description' => 'Searches posts' ),
			array( 'name' => 'display_404', 'parse_regex' => '/^.*$/', 'build_str' => '', 'handler' => 'UserThemeHandler', 'action' => 'display_404', 'priority' => 9999, 'description' => 'Displays an error page when a URL is not matched.' ),
			array( 'name' => 'display_post', 'parse_regex' => '#^(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$slug}(/page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_post', 'priority' => 9998, 'description' => 'Fallback to return post matching specified slug if no content_type match' ),

			// Form actions
			array( 'name' => 'submit_feedback', 'parse_regex' => '#^(?P<id>[0-9]+)/feedback/?$#i', 'build_str' => '{$id}/feedback', 'handler' => 'FeedbackHandler', 'action' => 'add_comment', 'priority' => 8, 'description' => 'Adds a comment to a post' ),

			// Admin actions
			array( 'name' => 'display_dashboard', 'parse_regex' => '#^admin/?$#', 'build_str' => 'admin', 'handler' => 'AdminDashboardHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Display the admin dashboard' ),
			array( 'name' => 'display_publish', 'parse_regex' => '#^admin/(?P<page>publish)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminPostsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage publishing posts' ),
			array( 'name' => 'display_posts', 'parse_regex' => '#^admin/(?P<page>posts)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminPostsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage posts' ),
			array( 'name' => 'delete_post', 'parse_regex' => '#^admin/(?P<page>delete_post)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminPostsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Delete a post' ),
			array( 'name' => 'user_profile', 'parse_regex' => '#^admin/(?P<page>user)/(?P<user>[^/]+)/?$#', 'build_str' => 'admin/{$page}/{$user}', 'handler' => 'AdminUsersHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'The profile page for a specific user' ),
			array( 'name' => 'display_users', 'parse_regex' => '#^admin/(?P<page>users)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminUsersHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage users' ),
			array( 'name' => 'own_user_profile', 'parse_regex' => '#^admin/(?P<page>user)/?$#', 'build_str' => 'admin/{$page}/{$user}', 'handler' => 'AdminUsersHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'The profile page for a specific user' ),
			array( 'name' => 'display_themes', 'parse_regex' => '#^admin/(?P<page>themes)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminThemesHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage themes' ),
			array( 'name' => 'activate_theme', 'parse_regex' => '#^admin/(?P<page>activate_theme)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminThemesHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Activate a theme' ),
			array( 'name' => 'preview_theme', 'parse_regex' => '#^admin/(?P<page>preview_theme)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminThemesHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Preview a theme' ),
			array( 'name' => 'configure_block', 'parse_regex' => '#^admin/(?P<page>configure_block)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminThemesHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Configure a block in an iframe' ),
			array( 'name' => 'display_plugins', 'parse_regex' => '#^admin/(?P<page>plugins)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminPluginsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage plugins' ),
			array( 'name' => 'plugin_toggle', 'parse_regex' => '#^admin/(?P<page>plugin_toggle)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminPluginsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Activate or deactivate a plugin' ),
			array( 'name' => 'display_options', 'parse_regex' => '#^admin/(?P<page>options)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminOptionsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'The options page for the blog' ),
			array( 'name' => 'display_comments', 'parse_regex' => '#^admin/(?P<page>comments)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminCommentsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage comments' ),
			array( 'name' => 'edit_comment', 'parse_regex' => '#^admin/(?P<page>comment)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminCommentsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Edit a comment' ),
			array( 'name' => 'display_groups', 'parse_regex' => '#^admin/(?P<page>groups)/?$#', 'build_str' => 'admin/{$page}', 'handler' => 'AdminGroupsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage groups' ),
			array( 'name' => 'display_group', 'parse_regex' => '#^admin/(?P<page>group)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminGroupsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage a group' ),
			array( 'name' => 'display_tags', 'parse_regex' => '#^admin/(?P<page>tags)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminTagsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage tags' ),
			array( 'name' => 'display_logs', 'parse_regex' => '#^admin/(?P<page>logs)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminLogsHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage logs' ),
			array( 'name' => 'display_import', 'parse_regex' => '#^admin/(?P<page>import)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminimportHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Manage importing content' ),
			array( 'name' => 'get_locale', 'parse_regex' => '#^admin/(?P<page>locale)/?$#i', 'build_str' => 'admin/{$page}', 'handler' => 'AdminLocaleHandler', 'action' => 'admin', 'priority' => 4, 'description' => 'Fetch the locale data as javascript' ),

			array( 'name' => 'admin', 'parse_regex' => '#^admin(?:/?$|/(?P<page>[^/]*))/?$#i', 'build_str' => 'admin/({$page})', 'handler' => 'AdminHandler', 'action' => 'admin', 'priority' => 6, 'description' => 'An admin action' ),

			// Admin AJAX actions
			array( 'name' => 'admin_ajax_dashboard', 'parse_regex' => '#^admin_ajax/(?P<context>dashboard)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminDashboardHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for the admin dashboard' ),
			array( 'name' => 'admin_ajax_posts', 'parse_regex' => '#^admin_ajax/(?P<context>posts)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminPostsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for the managing posts' ),
			array( 'name' => 'admin_ajax_update_posts', 'parse_regex' => '#^admin_ajax/(?P<context>update_posts)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminPostsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for the updating posts' ),
			array( 'name' => 'admin_ajax_media', 'parse_regex' => '#^admin_ajax/(?P<context>media)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminPostsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling from media silos' ),
			array( 'name' => 'admin_ajax_media_panel', 'parse_regex' => '#^admin_ajax/(?P<context>media_panel)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminPostsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling from media panels' ),
			array( 'name' => 'admin_ajax_media_upload', 'parse_regex' => '#^admin_ajax/(?P<context>media_upload)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminPostsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling from media panel uploads' ),
			array( 'name' => 'admin_ajax_add_block', 'parse_regex' => '#^admin_ajax/(?P<context>add_block)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminThemesHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for adding a block' ),
			array( 'name' => 'admin_ajax_delete_block', 'parse_regex' => '#^admin_ajax/(?P<context>delete_block)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminThemesHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for deleting a block' ),
			array( 'name' => 'admin_ajax_save_areas', 'parse_regex' => '#^admin_ajax/(?P<context>save_areas)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminThemesHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for saving areas' ),
			array( 'name' => 'admin_ajax_comments', 'parse_regex' => '#^admin_ajax/(?P<context>comments)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminCommentsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for managing comments' ),
			array( 'name' => 'admin_ajax_update_comment', 'parse_regex' => '#^admin_ajax/(?P<context>update_comment)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminCommentsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for updating a comment' ),
			array( 'name' => 'admin_ajax_groups', 'parse_regex' => '#^admin_ajax/(?P<context>groups)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminGroupsHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for managing groups' ),
			array( 'name' => 'admin_ajax_update_groups', 'parse_regex' => '#^admin_ajax/(?P<context>update_groups)/?$#', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminGroupsHandler', 'action' => 'admin_ajax', 'priority' => 4, 'description' => 'Authenticated ajax handler for updating a group' ),
			array( 'name' => 'admin_ajax_tags', 'parse_regex' => '#^admin_ajax/(?P<context>tags)/?$#', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminTagsHandler', 'action' => 'admin_ajax', 'priority' => 4, 'description' => 'Authenticated ajax handler for managing tags' ),
			array( 'name' => 'admin_ajax_get_tags', 'parse_regex' => '#^admin_ajax/(?P<context>get_tags)/?$#', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminTagsHandler', 'action' => 'admin_ajax', 'priority' => 4, 'description' => 'Authenticated ajax handler for retrieving tags' ),
			array( 'name' => 'admin_ajax_logs', 'parse_regex' => '#^admin_ajax/(?P<context>logs)/?$#', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminLogsHandler', 'action' => 'admin_ajax', 'priority' => 4, 'description' => 'Authenticated ajax handler for managing logs' ),
			array( 'name' => 'admin_ajax_delete_logs', 'parse_regex' => '#^admin_ajax/(?P<context>delete_logs)/?$#', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminLogsHandler', 'action' => 'admin_ajax', 'priority' => 4, 'description' => 'Authenticated ajax handler for deleting logs' ),

			array( 'name' => 'admin_ajax', 'parse_regex' => '#^admin_ajax/(?P<context>[^/]+)/?$#i', 'build_str' => 'admin_ajax/{$context}', 'handler' => 'AdminHandler', 'action' => 'admin_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling for the admin' ),

			// User actions
			array( 'name' => 'auth', 'parse_regex' => '#^auth/(?P<page>[^/]*)$#i', 'build_str' => 'auth/{$page}', 'handler' => 'UserHandler', 'action' => '{$page}', 'priority' => 7, 'description' => 'A user action or display, for instance the login screen' ),
			array( 'name' => 'user', 'parse_regex' => '#^user/(?P<page>[^/]*)$#i', 'build_str' => 'user/{$page}', 'handler' => 'UserHandler', 'action' => '{$page}', 'priority' => 7, 'description' => 'A user action or display, for instance the login screen' ),

			// AJAX requests
			array( 'name' => 'ajax', 'parse_regex' => '#^ajax/(?P<context>[^/]+)/?$#i', 'build_str' => 'ajax/{$context}', 'handler' => 'AjaxHandler', 'action' => 'ajax', 'priority' => 8, 'description' => 'Ajax handling' ),
			array( 'name' => 'auth_ajax', 'parse_regex' => '#^auth_ajax/(?P<context>[^/]+)/?$#i', 'build_str' => 'auth_ajax/{$context}', 'handler' => 'AjaxHandler', 'action' => 'auth_ajax', 'priority' => 8, 'description' => 'Authenticated ajax handling' ),

			// Atom Syndication Format
			array( 'name' => 'rsd', 'parse_regex' => '/^rsd$/i', 'build_str' => 'rsd', 'handler' => 'AtomHandler', 'action' => 'rsd', 'priority' => 1, 'description' => 'RSD output' ),
			array( 'name' => 'atom_entry', 'parse_regex' => '#^(?P<slug>[^/]+)/atom/?$#i', 'build_str' => '{$slug}/atom', 'handler' => 'AtomHandler', 'action' => 'entry', 'priority' => 8, 'description' => 'Atom Publishing Protocol' ),
			array( 'name' => 'atom_feed', 'parse_regex' => '#^atom/(?P<index>[^/]+)(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => 'atom/{$index}(/page/{$page})', 'handler' => 'AtomHandler', 'action' => 'collection', 'priority' => 8, 'description' => 'Atom collection' ),
			array( 'name' => 'atom_feed_comments', 'parse_regex' => '#^atom/comments(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => 'atom/comments(/page/{$page})', 'handler' => 'AtomHandler', 'action' => 'comments', 'priority' => 7, 'description' => 'Entries comments' ),
			array( 'name' => 'atom_feed_tag', 'parse_regex' => '#^tag/(?P<tag>[^/]+)/atom(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => 'tag/{$tag}/atom(/page/{$page})', 'handler' => 'AtomHandler', 'action' => 'tag_collection', 'priority' => 8, 'description' => 'Atom Tag Collection', 'parameters' => serialize( array( 'require_match' => Method::create('\\Habari\\Tag', 'rewrite_tag_exists') ) ) ),
			array( 'name' => 'atom_feed_entry_comments', 'parse_regex' => '#^(?P<slug>[^/]+)/atom/comments(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$slug}/atom/comments(/page/{$page})', 'handler' => 'AtomHandler', 'action' => 'entry_comments', 'priority' => 8, 'description' => 'Entry comments' ),
			array( 'name' => 'atom_feed_page_comments', 'parse_regex' => '#^(?P<slug>[^/]+)/atom/comments(?:/page/(?P<page>\d+))?/?$#i', 'build_str' => '{$slug}/atom/comments(/page/{$page})', 'handler' => 'AtomHandler', 'action' => 'entry_comments', 'priority' => 8, 'description' => 'Page comments' ),

			// Atom Publishing Protocol
			array( 'name' => 'atompub_servicedocument', 'parse_regex' => '/^atom$/i', 'build_str' => 'atom', 'handler' => 'AtomHandler', 'action' => 'introspection', 'priority' => 1, 'description' => 'Atom introspection' ),

			// Cron handling
			array( 'name' => 'cron', 'parse_regex' => '#^cron/(?P<time>[0-9.]+)/?$#i', 'build_str' => 'cron/{$time}', 'handler' => 'CronHandler', 'action' => 'poll_cron', 'priority' => 1, 'description' => 'Asyncronous cron processing' ),

			// XMLRPC requests
			array( 'name' => 'xmlrpc', 'parse_regex' => '#^xmlrpc/?$#i', 'build_str' => 'xmlrpc', 'handler' => 'XMLRPCServer', 'action' => 'xmlrpc_call', 'priority' => 8, 'description' => 'Handle incoming XMLRPC requests.' ),

	 */

	public function test_display_home()
	{
		$rule = URL::parse('');
		$this->assert_equal($rule->name, 'display_home');
	}

	public function test_display_entries_by_tag()
	{
		$this->mark_test_incomplete('This test needs to create a post that has the created tag, or the assertion won\'t pass.');
		$foo = Tag::create('foo');
		$url = URL::get('display_entries_by_tag', array('tag' => 'foo'));
		$this->output($url);
		$url = str_replace(Site::get_url('site', true), '', $url);
		$rule = URL::parse($url);
		$this->output($rule->name);
		$this->assert_equal($rule->name, 'display_entries_by_tag');
		$foo->delete();
	}

}