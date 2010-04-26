<?php
require_javascript('og/tasks/TasksTopToolbar.js');
require_javascript('og/CalendarToolbar.js');
require_javascript('og/CalendarFunctions.js');
require_javascript('og/EventPopUp.js'); 
?>

<script type="text/javascript">
	scroll_to = -1;
</script>

<?php
define('PX_HEIGHT',42);
$year = isset($_GET['year']) ? $_GET['year'] : (isset($_SESSION['year']) ? $_SESSION['year'] : date('Y'));
$month = isset($_GET['month']) ? $_GET['month'] : (isset($_SESSION['month']) ? $_SESSION['month'] : date('n'));
$day = isset($_GET['day']) ? $_GET['day'] : (isset($_SESSION['day']) ? $_SESSION['day'] : date('j'));

$_SESSION['year'] = $year;
$_SESSION['month'] = $month;
$_SESSION['day'] = $day;

$tags = active_tag();	

$user_filter = $userPreferences['user_filter'];
$status_filter = $userPreferences['status_filter'];

$user = Users::findById(array('id' => $user_filter));
/*
 * If user does not exists, assign logged_user() to $user 
 * to prevent null exception when calling getRangeTasksByUser(), because this func. expects an User instance.
 */
if ($user == null) $user = logged_user();

$use_24_hours = user_config_option('time_format_use_24');
$date_format = user_config_option('date_format', 'd/m/Y');
?>
<?php echo stylesheet_tag('event/day.css') ?>

<?php
	$today = DateTimeValueLib::now();
	$today->add('h', logged_user()->getTimezone());
	$currentday = $today->format("j");
	$currentmonth = $today->format("n");
	$currentyear = $today->format("Y");

	$dtv = DateTimeValueLib::make(0,0,0,$month,$day,$year);
	 
	$result = ProjectEvents::getDayProjectEvents($dtv, $tags, active_project(), $user_filter, $status_filter); 
	if(!$result) $result = array();	
	
	$alldayevents = array();
	$milestones = ProjectMilestones::getRangeMilestonesByUser($dtv, $dtv, ($user_filter != -1 ? $user : null), $tags, active_project());	
	$tasks = ProjectTasks::getRangeTasksByUser($dtv, $dtv, ($user_filter != -1 ? $user : null), $tags, active_project());
	
	foreach ($result as $key => $event){
		if ($event->getTypeId()> 1){
			$alldayevents[] = $event;
			unset($result[$key]);
		}
	}
	
	if($milestones)
		$alldayevents = array_merge($alldayevents,$milestones);
	if($tasks)
		$alldayevents = array_merge($alldayevents,$tasks);	
	
	$alldaygridHeight = count($alldayevents)*PX_HEIGHT/2 + PX_HEIGHT/3;
	
	$loc = new Localization();
	$loc->setDateFormat(lang('view date title'));
	$view_title = $loc->formatDate($dtv);// lang(strtolower(date('l', $dtv))) . date(' j, ', $dtv) . lang('month ' . date('n', $dtv)) . date(' Y', $dtv);

	$users_array = array();
	$companies_array = array();
	foreach($users as $u)
		$users_array[] = $u->getArrayInfo();
	foreach($companies as $company)
		$companies_array[] = $company->getArrayInfo();	
?>
<div id="calHiddenFields">
	<input type="hidden" id="hfCalUsers" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($users_array)))) ?>"/>
	<input type="hidden" id="hfCalCompanies" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($companies_array)))) ?>"/>
	<input type="hidden" id="hfCalUserPreferences" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($userPreferences)))) ?>"/>
</div>

<div class="calendar" style="padding:0px;height:100%;overflow:hidden;" id="cal_main_div" onmouseup="clearPaintedCells();">
<div id="calendarPanelTopToolbar" class="x-panel-tbar" style="width:100%;height:30px;display:block;background-color:#F0F0F0;"></div>

