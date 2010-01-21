#!/usr/bin/env php
<?php

// Load the XML source
$xml = new DOMDocument;
$xml->load(dirname(__FILE__) . '/report/out.xml');

$xsl = new DOMDocument;
$xsl->load(dirname(__FILE__) . '/phpunit.xsl');

// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

$phpu = $proc->transformToXML($xml);

$now = date('c');

$out="<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'><title>PHPUnit Output</title></head><body><h1>Generated $now</h1>$phpu</body></html>";

file_put_contents(dirname(__FILE__) . '/report/out.html', $out);
