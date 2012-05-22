<?php
// Copyright 2010      OrbitScripts, LLC      http://orbitscripts.com

/**
 * @file
 * Helpfile for the Publisher ID tab of the orbitscriptsads module configuration
 */

/**
 * Creates the help text for the Publisher ID tab
 *
 * @return
 *   HTML with the help text
 */
function orbitscriptsads_id_help_text() {
  $output = <<<EOF
<h2>Overview</h2>
<p>This module provides a simple way of selecting the orbitscriptsads Publisher ID for a single owner site
that does not need revenue sharing.</p>

<h2>Prerequisites</h2>
<p>You must have a Google orbitscriptsads account before using this module.</p>

<h2>Configuration</h2>
<p>To use this module, simply enter your Google orbitscriptsads Publisher ID in the text field, and click
the 'Save configuration' button.</p>
EOF;
  return $output;
}