<table style="width:100%;height:100%;">
<tr>
<td>
	<table style="width:100%;height:100%;">
		<tr>
			<td class="coViewHeader" id='cal_coViewHeader' colspan=2  rowspan=1>
				<div class="coViewTitle">				
					<table style="width:100%"><tr><td>
						<span id="chead0"><?php echo $view_title .' - '. ($user_filter == -1 ? lang('all users') : lang('calendar of', clean($user->getDisplayName()))); ?></span>
					</td><td style="width:100px;">
					<?php if (config_option("show_feed_links")) { ?>
						<?php echo checkbox_field("include_subws", true, array("id" => "include_subws", "style" => "float:right;", "onclick" => "javascript:og.change_link_incws('ical_link', 'include_subws')", "title" => lang('check to include sub ws'))) ?>
					 	<?php echo label_tag(lang('subws'), "include_subws", false, array("style" => "float:right;font-size:60%;margin:0px 3px;vertical-align:top;", "title" => lang('check to include sub ws')), "") ?>
					 	<?php 
					 		$export_name = active_project() != null ? clean(active_project()->getName()) : clean($user->getDisplayName());
					 		$export_ws = active_project() != null ? active_project()->getId() : 0;
					 	 ?>
					 	<a class="iCalSubscribe" id="ical_link" style="float:right;" href="<?php echo ROOT_URL ."/index.php?c=feed&a=ical_export&n=$export_name&cal=$export_ws&t=".$user->getToken()."&isw=1" ?>" 
					 		title="<?php echo lang('copy this url in your calendar client software')?>"
					 		onclick="Ext.Msg.show({
									  	title: '<?php echo escape_single_quotes(lang('import events from third party software')) ?>',
									   	msg: '<?php echo escape_single_quotes(lang('copy this url in your calendar client software')) ."<br><br><br>"?>'+document.getElementById('ical_link').href,
										icon: Ext.MessageBox.INFO }); return false;"></a>
					<?php } ?>
					 </td></tr></table>	
				</div>
			</td>
		</tr>
		
		<tr>
			<td class="coViewBody" style="padding:0px;height:100%;" colspan=2>
			<div id="chrome_main2" style="width:100%; height:100%;">
					
				<div id="allDayGrid" class="inset grid"  style="height: <?php echo $alldaygridHeight ?>px; margin-bottom: 5px;background:#E8EEF7;margin-right:0px;margin-left:40px;" 
					onclick="showEventPopup(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, -1, -1, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $dtv->format($date_format) ?>');" >
					
					<div id="allDay0" class="allDayCell" style="left: 0px; height: <?php echo $alldaygridHeight ?>px;border-left:3px double #DDDDDD !important; position:absolute;width:3px;"></div>
					<div id="alldayeventowner" onclick="og.disableEventPropagation(event) ">
						<?php	
							$top=0;
							foreach ($alldayevents as $event){	
								$tipBody = '';
								$divtype = '';
								$div_prefix = '';
								if ($event instanceof ProjectMilestone ){
									$div_prefix = 'd_ms_div_';
									$subject = clean($event->getName());
									$img_url = image_url('/16x16/milestone.png');
									$divtype = '<i>' . lang('milestone') . '</i> - ';
									$tipBody = lang('assigned to') .': '. clean($event->getAssignedToName()) . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : '');
								}elseif ($event instanceof ProjectTask){
									$div_prefix = 'd_ta_div_';
									$subject =$event->getTitle();
									$img_url = image_url('/16x16/tasks.png');
									$divtype = '<i>' . lang('task') . '</i> - ';
									$tipBody = lang('assigned to') .': '. clean($event->getAssignedToName()) . (trim(clean($event->getText())) != '' ? '<br><br>' . clean($event->getText()) : '');
								}elseif ($event instanceof ProjectEvent){
									$div_prefix = 'd_ev_div_';
									$subject = clean($event->getSubject());
									$img_url = image_url('/16x16/calendar.png');
									$divtype = '<i>' . lang('event') . '</i> - ';
									$tipBody = (trim(clean($event->getDescription())) != '' ? '<br>' . clean($event->getDescription()) : '');									
								}
								$tipBody = str_replace("\r", '', $tipBody);
								$tipBody = str_replace("\n", '<br>', $tipBody);
								if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';
								
								$dws = $event->getWorkspaces();
								$ws_color = 0;
								if (count($dws) >= 1){
									$ws_color = $dws[0]->getColor();
								}
								cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);	
														
						?>
						<div id="<?php echo $div_prefix . $event->getId() ?>" class="adc" style="left: 3px; top: <?php echo $top ?>px; z-index: 5;width: 99%;margin:1px;">
							<div class="t3 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-bottom:1px solid; border-color:<?php echo $border_color ?>"></div>
							<div class="noleft <?php echo  $ws_class?>" style="<?php echo  $ws_style?>; border-left:1px solid; border-right:1px solid; border-color:<?php echo $border_color ?>">							
								<div class="" style="overflow: hidden; padding-bottom: 1px;">
								
									<nobr style="display: block; text-decoration: none;"><a href='<?php echo $event->getViewUrl()?>' class='internalLink' onclick="og.disableEventPropagation(event);"><img src="<?php echo $img_url?>" align='absmiddle' border='0'> <span style="color:<?php echo $txt_color ?>!important"><?php echo $subject ?></span> </a></nobr>
								
								</div>
							</div>
							<div class="t3 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"></div>
						</div>
						<script type="text/javascript">
							addTip('<?php echo $div_prefix . $event->getId() ?>', <?php echo json_encode($divtype . $subject) ?>, <?php echo json_encode($tipBody) ?>);
						</script>
						<?php
								$top += 20;	
							}
						?>
					</div>
				</div>
				<div id="gridcontainer" style="background-color:#fff; overflow-x:hidden; overflow-y:scroll; height:504px; position:relative;" >	
						<div id='calowner' style="display:block; width:100%;">  
							<table cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed; width: 100%;height: 1008px;">
								<tr>
									<td id="rowheadcell" style="width: 40px;">
										<div id="rowheaders" style="height: 1008px; top: 0pt; left: 0pt;">										
										<?php
											$horas = array();
											$curr_hour = date("H");
											for ($hour=0; $hour<=23; $hour++){	
												$horas[$hour]	= 0;
												$procesados[$hour] = 0;
										?>
											<div style="height: 41px; top: 0ex;border-right:3px double #DDDDDD !important;background: #E8EEF7 none repeat scroll 0%;border-top:1px solid #DDDDDD;left:0pt;width: 100%;" id="rhead<?php echo $hour?>" class="rhead">
												<?php
													$hour == $curr_hour? print("<span id='curr_hour' style='visibility:hidden;height:0px;width:0px'></span>"):print('');
												?>
												<div class="rheadtext" style="text-align:right;padding-right:2px;"><?php echo date($use_24_hours ? "G:i" : "g a", mktime($hour, 0)) ?></div>
											</div>												
										<?php
											}
										?>

										</div>
									</td>
									<td id="gridcontainercell" style="width: auto;position:relative;" >	
										<div id="grid" style="height: 100%;background-color:#fff;position:relative;" class="grid">										
											<div id="decowner">
												
											</div>
											
											<?php
												for ($hour=0; $hour<=47; $hour++){	
													if ($hour % 2 == 0){
														$parity = "hruleeven";
														$style="border-top:1px solid #DDDDDD;";
													} else {
														$parity="hruleodd";
														$style="border-top:1px dotted #DDDDDD;";
													}
													$top = (PX_HEIGHT/2) * $hour;
													$div_id = 'h0_'.$hour;
											?>
													<div id="r<?php echo $hour?>"" class="hrule <?php echo $parity?>" style="top: <?php echo $top?>px; height:1px; z-index:101;position:absolute;left:0px;<?php echo $style?>;width:100%"></div>

													<div id="<?php echo $div_id?>" style="<?php echo $style ?>;width:100%;top: <?php echo $top?>px; z-index: 100; height:21px;position:absolute; border-left:3px double #DDDDDD;" 
														onmouseover="if (!selectingCells) overCell('<?php echo $div_id?>'); else paintSelectedCells('<?php echo $div_id?>');"
														onmouseout="if (!selectingCells) resetCell('<?php echo $div_id?>')";
														onmousedown="selectStartDateTime(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, <?php echo date("G",mktime($hour/2))?>, <?php echo ($hour % 2 ==0)?0:30 ?>); resetCell('<?php echo $div_id?>'); paintingDay=0; paintSelectedCells('<?php echo $div_id?>');"
														onmouseup="showEventPopup(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, <?php echo date("G",mktime(($hour+1)/2))?>, <?php echo (($hour+1) % 2 ==0)?0:30 ?>, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $dtv->format($date_format) ?>');"></div>
											<?php
												}
											?>
											
											<div id="eventowner" style="z-index: 102;" onclick="og.disableEventPropagation(event) ">
										<?php	
											$cells = array();
											for ($i = 0; $i < 24; $i++) {
												$cells[$i][0] = 0;
												$cells[$i][1] = 0;
											}
											foreach ($result as $event){
												$event_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
												$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
											
												$event_duration->add('s', -1);
												if ($event_start->getMinute() < 30) {
													$cells[$event_start->getHour()][0]++;
													$cells[$event_start->getHour()][1]++;
												} else $cells[$event_start->getHour()][1]++;
												for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++){
													$cells[$i][0]++;
													$cells[$i][1]++;
												}
												if ($event_duration->getMinute() > 0) {
													if ($event_duration->getHour() != $event_start->getHour()) {
														$cells[$event_duration->getHour()][0]++;
														if ($event_duration->getMinute() > 30) $cells[$event_duration->getHour()][1]++;
													}
												}
											}
											$occup = array(); //keys: hora - pos
											foreach ($result as $event){
												
												$event_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
												$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
											
												$event_id = $event->getId();
												$subject = clean($event->getSubject());
												$dws = $event->getWorkspaces();
												$ws_color = 0;
												
												if (count($dws) >= 1){
													$ws_color = $dws[0]->getColor();
												}	
												
												cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
												
												if($use_24_hours) $timeformat = 'G:i';
												else $timeformat = 'g:i A';
												$start_time = date($timeformat, $event_start->getTimestamp());
												$end_time = date($timeformat, $event_duration->getTimestamp());
												
												$hr_start = $event_start->getHour();
												$min_start = $event_start->getMinute();
												$hr_end = $event_duration->getHour();
												$min_end = $event_duration->getMinute();
												
												if ($event_start == $event_duration){
													$hr_end++;
												}
												$top = PX_HEIGHT * $hr_start + (PX_HEIGHT*(($min_start*100)/(60*100)));
												$bottom = PX_HEIGHT * $hr_end + (PX_HEIGHT*(($min_end*100)/(60*100)));
												$height = $bottom-$top - 4; //substract 4px for the rounded corners - 1px for separation
												if ($height < PX_HEIGHT/2 - 5) $height = PX_HEIGHT/2 - 4;
												
												$evs_same_time = 0;
												$i = $event_start->getHour();
												if ($event_start->getMinute() < 30) {
													if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
													if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
												} else if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
												
												for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++){
													if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
													if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
												}
												$i = $event_duration->getHour();
												if ($event_duration->getMinute() > 0) {
													if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
													if ($event_duration->getMinute() > 30) {
														if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
													}
												}
												
												$posHoriz = 0;
												$canPaint = false;
												while (!$canPaint) {
													$canPaint = true;
													if ($event_start->getMinute() < 30) {
														$canPaint = !(isset($occup[$event_start->getHour()][0][$posHoriz]) && $occup[$event_start->getHour()][0][$posHoriz]
																 || isset($occup[$event_start->getHour()][1][$posHoriz]) && $occup[$event_start->getHour()][1][$posHoriz]);
													} else {
														$canPaint = !(isset($occup[$event_start->getHour()][1][$posHoriz]) && $occup[$event_start->getHour()][1][$posHoriz]);
													}
													for($i = $event_start->getHour()+1; $canPaint && $i < $event_duration->getHour(); $i++) {
														if (isset($occup[$i][0][$posHoriz]) && $occup[$i][0][$posHoriz] || isset($occup[$i][1][$posHoriz]) && $occup[$i][1][$posHoriz]) {
															$canPaint = false;
														}
													}
													if ($canPaint) {
														if ($event_duration->getMinute() > 30) {
															$canPaint = !(isset($occup[$event_duration->getHour()][0][$posHoriz]) && $occup[$event_duration->getHour()][0][$posHoriz]
															|| isset($occup[$event_duration->getHour()][1][$posHoriz]) && $occup[$event_duration->getHour()][1][$posHoriz]);
														} else {
															$canPaint = !(isset($occup[$event_duration->getHour()][1][$posHoriz]) && $occup[$event_duration->getHour()][1][$posHoriz]);
														}
													}
													if (!$canPaint) $posHoriz++;
												}
												
												$width = 100 / $evs_same_time;
												$left = $width * $posHoriz + 0.25;
												$width -= 0.2;
												
												if ($event_start->getMinute() < 30) {
													$occup[$event_start->getHour()][0][$posHoriz] = true;
													$occup[$event_start->getHour()][1][$posHoriz] = true;
												} else {
													$occup[$event_start->getHour()][1][$posHoriz] = true;
												}
												for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++) {
													$occup[$i][0][$posHoriz] = true;
													$occup[$i][1][$posHoriz] = true;
												}
												if ($event_duration->getMinute() > 0) {
													$occup[$event_duration->getHour()][0][$posHoriz] = true;
													if ($event_duration->getMinute() > 30) {
														$occup[$event_duration->getHour()][1][$posHoriz] = true;
													}
												}
												
												if ($posHoriz+1 == $evs_same_time) $width = $width - 0.75;
												$procesados[$hr_start]++;
												
												$event_duration->add('s', 1);
												$end_time = date($timeformat, $event_duration->getTimestamp());
												$ev_duration = DateTimeValueLib::get_time_difference($event_start->getTimestamp(), $event_duration->getTimestamp()); 

												$tipBody = $event_start->format($use_24_hours ? 'G:i' : 'g:i A') .' - '. $event_duration->format($use_24_hours ? 'G:i' : 'g:i A') . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : '');
												$tipBody = str_replace(array("\r", "\n"), array(' ', '<br>'), $tipBody);
												if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';
										?>
												<script type="text/javascript">
													if (<?php echo $top; ?> < scroll_to || scroll_to == -1) {
														scroll_to = <?php echo $top;?>;
													}
													addTip('d_ev_div_' + <?php echo $event->getId() ?>, <?php echo json_encode(clean($event->getSubject())) ?>, <?php echo json_encode($tipBody); ?>);
												</script>
												
												<div id="d_ev_div_<?php echo $event->getId()?>" class="chip" style="position: absolute; top: <?php echo $top?>px; left: <?php echo $left?>%; width: <?php echo $width?>%;z-index:120;"  onclick="og.disableEventPropagation(event)">
													<div class="t1 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 2px 0px 2px;height:0px; border-bottom:1px solid;border-color:<?php echo $border_color ?>"></div>
													<div class="t2 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid;border-color:<?php echo $border_color ?>"></div>
													<div class="chipbody edit og-wsname-color-<?php echo  $ws_color?>">
														<dl class="<?php echo  $ws_class?>" style="height: <?php echo $height ?>px;<?php echo  $ws_style?>;border-left:1px solid;border-right:1px solid;border-color:<?php echo $border_color ?>"  onclick="og.openLink(og.getUrl('event', 'viewevent', {view:'day', id:<?php echo $event->getId()?>, user_id:<?php echo $user_filter?>}, null));">
															<dt class="<?php echo  $ws_class?>" style="<?php echo  $ws_style?>;">
																<table width="100%"><tr><td>
																	<a href='<?php echo $event->getViewUrl()."&amp;view=day&amp;user_id=".$user_filter ?>' class='internalLink' onclick="og.disableEventPropagation(event);" >
																	<span class="eventheadlabel" style="color:<?php echo $txt_color?>!important;padding-left:5px;"><?php echo "$start_time - $end_time"; ?></span>
																	</a>
																	<?php
																	if ($ev_duration['hours'] == 0) { ?>
																		-<a href='<?php echo $event->getViewUrl()."&amp;view=day&amp;user_id=".$user_filter ?>' class='internalLink' ><span style="color:<?php echo $txt_color?>!important;padding-left:5px;"><?php echo $subject?></span></a>
																	<?php } //if ?>
																</td><td align="right">
																<dd><div align="right" style="padding-right:4px;<?php echo ($ev_duration['hours'] == 0 ? 'height:'.$height.'px;' : '') ?>">
																<?php $invitations = $event->getInvitations(); 
																if ($invitations != null && is_array($invitations) && isset($invitations[$user_filter])) {
																	$inv = $invitations[$user_filter];
																	if ($inv->getInvitationState() == 0) { // Not answered
																		echo '<img src="' . image_url('/16x16/mail_mark_unread.png') . '"/>';
																	} else if ($inv->getInvitationState() == 1) { // Assist = Yes
																		echo '<img src="' . image_url('/16x16/complete.png') . '"/>';
																	} else if ($inv->getInvitationState() == 2) { // Assist = No
																		echo '<img src="' . image_url('/16x16/del.png') . '"/>';
																	} else if ($inv->getInvitationState() == 3) { // Assist = Maybe
																		echo '<img src="' . image_url('/16x16/help.png') . '"/>';
																	} else {
																		//echo "Not Invited";
																	}
																} // if ?>
																</div></dd>
															</td></tr></table>
															</dt>
															<?php
															if ($ev_duration['hours'] > 0) { ?>
															<dd>
																<div>
																	<a href='<?php echo $event->getViewUrl()."&amp;view=day&amp;user_id=".$user_filter ?>' onclick="og.disableEventPropagation(event);" class='internalLink' ><span style="color:<?php echo $txt_color?>!important;padding-left:5px;"><?php echo $subject?></span></a>
																</div>
															</dd>
															<?php } //if ?>
														</dl>
													</div>
													<div class="b2 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid; border-color:<?php echo $border_color ?>"> </div>
													<div class="b1 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 2px 0px 2px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"> </div>
												</div>
										<?php
											}
										?>
											</div>
										</div>
									</td>
									<td id="ie_scrollbar_adjust" style="width:0px;"></td>
								</tr>
							</table>
						</div><!--calowner -->															 
				</div><!--gridcontainer -->
			</div>		
			
			</td>
			</tr>
		</table>
	</td>
