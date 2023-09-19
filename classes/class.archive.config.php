<?php

class DUP_Archive_Config
{
    public $dup_type       = 'lite';
    public $created        = '';
    public $version_dup    = '';
    public $version_wp     = '';
    public $version_db     = '';
    public $version_php    = '';
    public $version_os     = '';
    public $blogname       = '';
    public $exportOnlyDB   = false;
    public $secure_on      = 0;
    public $secure_pass    = '';
    public $dbhost         = null;
    public $dbname         = null;
    public $dbuser         = null;
    public $cpnl_host      = null;
    public $cpnl_user      = null;
    public $cpnl_pass      = null;
    public $cpnl_enable    = null;
    public $wp_tableprefix = '';
    public $mu_mode        = 0;
    public $mu_generation  = 0;
    public $mu_siteadmins  = array();
    public $subsites       = array();
    public $main_site_id   = 1;
    public $mu_is_filtered = false;
    public $license_limit  = 0;
    public $license_type   = 0;
    public $dbInfo         = array();
    public $packInfo       = array();
    public $fileInfo       = array();
    public $wpInfo         = array();
    /** @var int<-1,max> */
    public $defaultStorageId = -1;
    /** @var string[] */
    public $components = array();
    /** @var string[] */
    public $opts_delete = array();
    /** @var array<string, mixed> */
    public $brand = array();
    /** @var array<string, mixed> */
    public $overwriteInstallerParams = array();
    /** @var string */
    public $installer_base_name = '';
    /** @var string */
    public $installer_backup_name = '';
    /** @var string */
    public $package_name = '';
    /** @var string */
    public $package_hash = '';
    /** @var string */
    public $package_notes = '';
}
