<?php

namespace Duplicator\Utils\Email;

use DUP_Package;
use DUP_Settings;
use DUP_PackageStatus;
use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;

/**
 * Email Summary
 */
class EmailSummary
{
    const SEND_FREQ_NEVER   = 'never';
    const SEND_FREQ_DAILY   = 'daily';
    const SEND_FREQ_WEEKLY  = 'weekly';
    const SEND_FREQ_MONTHLY = 'monthly';

    /**
     * Old option key for storing email summary info, used for migrating data to the correct option key
     */
    const INFO_OPT_OLD_KEY = 'duplicator-email-summary-info';
    const PEVIEW_SLUG      = 'duplicator-email-summary-preview';
    const INFO_OPT_KEY     = 'duplicator_email_summary_info';

    /** @var self The singleton instance */
    private static $self = null;

    /** @var int[] Manual package ids */
    private $manualPackageIds = array();

    /** @var int[] info about created storages*/
    private $failedPackageIds = array();

    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$self == null) {
            self::$self = new self();
        }

        return self::$self;
    }

    /**
     * Create Email Summary object
     */
    private function __construct()
    {
        if (($data = get_option(self::INFO_OPT_KEY)) !== false) {
            JsonSerialize::unserializeToObj($data, $this);
        }
    }

    /**
     * Returns the preview link
     *
     * @return string
     */
    public static function getPreviewLink()
    {
        return add_query_arg('page', self::PEVIEW_SLUG, admin_url('admin.php'));
    }

    /**
     * Add package to summary
     *
     * @param DUP_Package $package The package
     * @param int         $status  The status
     *
     * @return void
     */
    public function addPackage(DUP_Package $package, $status)
    {
        if ($status !== DUP_PackageStatus::COMPLETE && $status !== DUP_PackageStatus::ERROR) {
            return;
        }

        if ($status === DUP_PackageStatus::COMPLETE) {
            $this->manualPackageIds[] = $package->ID;
        } elseif ($status === DUP_PackageStatus::ERROR) {
            $this->failedPackageIds[] = $package->ID;
        }

        $this->save();
    }

    /**
     * Returns info about created packages
     *
     * @return array<int|string, array<string, string|int>>
     */
    public function getPackagesInfo()
    {
        $packagesInfo           = array();
        $packagesInfo['manual'] = array(
            'name'     => __('Successful', 'duplicator'),
            'count'    => count($this->manualPackageIds),
        );

        $packagesInfo['failed'] = array(
            'name'     => __('Failed', 'duplicator'),
            'count'    => count($this->failedPackageIds),
        );

        return $packagesInfo;
    }

    /**
     * Get all frequency options
     *
     * @return array<int, string>
     */
    public static function getAllFrequencyOptions()
    {
        return array(
            self::SEND_FREQ_NEVER   => esc_html__('Never', 'duplicator'),
            self::SEND_FREQ_DAILY   => esc_html__('Daily', 'duplicator'),
            self::SEND_FREQ_WEEKLY  => esc_html__('Weekly', 'duplicator'),
            self::SEND_FREQ_MONTHLY => esc_html__('Monthly', 'duplicator'),
        );
    }

     /**
     * Get the frequency text displayed in the email
     *
     * @return string
     */
    public static function getFrequencyText()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        switch ($frequency) {
            case self::SEND_FREQ_DAILY:
                return esc_html__('day', 'duplicator');
            case self::SEND_FREQ_MONTHLY:
                return esc_html__('month', 'duplicator');
            case self::SEND_FREQ_WEEKLY:
            default:
                return esc_html__('week', 'duplicator');
        }
    }

    /**
     * Reset plugin data
     *
     * @return bool True if data has been reset, false otherwise
     */
    public function resetData()
    {
        $this->manualPackageIds = array();
        $this->failedPackageIds = array();

        return $this->save();
    }

    /**
     * Save plugin data
     *
     * @return bool True if data has been saved, false otherwise
     */
    private function save()
    {
        return update_option(self::INFO_OPT_KEY, JsonSerialize::serialize($this));
    }
}
