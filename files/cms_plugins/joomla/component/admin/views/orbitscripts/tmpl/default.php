<?php defined('_JEXEC') or die('Restricted access'); ?>
<style>
.icon-48-orbitscriptsads64 {
	background-image:url("components/com_orbitscriptsads/views/orbitscripts/img/orbitscriptsads64.png");
}
</style>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'Channel Name' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Status' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Position' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Dimension' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)	{
		$row = &$this->items[$i];
		$published  = JHTML::_('grid.published', $row, $i );
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_modules&client=0&task=edit&cid[]='. $row->id );
		
		//Get dimension
		$param = explode("\n", $row->params);
		if (isset($param[2])) {
			$param = str_replace('keys=','',$param[2]);
		} else {unset($param);}
		
		if (!empty($param)) $keys = unserialize(str_replace("'",'"',$param));
		
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center">
				<?php echo $row->id; ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->title; ?></a>
			</td>
			<td align="center">
				<?php echo $published; ?>
			</td>
			<td align="center">
				<?php echo $row->position; ?>
			</td>
			<td align="center">
				<?php if (isset($keys)) echo $keys['width'].'x'.$keys['height']; ?>
			</td>				
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>

<input type="hidden" name="option" value="com_orbitscriptsads" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="orbitscriptsads" />
</form>
