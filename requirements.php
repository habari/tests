<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
		define('MIN_PHP_VERSION', '5.2.0');

		// Required extensions, this list will augment with time
		// Even if they are enabled by default, it seems some install turn them off
		// We use the URL in the Installer template to link to the installation page
		$required_extensions = array(
			'pdo' => 'http://php.net/pdo',
			'hash' => 'http://php.net/hash',
			'iconv' => 'http://php.net/iconv',
			'tokenizer' => 'http://php.net/tokenizer',
			'simplexml' => 'http://php.net/simplexml',
			'mbstring' => 'http://php.net/mbstring',
			'json' => 'http://php.net/json',
			'pcre' => 'http://php.net/pcre'
			);
		$requirements_met = true;

		/* Check versions of PHP */
		$php_version_ok = version_compare(phpversion(), MIN_PHP_VERSION, '>=');
		
		if (! $php_version_ok) {
			$requirements_met = false;
		}
		/* Check for required extensions */
		$missing_extensions = array();
		foreach ($required_extensions as $ext_name => $ext_url) {
			if (!extension_loaded($ext_name)) {
				$missing_extensions[$ext_name] = $ext_url;
				$requirements_met = false;
			}
		}

		if ( extension_loaded('pdo') ) {
			/* Check for PDO drivers */
			$pdo_drivers = PDO::getAvailableDrivers();
			if ( ! empty( $pdo_drivers ) ) {
				$pdo_drivers = array_combine( $pdo_drivers, $pdo_drivers );
				// Include only those drivers that we include database support for
				$pdo_schemas = array( 'mysql', 'pgsql', 'sqlite' );
				$pdo_drivers = array_intersect(
					$pdo_drivers,
					$pdo_schemas
				);
				$pdo_missing_drivers = array_diff(
					$pdo_schemas,
					$pdo_drivers
				);
			}

			$pdo_drivers_ok = count( $pdo_drivers );

			if ( ! $pdo_drivers_ok ) {
				$requirements_met = false;
			}

			if ( $requirements_met && ! preg_match( '/\p{L}/u', 'a' ) ) {
				$requirements_met = false;
			}

		}
		else {
			$pdo_drivers_ok = false ;
			$pdo_drivers = array();
			$requirements_met = false;
		}

		/**
		 * $local_writable is used in the template, but never set in Habari
		 * Won't remove the template code since it looks like it should be there
		 *
		 * This will only meet the requirement so there's no "undefined variable" exception
		 */
		$local_writable = true ;

		/*return $requirements_met;*/
?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Content-Language" content="en">
	<meta name="robots" content="no index,no follow">
	<title>Habari Requirements Check</title>
<style type="text/css">
body {
	font-family: Helvetica Neue, Helvetica, Arial, Sans-Serif;
	padding: 0;
	margin: 0;
	}

#installer {
	text-align: center;
	min-width: 600px;
	background: #f1f1f1;
	}

a {
	color: #999;
	}

#wrapper {
	width: 552px;
	margin: 0 auto 50px;
	}

#masthead {
	margin: 30px 0;
	}

#masthead h1 {
	font-size: 35px;
	line-height: 35px;
	color: #333;
	margin-bottom: 0px;
	text-transform: lowercase;
	}

#masthead p {
	font-size: 11px;
	text-transform: lowercase;
	color: #666;
	margin: 0px;
	}

#sitename {
	font-weight: bold;
	}

#footer a {
	font-size: 11px !important;
	text-transform: lowercase;
	color: #999;
	padding: 0 3px;
	}

#footer a:hover {
	color: #666;
	}
	
ul {
	list-style: none;
	padding: 0;
	margin: 0;
	}

</style>
</head>

<body id="installer">
	<div id="wrapper">
		<?php $tab = 1; ?>
		<div id="masthead">
			<h1>Habari</h1>
			<div id="footer">
				<p class="left"><a href="http://wiki.habariproject.org/" title="Read the Habari wiki" tabindex="<?php echo $tab++ ?>">Wiki</a> &middot;
					<a href="http://groups.google.com/group/habari-users" title="Ask the community" tabindex="<?php echo $tab++ ?>">Mailing List</a>
				</p>
			</div>
		</div>


