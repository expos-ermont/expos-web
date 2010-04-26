
    About OpenGoo 1.4.1
    ===================

    OpenGoo is a free and open source WebOffice, project management and collaboration
    tool, licensed under the Affero GPL 3 license.

    visit:
        * http://www.opengoo.org/
        * http://forums.opengoo.org/
        * http://sourceforge.net/projects/opengoo

    contact:
        * contact@opengoo.org


    System requirements
    ===================

    OpenGoo requires a web server, PHP (5.0 or greater) and MySQL (InnoDB
    support recommended). The recommended web server is Apache.

    OpenGoo is not PHP4 compatible and it will not run on PHP versions prior
    to PHP5.

    Recommended:

    PHP 5.2+
    MySQL 4.1+ with InnoDB support
    Apache 2.0+

        * PHP    : http://www.php.net/
        * MySQL  : http://www.mysql.com/
        * Apache : http://www.apache.org/

    Alternatively, if you just want to test OpenGoo and you don't care about security
    issues with your files, you can download XAMPP, which includes all that is needed
    by OpenGoo (Apache, PHP 5, MySQL) in a single download.
    You can configure MySQL to support InnoDB by commenting or removing
    the line 'skip-innodb' in the file '<INSTALL_DIR>/etc/my.cnf'.

        * XAMPP  : http://www.apachefriends.org/en/xampp


    Installation
    ============

    1. Download OpenGoo - http://www.opengoo.org/
    2. Unpack and upload to your web server
    3. Direct your browser to the public/install directory and follow the installation
    procedure

    You should be finished in a matter of minutes.
    
    WARNING: Default memory limit por PHP is 8MB. As a new OpenGoo install consumes about 10 MB,
    administrators could get a message similar to "Allowed memory size of 8388608 bytes exhausted".
    This can be solved by setting "memory_limit=32" in php.ini.    


    Upgrade instructions
    ====================
    
    1. Backup you current installation (important!)
    2. Download OpenGoo 1.4.1 - http://www.opengoo.org/
    3. Unpack into your OpenGoo installation, overwriting your previous files and folders,
    	but keeping your config, upload and public/files folders.
    5. Go to <your_opengoo>/public/upgrade in your browser and choose to upgrade
    	from your current version to 1.4.1
    6. Refresh your browser or clear its cache to load new javascript, css and images.   

    
	Open Source Libraries 
	=====================
	
	The following open source libraries and applications have been adapted to work with OpenGoo:
	- ActiveCollab 0.7.1 - http://www.activecollab.com
	- ExtJs - http://www.extjs.com
	- Reece Calendar - http://sourceforge.net/projects/reececalendar
	- Swift Mailer - http://www.swiftmailer.org
	- Open Flash Chart - http://teethgrinder.co.uk/open-flash-chart
	- Slimey - http://slimey.sourceforge.net
	- FCKEditor - http://www.fckeditor.net
	- JSSoundKit - http://jssoundkit.sourceforge.net
	- PEAR - http://pear.php.net


	Changelog
	=========

	Since 1.4
	---------
	
	- usability: Dim dates on calendar's monthly view which belong to another month.
	
	- bugfix: Date pickers ignored the start monday setting.
	- bugfix: Week number on weekly view was always the same.
	- bugfix: No error was displayed if adding a document with no comments even if file revision comments were required.
	- bugfix: Non admin user with write permissions for Contacts on a workspace couldn't edit contacts of that workspace without the can manage contacts permission.
	- bugfix: Removed repeated SEED constant definitions in config.php
	- bugfix: No notification was being sent to people invited to an event.
	- bugfix: Workspace comment permissions were not working properly on object views.
	- bugfix: Object picker sometimes ordered only the loaded data and not all of the data in the server.
	- bugfix: Translate tool didn't recognize javascript translation keys enclosed with double quotes. Now comboboxes are ordered alphabetically.

	Since 1.4 RC
	------------
	
	- usability: Now you can open weblinks with ctrl+click, mouse wheel or context menu.
	- usability: Users are now automatically subscribed to emails they receive.
	
	- bugfix: When fetching email through IMAP, an error on one email would cause no more emails to be fetched.
	- bugfix: Encoding was being ignored when displaying text files.
	- bugfix: Paused time was not being calculated correctly for incomplete tasks on reports.
	- bugfix: Task filters on the task panel were sometimes left blank.
	- bugfix: Users without permissions on their personal workspaces were still able to write documents.
	- bugfix: When viewing an event and then closing it you were always being taken to "today" instead of the date you were viewing.
	- bugfix: When viewing page X of Email module and changing to a workspace with less pages, you were still being shown page X with no objects (same for other listings).
	- bugfix: Zip files with mime types other than application/zip were not being recognized as zip files.
	
	- system: Help panel removed as it was not helpful enough. Top right help link now points to the wiki.

	Since 1.4 beta 2
	----------------
	
	- bugfix: Login url doesn't redirect correctly when already logged in
	- bugfix: Note modified notification sends wrong modifier
	- bugfix: Dates on time report
	- bugfix: Missing lang new notification comment
	- bugfix: Repeating events were not being shown correctly if not using GMT timezone
	- bugfix: Email filter wasn't being kept when changing workspace
	- bugfix: Some text on notifications was not on the user's locale
	- bugfix: Tag control had wrong width
	- bugfix: Workspace tree nodes not expanded correclty when filtering in Chrome
	- bugfix: When deleting an email account while filtering by that account an error is shown
	- bugfix: Autocomplete on emails didn't check for permissions

	- usability: Object description added to notifications
	- usability: A maximum of two error messages are now displayed on the GUI simultaneously
	- usability: Don't show the Custom Porperty ctageory for users if there are none defined
	- usability: When a translation isn't found, and when not debugging, show the english text 

	Since 1.4 beta
	--------------

	- feature: Several improvements to custom reports and custom properties, like pagination, memo field type
	- feature: Config option to show week number in calendar
	
	- usability: Added word OpenGoo to new account email subject; ie: 'Your new OpenGoo account has been created'	
	- usability: Amount of tasks shown on the tasks panel can be configured	
	- usability: Custom properties for workspaces displays nothing when none defined	
	- usability: Don't show the help if the lang is not there	
	- usability: Make date widget help string dependant on the date format (now it is a static lang)	
	- usability: There should be contextual help explaining about File links (files with URL) when uploading
	- usability: Change the updated_on field when a comment is added
	- usability: Include Note's text on modification notification
	- usability: Complete workspace path on email notifications
	- usability: Increase size of textareas when editing notes, comments and descriptions
	- usability: Limit Contact field sizes on GUI to match the size in the database
	- usability: Open objects in new tab in reports
	- usability: When editing comments the submit button should read Save Changes instead of Edit Comment
		
	- bugfix: Avoid using mb_detect_encoding if not available (mbstring extension not installed)	
	- bugfix: Calendar view sometimes was not being remembered on calendar	
	- bugfix: Companies with no workspaces were not listed	
	- bugfix: Error adding custom report	
	- bugfix: Error importing contacts (country)	
	- bugfix: Error when creating a task from quick add	
	- bugfix: Error when listing contacts	
	- bugfix: Minor problem(s) with workspace selection control when editing a workspace on IE7	
	- bugfix: When adding a new user, opengoo refreshes and the user is not added	
	- bugfix: Sometimes when changing localization nothing happens.	
	- bugfix: When deleting a tag it is still filtering by that tag	
	- bugfix: When filtering for workspaces on a workspace chooser, the workspaces aren't expanded
	- bugfix: Load custom css even when using minified CSS that doesn't include it
	- bugfix: Avoid popup blocking when downloading
	- bugfix: Error when deleting workspace with assigned emails
	- bugfix: Error 500 on Overview only when not in debug mode
	- bugfix: Comment notifications were not being sent

	- security: Config option to enable/disable feeds with warning about security issues

	Since 1.3.1
	-----------
	
	- feature: Custom properties per object type, used for extending the number of fields for each object type.
	- feature: Custom reports based on object types. Custom properties are also displayed and can be used in filtering and ordering criteria.
	- feature: References to external documents via urls can be added as documents in the upload file view.
	- feature: New "Getting started" widget displays information that helps new users in using the system
	- feature: Contextual help messages added in places throughout the system. 
	- feature: Workspace information widget improved, shows users and contacts assigned to the current workspace.
	- feature: Configurable date format.
	- feature: Allow to unclassify email.
	- feature: Notify an event creator when someone confirms assistance to an event.
	- feature: Calendar option - Start Week on Monday.
	- feature: Config option to automatically check out documents when editing online.
	- feature: Companies now have a field for adding notes on it.
	
	- administration: User password security and complexity options are now configurable
	- administration: Document revision comments can be set as required via a configuration option
	- administration: Billing currency symbol is now configurable
	- administration: New "Can manage reports" permission added to users and groups, allowing the creation, edition and deletion of custom reports.
	
	- usability: Three to four contacts / users are displayed in one column in the workspace info, which can be expanded to include all contacts / users
	- usability: Contacts can now be assigned to workspaces through the edit workspace view.
	- usability: "Checked out" icon displayed with documents in the documents widget
	- usability: "Checked out" information now displayed in the document view header and under the properties panel.
	- usability: Editable documents can be expanded to fill the whole page for easier viewing.
	- usability: Improved the reporting panel view. This panel will be displayed by default on new installations
	- usability: Added support for html help files in the right sidebar.
	- usability: Calendar, monthly view: Paint all events with workspace color
	- usability: Improved content of email notifications (more info and in user's language).

	- system: Initial loading time reduced by loading javascript files as they are needed.
	- system: Added new lang folder for plugin langs, which is loaded in filename order and after default OpenGoo lang files.
	- system: Added new lang folder for help langs, displayed in the right sidebar of OpenGoo.
	- system: New hooks added.
	- system: Mail notifications can be sent through cron, so that user doesn't have to wait for it to send.
	- system: Slimey updated to 0.2. It is now translatable.
	
	- bugfix: Handle timezones correctly.
	- bugfix: Various issues with importing/exporting events.
	- bugfix: Bug when fetching imap folders with non-ascii characters.
	- bugfix: Calendar doesn't show milestones assigned to user, without tasks assigned to user.
	- bugfix: Calendar titles too high.
	- bugfix: Contact/User deletion.
	- bugfix: Company csv export puts values in different order than titles.
	- bugfix: Contact import crashes with chinese characters.
	- bugfix: Mail doesn't show images that are attachments.
	- bugfix: Minor CSS issue (email actions inherit CSS style from HTML emails).
	- bugfix: When editing an IMAP account, changing the IMAP data makes no effect.
	- bugfix: Assigning a role when editing a contact which already had a role would duplicate roles. 
