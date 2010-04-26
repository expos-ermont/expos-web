<?php

  return array(
	// ########## QUERY ERRORS ###########
	"CAL_QUERY_GETEVENT_ERROR" => "Chyba databáze: Selhalo načítání události podle ID",
	"CAL_QUERY_SETEVENT_ERROR" => "Chyba databáze: Nepodařilo se nastavit data události",
	// ########## SUBMENU ITEMS ###########
	"CAL_SUBM_LOGOUT" => "Odhlásit",
	"CAL_SUBM_LOGIN" => "Přihlásit",
	"CAL_SUBM_ADMINPAGE" => "Stránka správy",
	"CAL_SUBM_SEARCH" => "Vyhledat",
	"CAL_SUBM_BACK_CALENDAR" => "Zpět do kalendáře",
	"CAL_SUBM_VIEW_TODAY" => "Zobrazit dnešní události",
	"CAL_SUBM_ADD" => "Přidat dnešní událost",
	// ########## NAVIGATION MENU ITEMS ##########
	"CAL_MENU_BACK_CALENDAR" => "Zpět do kalendáře",
	"CAL_MENU_NEWEVENT" => "Nová událost",
	"CAL_MENU_BACK_EVENTS" => "Zpět do událostí",
	"CAL_MENU_GO" => "Jdi",
	"CAL_MENU_TODAY" => "Dnes",
	// ########## USER PERMISSION ERRORS ##########
	"CAL_NO_READ_PERMISSION" => "Nemáte oprávnění ke zobrazení této události.",
	"CAL_NO_WRITE_PERMISSION" => "Nemáte oprávnění pro přidání nebo úpravu této události.",
	"CAL_NO_EDITOTHERS_PERMISSION" => "Nemáte oprávnění k úpravám událostí ostatních uživatelů.",
	"CAL_NO_EDITPAST_PERMISSION" => "Nemáte oprávnění k přidání nebo úpravě události v minulosti.",
	"CAL_NO_ACCOUNTS" => "Tento kalendář neumožňuje účty; pouze root se může přihlásit.",
	"CAL_NO_MODIFY" => "nelze upravovat",
	"CAL_NO_ANYTHING" => "Nemáte oprávnění dělat cokoliv na této stránce",
	"CAL_NO_WRITE", "Nemáte oprávnění pro vytvoření nové události",
	// ############ DAYS ############
	"CAL_MONDAY" => "Pondělí",
	"CAL_TUESDAY" => "Úterý",
	"CAL_WEDNESDAY" => "Středa",
	"CAL_THURSDAY" => "Čtvrtek",
	"CAL_FRIDAY" => "Pátek",
	"CAL_SATURDAY" => "Sobota",
	"CAL_SUNDAY" => "Neděle",
	"CAL_SHORT_MONDAY" => "Po",
	"CAL_SHORT_TUESDAY" => "Út",
	"CAL_SHORT_WEDNESDAY" => "St",
	"CAL_SHORT_THURSDAY" => "Čt",
	"CAL_SHORT_FRIDAY" => "Pá",
	"CAL_SHORT_SATURDAY" => "So",
	"CAL_SHORT_SUNDAY" => "Ne",
	// ############ MONTHS ############
	"CAL_JANUARY" => "Leden",
	"CAL_FEBRUARY" => "Únor",
	"CAL_MARCH" => "Březen",
	"CAL_APRIL" => "Duben",
	"CAL_MAY" => "Květen",
	"CAL_JUNE" => "Červen",
	"CAL_JULY" => "Červenec",
	"CAL_AUGUST" => "Srpen",
	"CAL_SEPTEMBER" => "Září",
	"CAL_OCTOBER" => "Říjen",
	"CAL_NOVEMBER" => "Listopad",
	"CAL_DECEMBER" => "Prosinec",
	
	
	
	
	
	
	// SUBMITTING/EDITING EVENT SECTION TEXT (event.php)
	"CAL_MORE_TIME_OPTIONS" => "Víc časových voleb",
	"CAL_REPEAT" => "Opakovat",
	"CAL_EVERY" => "Každý",
	"CAL_REPEAT_FOREVER" => "Opakovat navždy",
	"CAL_REPEAT_UNTIL" => "Opakovat dokud",
	"CAL_TIMES" => "Krát",
	"CAL_HOLIDAY_EXPLAIN" => "Umožnit událost opakovat",
	"CAL_DURING" => "Během",
	"CAL_EVERY_YEAR" => "Každý rok",
	"CAL_HOLIDAY_EXTRAOPTION" => "Nebo, protože to spadá na poslední týden v měsíci, zkontrolujte zda událost může být POSLEDNÍ",
	"CAL_IN" => "v",
	"CAL_PRIVATE_EVENT_EXPLAIN" => "Toto je soukromá událost",
	"CAL_SUBMIT_ITEM" => "Odeslat položku",
	"CAL_MINUTES" => "Minuty", 
	"CAL_MINUTES_SHORT" => "min",
	"CAL_TIME_AND_DURATION" => "Datum, čas a délka",
	"CAL_REPEATING_EVENT" => "Opakující se událost",
	"CAL_EXTRA_OPTIONS" => "Další volby",
	"CAL_ONLY_TODAY" => "Pouze tento den",
	"CAL_DAILY_EVENT" => "Opakovat denně",
	"CAL_WEEKLY_EVENT" => "Opakovat týdně",
	"CAL_MONTHLY_EVENT" => "Opakovat měsíčně",
	"CAL_YEARLY_EVENT" => "Opakovat ročně",
	"CAL_HOLIDAY_EVENT" => "Opakování dovolené",
	"CAL_UNKNOWN_TIME" => "Neznámý počáteční čas",
	"CAL_ADDING_TO" => "Přidáno k",
	"CAL_ANON_ALIAS" => "Název aliasu",
	"CAL_EVENT_TYPE" => "Typ události",
	
	// MULTI-SECTION RELATED TEXT (used by more than one section, but not everwhere)
	"CAL_DESCRIPTION" => "Description", // (hledat, zobrazit datum, zobrazit událost)
	"CAL_DURATION" => "Duration", // (zobrazit událost, zobrazit datum)
	"CAL_DATE" => "Date", // (hledat, zobrazit datum)
	"CAL_NO_EVENTS_FOUND" => "No events found", // (hledat, zobrazit datum)
	"CAL_NO_SUBJECT" => "No Subject", // (hledat, zobrazit událost, zobrazit událost, kalendář)
	"CAL_PRIVATE_EVENT" => "Private Event", // (hledat, zobrazit událost)
	"CAL_DELETE" => "Delete", // (zobrazit událost, zobrazit datum, admin)
	"CAL_MODIFY" => "Modify", // (zobrazit událost, zobrazit datum, admin)
	"CAL_NOT_SPECIFIED" => "Not Specified", // (zobrazit událost, zobrazit datum, kalendář)
	"CAL_FULL_DAY" => "All Day", // (zobrazit událost, zobrazit datum, odevzdat událost)
	"CAL_HACKING_ATTEMPT" => "Pokus o hackerský útok - IP adresa zaznamenána", // (smazat)
	"CAL_TIME" => "Time", // (zobrazit datum, zobrazit událost)
	"CAL_HOURS" => "Hours", // (zobrazit událost, zobrazit událost)
	"CAL_HOUR" => "Hour", // (zobrazit událost, odevzdat událost)
	"CAL_ANONYMOUS" => "Anonymous", // (zobrazit událost, zobrazit datum, odevzdat událost),
	
	
	"CAL_SELECT_TIME" => "Vybrat počáteční čas",
	
	'event invitations' => 'Pozvání k události',
	'event invitations desc' => 'Pozvat vybrané lidi k této události',
	'send new event notification' => 'Poslat potvrzení e-mailem',
	'new event notification' => 'Nová událost byla přdána',
    'change event notification' => 'Událost byla změněna',
	'deleted event notification' => 'Událost byla smazána',
	'attendance' => 'Budete se podílet?',
    'confirm attendance' => 'Potvrdit přítomnost',
    'maybe' => 'Možná',
    'decide later' => 'Rozhodnout později',
    'view event' => 'Zobrazit událost',
	'new event created' => 'Nová vudálost vytvořena',
	'event changed' => 'Událost změněna',
 	'event deleted' => 'Událost smazána',
	'calendar of' => '{0}\ kalendářů',
	'all users' => 'Všichni uživatelé',
  	'error delete event' => 'Při mazání události se vyskytla chyba',  
  	'event invitation response' => 'Event invitation response',
  	'user will attend to event' => '{0} will attend to this event.',
  	'user will not attend to event' => '{0} will not attend to this event',  
  
	"days" => "dny",
	"weeks" => "týdny",
	"months" => "měsíce",
	"years" => "roky",

	'invitations' => 'Pozvánky',
	'pending response' => 'Čekající odpověď',
	'participate' => 'Bude se účastnit',
 	'no invitations to this event' => 'Této události nebyly odeslány žádné pozvánky',
	'duration must be at least 15 minutes' => 'Délka musí být alespoň 15 minut',
  
	'event dnx' => 'Požadovaná událost neexistuje',
	'no subject' => 'Žádný předmět',
	'success import events' => '{0} events were imported.',
	'no events to import' => 'There are no events for import',
	'import events from file' => 'Event import from file',
	'file should be in icalendar format' => 'File should be in iCalendar format',
	'export calendar' => 'Export kalendáře',
	'range of events' => 'Event range',
	'from date' => 'Od',
	'to date' => 'Do',
	'success export calendar' => '{0} Events were exported.',
	'calendar name desc' => 'Name for the calendar to export',
	'calendar will be exported in icalendar format' => 'Calendar will be exported in iCalendar format.',
	'view date title' => 'l, m/d/Y',

  	'copy this url in your calendar client software' => 'Copy this url in your calendar client software, in order to import events from this calendar',
	'import events from third party software' => 'Import events from third party software',
	'subws' => 'Sub Ws.',
	'check to include sub ws' => 'Check this to include sub workspaces in the url',
  ); // array
?>