</tr></table>
</div>

<?php
	$wdst = user_config_option('work_day_start_time', '09:00');
	$h_m = explode(':', $wdst);
	if (str_ends_with($wdst, 'PM')) {
		$h_m[0] = ($h_m[0] + 12) % 24;
		$h_m[1] = substr($h_m[1], 0 , strpos(' ', $h_m[1]));
	}
	$defaultScrollTo = PX_HEIGHT * ($h_m[0] + ($h_m[1] / 60));
	
 ?>
 
<script type="text/javascript">
	// Top Toolbar	
	ogCalendarUserPreferences = Ext.util.JSON.decode(document.getElementById('hfCalUserPreferences').value);
	var ogCalTT = new og.CalendarTopToolbar({
		usersHfId:'hfCalUsers',
		companiesHfId:'hfCalCompanies',
		renderTo:'calendarPanelTopToolbar'
	});	

	// Mantain the actual values after refresh by clicking Calendar tab.
	var dtv = new Date('<?php echo $month.'/'.$day.'/'.$year ?>');
	og.calToolbarDateMenu.picker.setValue(dtv);

	// scroll to first event
	var scroll_pos = (scroll_to == -1 ? <?php echo $defaultScrollTo ?> : scroll_to);
	Ext.get('gridcontainer').scrollTo('top', scroll_pos, true);
	
	if (Ext.isIE) document.getElementById('ie_scrollbar_adjust').style.width = '15px';
	
	// resize grid
	function resizeGridContainer(e, id) {
		maindiv = document.getElementById('cal_main_div');
		if (maindiv == null) {
			og.removeDomEventHandler(window, 'resize', id);
		} else {
			var divHeight = maindiv.offsetHeight;
			divHeight = divHeight - 30 - <?php echo (PX_HEIGHT + $alldaygridHeight); ?>;
			document.getElementById('gridcontainer').style.height = divHeight + 'px';
		}
	}
	resizeGridContainer();
	if (Ext.isIE) {
		og.addDomEventHandler(document.getElementById('cal_main_div'), 'resize', resizeGridContainer);
	} else {
		og.addDomEventHandler(window, 'resize', resizeGridContainer);
	}
	
	// init tooltips
	Ext.QuickTips.init();
		
</script>