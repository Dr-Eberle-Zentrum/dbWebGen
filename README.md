# dbWebGen - Database Web Generator for PHP
This PHP application automatically generates web forms to create, edit, view and browse data records in relational databases. 

## Requirements
* Webserver running PHP (lowest tested version is 5.3)
* Database server (currently working only with PostgreSQL; lowest tested version is 9.2)
* A database

## Get it Running
* Clone this repository into any folder that is served by your webserver.
* Since this repository contains the app engine only, you need to create another folder that will serve as the actual app folder
* In the app folder, create a PHP file that serves as the main entry point of the app (typically `index.php`). This file is very simple: it must include a definition of the constant `ENGINE_PATH`, which shall define the path to app engine folder. The other line in this file is the inclusion of `engine.php` from the app engine folder.
* Copy `settings.template.php` into your app folder, rename it to `settings.php`, and fill the file with settings that reflect your app and database structure.
* Direct your web browser to the app folder and be happy.

## Example Database and App
An example can be seen in the `dbWebGen-demo` repository

## License
This code is licensed under the MIT license. See the [LICENSE](LICENSE) file.