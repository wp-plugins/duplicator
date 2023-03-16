<?php

class DUP_Archive_Config
{
    public $dup_type                 = 'lite';
    public $created                  = '';
    public $version_dup              = '';
    public $version_wp               = '';
    public $version_db               = '';
    public $version_php              = '';
    public $version_os               = '';
    public $blogname                 = '';
    public $exportOnlyDB             = false;
    public $secure_on                = false;
    public $secure_pass              = '';
    public $dbhost                   = null;
    public $dbname                   = null;
    public $dbuser                   = null;
    public $cpnl_host                = null;
    public $cpnl_user                = null;
    public $cpnl_pass                = null;
    public $cpnl_enable              = null;
    public $wp_tableprefix           = '';
    public $mu_mode                  = 0;
    public $mu_generation            = 0;
    public $mu_siteadmins            = array();
    public $subsites                 = array();
    public $main_site_id             = 1;
    public $mu_is_filtered           = false;
    public $license_limit            = 0;
    public $license_type             = 0;
    public $dbInfo                   = array();
    public $packInfo                 = array();
    public $fileInfo                 = array();
    public $wpInfo                   = array();
    public $opts_delete              = array();
    public $brand                    = array();
    public $overwriteInstallerParams = array();
    public $installer_base_name      = '';
    public $installer_backup_name    = '';
    public $package_name             = '';
    public $package_hash             = '';
    public $package_notes            = '';
}
