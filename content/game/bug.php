<?php 

class BugPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Bug Reporting'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
startBox("Bugs"); ?>  
Reporting bugs is very important so that we can keep Stendhal running smoothly. So, if a bug is worth asking /support or a developer about, it is worth submitting a bug report on. 

<p>We have made this page to help you with the process of submitting a bug report, because we find them so helpful. 

<p> You can submit a new report <a href="http://sourceforge.net/tracker/?func=add&amp;group_id=1111&amp;atid=101111">here</a>, or browse the <a href="http://sourceforge.net/tracker/?group_id=1111&amp;atid=101111">bugs tracker</a>, you may wish to select Category <b>Open</b> and then click <b>Filter</b> if you only wish to see the Open bug reports. 



<p>We are more than happy to close reports that are not really bugs, and would prefer you to submit a bug report and us close it, than not submit it and we never find out about it. Having said that, before submitting a bug you should scan over the previously posted bugs summaries so that you don't report an already known bug.

<?php endBox(); ?>
<?php startBox("Making a Report"); ?>  

If you need any help on submitting a bug report feel free to come and ask the developers and contributors at the 
<?php echo '<a href="'.rewriteURL('/development/chat.html').'">'?>
irc channel #arianne</a>, you can just ask the question as the channel is logged, then wait for an answer incase noone was physically present when you arrived. 
<p>If there are multiple bugs to report, please open a report for each.</p> 
<ul>
<li> Summary - This is the title of the bug report. Choose a meaningful and brief sentence.
	<ul>
	<li>Bad summary: "there is a bug", "bug found" "error occurred"</li>
	<li>Good summary: "Stendhal 0.67 map wrong at x y in -3_semos_cave" or "Test client: buddies panel does not show buddies online state"</li>
	</ul>

<li> Description - This is where to write what happened. Please try to include: 
	<ul>
	<li> Where were you when the bug happened? (use '/where yourUserName' in game)</li>
	<li> What did you do when the bug happened?</li>
	<li> What did happen, what did you expect to happen</li>
	<li> Is it reproducable? If so: Which steps are needed to reproduce it?</li>
	<li> when talking about map-errors like 'you can walk under a chair' please provide the exact position and if possible a little screenshot.</li>
	<li> If things in client look weird a screenshot says more than 1000 words</li>
	<li> Your email address, if you are not a logged in member of sourceforge</li>
	<li><small>Unless you have a good reason to think it's irrelevant:</small>
	<li> Your Operating system</li>
	<li> If you run from webstart or downloaded client</li>
	<li> What java version you have (In Linux, type <i>java --version</i> in a command line.  Windows users, check <a href="http://www.java.com/en/download/installed.jsp?detect=jre&amp;try=1" class="external text" title="http://www.java.com/en/download/installed.jsp?detect=jre&amp;try=1" rel="nofollow">here</a>)</li>
	<li>any error logs (in log/stendhal.txt)</li>
	</ul>
</li>


<li>Private - please tick the check box for sensitive or easily abused issues.</li>
<li>File Upload - here is where to attach a screenshot or a text output of an error log.</li>
<li>Everything Else - don't worry about any of the other entries.</li>
</ul>
<?php endBox(); ?>
<?php startBox("Pre release Testers"); ?> 
<p>Please make sure you connect with the MAIN-client to the MAIN-server
and with the (up to date!) TEST-client to the TEST-server
</p>
<p>Please add to the bug summary if you were on TEST or on MAIN
<?php endBox(); ?>
<?php startBox("Developers"); ?> 
<p>Please make sure the bug also happens with latest cvs-HEAD <b>without</b> the changes you made to the source and did not yet commit.
</p><p>Remember the importance of 'ant clean' or whatever the equivalent is in your IDE.
</p>
<?php endBox(); 
	}
}
$page = new BugPage();
?>