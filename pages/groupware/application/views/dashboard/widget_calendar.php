<?php
	$show_help_option = user_config_option('show_context_help', 'until_close'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_calendar_widget_context_help', true, logged_user()->getId()))) {
		render_context_help($this, 'chelp calendar widget', 'calendar_widget');
	}
	
	require_javascript('og/EventPopUp.js');

	$tags = active_tag();
	
	//$startday = date("d",mktime()) - (date("N", mktime()) %7);
	if (user_config_option("start_monday")) {
		$startday = date("j") - date("N") + 1; // beginning of the week, monday
	} else {
		$startday = date("j") - date("w"); // beginning of the week, sunday
	}
	$endday = $startday + 7;
	$today = DateTimeValueLib::now()->add('h', logged_user()->getTimezone());
	$currentday = $today->getDay();
	$currentmonth = $today->getMonth();
	$currentyear = $today->getYear();
	
	$date_start = new DateTimeValue(mktime(0,0,0,$currentmonth,$startday,$currentyear)); 
	$date_end = new DateTimeValue(mktime(0,0,0,$currentmonth,$endday,$currentyear)); 
	$milestones = ProjectMilestones::getRangeMilestonesByUser($date_start,$date_end,logged_user(), $tags, active_project());
	$tasks = ProjectTasks::getRangeTasksByUser($date_start,$date_end,logged_user(), $tags, active_project());
	
	$use_24_hours = user_config_option('time_format_use_24');
	if($use_24_hours) $timeformat = 'G:i';
	else $timeformat = 'g:i A';
  
	// load the day we are currently viewing in the calendar

	$output ='';
	if(user_config_option("start_monday")) $firstday = (date("w", mktime(0,0,0,$currentmonth,1,$currentyear))-1) % 7;
	else $firstday = (date("w", mktime(0,0,0,$currentmonth,1,$currentyear))) % 7; // Numeric representation of day of week.
	$lastday = date("t", mktime(0,0,0,$currentmonth,1,$currentyear)); // # of days in the month
	
	$output .= "<table id=\"calendar\" border='0' style='width:100%;border-collapse:collapse' cellspacing='0' cellpadding='0'>\n";
	$day = date("d");
	$month = date("m");
	$year = date("Y");
	// Loop to render the calendar
	
	$can_add_event = !active_project() || ProjectEvent::canAdd(logged_user(),active_project());	
					$output .= "<tr>";
					
					if(!user_config_option("start_monday")) {
						$output .= "    <th width='12.5%' align='center'>" .  lang('sunday short') . '</th>' . "\n";
					}
					$output .= '
					<th width="15%">' . lang('monday short') . '</th>
					<th width="15%">' . lang('tuesday short') . '</th>
					<th width="15%">' . lang('wednesday short') . '</th>
					<th width="15%">' . lang('thursday short') . '</th>
					<th width="15%">' . lang('friday short') . '</th>
					<th width="12.5%">' . lang('saturday short') . '</th>';
					
					if(user_config_option("start_monday")) {
						$output .= '<th width="12.5%">' . lang('sunday short') . '</th>';
					}
					$output .= '</tr>';
	for ($week_index = 0;$week_index<1; $week_index++) {
		$output .= '  <tr>' . "\n";
		for ($day_of_week = 0; $day_of_week < 7; $day_of_week++) {
			$i = $week_index * 7 + $day_of_week;
			$day_of_month = $i + $startday;
			// see what type of day it is
			if($currentyear == $year && $currentmonth == $month && $currentday == $day_of_month){
				$daytitle = 'todaylink';
			}else $daytitle = 'daylink';
			
			// if weekends override do this
			if(cal_option("weekendoverride")){
				// set whether the date is in the past or future/present
				if($day_of_week==0 OR $day_of_week==6){
					$daytype = "weekend";
				}elseif($day_of_month <= $lastday AND $day_of_month >= 1){
					$daytype = "weekday";
				}else{
					$daytype = "weekday_future";
				}
			}else{
				if( !user_config_option("start_monday") AND ($day_of_week==0 OR $day_of_week==6) AND $day_of_month <= $lastday AND $day_of_month >= 1){
					$daytype = "weekend";
				}elseif( user_config_option("start_monday") AND ($day_of_week==5 OR $day_of_week==6) AND $day_of_month <= $lastday AND $day_of_month >= 1){
					$daytype = "weekend";
				}elseif($day_of_month <= $lastday AND $day_of_month >= 1){
					$daytype = "weekday";
				}else{
					$daytype = "weekday_future";
				}
			}
			// see what type of day it is
			if($currentyear == $year && $currentmonth == $month && $currentday == $day_of_month){
			  $daytitle = 'todaylink';
			  $daytype = "today";
			}elseif($day_of_month > $lastday OR $day_of_month < 1){
				$daytitle = 'extralink';
			}else $daytitle = 'daylink';
			// writes the cell info (color changes) and day of the month in the cell.
			$output .= "<td valign=\"top\" class=\"$daytype\" ";
			if($day_of_month <= $lastday AND $day_of_month >= 1){ 
				$p = cal_getlink("index.php?action=viewdate&day=$day_of_month&month=$month&year=$year");
				$t = cal_getlink("index.php?action=add&day=$day_of_month&month=$month&year=$year");
				$w = $day_of_month;
				$dtv = DateTimeValueLib::make(0,0,0,$month,$day_of_month,$year);
			}elseif($day_of_month < 1){
				$p = cal_getlink("index.php?action=viewdate&day=$day_of_month&month=$month&year=$year");
				$t = cal_getlink("index.php?action=add&day=$day_of_month&month=$month&year=$year");
				//$w = "&nbsp;";
				$w = idate('d', mktime(0, 0, 0, $month, 0, $year)) + $day_of_month;
				$dtv = DateTimeValueLib::make(0,0,0,$month,$day_of_month,$year);  
			}else{
				if($day_of_month==$lastday+1){
					$month++;
					if($month==13){
						$month = 1;
						$year++;
					}
				}
				$p = cal_getlink("index.php?action=viewdate&day=".($day_of_month-$lastday)."&month=$month&year=$year");
				$t = cal_getlink("index.php?action=add&day=".($day_of_month-$lastday)."&month=$month&year=$year");
				$w = $day_of_month - $lastday;
				$dtv = DateTimeValueLib::make(0,0,0,$month,$w,$year);
			}
			$loc = Localization::instance();
			
			$start_value = $dtv->format(user_config_option('date_format', 'd/m/Y'));
			$popupTitle = lang('add event');
			$output .= "><div style='z-index:0; min-height:100px; height:100%;cursor:pointer' onclick=\"og.EventPopUp.show(null, {caller:'overview-panel', day:'".$dtv->getDay()."', month:'".$dtv->getMonth()."', year:'".$dtv->getYear()."', type_id:1, hour:'9', minute:'0', durationhour:1, durationmin:0, start_value: '$start_value', start_time:'9:00', title:'".format_datetime($dtv, 'l, j F', logged_user()->getTimezone()) ."', view: 'week', title: '$popupTitle', time_format: '$timeformat', hide_calendar_toolbar: 0},'');\") >
			<div class='$daytitle' style='text-align:right'>";
			//if($day_of_month >= 1){
				$output .= "<a class='internalLink' href=\"$p\" onclick=\"og.disableEventPropagation(event);\"  style='color:#5B5B5B' >$w</a>";				
				// only display this link if the user has permission to add an event
				if(!active_project() || ProjectEvent::canAdd(logged_user(),active_project())){
					// if single digit, add a zero
					$dom = $day_of_month;
					if($dom < 10) $dom = "0".$dom;
					// make sure user is allowed to edit the past
						
				}
				
			//}else $output .= "&nbsp;";
			$output .= "</div>";
			// This loop writes the events for the day in the cell
			if (is_numeric($w)){
				$result = ProjectEvents::getDayProjectEvents($dtv, $tags, active_project(), logged_user()->getId(), ' 0 1 3'); 
				if(!$result)
					$result = array();
				if($milestones)
					$result = array_merge($result,$milestones );
					
					
				if($tasks)
					$result = array_merge($result,$tasks );
				
				if(count($result)<1) $output .= "&nbsp;";
				else{
					$count=0;
					foreach($result as $event){
						if($event instanceof ProjectEvent ){
							$count++;
							$subject =   clean($event->getSubject());
							$typeofevent = $event->getTypeId(); 
							$private = $event->getIsPrivate(); 
							$eventid = $event->getId();

							$event_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
							$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
							
							// make the event subjects links or not according to the variable $whole_day in gatekeeper.php
							if(!$private && $count <= 3){
								$output .= "<div class='event_block'   style='z-index:1000;'>";
								if($subject=="") $subject = "[".lang('CAL_NO_SUBJECT')."]";
								$output .= "<span id='o_ev_div_" . $event->getId() . "'>";			
								$output .= "<a style='vertical-align:bottom;' href='".cal_getlink("index.php?action=viewevent&amp;id=".$event->getId())."' class='internalLink' onclick=\"og.disableEventPropagation(event);\" >";
								$output .= "<img src=" . image_url('/16x16/calendar.png') . " align='absmiddle'>";
								$output .= $subject."</a>";
								$output .= '</span>';
								$output .= "</div>";
								
								$tip_text = str_replace("\r", '', $event->getTypeId() == 2 ? lang('CAL_FULL_DAY') : $event_start->format($use_24_hours ? 'G:i' : 'g:i A') .' - '. $event_duration->format($use_24_hours ? 'G:i' : 'g:i A') . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : ''));
								$tip_text = str_replace("\n", '<br>', $tip_text);
								if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
								?>
								<script type="text/javascript">
									addTip('o_ev_div_<?php echo $event->getId() ?>', '<i>' + lang('event') + '</i> - '+<?php echo json_encode(clean($event->getSubject())) ?>, <?php echo json_encode($tip_text);?>);
								</script>
								<?php
							}
						} elseif($event instanceof ProjectMilestone ){
							$milestone=$event;
							$due_date=$milestone->getDueDate();
							if ($dtv->getTimestamp() == mktime(0,0,0,$due_date->getMonth(),$due_date->getDay(),$due_date->getYear())) {	
								$count++;
								if ($count<=3){
									$subject = "&nbsp;". clean($milestone->getName())." - <i>Milestone</i>";
									$cal_text = clean($milestone->getName());
									$output .= '<div class="event_block">';
									$output .= "<span id='o_ms_div_" . $milestone->getId() . "'>";
									$output .= "<a style='vertical-align:bottom;' href='".$milestone->getViewUrl()."' class='internalLink' onclick=\"og.disableEventPropagation(event);\" >";
									$output .= "<img src=" . image_url('/16x16/milestone.png') . " align='absmiddle'>";
									$output .= $cal_text."</a>";
									$output .= '</span>';
									$output .= "</div>";
									
									$tip_text = str_replace("\r", '', lang('assigned to') .': '. clean($milestone->getAssignedToName()) . (trim(clean($milestone->getDescription())) == '' ? '' : '<br><br>'. clean($milestone->getDescription())));
									$tip_text = str_replace("\n", '<br>', $tip_text);
									if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
									?>
									<script type="text/javascript">
										addTip('o_ms_div_<?php echo $milestone->getId() ?>', '<i>' + lang('milestone') + '</i> - '+<?php echo json_encode(clean($milestone->getTitle())) ?>, <?php echo json_encode($tip_text)?>);
									</script>
									<?php
								}//if count
							}
							
						}//endif milestone
						elseif($event instanceof ProjectTask){
							$task=$event;
							$start_date=$task->getStartDate();
							$due_date=$task->getDueDate();
							if ($dtv->getTimestamp() == mktime(0,0,0,$due_date->getMonth(),$due_date->getDay(),$due_date->getYear())) {	
								$count++;
								if ($count<=3){
									$subject = clean($task->getTitle()).'- <i>Task</i>';
									$cal_text = clean($task->getTitle());
									
									$output .= '<div class="event_block">';
									$output .= "<span id='o_ta_div_" . $task->getId() . "'>";
									$output .= "<a style='vertical-align:bottom;' href='".$task->getViewUrl()."' class='internalLink' onclick=\"og.disableEventPropagation(event);\" >";
									$output .= "<img src=" . image_url('/16x16/tasks.png') . " align='absmiddle'>";
									$output .= $cal_text."</a>";
									$output .= '</span>';
									$output .= "</div>";
									
									$tip_text = str_replace("\r", '', lang('assigned to') .': '. clean($task->getAssignedToName()) . (trim(clean($task->getText())) == '' ? '' : '<br><br>'. clean($task->getText())));
									$tip_text = str_replace("\n", '<br>', $tip_text);													
									if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
									?>
									<script type="text/javascript">
										addTip('o_ta_div_' + <?php echo $task->getId() ?>, '<i>' + lang('task') + '</i> - ' + <?php echo json_encode(clean($task->getTitle()))?>, <?php echo json_encode($tip_text);?>);
									</script>
									<?php
								}//if count
							}
						}//endif task
					} // end foreach event writing loop
					if ($count > 3) {
						$output .= '<div style="witdh:100%;text-align:center;font-size:9px" ><a href="'.$p.'" class="internalLink"  onclick="og.disableEventPropagation(event);nd();">'.($count-3) . ' ' . lang('more') .'</a></div>';
					}
				}
				
				$output .= '</div></td>';
			} //if is_numeric($w) 
		} // end weekly loop
		$output .= "\n  </tr>\n";
		// If it's the last day, we're done
		if($day_of_month >= $lastday+7) {
			break;
		}
	} // end main loop
echo $output . '</table>';
  ?>