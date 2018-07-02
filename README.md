# dbWebGen - Database Web App Generator for PHP
This PHP application automatically generates a responsive web app on top of your relational database. The app allows users to
* Create and edit records via web forms, taking into account foreign keys and other constraints
* View stored records along with related records from other tables
* Browse, search, and filter records in tables
* Query the database, visualize the results, and share the visualizations. Currenty the engine offers various visualizations like tables, bar charts, leaflet maps, sankeys, timelines, graphs/networks, social network analysis, geomaps and others

Developers may add custom functionality and extensions to the engine through hook functions in plugins, and admins may exploit an [extensive array of settings](settings.template.php) controlling the engine.

## Requirements
* Webserver running PHP (lowest tested version is 5.3)
* Database server (currently working only with PostgreSQL -- lowest tested version is 9.2; experimental support for MySQL)
* A database

## Get it Running
* Clone this repository into any folder that is served by your webserver.
* Run `npm install` in this folder.
* Since this repository contains the app engine only, you need to create another folder that will serve as the actual app folder
* In the app folder, create a PHP file that serves as the main entry point of the app (typically `index.php`). This file is very simple: it must include a definition of the constant `ENGINE_PATH`, which shall define the relative path to app engine folder. The other line in this file is the inclusion of `engine.php` from the app engine folder. You can also define the language by defining `DBWEBGEN_LANG` (currently English `en` and German `de` are available).

  Note: if required, you may use `ENGINE_PATH_LOCAL` to define the relative or absolute local file system path to the engine folder, which is used for including `.php` files; the `ENGINE_PATH` is used to point to files in `<script>` or `<link>` tags, so those must be resolveable by the web server.

  The typical index file would then look like this:

  ```php
  <?php
     define('ENGINE_PATH', '../dbWebGen/');
     define('DBWEBGEN_LANG', 'de');
     include ENGINE_PATH . 'engine.php';
  ?>
  ```
* Direct your web browser to the app folder. Upon first visit, a setup wizward will allow you to specify all app settings, which will be stored in a file called `settings.php`. If you encounter any issues or somehow misconfigure your app, you can also manually edit the `settings.php` file with explanations provided in [settings.template.php](settings.template.php)

## Example Database and App
An example app using this engine can be seen in the [dbWebGen-demo](https://github.com/eScienceCenter/dbWebGen-demo) repository

## Selected Features
* Fully localized in English and German; other languages can be added easily
* Access control via users table in database, custom authentication functions, or static user arrays
* Paginated, searchable, sortable viewing of table contents
* Viewing full details of single records
* Single-page or tabbed record editing forms
* Inline creation and editing of records referenced via foreign keys
* Conditional display and labeling of record fields in editing forms
* Grouped field display in editing forms (e.g. when date components are stored in separate columns)
* Hyperlinked display of records linked via foreign keys
* Record merging wizard
* Global and table-local search, allowing custom search transformations (e.g. removing diacritics, unaccenting, etc.)
* Custom menus, pages, and plugins with access to dbWebGen global objects, settings, and variables
* Input controls for most important field types, e.g. dropdown boxes, date pickers, geomap pickers, boolean toggles, text boxes, file uploads
* Custom JavaScript form validation

## Limitations
The major limitation currently is that the table settings for composite primary keys (e.g. in N:M-tables where each primary key part is a foreign key) currently only support a maximum of two primary key columns per table. If a composite primary key in some table in your DB consists of more than two columns, the remedy would be to create an artificial single-column primary key with auto-increment value for such tables.

## Screenshots
Below are some screenshots from a database app that uses dbWebGen to allow users to work with historic documents from 19th century Oman. Click any thumbnail to view at full resolution.

[![Data in a Table](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/list_documents_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/list_documents.png)  
[![Search Filter](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/filter_persons_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/filter_persons.png)  
[![View a Record](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/view_document_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/view_document.png)  
[![New Record Form](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/new_document_recipient_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/new_document_recipient.png)  
[![Edit Record Form](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/edit_document_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/edit_document.png)  
[![Query with Network Visualization](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/query_network_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/query_network.png)  
[![Query with Map Visualization](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/query_map_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/query_map.png)  
[![Responsive Edit](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/edit_responsive_th.png)](https://esciencecenter.github.io/assets/dbWebGen/screenshots/alhamra/edit_responsive.png)

## License
This code is licensed under the MIT license. See the [LICENSE](LICENSE) file.
