	var selectingCells = false;
	var actualSelCell = '';
	var selectedCells = [];
	var paintingDay = 0;
	var old_back_color = 'transparent';

	var scroll_to = -1;
	var cant_tips = 0;
	var tips_array = [];

	og.change_link_incws = function(hrefid, checkid) {
		var link = document.getElementById(hrefid).href
		if (document.getElementById(checkid).checked) { 
			document.getElementById(hrefid).href = link.replace('isw=0', 'isw=1');
		} else {
			document.getElementById(hrefid).href = link.replace('isw=1', 'isw=0');
		}
	}
	
	addTip = function(div_id, title, bdy) {
		tips_array[cant_tips++] = new Ext.ToolTip({
			target: div_id,
	        html: bdy,
	        title: title,
	        hideDelay: 1500,
	        closable: true
		});
	}
	
	function overCell(cell_id) {
		ele = Ext.get(cell_id);
		//if (ele != null) {
			if (!selectingCells) old_back_color = ele.getStyle('background-color');
			ele.setStyle('background-color', '#DDFFDD');
			ele.setStyle('z-index', '90');
		//}
	}
	
	function resetCell(cell_id) {
		ele = Ext.get(cell_id);
		//if (ele != null) {
			ele.setStyle('background-color', old_back_color);
			ele.setStyle('z-index', '100');
		//}
	}
	
	function minSelectedCell() {
		min_val = 99;
		for (i=0; i<selectedCells.length; i++) {
			if (selectedCells[i] != '') {
				str_temp = selectedCells[i].split('_');
				min_val = parseInt(str_temp[1]) < min_val ? parseInt(str_temp[1]) : min_val;
			}
		}
		return min_val;
	}
	
	function paintSelectedCells(cell_id) {
		str_temp = cell_id.split('_');
		cell_id = 'h' + paintingDay + '_' + str_temp[1];

		if (selectingCells && actualSelCell != cell_id) {
			for (i=0; i<selectedCells.length; i++) {
				curr_split = selectedCells[i].split('_');
				if (parseInt(curr_split[1]) > parseInt(str_temp[1])/*cell_id*/) {
					resetCell(selectedCells[i]);
					selectedCells[i] = '';
				}
			}
		
			i = minSelectedCell();
			if (i == 99) i = str_temp[1];
			do {
				temp_cell = 'h' + paintingDay + '_' + i;
				overCell(temp_cell);
				selectedCells[selectedCells.length] = temp_cell;
				i++;
			} while (temp_cell != cell_id && i < 48);
			actualSelCell = cell_id;
		}
	}
	
	function clearPaintedCells() {
		for (i=0; i<selectedCells.length; i++) {
			if (selectedCells[i] != '') resetCell(selectedCells[i]);
		}
		selectedCells = [];
		selectingCells = false;
		actualSelCell = '';
	}
	
	// hour range selection
	var ev_start_day, ev_start_month, ev_start_year, ev_start_hour, ev_start_minute;
	var ev_end_day, ev_end_month, ev_end_year, ev_end_hour, ev_end_minute;
	
	function selectStartDateTime(day, month, year, hour, minute) {
		selectingCells = true;
		selectDateTime(true, day, month, year, hour, minute);
	}
	
	function selectEndDateTime(day, month, year, hour, minute) {
		selectDateTime(false, day, month, year, hour, minute);
	}
	
	function selectDateTime(start, day, month, year, hour, minute) {
		if (start == true) {
			ev_start_day = day;
			ev_start_month = month; 
			ev_start_year = year; 
			ev_start_hour= hour; 
			ev_start_minute = minute; 
		} else {
			ev_end_day = day; 
			ev_end_month = month; 
			ev_end_year = year; 
			ev_end_hour = hour; 
			ev_end_minute = minute; 
		}
		
	}
	
	function setSelectedStartTime() {
		min_val = minSelectedCell();
		ev_start_hour = Math.floor(min_val / 2);
		ev_start_minute = (min_val % 2 == 0) ? 0 : 30;
	}
	
	function getDurationMinutes() {
		setSelectedStartTime();
		
		var s_val = new Date();
		s_val.setFullYear(ev_start_year);
		s_val.setMonth(ev_start_month);
		s_val.setDate(ev_start_day);
		s_val.setHours(ev_start_hour);
		s_val.setMinutes(ev_start_minute);
		s_val.setSeconds(0);
		s_val.setMilliseconds(0);
		
		var e_val = new Date();
		e_val.setFullYear(ev_start_year);
		e_val.setMonth(ev_start_month);
		e_val.setDate(ev_start_day);
		e_val.setHours(ev_end_hour);
		e_val.setMinutes(ev_end_minute);
		e_val.setSeconds(0);
		e_val.setMilliseconds(0);
		
		if (ev_end_hour == 0) e_val.setDate(e_val.getDate() + 1);
		
		var millis = e_val.getTime() - s_val.getTime();
		
		return ((millis / 1000) / 60); 		
	}
	
	function showEventPopup(day, month, year, hour, minute, use_24hr, st_val) {
		var typeid = 1, hrs = 1, mins = 0;
		if (hour == -1 || minute == -1) {
			hour = 0;
			minute = 0;
			typeid = 2;
			ev_start_hour = ev_start_minute = durationhour = durationmin = 0;
			ev_start_day = day;
			ev_start_month = month;
			ev_start_year = year;
		} else {
			selectEndDateTime(day, month, year, hour, minute);
			hrs = 0;
			mins = getDurationMinutes();
			while (mins >= 60) {
				mins -= 60;
				hrs +=1;
			}
			if (hrs == 0) {
				hrs = 1;
				mins = 0;
			}
		}
		
		if (use_24hr) {
			st_hour = ev_start_hour;
			ampm = '';
		} else {
			if (ev_start_hour >= 12) {
				st_hour = ev_start_hour - (ev_start_hour > 12 ? 12 : 0);
				ampm = ' PM';
			} else {
				if (ev_start_hour == 0) st_hour = 12;
				else st_hour = ev_start_hour;
				ampm = ' AM';
			}
		}
		st_time = st_hour + ':' + ev_start_minute + (ev_start_minute < 10 ? '0' : '') + ampm;
		
		og.EventPopUp.show(null, {day: ev_start_day,
								month: ev_start_month,
								year: ev_start_year,
								hour: ev_start_hour,
								minute: ev_start_minute,
								durationhour: hrs,
								durationmin: mins,
								start_value: st_val,
								start_time: st_time,
								type_id: typeid,
								view:'week', 
								title: lang('add event'),
								time_format: use_24hr ? 'G:i' : 'g:i A',
								hide_calendar_toolbar: 1
								}, '');
		clearPaintedCells();								
	}