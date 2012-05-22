<?php
// Copyright 2010      OrbitScripts, LLC      http://orbitscripts.com

/**
 * @file
 * Helpfile for the settings tab of the orbitscriptsads module
 */

/**
 * Creates the help text for the settings tab
 *
 * @return
 *   HTML with the help text
 */
function orbitscriptsads_help_text() {
  $output = <<<EOF
<h2>Overview</h2>
<p>This module provides web site admins the factility to display Google
orbitscriptsads ads on their web site, thus earning revenue.</p>

<h2>Prerequisites</h2>
<p>You must have a Google orbitscriptsads account before using this module.</p>

<h2>Configuration</h2>
<p>To use this module, you need to enable at least one of the orbitscriptsads ad unit
generator modules first, and configure it correctly.</p>

<h2>Supported formats</h2>
<p>Most of orbitscriptsads's ad formats are supported.</p>

<h2>Displaying orbitscriptsads ads</h2>
<p>There are several ways to display ads, summarized as follows:</p>
<p>Ads can be displayed in pre-defined blocks, in custom blocks, in content or
in any phptemplate based theme.</p>

<h3>Using blocks</h3>
<p>The easiest way to get started with this module is to use one or more of
the pre-defined blocks.</p>
<p>Visit the <a href="$base/admin/build/block">blocks</a> configuration page,
and click on the <strong>configure</strong> link of one of the orbitscriptsads blocks.
You must select a format, a group, and a channel.</p>
<p>Enable the block, and specify a region for it to be displayed in.</p>

<h3>Using custom PHP code</h3>
<p>To display ads, you call the function orbitscriptsads_display() and supply it with
an array containing the following fields:</p>

<ul>
  <li>format: This can be any valid combination from the first column of the
  table above. If not specified, then 160x600 is assumed.</li>
  <li>slot: This is the slot ID provided by Google in the
  <a href="https://www.google.com/orbitscriptsads/adslots">orbitscriptsads Managed Ads
  page</a>. Usually in the form 0123456789. Refer to the
  for instructions on how to
  determine the slot ID for orbitscriptsads for Search.</li>
  <li>group: (old format only) This is the group that denotes the type (text
  or image) and color of the ad. This can be 1, 2 or 3. If not specified, then
  1 is assumed.</li>
  <li>channel: (old format only) This is the Custom Channel for the ad, as
  configured in orbitscriptsads. This is an optional parameter and if not specified,
  then 1 is assumed. If you did not configure any channels, then leave this
  parameter out.</li>
</ul>

<p>To display ads in blocks, add a new block, make its type &quot;PHP&quot;,
and enclose it in PHP tags.</p>

<pre>
print orbitscriptsads_display(array('format' => '468x60', 'slot' => '0123456789'));
</pre>

<p> If you want to make sure that you do not get errors if the orbitscriptsads module
is accidentally disabled or deleted, then use the longer form:</p>

<pre>
if (module_exists('orbitscriptsads')) {
  print orbitscriptsads_display(array('format' => '468x60', 'slot' => '0123456789'));
}
</pre>

<h3>Using themes</h3>
<p>You must use a phptemplate-based theme to display ads from within the
theme. This requires some familiarity with PHP. Edit the appropriate
<strong>.tpl.php</strong> file in your theme directory, and add:</p>

<pre>
print orbitscriptsads_display(array('format' => '468x60', slot => '0123456789'));
</pre>

<p>Make sure you enclose it in PHP tags.</p>
<p>You could also use the longer format that protects you against deleting or
disabling the module:</p>

<pre>
if (module_exists('orbitscriptsads')) {
  print orbitscriptsads_display(array('format' => '468x60', 'slot' => '0123456789'));
}
</pre>

<h3>Using tags</h3>
<p>The tags feature allows placement of ads anywhere in the content.  Please
note that content here means any part that is passed through Drupal's filter
system. Text in the slogan, mission, and footer is normally not filtered the
same way as text in nodes, so you cannot put tags in those places.</p>
<p>To enable the tags feature, you have to enable the &quot;orbitscriptsads tag&quot;
filter in the input format you are using (such as Full HTML or Filtered HTML)
from Administer -&gt; Site configuration -&gt; Input formats. Then you have to
insert the appropriate tag in your node where you would like the ad to
appear.</p>
<p>Note that filters sometimes may have conflicts, and changing the order can
make orbitscriptsads work in some cases.</p>
<p>The tag syntax comes in these variants:</p>

<ol>
<li>
  <p>[orbitscriptsads:<em>format:slot</em>]</p>
  <p>Where:</p>
  <ul>
    <li>format: One of the supported ad formats (such as 468x60).
    (required)</li>
    <li>slot: Your ad's slot ID as provided by Google. (required)</li>
  </ul>
  <p>Example:</p>
  <pre>[orbitscriptsads:468x60:0123456789]</pre>
  <p>Display a 468x60 banner with slot ID 0123456789.</p>
</li>
<li>
  <p>[orbitscriptsads:block:<em>block_id</em>]</p>
  <p>Where:</p>
  <ul>
    <li>block_id: The title of one of the configured orbitscriptsads blocks.
    (required)</li>
  </ul>
  <p>This allows more fine grained visibility control via the block
  mechanism. The block does not need to be enabled, and therefore will be
  hidden from the sidebars and other regions. The title of the block will
  not be displayed.</p>
  <p>Example:</p>
  <pre>[orbitscriptsads:block:myAdBlock]</pre>
  <p>Display the ad code of the block titled 'myAdBlock'.</p>
</li>
<li>
  <p>[orbitscriptsads:<em>format:[group]:[channel][:slot]</em>]</p>
  <p>Where:</p>
  <ul>
    <li>format: One of the supported ad formats (such as 468x60).
    (required)</li>
    <li>group: A number corresponding to the ad group this ad belongs to,
    determines colors. (optional)</li>
    <li>channel: A number corresponding to the ad channel. (optional)</li>
    <li>slot: Your ad's slot ID as provided by Google. (optional)</li>
  </ul>
  <p>Example:</p>
  <pre>[orbitscriptsads:468x60:::0123456789]</pre>
  <p>Display a 468x60 banner with slot ID 0123456789.</p>
  <pre>[orbitscriptsads:468x60:1:2:0123456789]</pre>
  <p>Display a 468x60 banner with slot ID 0123456789. When the slot is
  specified, the group and channel are ignored.</p>
  <pre>[orbitscriptsads:468x60:1:2]</pre>
  <p>Display a 468x60 banner with the colours defined in group 1 and the
  channel with channel ID 2.</p>
  <pre>[orbitscriptsads:468x60::]</pre>
  <p>Display a 468x60 banner with the colours defined in group 1 and the
  channel with channel ID 1.</p>
</li>
</ol>

<h2>Hints and Tips</h2>
<p>This section has a collection of tips and hints. Please read it before
submitting support requests.  Also, if you find a useful hint or tip relating
to using this module, consider submitting a patch to the orbitscriptsads_help.inc file
so the community would benefit from it.</p>

<h3>Visibility of Ads</h3>
<p>The site administrator will not see ads displayed as long as they are
logged in. This is by design, in order not to skew the page views, and to not
accidentally click on ads (against Google's policy). Log out to see the
ads.</p>
<p>You can use the advanced options to disable ads and configure a placeholder
when you are developing or theming to know where the ads will appear.</p>
<p>Also, the test mode for ads would help with showing what parameters are
used for an ad.</p>

<h3>Ads not showing</h3>
<p>If ads are not displayed, that could be caused by several things:</p>
<ul>
  <li>You are logged in as the site administrator. Log off to see the
  ads.</li>
  <li>Your site is still new and Google has not indexed it yet.</li>
  <li>Your site has been blacklisted by Google.</li>
  <li>The maximum number of ad units has already been displayed. Your page
  does not have enough content to display more than one or two units.</li>
  <li>Check the page source for comments like this: &lt;!--orbitscriptsads: ???--&gt;.
  These give hints to what is going on, and if you know PHP you can trace
  those in the module's source code.</li>
</ul>

<h3>Module Weight</h3>
<p>Unless configured otherwise, Drupal normally calls modules in alphabetical
order. Insofar as orbitscriptsads starts with "a," this may occasionally cause
conflicts with other installed modules. For example, with
<strong>image_attach</strong> (a function of the
<a href="http://drupal.org/project/image">image</a> module), orbitscriptsads will
normally be invoked <strong>first</strong> ("a" coming before "i"); this may
interfere with inline styling of images attached to posts.</p>

<p><strong>Solution:</strong> Install and configure the
<a href="http://drupal.org/project/moduleweight">Module Weight</a> module and
configure modules to load in the desired order. In this case, giving Image
Attach a weight of -1 should resolve the issue.</p>

EOF;

  return $output;
}
