<?php
/*
 Copyright (C) 2011 Faiumoni

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('scripts/memberdata.php');

class MemberdataPage extends Page {

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Memberdata'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if(!isset($_SESSION['account'])) {
			startBox("Meeting");
			echo '<p>Please <a href="/?id=content/association/login&amp;url=/?content/association/memberdata">login</a>.</p>';
			endBox();
		} else {
			$this->printForm();
		}
	}
	
	function printForm() {
		$playerId = $_SESSION['account']->id;
		$saved = saveMemberdata($playerId);
		$registration = getMemberdata($playerId);

		startBox('Membership Data');
?>
		<p>Note: You can modify this form later.</p>

		<?php
			if ($saved) {
				echo '<p style="font-size: +3; color:black; font-weight: bold; border: 3px solid green; padding: 1em">Data saved successfully.</p>';
			}
		?>

		<form method="POST" action="/index.php?id=content/association/memberdata">
		<table>
		<tr>
			<td><label for="realname">First and last name:</label><span style="color: red">*</span></td>
			<td><input type="text" name="realname" value="<?php echo htmlspecialchars($registration->realname)?>"></td>
		</tr>

		<tr>
			<td><label for="street">Street and Number:</label><span style="color: red">*</span></td>
			<td><input type="text" name="street" value="<?php echo htmlspecialchars($registration->street)?>"></td>
		</tr>

		<tr>
			<td><label for="city">Postalcode and City:</label><span style="color: red">*</span></td>
			<td><input type="text" name="city" value="<?php echo htmlspecialchars($registration->city)?>"></td>
		</tr>

		<tr>
			<td><label for="country">Country:</label><span style="color: red">*</span></td>
			<td><input type="text" name="country" value="<?php echo htmlspecialchars($registration->country)?>"></td>
		</tr>

		<tr>
			<td><label for="email">Email:</label><span style="color: red">*</span></td>
			<td><input type="text" name="email" value="<?php echo htmlspecialchars($registration->email)?>"></td>
		</tr>

		<tr>
			<td><label for="visiblename">Visibility of Name:</label></td>
			<td>
				<input type="radio" name="visiblename" id="visiblename_public" value="public" <?php if($registration->visiblename=="public") echo 'checked="checked"'?>>
				<label for="visiblename_public">Public</label>
				<input type="radio" name="visiblename" id="visiblename_members" value="members" <?php if($registration->visiblename=="members") echo 'checked="checked"'?>>
				<label for="visiblename_members">Members only</label>
			</td>
		</tr>

		<tr>
			<td><label for="visibleemail">Visibility of eMail:</label></td>
			<td>
				<input type="radio" name="visibleemail" id="visibleemail_members" value="yes" <?php if($registration->visibleemail=="members") echo 'checked="checked"'?>>
				<label for="visibleemail_members">Members</label>
				<input type="radio" name="visibleemail" id="visibleemail_management" value="tentative" <?php if($registration->visibleemail=="management") echo 'checked="checked"'?>>
				<label for="visibleemail_management">Management only</label>
			</td>
		</tr>

		<tr>
			<td></td>
			<td><input type="submit" value="Save"></td>
		</tr>

		</table>
		</form>
		<p>A red asterix <span style="color: red">*</span> marks a required field.</p>

		<?php

		endBox(); 

	}
}
$page = new MemberdataPage();
?>