<?php if ( $requirements_met == false ) { ?>

	<div id="header">
		<h1>Before you install <em>Habari</em>...</h1>
	</div>
	<?php /* This whole chunk can probably be removed */ ?>
	<?php if (! $local_writable == true ) {?>
		<h2>Writable directory needed...</h2>
		<?php if (PHP_OS != 'WIN') {?>
			<p class="instructions">
				Before you can install Habari, you first need to make the install directory writable by php, so that the installation script can write your configuration information properly. The exact process of server and the ownership of the directory.
			</p>
			<p>
				If your webserver is part of the group which owns the directory, you'll need to add group write permissions to the directory. The procedure for this is as follows:
			</p>
			<ol>
				<li>
					Open a terminal window, and then change to the installation directory:
					<pre><strong>$&gt;</strong> cd <i>/path/to/habari/</i></pre>
				</li>
				<li>
					Change the <em>mode</em> (permissions) of the current directory:
					<pre><strong>$&gt;</strong> chmod g+w .</pre><br />
					<pre><strong>$&gt;</strong> chmod g+x .</pre>
					<p class="note">
						<em>Note</em>: You may need to use <strong>sudo</strong> and enter an administrator password if you do not own the directory.
					</p>
				</li>
			</ol>
			<p>
				If the webserver is not part of the group which owns the directory, you will need to <strong>temporarily</strong> grant world write permissions to the directory:
			</p>
			<ol>
				<li>
					<pre><strong>$&gt;</strong> chmod o+w .</pre><br />
					<pre><strong>$&gt;</strong> chmod o+x .</pre>
				</li>
			</ol>
			<p>
				<strong>Be sure to remove the write permissions on the directory as soon as the installation is completed.</strong>
			</p>
		<?php } else {?>
			<strong>@todo Windows instructions</strong>
		<?php }?>
	<?php }?>

	<?php if (! $php_version_ok) {?>
		<h2>PHP Upgrade needed...</h2>
		<p class="instructions">
			<em>Habari</em> <?php echo('requires PHP 5.2 or newer. Your current PHP version is %s.');
			echo(phpversion()); ?>
		</p>
		<strong>@todo Upgrading PHP instructions</strong>
	<?php }?>

	<?php if (! empty($missing_extensions)) {
		foreach ($missing_extensions as $ext_name => $ext_url) {
			$missing_ext_html[]= '<a href="' . $ext_url . '">' . $ext_name . '</a>';
		}
		$missing_ext_html = implode( ', ', $missing_ext_html );
	?>
		<h2>Missing Extensions</h2>
		<p class="instructions">
			<em>Habari</em> requires that the following PHP extensions to be installed: <?php echo $missing_ext_html; ?>. Please contact your web hosting provider if you do not have access to your server.
		</p>
	<?php }?>

	<?php if ( extension_loaded( 'pcre' ) && ! preg_match( '/\p{L}/u', 'a' ) ) : ?>
		<h2>Unicode support needed...</h2>
		<p class="instructions">
			<em>Habari</em> requires PHP's PCRE extension to have Unicode support enabled. Please contact your web hosting provider if you do not have access to your server.
		</p>

	<?php endif; ?>

	<?php if ( ! $pdo_drivers_ok && ! array_key_exists( 'pdo', $missing_extensions )  ) { ?>
		<h2>No PDO drivers enabled</h2>
		<p class="instructions"><em>Habari</em> requires that at least one <a href="http://www.php.net/pdo">PDO driver</a> be installed. Please ask your hosting provider to enable one of the PDO drivers supported by Habari.</p>
	<?php } ?>

<?php } else { ?>
	<div id="header">
	<h1>Your system is ready for Habari!</h1>
	</div>
	<ul>
		<li>Download the <a href="http://www.habariproject.org/en/download">latest stable version of Habari</a></li>
		<li>View the <a href="http://wiki.habariproject.org/en/Installation#Installing_Habari">installation instructions</a></li>
		<li>Get <a href="http://www.mibbit.com/?server=irc.freenode.net&channel=%23habari">live help</a></li>
	</ul>
<?php } ?>

</div><!-- end wrapper -->
</body>
</html>
