<?php
  // translation provided by: eR System - http://www.ersystem.biz/ [http://www.ersystem.biz/] - Wiktor Falana, Robert Rychcicki (QA)
  return array(
  
    // ---------------------------------------------------
    //  Administration tools
    // ---------------------------------------------------
    
    'administration tool name test_mail_settings' => 'Przetestuj ustawienia poczty',
    'administration tool desc test_mail_settings' => 'Użyj tego narzędzia by wysłać testowe wiadomości i sprawdzić czy moduł pocztowy OpenGoo jest dobrze skonfigurowany',
    'administration tool name mass_mailer' => 'Masowe wysyłanie wiadomości',
    'administration tool desc mass_mailer' => 'Proste narzędzie które pozwoli Ci wysłać wiadomości ze zwykłym tekstem do dowolnej grupy użytkowników zarejestrowanych w systemie',
  
    // ---------------------------------------------------
    //  Configuration categories and options
    // ---------------------------------------------------
  
    'configuration' => 'Konfiguracja',
    
    'mail transport mail()' => 'Domyślne ustawienia PHP',
    'mail transport smtp' => 'Serwer SMTP',
    
    'secure smtp connection no'  => 'Nie',
    'secure smtp connection ssl' => 'Tak, użyj SSL',
    'secure smtp connection tls' => 'Tak, użyj TLS',
    
    'file storage file system' => 'System plików',
    'file storage mysql' => 'Baza danych (MySQL)',
    
    // Categories
    'config category name general' => 'Ogólne',
    'config category desc general' => 'Ogólne ustawienia OpenGoo.',
    'config category name mailing' => 'Wysyłanie wiadomości e-mail',
    'config category desc mailing' => 'Użyj tych opcji by skonfigurować sposób w jaki OpenGoo wysyła wiadomości. Możesz użyć opcji ze swojego php.ini albo ustawić dowolny inny serwer SMTP.',
  	'config category name modules' => 'Moduły',
    'config category desc modules' => 'Użyj tych opcji by włączyć lub wyłączyć poszczególne moduły OpenGoo. Wyłączenie modułu powoduje jedynie jego ukrycie na ekranie, nie usuwa ono uprawnień użytkowników do tworzenia lub edycji obiektów.',
    
    // ---------------------------------------------------
    //  Options
    // ---------------------------------------------------
    
    // General
    'config option name site_name' => 'Nazwa strony',
    'config option desc site_name' => 'Ta wartość będzie wyświetlona jako nazwa na stronie Pulpitu',
    'config option name file_storage_adapter' => 'Magazyn plików',
    'config option desc file_storage_adapter' => 'Wybierz gdzie chcesz przechowywać wysłane dokumenty. UWAGA: Zmiana tego ustawienia sprawi, że dostęp do wcześniej wysłanych plików będzie niemożliwy.',
    'config option name default_project_folders' => 'Domyślne katalogi',
    'config option desc default_project_folders' => 'Katalogi które zostaną utworzone wraz z utworzeniem nowego projektu. Nazwa każdego folderu powinna być umieszczona w nowej linii. Zduplikowane lub puste linie zostaną zignorowane.',
    'config option name theme' => 'Skórka',
    'config option desc theme' => 'Używając skórek możesz zmienić domyślny wygląd OpenGoo. Musisz odświeżyć stronę by zobaczyć zmiany.',
  	'config option name days_on_trash' => 'Liczba dni w koszu',
    'config option desc days_on_trash' => 'Ile dni obiekt jest trzymany w koszu zanim zostanie automatycznie skasowany. Jeśli ustawione na 0 obiekty nigdy nie zostaną automatycznie skasowane.',

  	'config option name enable_notes_module' => 'Włącz moduł Notatki',
  	'config option name enable_email_module' => 'Włącz moduł E-mail',
  	'config option name enable_contacts_module' => 'Włącz moduł Kontakty',
  	'config option name enable_calendar_module' => 'Włącz moduł Kalendarz',
  	'config option name enable_documents_module' => 'Włącz moduł Dokumenty',
  	'config option name enable_tasks_module' => 'Włącz moduł Zadania',
  	'config option name enable_weblinks_module' => 'Włącz moduł Odnośniki',
  	'config option name enable_time_module' => 'Włącz moduł Czas',
  	'config option name enable_reporting_module' => 'Włącz moduł Raporty',
  
    'config option name upgrade_check_enabled' => 'Włącz sprawdzanie uaktualnień',
    'config option desc upgrade_check_enabled' => 'Jeśli włączone system będzie raz dziennie sprawdzał, czy są nowe wersje OpenGoo do pobrania',
	'config option name work_day_start_time' => 'Początek dnia pracy',
  	'config option desc work_day_start_time' => 'Określa czas, kiedy rozpoczyna się dzień pracy',
    
	'config option name use_minified_resources' => 'Używaj zmniejszonych zasobów',
  	'config option desc use_minified_resources' => 'Używa skompresowanego Kavascript i CSS aby poprawić wydajność. Musisz zrekompresować JS i CSS jeśli coś w nich zmienisz używając narzędzi z katalogu public/tools.',
	
    // Mailing
    'config option name exchange_compatible' => 'Tryb zgodności z Microsoft Exchange',
    'config option desc exchange_compatible' => 'Jeśli używasz serwera Microsoft Exchange użyj tej opcji by uniknąć problemów z obsługą wiadomości e-mail.',
    'config option name mail_transport' => 'Transport wiadomości',
    'config option desc mail_transport' => 'Możesz użyć domyślnych ustawień PHP dla wysyłania wiadomości e-mail lub ustawić własny serwer SMTP',
    'config option name smtp_server' => 'Serwer SMTP',
    'config option name smtp_port' => 'Port SMTP',
    'config option name smtp_authenticate' => 'Użyj uwierzytelniania SMTP',
    'config option name smtp_username' => 'Nazwa użytkownika SMTP',
    'config option name smtp_password' => 'Hasło SMTP',
    'config option name smtp_secure_connection' => 'Użyj bezpiecznego połączenia SMTP',
  
 	'can edit company data' => 'Może edytować dane firmy',
  	'can manage security' => 'Może zarządzać zabezpieczeniami',
  	'can manage workspaces' => 'Może zarządzać projektami',
  	'can manage configuration' => 'Może zarządzać konfiguracją',
  	'can manage contacts' => 'Może zarządzać kontaktami',
  	'group users' => 'Zgrupuj użytkowników',
    
  	
  	'user ws config category name dashboard' => 'Ustawienia Pulpitu',
  	'user ws config category name task panel' => 'Ustawienia Zadań',
  	'user ws config category name general' => 'Ogólne',
  	'user ws config option name show pending tasks widget' => 'Pokaż fiszkę Zadania do wykonania',
  	'user ws config option name pending tasks widget assigned to filter' => 'Pokaż Zadania do wykonania przypisane do',
  	'user ws config option name show late tasks and milestones widget' => 'Pokaż fiszkę Opóźnione Zadania i Etapy',
  	'user ws config option name show messages widget' => 'Pokaż fiszkę Notatki',
  	'user ws config option name show comments widget' => 'Pokaż fiszkę Komentarze',
  	'user ws config option name show documents widget' => 'Pokaż fiszkę Dokumenty',
  	'user ws config option name show calendar widget' => 'Pokaż fiszkę Mini-Kalendarz',
  	'user ws config option name show charts widget' => 'Pokaż fiszkę Wykresy',
  	'user ws config option name show emails widget' => 'Pokaż fiszkę Wiadomości e-mail',
  	'user ws config option name localization' => 'Język',
  	'user ws config option desc localization' => 'Etykiety i daty będą wyświetlane zgodnie z tym ustawieniem. Musisz odświeżyć stronę by zobaczyć zmiany.',
  	'user ws config option name initialWorkspace' => 'Projekt początkowy',
  	'user ws config option desc initialWorkspace' => 'To ustawienie pozwala Ci wybrać który projekt zostanie wyświetlony gdy się zalogujesz. Możesz również ustawić ostatni poprzednio wyświetlony projekt.',
  	'user ws config option name rememberGUIState' => 'Pamiętaj stan Interfejsu Użytkownika',
  	'user ws config option desc rememberGUIState' => 'To ustawienie pozwala Ci na zapisanie stanu interfejsu graficznego (tj. rozmiarów paneli, zwiniętych/rozwiniętych list etc.) dla następnego logowania. UWAGA: Ta funkcja jest obecnie w fazie BETA.',
  	'user ws config option name time_format_use_24' => 'Używaj czasu dwudziestoczterogodzinnego (24h)',
  	'user ws config option desc time_format_use_24' => 'To ustawienie sprawia że czas będzie przedstawiany w formacie \'hh:mm\' od 00:00 do 23:59. W przeciwnym wypadku godziny będą wyświetlane w przedziale 1-12 z dodatkiem AM/PM.',
  	'user ws config option name work_day_start_time' => 'Początek dnia pracy',
	'user ws config option desc work_day_start_time' => 'Określa czas, kiedy rozpoczyna się dzień pracy',
  	
  	'user ws config option name my tasks is default view' => 'Zadania Przypisane Do Mnie jest domyślnym widokiem',
  	'user ws config option desc my tasks is default view' => 'Jeśli to ustawienie jest nieaktywne, panel Zadania wyświetli wszystkie zadania',
  	'user ws config option name show tasks in progress widget' => 'Pokaż fiszkę \'Zadania w trakcie realizacji\'',
  	'user ws config option name can notify from quick add' => 'Pokazuj opcję powiadamiania',
  	'user ws config option desc can notify from quick add' => 'To ustawienie sprawia że możliwe jest włączenie powiadamiania odpowiednich użytkowników o dodaniu lub zaktualizowaniu zadania',
 	
  	'backup process desc' => 'Kopia Bezpieczeństwa zapisuje stan całej aplikacji do skompresowanego katalogu. Funkcji tej można użyć by w prosty sposób utworzyć kopię bezpieczeństwa instalacji OpenGoo. <br> Utworzenie kopii bezpieczeństwa bazy danych i systemu plików może zająć więcej niż kilka sekund, więc proces podzielono na 3 etapy: <br>1.- Start procesu tworzenia kopii bezpieczeństwa, <br>2.- Pobranie kopii bezpieczeństwa. <br> 3.- Opcjonalnie, kopia bezpieczeństwa może zostać ręcznie usunięta by nie była dostępna w przyszłości. <br> ',
  	'start backup' => 'Rozpocznij tworzenie kopii bezpieczeństwa',
    'start backup desc' => 'Rozpoczęcie tworzenia kopii bezpieczeństwa oznacza, że poprzednie jej wersje zostaną usunięte.',
  	'download backup' => 'Pobierz kopię bezpieczeństwa',
    'download backup desc' => 'By móc pobrać kopię bezpieczeństwa musisz wpierw ją utworzyć.',
  	'delete backup' => 'Usuń kopię bezpieczeństwa',
    'delete backup desc' => 'Usuwa ostatnią kopię bezpieczeństwa, by nie można było jej pobrać. Zalecane po pobraniu kopii.',
    'backup' => 'Kopia Bezpieczeństwa',
    'backup menu' => 'Menu Kopii Bezpieczeństwa',
   	'last backup' => 'Ostatnią kopię bezpieczeństwa utworzono',
   	'no backups' => 'Nie ma żadnych kopii bezpieczeństwa do pobrania',
   	
   	'user ws config option name always show unread mail in dashboard' => 'Zawsze pokazuj nieprzeczytane wiadomości na Pulpicie',
   	'user ws config option desc always show unread mail in dashboard' => 'Jeśli wyłączone wyświetlane będą wiadomości e-mail z aktywnego projektu',
   	'workspace emails' => 'Wiadomości projektu',
  	'user ws config option name tasksShowWorkspaces' => 'Pokaż projekty',
  	'user ws config option name tasksShowTime' => 'Pokaż czas',
  	'user ws config option name tasksShowDates' => 'Pokaż daty',
  	'user ws config option name tasksShowTags' => 'Pokaż tagi',
  	'user ws config option name tasksGroupBy' => 'Grupuj według',
  	'user ws config option name tasksOrderBy' => 'Sortuj według',
  	'user ws config option name task panel status' => 'Stan',
  	'user ws config option name task panel filter' => 'Filtruj według',
  	'user ws config option name task panel filter value' => 'Wartość filtrowana',
  
  	'templates' => 'Szablony',
	'add template' => 'Dodaj szablon',
	'confirm delete template' => 'Czy na pewno chcesz usunąć ten szablon?',
	'no templates' => 'Nie ma żadnych szablonów',
	'template name required' => 'Wymagana jest nazwa szablonu',
	'can manage templates' => 'Może zarządzać szablonami',
	'new template' => 'Nowy szablon',
	'edit template' => 'Edytuj szablon',
	'template dnx' => 'Szablon nie istnieje',
	'success edit template' => 'Szablon został zmieniony',
	'log add cotemplates' => '{0} dodany',
	'log edit cotemplates' => '{0} zmieniony',
	'success delete template' => 'Szablon został usunięty',
	'error delete template' => 'Nie udało się usunąć szablonu',
	'objects' => 'Obiekty',
	'objects in template' => 'Obiekty w szablonie',
	'no objects in template' => 'Nie ma żadnych obiektów w tym szablonie',
	'add to a template' => 'Dodaj do szablonu',
  	'add an object to template' => 'Dodaj obiekt do tego szablonu',
	'you are adding object to template' => 'Dodajesz {0} \'{1}\' do szablonu. Wybierz szablon poniżej albo utwórz nowy szablon dla {0}.',
	'success add object to template' => 'Dodano obiekt do szablonu',
	'object type not supported' => 'Ten rodzaj obiektów nie jest obsługiwany przez szablony',
  	'assign template to workspace' => 'Przypisz szablon do projektu',
  
  	'cron events' => 'Zdarzenia Cron',
  	'about cron events' => 'Dowiedz się więcej o zdarzeniach Cron...',
  	'cron events info' => 'Zdarzenia Cron pozwalają Ci na okresowe wykonywanie pewnych czynności w OpenGoo bez potrzeby logowania się do systemu. Aby włączyć zdarzenia Cron musisz skonfigurować zadanie Cron tak by okresowo wykonywało plik "cron.php", zlokalizowany w katalogu głównym OpenGoo. Cykl wykonywania tego zadania zdecyduje o tym jak często będziesz mógł wykonywać zdarzenia Cron. Przykładowo, jeśli skonfigurujesz zadanie Cron tak by wykonywane było co 5 minut, a równocześnie ustawisz zdarzenie Cron sprawdzające dostępnośc aktualizacji co 1 minutę, to zdarzenie to będzie w stanie sprawdzić dostępność aktualizacji tylko raz na 5 minut. Aby dowiedzieć się, jak skonfigurować zadanie Cron, poproś o pomoc swojego administratora systemu lub dostawcę usług hostingowych.',
  	'cron event name check_mail' => 'Sprawdź czy nie ma nowych wiadomości e-mail',
  	'cron event desc check_mail' => 'To zdarzenie Cron sprawdzi, czy nie ma nowych wiadomości e-mail na wszystkich kontach pocztowych w systemie.',
  	'cron event name purge_trash' => 'Opróżnij kosz',
  	'cron event desc purge_trash' => 'To zdarzenie Cron usunie obiekty starsze niż liczba dni ustawiona w pozycji \'Liczba dni w koszu\' w opcjach konfiguracyjnych.',
  	'cron event name send_reminders' => 'Wyślij przypomnienia',
  	'cron event desc send_reminders' => 'To zdarzenie Cron wyśle wiadomości e-mail z przypomnieniami.',
  	'cron event name check_upgrade' => 'Sprawdź uaktualnienia',
  	'cron event desc check_upgrade' => 'To zdarzenie Cron sprawdzi, czy nie ma nowych wersji OpenGoo do pobrania.',
  	'cron event name create_backup' => 'Utwórz kopię bezpieczeństwa',
  	'cron event desc create_backup' => 'Tworzy kopię bezpieczeństwa, którą możesz następnie pobrać w sekcji Kopia Bezpieczeństwa działu Administracja.',
  	'next execution' => 'Następne wykonanie',
  	'delay between executions' => 'Opóźnienie między wykonaniami',
  	'enabled' => 'Włączone',
  	'no cron events to display' => 'Nie ma żadnych zdarzeń Cron do wyświetlenia',
  	'success update cron events' => 'Zdarzenia Cron zostały zaktualizowane',
  
  	'manual upgrade' => 'Aktualizacja ręczna',
  	'manual upgrade desc' => 'Aby ręcznie zaktualizować OpenGoo, musisz pobrać nową wersję OpenGoo, wypakować ją do katalogu głównego obecnej instalacji a następnie przejść do katalogu <a href="public/upgrade">\'public/upgrade\'</a> w swojej przeglądarce by rozpocząć proces aktualizacji.',
  	'automatic upgrade' => 'Aktualizacja automatyczna',
  	'automatic upgrade desc' => 'W procesie aktualizacji automatycznej system sam pobierze i wypakuje nową wersję oprogramowania, a następnie przeprowadzi proces aktualizacji. Serwer www potrzebuje w tym celu uprawnień do zapisu do wszystkich katalogów.',
  	'start automatic upgrade' => 'Rozpocznij automatyczną aktualizację',
  ); // array

?>