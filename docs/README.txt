 eXhibition - Content management for the visual arts
 copyright (c) 2006 sketchdude

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*****************************************************************************************

Installation Notes:

Quick install

1. Copy or upload the entire exhibition directory to your servers web folder.
2. Under Unix, set file permissions for the entire images folder and all of it's contents:
   cd /usr/local/apache/htdocs/exhibition
   chmod -R images 777
3. Create a mysql database and a user with full permissions
4. Edit the file exhibition/includes/config_inc.php
5. Point your web browser to:
   http://yoursite.com/exhibition/install
6. Run the install script, filling in all of the required data.
7. Back up and delete the install directory and the sql directory from the server.
8. You should be able to log in to the administration area now.

To manually install (without the install script)

1. Copy or upload the entire exhibition directory to your servers web folder.
2. Under Unix, set file permissions for the entire images folder and all of it's contents:
   cd /usr/local/apache/htdocs/exhibition
   chmod -R images 777
3. Create a mysql database and a user with full permissions
4. Build the database tables by running the exhibition/sql/exhibition.sql script through mysql.
5. Edit the file exhibition/includes/config_inc.php
6. Back up and delete the install directory and the sql directory from the server.
7. You should be able to log in to the administration area now. 

*****************************************************************************************

Running the SQL script in MySQL:

By default, exhibition.sql will write to a database named "exhibition" and create an
administrative user and password:

username: admin
password: password

The only way to change this default is to update the database in mysql, so read on
if you want to use a better name and password for the site administrator.

Again, the ONLY way to create the admin user is with the exhibition.sql file or with
your mysql client! Be sure to md5 encrypt the password before you add it, or it will not
work!

*****************************************************************************************

Database Name:

To use the default, log in to mysql and create a database named 'exhibition', and a
mysql user for the database with full permissions. 

To use a different name for your database, first create a db in mysql, using what ever
database name you prefer. Next, edit line 7 of exhibition.sql where it says:

USE exhibition;

And change the word "exhibition" to the db name you chose instead.

*****************************************************************************************

Site Administrator Name:

To use a different admin username, edit line 48 of exhibition.sql where it says:

INSERT INTO admin (admin_name, admin_pass, admin_email) VALUES ('admin',...

And change the value ('admin') to the site administrator name you chose.

*****************************************************************************************

Site Administrator Password:

The password is md5 encrypted, so the actual value for 'admin_pass' which is passed to
mysql is:

5f4dcc3b5aa765d61d8327deb882cf99

This is the encrypted value for "password" on line 48 of exhibition.sql. Use PHP's md5
function to get a new value for a different word:

<?php

// edit and run this code on your server, then view it in a browser
echo md5('newWord');

?>

And copy the new value from your browser to line 48 over the old encrypted value:
('5f4dcc3b5aa765d61d8327deb882cf99')

*****************************************************************************************

Unix/Linux File Permissions:

Once you've copied the exhibition folder to a web directory on your server, you'll 
need to chmod the exhibition/images directory and all of its subdirectories to
allow uploads:

cd /usr/local/apache/htdocs/exhibition
chmod -R 777 images/

*****************************************************************************************

Configuration File:

Edit: exhibition/includes/config_inc.php
At the very minimum you need to edit the 4 mysql settings beginning on line 24. Once
those are correct, you can point your browser to the front end at:
http://yoursite.com/exhibition/

And you can log in to the administrative back-end at:
http://yoursite.com/exhibition/admin/

*****************************************************************************************

Enjoy!

Please report bugs, issues, feedback and/or submit patches to:
sketchdude@gmail.com

And let me know how you like the scripts!

*****************************************************************************************