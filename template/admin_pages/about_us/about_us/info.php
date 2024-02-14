<div class="dup-admin-about-section dup-admin-columns">
    <div class="dup-admin-column-60">
        <h3>
            <?php esc_html_e('Hello and welcome to Duplicator, the most reliable WordPress backup and migration plugin. ' .
                'At Duplicator, we build software that helps protect your website with our reliable secure backups and ' .
                'migrate your website without any manual effort.', 'duplicator'); ?>
        </h3>
        <p>
            <?php esc_html_e('Over the years, we found that most WordPress backup and migration plugins were unreliable, ' .
                'buggy, slow, and very hard to use. So we started with a simple goal: build a WordPress backup and migration ' .
                'plugin that’s both easy and powerful.', 'duplicator'); ?>
        </p>
        <p>
            <?php esc_html_e('Our goal is to take the pain out of creating backups, migrations, and make it easy.', 'duplicator'); ?>
        </p>
        <p>
            <?php
            printf(
                wp_kses(
                    /* translators: %1$s - WPBeginner URL; %2$s - OptinMonster URL; %3$s - MonsterInsights URL. */
                    __(
                        'Duplicator is brought to you by the same team that’s behind the largest WordPress resource site, ' .
                        '<a href="%1$s" target="_blank" rel="noopener noreferrer">WPBeginner</a>, the most popular ' .
                        'lead-generation software, <a href="%2$s" target="_blank" rel="noopener noreferrer">OptinMonster</a>, ' .
                        'the best WordPress analytics plugin, <a href="%3$s" target="_blank" rel="noopener noreferrer">MonsterInsights</a>, and more!',
                        'duplicator'
                    ),
                    array(
                        'a' => array(
                            'href'   => array(),
                            'rel'    => array(),
                            'target' => array(),
                        ),
                    )
                ),
                'https://www.wpbeginner.com/?utm_source=duplicatorplugin&utm_medium=pluginaboutpage&utm_campaign=aboutduplicator',
                'https://optinmonster.com/?utm_source=duplicatorplugin&utm_medium=pluginaboutpage&utm_campaign=aboutduplicator',
                'https://www.monsterinsights.com/?utm_source=duplicatorplugin&utm_medium=pluginaboutpage&utm_campaign=aboutduplicator'
            );
            ?>
        </p>
        <p>
            <?php esc_html_e('Yup, we know a thing or two about building awesome products that customers love.', 'duplicator'); ?>
        </p>
    </div>

    <div class="dup-admin-column-40 dup-admin-column-last">
        <figure>
            <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/about/team.jpeg" alt="<?php esc_attr_e('The Awesome Motive Team photo', 'duplicator'); ?>">
            <figcaption>
                <?php esc_html_e('The Awesome Motive Team', 'duplicator'); ?><br>
            </figcaption>
        </figure>
    </div>
</div>