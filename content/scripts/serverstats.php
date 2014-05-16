<?php
function getVariable($xmlStats, $type) {
  foreach($xmlStats['statistics'][0]['attrib'] as $i=>$j) {
    if(is_array($j) and $j['name']==$type) {
	  return $j['value'];
	}
  }
  
  return 0;
}

function getServerUptime($xmltree) {
	$secs = $xmltree['statistics'][0]['uptime']['0 attr']['value'];
	$hours = $secs/3600;
	$days = (int)($hours/24);
	$remhours= $hours%24;
	return $days.' days and '.$remhours.' hours';
}

class ServerStatsPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Server Statistics'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {

$content=implode("",file(STENDHAL_SERVER_STATS_XML));
$xmlStats = XML_unserialize($content);

startBox("Detailed statistics");
?>
<div class="uptime">
  <?php echo getServerUptime($xmlStats); ?> since last server reset.
</div>
<div class="variable">
  <div class="title">Bytes managed</div>
  <div class="table">
    <div class="label">Received</div>
    <div class="data"><?php echo number_format(getVariable($xmlStats,"Bytes recv"), 0, '.', ' '); ?></div>
  </div>
  <div class="table">
    <div class="label">Send</div>
    <div class="data"><?php echo number_format(getVariable($xmlStats,"Bytes send"), 0, '.', ' '); ?></div>
  </div>
</div>

<div class="variable">
  <div class="title">Messages managed</div>
  <div class="table">
    <div class="label">Received</div>
    <div class="data"><?php echo number_format(getVariable($xmlStats,"Message recv"), 0, '.', ' '); ?></div>
  </div>
  <div class="table">
    <div class="label">Send</div>
    <div class="data"><?php echo number_format(getVariable($xmlStats,"Message send"), 0, '.', ' '); ?></div>
  </div>
</div>

<div class="variable">
  <div class="title">Players handled</div>
  <?php
  $list=array("login","invalid login","logout","timeout");
  
  foreach($list as $action) {
    ?>
    <div class="table">
      <div class="label"><?php echo ucfirst($action); ?></div>
      <div class="data"><?php echo number_format(getVariable($xmlStats,"Players ".$action), 0, '.', ' '); ?></div>
    </div>
  <?php  
  } 
  ?>
</div>

<div class="variable">
  <div class="title">Actions managed</div>
  <div class="table">
    <div class="label">Total</div>
    <div class="data"><?php echo number_format(getVariable($xmlStats,"Actions added"), 0, '.', ' '); ?></div>
  </div>
  <?php
  $list=array("move","chat","attack","inspect","who","where");
  
  foreach($list as $action) {
    ?>
    <div class="table">
      <div class="label"><?php echo ucfirst($action); ?></div>
      <div class="data"><?php echo number_format(getVariable($xmlStats,"Actions ".$action), 0, '.', ' '); ?></div>
    </div>
  <?php  
  } 
  ?>
</div>
<?php
endBox();
	}
}
$page = new ServerStatsPage();
?>