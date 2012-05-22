<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
//on dom ready...
window.addEvent( 'domready', function() {
	$('refresh_channels').addEvent( 'click', function() {
            $('ajax-container-channels').empty().addClass('ajax-loading');
            var a = new Ajax('index.php?option=com_orbitscriptsads&controller=orbitscriptsapi&task=getChannelsHtml', {
                    method: 'get',
        			update: $('ajax-container-channels'),
                    onComplete: function(response) {
                            // Other code to execute when the request completes.
                            $('ajax-container-channels').removeClass('ajax-loading').setHTML(response);
                    }
            }).request();
    });

	$('refresh_palettes').addEvent( 'click', function() {
        $('ajax-container-palettes').empty().addClass('ajax-loading');
        var a = new Ajax('index.php?option=com_orbitscriptsads&controller=orbitscriptsapi&task=getPalettesHtml', {
                method: 'get',
    			update: $('ajax-container-palettes'),
                onComplete: function(response) {
                        // Other code to execute when the request completes.
                        $('ajax-container-palettes').removeClass('ajax-loading').setHTML(response);
                }
        }).request();
	});
});
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Channel' ); ?>:
				</label>
			</td>
			<td>
           <div id="ajax-container-channels">
                 <?php echo $this->orbitscriptsapi->execute('getChannelsHtml');?>
            </div>
			</td>
			<td>
				<input id="refresh_channels" class="text_area" type="button" name="palette" value="Refresh" /> 
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Palette' ); ?>:
				</label>
			</td>
			<td>
			    <div id="ajax-container-palettes">
				   <?php echo $this->orbitscriptsapi->execute('getPalettesHtml');?>
				</div>
			</td>
			<td>
				<input id="refresh_palettes" class="text_area" type="button" name="palette" value="Refresh" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="clr"></div>
<input type="hidden" name="option" value="com_orbitscriptsads" />
<input type="hidden" name="id" value="<?php echo $this->orbitscriptsads->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="palette_name" value="" id="palette_name"/>
<input type="hidden" name="channel_name" value="" id="channel_name"/>
<input type="hidden" name="controller" value="orbitscriptsads" />
</form>
