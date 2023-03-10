     Update Sponzy - Support Creators Content Script v4.5       

Sponzy - Support Creators Content Script v4.5
=============================================

Updated: 29, October 2022

Changelog
---------

*   Fixed
*   Verify ZIP creator rule
*   Account verification (Email incorrect)
*   Stories with text disappeared on close
*   Pagination with filter on creators, live and categories
*   Sort pagination on Members on Panel Admin
*   Invoice error with transaction canceled
*   Backblaze region missing
*   New
*   Push notification on New messages (Only if the user has 10 minutes offline and less than 1 day)
*   Withdrawal option with Western Union
*   Restriction of videos to MP4 if encoding is disabled
*   Increased story thumbnail size
*   Username to registration with first name and ID
*   Images and videos adapted in stories on mobile devices
*   More visible close story button and it works

Installation
------------

**IMPORTANT:** If you have made changes to the script make a backup of the files, because they can be replaced.

**IMPORTANT:** You must update from **_version 4.4_**. if you upgrade from another version will throw an error.

**IMPORTANT:** Please follow the steps below strictly, otherwise the update will fail.

*   Upload the file **`v4.5.zip`** found inside the **`Update-v4.5`** folder, into the **`public_html`** or **`www`** folder on your server, or where you have the script installed, you must make sure that it is the root directory.
  
*   Unzip the file **`v4.5.zip`** and enter at URL **`https://yoursite.com/v4.5/`** or if you have the script in subdirectory (subdomain) **`https://yoursite.com/test/v4.5/`**

**IMPORTANT:** You must be logged in as Admin

  
*   If all goes well, this message will appear, that's it! The new version has already been installed.
![](../assets/images/01.png)  
*   Add these new text strings if you have created a language other than Spanish or English, otherwise you should not do anything because they will be added automatically. **`resources / lang / xx / general.php`**

    // Version 4.5
    'document_id' => 'Document ID',
    'new_msg_from' => 'New message from',

###### If you have multiple languages on your site you should add to each file.

* * *

Any problem or doubt send me an email to **[support@miguelvasquez.net](mailto:support@miguelvasquez.net)**  
Do not forget to visit **[miguelvasquez.net](https://miguelvasquez.net/)**

© Miguel Vasquez - Web Design and Development All Rights Reserved.[](https://www.facebook.com/MiguelVasquezWeb)[](https://twitter.com/MigueVasquezweb)[](https://instagram.com/miguelvasquezweb)
