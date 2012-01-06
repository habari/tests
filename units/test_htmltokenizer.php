<?php
/**
 * Test for the HTMLTokenizer class.
 */
include('../bootstrap.php');

class HTMLTokenizerTest extends UnitTestCase
{
	protected $html_strs = array();

	function setup()
	{

// hixie's tasty tag soup -- http://ln.hixie.ch/?start=1137740632&count=1
$this->html_strs[]= <<<_EOF_
<!DOCTYPE HTML><title>Hello World</title><p title="example">Some text.</p><!-- A comment. -->
_EOF_;

// BigJibby's tag soup
$this->html_strs[]= <<<_EOF_
<p>I am <div><script src="ohnoes" /><a>not a paragraph.</a><p CLASS=old><span> Or am I?</span>
_EOF_;

// ha.ckers.org
$this->html_strs[]= <<<_EOF_
<IMG src="http://ha.ckers.org/" style"="style="a/onerror=alert(String.fromCharCode(88,83,83))//" &ampgt;`&gt
_EOF_;

$this->html_strs[]= <<<_EOF_
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
[ <!ATTLIST html habari CDATA #IMPLIED> ]
>
<html>
<head habari="rocks">
<title>Foo Bar</title>
</head>
<body>
<h1>Hello World</h1>
<p>This is a good <a href="http://google.com/search?q=html">HTML</a> document.</p>
<![CDATA[This is &amp; <a href="foo">CDATA</a>.]]><strong>Lo bob</strong>.
</body>
</html>
_EOF_;

$this->html_strs[]= <<<_EOF_
<html><title>Oh &apos;eck!<body>This is a badly tag-soupy HTML document.</html>
_EOF_;

$this->html_strs[]= <<<_EOF_
<html>
<head><title>Hey</title></head>
<body onLoad="window.alert('zomg.');">
<p onClick="window.alert('stole yer cookies!');">Do not click here.</p>
<script>alert("See this?")</script>
</body>
</html>
_EOF_;
	}

	function test_tokenizer()
	{
		foreach ( $this->html_strs as $html_str ) {
			$t = new HTMLTokenizer( $html_str );
			$tokens = $t->parse();
			$new_str = (string)$tokens;

			$this->assert_identical( $html_str, $new_str );
			$this->output( Utils::htmlspecialchars( $html_str ) . "<br>" . Utils::htmlspecialchars( $new_str ) );
		}
	}

	function teardown()
	{
	}
}
HTMLTokenizerTest::run_one( 'HTMLTokenizerTest' );
?>
