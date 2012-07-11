<?php
// Copyright 2005-2008 Khalid Baheyeldin - http://2bits.com
// Copyright 2008 Joao Ventura - http://www.venturas.org

/**
 * @file
 * Helpfile for the Managed ads generator module
 */

/**
 * Creates the help text for the Managed Ads tab
 *
 * @return
 *   HTML with the help text
 */
function orbitscriptsads_managed_help_text() {
  $output = <<<EOF
<h2>Overview</h2>
<p>This module enables the simple inclusion of the new form of Google
orbitscriptsads Managed Ads in your Drupal site.</p>

<h2>Prerequisites</h2>
<p>You must have a Google orbitscriptsads account before using this module.</p>

<h2>Configuration</h2>
<p>To use this module, simply go to
<a href="https://www.google.com/orbitscriptsads/settings">Google's orbitscriptsads site</a>
and create your ad units. Simply note down their slot numbers and use them
in this module.</p>
<p>When you create your ad in your Google orbitscriptsads account, it generally
takes less than 30 minutes before they start showing in your site, but it
can take as long as 48 hours. See the <a
href="https://www.google.com/orbitscriptsads/support/bin/answer.py?answer=44511">
orbitscriptsads Code Implementation Guide</a> for more details.</p>
EOF;
  return $output;
}
