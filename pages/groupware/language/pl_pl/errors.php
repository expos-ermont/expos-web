<?php
  // translation provided by: eR System - http://www.ersystem.biz/ [http://www.ersystem.biz/] - Wiktor Falana, Robert Rychcicki (QA)
  /**
  * Error messages
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */

  // Return langs
  return array(
  
    // General
    'invalid email address' => 'Format podanego adresu e-mail jest nieprawidłowy',
   
    // Company validation errors
    'company name required' => 'Wymagane jest podanie nazwy firmy / organizacji',
    'company homepage invalid' => 'Format podanego adresu strony domowej jest nieprawidłowy',
    
    // User validation errors
    'username value required' => 'Wymagane jest podanie nazwy użytkownika',
    'username must be unique' => 'Przepraszamy ale podana nazwa użytkownika jest już zajęta',
    'email value is required' => 'Wymagane jest podanie adresu e-mail',
    'email address must be unique' => 'Przepraszamy ale podany adres e-mail jest już używany',
    'company value required' => 'Użytkownik musi być częścią firmy / organizacji',
    'password value required' => 'Wymagane jest podanie hasła',
    'passwords dont match' => 'Hasła się różnią',
    'old password required' => 'Wymagane jest podanie poprzedniego hasła',
    'invalid old password' => 'Poprzednie hasło jest nieprawidłowe',
    'users must belong to a company' => 'Kontakt musi należeć do firmy by można było utworzyć użytkownika',
    'contact linked to user' => 'Kontakt jest dowiązany do użytkownika {0}',
    
    // Avatar
    'invalid upload type' => 'Nieprawidłowy typ pliku. Dozwolone typy to {0}',
    'invalid upload dimensions' => 'Nieprawidłowe wymiary obrazu. Wymiary maksymalne to {0}x{1} pikseli',
    'invalid upload size' => 'Nieprawidłowy rozmiar pliku obrazu. Maksymalny rozmiar pliku obrazu to {0}',
    'invalid upload failed to move' => 'Nie udało się przenieść wysłanego pliku',
    
    // Registration form
    'terms of services not accepted' => 'Aby utworzyć konto musisz przeczytać i zaakceptować nasze Warunki Świadczenia Usługi',
    
    // Init company website
    'failed to load company website' => 'Nie udało się wczytać strony. Nie znaleziono firmy właścicielskiej',
    'failed to load project' => 'Nie udało się wczytać aktywnego projektu',
    
    // Login form
    'username value missing' => 'Podaj swoją nazwę użytkownika',
    'password value missing' => 'Podaj swoje hasło',
    'invalid login data' => 'Nie udało się zalogować na Twoje konto. Sprawdź czy podałeś poprawną nazwę użytkownika i hasło i spróbuj ponownie',
    
    // Add project form
    'project name required' => 'Wymagane jest podanie nazwy projektu',
    'project name unique' => 'Nazwa projektu nie może być już używana',
    
    // Add message form
    'message title required' => 'Wymagane jest podanie tytułu',
    'message title unique' => 'Tytuł nie może być już używany w danym projekcie',
    'message text required' => 'Wymagane jest podanie treści',
    
    // Add comment form
    'comment text required' => 'Wymagane jest podanie treści komentarza',
    
    // Add milestone form
    'milestone name required' => 'Wymagane jest podanie nazwy etapu',
    'milestone due date required' => 'Wymagane jest podanie oczekiwanej daty realizacji etapu',
    
    // Add task list
    'task list name required' => 'Wymagane jest podanie nazwy zadania',
    'task list name unique' => 'Nazwa zadania nie może być już używana w danym projekcie',
    'task title required' => 'Wymagane jest podanie tytułu zadania',
  
    // Add task
    'task text required' => 'Wymagane jest podanie treści zadania',
    
    // Add event
    'event subject required' => 'Wymagane jest podanie tematu wydarzenia',
    'event description maxlength' => 'Opis musi być krótszy niż 3000 znaków',
    'event subject maxlength' => 'Temat musi być krótszy niż 100 znaków',
    
    // Add project form
    'form name required' => 'Wymagane jest podanie nazwy formularza',
    'form name unique' => 'Nazwa formularza nie może być już używana',
    'form success message required' => 'Wymagane jest podanie powiadomienia o sukcesie',
    'form action required' => 'Wymagane jest podanie adresu docelowego dla formularza',
    'project form select message' => 'Wybierz powiadomienie',
    'project form select task lists' => 'Wybierz zadanie',
    
    // Submit project form
    'form content required' => 'Wpisz zawartość w pole tekstowe',
    
    // Validate project folder
    'folder name required' => 'Wymagane jest podanie nazwy katalogu',
    'folder name unique' => 'Nazwa katalogu nie może być już używana w danym projekcie',
    
    // Validate add / edit file form
    'folder id required' => 'Wybierz katalog',
    'filename required' => 'Wymagane jest podanie nazwy pliku',
    
    // File revisions (internal)
    'file revision file_id required' => 'Wersje muszą być powiązane z jakimś plikiem',
    'file revision filename required' => 'Wymagane jest podanie nazwy pliku',
    'file revision type_string required' => 'Nieznany typ pliku',
    
    // Test mail settings
    'test mail recipient required' => 'Wymagane jest podanie adresu odbiorcy',
    'test mail recipient invalid format' => 'Format podanego adresu odbiorcy jest nieprawidłowy',
    'test mail message required' => 'Wymagane jest podanie treści wiadomości',
    
    // Mass mailer
    'massmailer subject required' => 'Wymagane jest podanie tematu wiadomości',
    'massmailer message required' => 'Wymagane jest podanie treści wiadomości',
    'massmailer select recepients' => 'Wybierz użytkowników którzy mają otrzymać tą wiadomość',
    
  	//Email module
  	'mail account name required' => 'Wymagane jest podanie nazwy konta',
  	'mail account id required' => 'Wymagane jest podanie Id konta',
  	'mail account server required' => 'Wymagane jest podanie serwera',
  	'mail account password required' => 'Wymagane jest podanie hasła',
    'send mail error' => 'Błąd podczas wysyłania wiadomości. Prawdopodobnie z powodu nieprawidłowych ustawień SMTP.',
    'email address already exists' => 'Ten adres e-mail jest już używany.',	
  
  	'session expired error' => 'Sesja przeterminowała się z powodu braku aktywności użytkownika. Zaloguj się ponownie',
  	'unimplemented type' => 'Nieobsługiwany typ',
  	'unimplemented action' => 'Nieobsługiwana akcja',
  
  	'workspace own parent error' => 'Projekt nie może być nadrzędny wobec samego siebie(rodzic-rodzic/dziecko)',
  	'task own parent error' => 'Zadanie nie może być nadrzędne wobec siebie samego(rodzic-rodzic/dziecko)',
  	'task child of child error' => 'Zadanie nie może być podrzędne wobec obiektów będących podrzędnymi wobec tego zadania (rodzic-dziecko-rodzic)',
  
  	'chart title required' => 'Wymagane jest podanie tytułu wykresu.',
  	'chart title unique' => 'Tytuł wykresu nie może być już używany.',
    'must choose at least one workspace error' => 'Musisz wybrać przynajmniej jeden projekt w którym chcesz umieścić obiekt.',
    
    
    'user has contact' => 'Kontakt został już przyporządkowany temu użytkownikowi.',
    
    'maximum number of users reached error' => 'Osiągnięto limit ilości użytkowników',
	'maximum number of users exceeded error' => 'Przekroczono limit ilości użytkowników. Aplikacja nie będzie działać dopóki ten problem nie zostanie rozwiązany.',
	'maximum disk space reached' => 'Zapełniłeś/aś cała przydzieloną Ci przestrzeń na dysku (quota). Usuń jakieś obiekty zanim dodasz kolejne lub skontaktuj się z obsługą klienta by włączyć kolejnych użytkowników.',
	'error db backup' => 'Wystąpił błąd podczas tworzenia kopii bezpieczeństwa bazy danych: {0}.',
	'backup command failed' => 'Wystąpił błąd podczas tworzenia kopii bezpieczeństwa. Sprawdź wartość stałej MYSQLDUMP_COMMAND.',
  	'success db backup' => 'Kopia bezpieczeństwa została utworzona.',
	'error create backup folder' => 'Wystąpił błąd podczas tworzenia katalogu kopii bezpieczeństwa. Nie można dokończyć procesu tworzenia kopii bezpieczeństwa',
	'error delete backup' => 'Wystąpił błąd podczas usuwania kopii bezpieczeństwa bazy danych,',
	'success delete backup' => 'Kopia bezpieczeństwa została usunięta',
    'name must be unique' => 'Przepraszamy ale wybrana nazwa jest już używana',
  	'not implemented' => 'Nieobsługiwane',
	'return code' => 'Kod powrotu: {0}',
	'task filter criteria not recognised' => 'Kryterium dla filtra Zadań \'{0}\' nie rozpoznane',
   ); // array

?>