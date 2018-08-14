-- eXhibition - A PHP/MySQL Art Publishing System
-- copyright (c) 2008 sketchdude

-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; either version 2
-- of the License, or (at your option) any later version.

-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.

-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

-- MySQL dump 9.09
--
-- Host: localhost    Database: exhibition
-- ------------------------------------------------------
-- Server version	4.0.16-nt

USE exhibition;

CREATE TABLE admin (
  admin_id int(10) unsigned NOT NULL auto_increment,
  admin_name varchar(25) NOT NULL default '',
  admin_pass varchar(32) NOT NULL default '',
  admin_email varchar(55) NOT NULL default '',
  last_visited timestamp(14) NOT NULL,
  google_settings_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (admin_id)
) TYPE=MyISAM;

INSERT INTO admin (admin_id, admin_name, admin_pass, admin_email) VALUES (null, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin@yoursite.com');

CREATE TABLE artist (
  artist_id int(10) unsigned NOT NULL auto_increment,
  name varchar(125) NOT NULL default '',
  birth_date date NOT NULL default '0000-00-00',
  death_date date NOT NULL default '0000-00-00',
  location varchar(75) NOT NULL default '',
  PRIMARY KEY (artist_id)
) TYPE=MyISAM;

CREATE TABLE artwork (
  artwork_id int(10) unsigned NOT NULL auto_increment,
  thumbnail varchar(255),
  type enum('sale','exhibit') NOT NULL default 'sale',
  artist_id int(10) unsigned NOT NULL default '0',
  medium varchar(35) default NULL,
  size varchar(25) default NULL,
  style varchar(45) default NULL,
  subject varchar(45) default NULL,
  price double(16,2) NOT NULL default '0.00',
  shipping double(16,2) NOT NULL default '0.00',
  handling double(16,2) NOT NULL default '0.00',
  sale_amount double(16,2) NOT NULL default '0.00',
  sale_date datetime NOT NULL default '0000-00-00 00:00:00',
  date_completed date NOT NULL default '0000-00-00',
  status enum('available','pending','sold') NOT NULL default 'available',
  qty_instock int(10) unsigned NOT NULL default '1',
  comments enum('enable','disable') NOT NULL default 'disable',
  gallery_id int(10) unsigned NOT NULL default '0',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (artwork_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE category (
  category_id int(10) unsigned NOT NULL auto_increment,
  cat_name varchar(35) NOT NULL default '',
  cat_type enum('page', 'gallery', 'product', 'site') NOT NULL default 'site',
  sidebar enum('one','two') NOT NULL default 'one',
  rss_channel enum('yes','no') NOT NULL default 'no',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE comment (
  comment_id int(10) unsigned NOT NULL auto_increment,
  comment_parent tinyint(5) unsigned NOT NULL default '0',
  parent_type enum('art','page','comment') NOT NULL default 'art',
  author varchar(25) NOT NULL default '',
  salutation tinytext NOT NULL default '',
  message text NOT NULL,
  msg_type enum('public','private') NOT NULL default 'public',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (comment_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE customer (
  customer_id int(10) unsigned NOT NULL auto_increment,
  public_name varchar(75) NOT NULL default '',
  first_name varchar(35) NOT NULL default '',
  last_name varchar(35) NOT NULL default '',
  address1 varchar(65) NOT NULL default '',
  address2 varchar(65) NOT NULL default '',
  city varchar(40) NOT NULL default '',
  state tinyint(2) unsigned NOT NULL default 0,
  post_code varchar(25) NOT NULL default '',
  country tinyint(3) unsigned NOT NULL default 0,
  phone varchar(16) NOT NULL default '',
  email varchar(55) NOT NULL default '',
  bill_address varchar(65) NOT NULL default '',
  bill_address2 varchar(65) NOT NULL default '',
  bill_city varchar(40) NOT NULL default '',
  bill_state tinyint(2) unsigned NOT NULL default 0,
  bill_post_code varchar(25) NOT NULL default '',
  bill_country tinyint(3) unsigned NOT NULL default 0,
  ship_address varchar(65) NOT NULL default '',
  ship_address2 varchar(65) NOT NULL default '',
  ship_city varchar(40) NOT NULL default '',
  ship_state tinyint(2) unsigned NOT NULL default 0,
  ship_post_code varchar(25) NOT NULL default '',
  ship_country tinyint(3) unsigned NOT NULL default 0,
  date_entered datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (customer_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE gallery (
  gallery_id int(10) unsigned NOT NULL auto_increment,
  category_id int(10) unsigned NOT NULL default '0',
  gallery_name varchar(75) NOT NULL default '',
  gallery_icon varchar(155) NOT NULL default '',
  art_per_page int(2) unsigned NOT NULL default '0',
  art_per_row int(2) unsigned NOT NULL default '0',
  thumbnail_max int(3) unsigned NOT NULL default '0',
  rss_channel enum('yes','no') NOT NULL default 'no',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (gallery_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE google_settings (
  google_settings_id int(10) unsigned NOT NULL auto_increment,
  google_name varchar(32) NOT NULL default 'Google Sandbox Account',
  google_merchant_id varchar(15) NOT NULL default '',
  google_merchant_key varchar(22) NOT NULL default '',
  google_server_type enum('sandbox', 'live') NOT NULL default 'sandbox',
  security_protocol enum('http', 'https') NOT NULL default 'http',
  PRIMARY KEY  (google_settings_id)
) TYPE=MyISAM;

CREATE TABLE image (
  image_id int(10) unsigned NOT NULL auto_increment,
  artwork_id int(10) unsigned NOT NULL default '0',
  path varchar(255) NOT NULL default '',
  caption varchar(255),
  PRIMARY KEY  (image_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE meta_data (
  meta_data_id int(10) unsigned NOT NULL auto_increment,
  meta_type enum('site','artwork','gallery','page','product','category','comment') NOT NULL default 'site',
  content_id int(10) unsigned NOT NULL default '0',
  title varchar(55) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  keywords varchar(255) NOT NULL default '',
  display enum('show','hide') NOT NULL default 'show',
  rss_feed enum('enable', 'disable') NOT NULL default 'disable',
  priority smallint(5) unsigned NOT NULL default '0',
  link varchar(155) NOT NULL default '',
  pub_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (meta_data_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE orders (
  order_id int(10) unsigned NOT NULL auto_increment,
  order_number varchar(32) NOT NULL default '',
  order_date datetime NOT NULL default '0000-00-00 00:00:00',
  payment_date datetime NOT NULL default '0000-00-00 00:00:00',
  completed datetime NOT NULL default '0000-00-00 00:00:00',
  ship_date  datetime NOT NULL default '0000-00-00 00:00:00',
  notes tinytext NOT NULL default '',
  trans_status enum('new', 'paid', 'shipped', 'complete', 'cancelled') NOT NULL default 'new',
  customer_id int(10) unsigned NOT NULL default '0',
  artwork_id int(10) unsigned NOT NULL default '0',
  product_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (order_id) 
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE page (
  page_id int(10) unsigned NOT NULL auto_increment,
  category_id int(10) unsigned NOT NULL default '0',
  name varchar(35) NOT NULL default '',
  text text NOT NULL,
  comments enum('enable','disable') NOT NULL default 'disable',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (page_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE product (
  product_id int(10) unsigned NOT NULL auto_increment,
  category_id int(10) unsigned NOT NULL default '0',
  product_name varchar(55) NOT NULL default '',
  picture varchar(255) NOT NULL default '',
  qty_instock int(10) unsigned NOT NULL default '1',
  price double(16,2) NOT NULL default '0.00',
  shipping double(16,2) NOT NULL default '0.00',
  handling double(16,2) NOT NULL default '0.00',
  meta_data_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (product_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE signature (
  signature_id int(10) unsigned NOT NULL auto_increment,
  author varchar(25) NOT NULL default 'Webmaster',
  salutation tinytext NOT NULL default '',
  PRIMARY KEY  (signature_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE underground_channel (
  channel_id int(10) unsigned NOT NULL auto_increment,
  provider_id int(10) unsigned NOT NULL default '0',
  copyright varchar(155) NOT NULL default '',
  pub_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  channel_link varchar(155) NOT NULL default '',
  channel_title varchar(55) NOT NULL default '',
  channel_description varchar(255) NOT NULL default '',
  image_url varchar(155) NOT NULL default '',
  image_title varchar(55) NOT NULL default '',
  image_link varchar(155) NOT NULL default '',
  image_width tinyint unsigned NOT NULL default '0',
  image_height tinyint unsigned NOT NULL default '0',
  PRIMARY KEY  (channel_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE underground_item (
  item_id int(10) unsigned NOT NULL auto_increment,
  channel_id int(10) unsigned NOT NULL default '0',
  title varchar(55) NOT NULL default '',
  link varchar(155) NOT NULL default '',
  description text NOT NULL default '',
  pub_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated  datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (item_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE underground_message (
  message_id int(10) unsigned NOT NULL auto_increment,
  provider_id int(10) unsigned NOT NULL default '0',
  author varchar(55) NOT NULL default '',
  title varchar(55) NOT NULL default '',
  message text NOT NULL default '',
  PRIMARY KEY  (message_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE underground_provider (
  provider_id int(10) unsigned NOT NULL auto_increment,
  provider_name varchar(55) NOT NULL default '',
  provider_url varchar(155) NOT NULL default '',
  max_items int(11) NOT NULL default '0',
  blocked enum('on','off') NOT NULL default 'off',
  PRIMARY KEY  (provider_id)
} TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE waiting_list (
  list_id int(10) unsigned NOT NULL auto_increment,
  name varchar(55) NOT NULL default '',
  email varchar(155) NOT NULL default '',
  add_date datetime NOT NULL default '0000-00-00 00:00:00',
  offer_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (list_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;