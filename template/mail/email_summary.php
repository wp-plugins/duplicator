<?php

/**
 * Duplicator schedule success mail
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Installer\Utils\LinkManager;
use Duplicator\Utils\Upsell;
use Duplicator\Utils\Email\EmailHelper;
use Duplicator\Utils\Email\EmailSummary;

/**
 * Variables
 *
 * @var array<string, mixed> $tplData
 */

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width">
        <title><?php _e('Duplicator', 'duplicator'); ?></title>
        <style type="text/css">
            a {
              text-decoration: none;
            }

            @media only screen and (max-width: 599px) {
              table.body .main-tbl {
                width: 95% !important;
              }

              .header {
                padding: 15px 15px 12px 15px !important;
              }

              .header img {
                width: 200px !important;
                height: auto !important;
              }
              .content {
                padding: 30px 40px 20px 40px !important;
              }
            }
        </style>
    </head>
    <body <?php EmailHelper::printStyle('body'); ?>>
        <table <?php EmailHelper::printStyle('table body'); ?>>
            <tr <?php EmailHelper::printStyle('tr'); ?>>
                <td <?php EmailHelper::printStyle('td'); ?>>
                    <table <?php EmailHelper::printStyle('table main-tbl'); ?>>
                        <tr <?php EmailHelper::printStyle('tr'); ?>>
                            <td <?php EmailHelper::printStyle('td logo txt-center'); ?>>
                               <img 
                                    src="<?php echo DUPLICATOR_PLUGIN_URL . 'assets/img/email-logo.png'; ?>"
                                    alt="logo"
                                    <?php EmailHelper::printStyle('img'); ?>
                                > 
                            </td>
                        </tr>
                        <tr <?php EmailHelper::printStyle('tr'); ?>>
                            <td <?php EmailHelper::printStyle('td content'); ?>>
                                <table <?php EmailHelper::printStyle('table main-tbl-child'); ?>>
                                    <tr <?php EmailHelper::printStyle('tr'); ?>>
                                        <td <?php EmailHelper::printStyle('td'); ?>>
                                            <h6 <?php EmailHelper::printStyle('h6'); ?>>Hi there!</h6>
                                            <p <?php EmailHelper::printStyle('p subtitle'); ?>>
                                                <?php
                                                printf(
                                                    _x(
                                                        'Here\'s a quick overview of your backups in the past %s.',
                                                        '%s is the frequency of email summaries.',
                                                        'duplicator'
                                                    ),
                                                    EmailSummary::getFrequencyText()
                                                );
                                                ?>
                                            </p>
                                            <p <?php EmailHelper::printStyle('p'); ?>>
                                                <strong style="<?php EmailHelper::printStyle('strong'); ?>"> 
                                                    <?php _e('Did you know?', 'duplicator'); ?>
                                                </strong>
                                                </br>
                                                <?php
                                                if (rand(0, 100) % 2 === 0) {
                                                    _e(
                                                        'With Duplicator Pro you can create fully automatic backups! Schedule your preferred ' .
                                                        'intervals for backups - daily, weekly, or monthly and never worry about data loss again!',
                                                        'duplicator'
                                                    );
                                                } else {
                                                    _e(
                                                        'With Duplicator Pro you can store backups in Google Drive, Amazon S3, OneDrive, Dropbox, ' .
                                                        'or any SFTP/FTP server for added protection.',
                                                        'duplicator'
                                                    );
                                                }
                                                ?>
                                            </p>
                                            <p <?php EmailHelper::printStyle('p'); ?>>
                                                <?php
                                                    printf(
                                                        esc_html_x(
                                                            'To unlock scheduled backups, remote storages and many other features, %supgrade to PRO%s!',
                                                            '%s and %s are opening and closing link tags to the pricing page.',
                                                            'duplicator'
                                                        ),
                                                        '<a href="' . Upsell::getCampaignUrl('email-summary', 'Upgrade to PRO') . '" style="'
                                                        . EmailHelper::getStyle('inline-link') . '">',
                                                        '</a>'
                                                    );
                                                    ?>
                                            </p>
                                            <?php if (count($tplData['packages']) > 0) : ?>
                                            <p <?php EmailHelper::printStyle('p'); ?>>
                                                <?php _e('Below are the total numbers of successful and failed backups.', 'duplicator'); ?>
                                            </p>
                                            <table <?php EmailHelper::printStyle('table stats-tbl'); ?>>
                                                <tr <?php EmailHelper::printStyle('tr'); ?>>
                                                    <th <?php EmailHelper::printStyle('th'); ?>>
                                                        <?php _e('State', 'duplicator'); ?>
                                                    </th>
                                                    <th <?php EmailHelper::printStyle('th stats-count-cell'); ?>>
                                                        <?php _e('Backups', 'duplicator'); ?>
                                                    </th>
                                                </tr>
                                                <?php foreach ($tplData['packages'] as $id => $packageInfo) : ?>
                                                <tr <?php EmailHelper::printStyle('tr'); ?>>
                                                    <td <?php EmailHelper::printStyle('td stats-cell'); ?>>
                                                        <?php echo $packageInfo['name']; ?>
                                                    </td>
                                                    <td <?php EmailHelper::printStyle('td stats-cell stats-count-cell'); ?>>
                                                        <?php if ($id !== 'failed') : ?>
                                                            <span <?php EmailHelper::printStyle('txt-orange'); ?>>
                                                                <?php echo $packageInfo['count']; ?>
                                                            </span>
                                                        <?php else : ?>
                                                            <?php echo $packageInfo['count']; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </table>
                                            <?php else : ?>
                                            <p <?php EmailHelper::printStyle('p'); ?>>
                                                <?php echo __('No backups were created in the past week.', 'duplicator'); ?>
                                            </p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td <?php EmailHelper::printStyle('td unsubscribe'); ?>>
                                <?php
                                printf(
                                    _x(
                                        'This email was auto-generated and sent from %s.',
                                        '%s is an <a> tag with a link to the current website.',
                                        'wpforms-lite'
                                    ),
                                    '<a href="' . get_site_url() . '" ' .
                                    'style="' . EmailHelper::getStyle('footer-link') . '">'
                                    . wp_specialchars_decode(get_bloginfo('name')) . '</a>'
                                );
                                ?>

                                <?php
                                $faqUrl = LinkManager::getDocUrl('how-to-disable-email-summaries', 'email_summary', 'how to disable');
                                printf(
                                    esc_html_x(
                                        'Learn %1show to disable%2s.',
                                        '%1s and %2s are opening and closing link tags to the documentation.',
                                        'wpforms-lite'
                                    ),
                                    '<a href="' . $faqUrl . '" style="' . EmailHelper::getStyle('footer-link') . '">',
                                    '</a>'
                                );
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
    
