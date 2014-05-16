<?php
class AdminScreenshotsPage extends Page {
	function writeContent() {

if(getAdminLevel()<400) {
 die("Ooops!");
}
   
if(isset($_POST['action'])) {
  if($_REQUEST['action']=='submit') {

    startBox("Adding Event");
      $target_path = "screenshots/";
      $target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
      if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
        echo "The file ".  basename( $_FILES['uploadedfile']['name']).  " has been uploaded";
      } else{
        echo "There was an error uploading the file, please try again!";
      }
      addScreenshot($target_path,$_REQUEST['description']);
    endBox();
  } elseif($_REQUEST['action']=='update') {
    startBox("Updating event");
      updateScreenshot($_REQUEST['event_id'],$_REQUEST['url'],$_REQUEST['description']);
    endBox();
  } elseif($_REQUEST['action']=='delete') {
    startBox("Deleting event");
      foreach($_REQUEST['delete'] as $id) {
        deleteScreenshot($id);
      }
    endBox();
  }
}

if ((isset($_REQUEST['action'])) && $_REQUEST['action']=='edit') {    
  $id=mysql_real_escape_string($_REQUEST['edit']);  
  $screenshotToEdit=getScreenshots('where id="'.$id.'"');
  if(sizeof($screenshotToEdit)==0) {
    startBox("Edit events");
      echo '<div class="error">No such screenshot</div';
    endBox();
  } else {
    $edited=$screenshotToEdit[0];
  }
}

  /*
   * Show all the previous event items, just header and to tickets approbed and deleted.
   */ 
  $screenshots=getScreenshots();
  startBox("Admin on existing screenshots");
  ?>
  <form class="news" method="post" action="/?id=content/admin/screenshots" name="updateevents">
  <input type="hidden" name="action" value="delete"/>
  <?php
  foreach($screenshots as $item) {
    ?>
    <div class="events_list">
    <input type="checkbox" name="delete[]" value="<?php echo $item->id; ?>">
    <span><a href="/?id=content/admin/screenshots&amp;action=edit&amp;edit=<?php echo $item->id; ?>"><?php $item->showThumbnail(); ?></a></span>
    </div>
    <?php
    }
  ?> 
  <div style="text-align: right;"><input type="submit" value="Delete"></div>
  </form>
  <?php
  endBox();  
?>

<?php startBox((isset($edited)?'Edit':'Submit').' screenshots'); ?>
<form class="news" method="post" enctype="multipart/form-data" action="/?id=content/admin/screenshots" name="submitevents">
  <?php 
   if(isset($edited)) {
   ?>
  <input type="hidden" name="action" value="update"/>
  <input type="hidden" name="event_id" value="<?php echo $_REQUEST['edit']; ?>"/>
   <?php
   } else {
   ?>
  <input type="hidden" name="action" value="submit"/>
  <?php
   }
   ?>
  <table width="100%">
    <tbody>
      <tr>
        <td>URL</td>
      </tr>      
      <tr>      
        <td><input name="uploadedfile" type="file" size="65"/>
        </td>
      </tr>
      <tr>
        <td>Extended description</td>
      </tr>
      <tr>
        <td><textarea rows="14" name="description"><?php if(isset($edited)) echo $edited->description; ?></textarea></td>
      </tr>
      <tr>
        <td><input type="submit" value="Submit"></td>
      </tr>
    </tbody>
  </table>
  <br>
</form>
<?php
		endBox();
	}
}
$page = new AdminScreenshotsPage();
?>