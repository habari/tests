<?php

/**
 * Test for the HTMLTokenizer class.
 */
include('../htdocs/system/classes/htmltokenizer.php');

include('../htdocs/system/classes/utils.php');

// hixie's tasty tag soup -- http://ln.hixie.ch/?start=1137740632&count=1
$html_strs[]= <<<_EOF_
<!DOCTYPE HTML><title>Hello World</title><p title="example">Some text.</p><!-- A comment. -->
_EOF_;

// BigJibby's tag soup
$html_strs[]= <<<_EOF_
<p>I am <div><script src="ohnoes" /><a>not a paragraph.</a><p CLASS=old><span> Or am I?</span>
_EOF_;

$html_strs[]= <<<_EOF_
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

$html_strs[]= <<<_EOF_
<html><title>Oh &apos;eck!<body>This is a badly tag-soupy HTML document.</html>
_EOF_;

$html_strs[]= <<<_EOF_
<html>
<head><title>Hey</title></head>
<body onLoad="window.alert('zomg.');">
<p onClick="window.alert('stole yer cookies!');">Do not click here.</p>
<script>alert("See this?")</script>
</body>
</html>
_EOF_;

foreach ($html_strs as $html_str) {
	$t= new HTMLTokenizer( $html_str );
	$tokens= $t->parse();
	Utils::debug( $html_str, $tokens );
}

?>
