<?php
class mercury_fulltext extends Plugin

	{
	private $host;
	function about()
		{
		return array(
			2.1,
			"Try to get fulltext of the article using Mercury Parser",
			"https://github.com/jhotmann/mercury_fulltext/"
		);
		}

	function save()
		{
		$this->host->set($this, "mercury_API", $_POST["mercury_API"]);
		echo __("API key saved.");
		}

	function init($host)
		{
		$this->host = $host;
		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		$host->add_hook($host::HOOK_PREFS_EDIT_FEED, $this);
		$host->add_hook($host::HOOK_PREFS_SAVE_FEED, $this);
		$host->add_filter_action($this, "action_inline", __("Inline content"));
		}

	function hook_prefs_tab($args)
		{
		if ($args != "prefFeeds") return;

		if (version_compare(PHP_VERSION, '5.6.0', '<')) {
			print_error("This plugin requires PHP version 5.6.");
		}
		print "<div dojoType=\"dijit.layout.AccordionPane\" 
		title=\"<i class='material-icons'>extension</i> " . __('Mercury_fulltext settings (mercury_fulltext)') . "\">";
		print_notice("Enable the plugin for specific feeds in the feed editor.");
		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) $enabled_feeds = array();
		$enabled_feeds = $this->filter_unknown_feeds($enabled_feeds);
		$this->host->set($this, "enabled_feeds", $enabled_feeds);
		if (count($enabled_feeds) > 0)
			{
			print "<h3>" . __("Currently enabled for (click to edit):") . "</h3>";
			print "<ul class=\"browseFeedList\" style=\"border-width : 1px\">";
			foreach($enabled_feeds as $f)
				{
				print "<li>" . "<img src='images/pub_set.png'
						style='vertical-align : middle'> <a href='#'
						onclick='editFeed($f)'>" . Feeds::getFeedTitle($f) . "</a></li>";
				}

			print "</ul>";
			}

		print "</div>";
		}

	function hook_prefs_edit_feed($feed_id)
		{
		print "<div class=\"dlgSec\">" . __("Mercury") . "</div>";
		print "<div class=\"dlgSecCont\">";
		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) $enabled_feeds = array();
		$key = array_search($feed_id, $enabled_feeds);
		$checked = $key !== FALSE ? "checked" : "";
		print "<hr/><input dojoType=\"dijit.form.CheckBox\" type=\"checkbox\" id=\"mercury_fulltext_enabled\"
			name=\"mercury_fulltext_enabled\"
			$checked>&nbsp;<label for=\"mercury_fulltext_enabled\">" . __('Get fulltext via Mercury Parser') . "</label>";
		print "</div>";
		}

	function hook_prefs_save_feed($feed_id)
		{
		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) $enabled_feeds = array();
		$enable = checkbox_to_sql_bool($_POST["mercury_fulltext_enabled"]);
		$key = array_search($feed_id, $enabled_feeds);
		if ($enable)
			{
			if ($key === FALSE)
				{
				array_push($enabled_feeds, $feed_id);
				}
			}
		  else
			{
			if ($key !== FALSE)
				{
				unset($enabled_feeds[$key]);
				}
			}

		$this->host->set($this, "enabled_feeds", $enabled_feeds);
		}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	function hook_article_filter_action($article, $action)
		{
		return $this->process_article($article);
		}

	function process_article($article)
		{
    $url = str_replace('&', '\&', $article['link']);
    $filename = md5($url) . '.txt';
    passthru('mercury-parser ' . $url . ' > ' . $filename);
    $output = json_decode(file_get_contents($filename));
    $extracted_content = $output->content;
    unlink($filename);

		if ($extracted_content)
			{
			$article["content"] = $extracted_content;
			}

		return $article;
		}


	function hook_article_filter($article)
		{
		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) return $article;
		$key = array_search($article["feed"]["id"], $enabled_feeds);
		if ($key === FALSE) return $article;
		return $this->process_article($article);
		}

	function api_version()
		{
		return 2;
		}

	private
	function filter_unknown_feeds($enabled_feeds)
		{
		$tmp = array();
		foreach ($enabled_feeds as $feed) {
			$sth = $this->pdo->prepare("SELECT id FROM ttrss_feeds WHERE id = ? AND owner_uid = ?");
			$sth->execute([$feed, $_SESSION['uid']]);

			if ($row = $sth->fetch()) {
				array_push($tmp, $feed);
			}
		}

		return $tmp;
		}
	}
