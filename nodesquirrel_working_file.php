<?
/* firstform $form += backup_migrate_nodesquirrel_info_form(); */
 $form = array();

  $form['nodesquirrel_info'] = array(
    '#type' => 'fieldset',
    '#title' => t('What is NodeSquirrel?'),
  );

  $form['nodesquirrel_info']['intro'] = array(
    '#type' => 'markup',
    '#markup' => t('<p>NodeSquirrel is the cloud backup service for Drupal built by the maintainers of Backup and Migrate. It is secure, reliable and affordable.</p><p>NodeSquirrel is a paid service and profits help support Backup and Migrate.</p><p>Find out more at !nodesquirrel</p>', array('!nodesquirrel' => l('nodesquirrel.com', 'http://www.nodesquirrel.com'), '!add' => l(t('add other offsite destinations'), BACKUP_MIGRATE_MENU_PATH . '/destination/list/add'), '!bam' => l(t('Backup and Migrate project page'), 'http://drupal.org/project/backup_migrate'))),
    );

 /*secondform $form += backup_migrate_nodesquirrel_status_form($key, $destination, $status); */
    $form = array();

  $form['nodesquirrel_status'] = array(
    '#type' => 'fieldset',
    '#title' => t('NodeSquirrel Status'),
  );
  $form['nodesquirrel_status']['status'] = array(
    '#type' => 'item',
    '#title' => t('NodeSquirrel Status'),
    '#markup' => t('Not Configured. Enter your Secret Key below to get started.'),
  );

  // Warn the user if the key they entered is invalid.
  if ($key && empty($status)) {
    $form['nodesquirrel_status']['status']['#markup'] = t('Your secret key does not seem to be valid. Please check that you entered it correctly or visit !ns to generate a new key.', array('!ns' => backup_migrate_nodesquirrel_get_activate_link()));
  }
  else if (!empty($destination) && is_array($status)) {
    if (!empty($status['lifetime_backups_used']) && !empty($status['lifetime_backups']) && $status['lifetime_backups_used'] >= $status['lifetime_backups']) {
      $form['nodesquirrel_status']['status']['#markup'] = t('Your !num backup trial has expired. Visit !link to continue backing up.', array('!num' => $status['lifetime_backups'], '!link' => backup_migrate_nodesquirrel_get_plan_link()));
    }
    else {
      $form['nodesquirrel_status']['status']['#markup'] = t('Ready to Backup');
      if (user_access('perform backup')) {
        $form['nodesquirrel_status']['status']['#markup'] .= ' ' . l('(' . t('backup now') . ')', BACKUP_MIGRATE_MENU_PATH);
      }
    }
    if (!empty($status['plan_name'])) {
      $form['nodesquirrel_status']['plan_name'] = array(
        '#type' => 'item',
        '#title' => t('Current Plan'),
        '#markup' => check_plain($status['plan_name'])
      );

      if (isset($status['plan_id']) && strpos($status['plan_id'], 'trial') !== FALSE) {
        if (isset($status['lifetime_backups']) && isset($status['lifetime_backups_used'])) {
          $remains = $status['lifetime_backups'] - $status['lifetime_backups_used'];
          $remains = $remains > 0 ? $remains : t('none');
          $form['nodesquirrel_status']['plan_name']['#markup'] .= ' ' . t('(@remains remaining of @backups backup trial)', array('@backups' => $status['lifetime_backups'], '@remains' => $remains));
        }

        if (isset($status['lifespan']) && isset($status['age']) && $status['lifespan'] > 0) {
          $remains = ceil(($status['lifespan'] - $status['age']) / 86400);
          if ($remains <= 0) {
            $form['nodesquirrel_status']['plan_name']['#markup'] .= ' ' . t('(Your !span day trial has expired.)', array('!span' => ceil($status['lifespan'] / 86400)));
          }
          else {
            $form['nodesquirrel_status']['plan_name']['#markup'] .= ' ' . format_plural($remains, '(1 day remaining)', '(!span days remaining)', array('!span' => ceil($remains)));
          }
        }
      }

    }

    if (isset($status['backups_used'])) {
      $form['nodesquirrel_status']['backups_used'] = array(
        '#type' => 'item',
        '#title' => t('Number of Stored Backups'),
        '#markup' => $status['backups_used'] == 0 ? t('None') : number_format($status['backups_used'])
      );
    }

    if (isset($status['last_backup'])) {
      $form['nodesquirrel_status']['last_backup'] = array(
        '#type' => 'item',
        '#title' => t('Last Backup'),
        '#markup' => empty($status['last_backup']) ? t('Never') : t('!date (!ago ago)', array('!date' => format_date($status['last_backup'], 'small'), '!ago' => format_interval(time() - $status['last_backup'], 1)))
      );
    }
    if ($status['bytes_per_locker']) {
      if (isset($status['bytes_used'])) {
        $form['nodesquirrel_status']['space'] = array(
          '#type' => 'item',
          '#title' => t('Storage Space'),
          '#markup' => t('!used used of !total (!remaining remaining)', array('!used' => backup_migrate_format_size($status['bytes_used']), '!total' => backup_migrate_format_size($status['bytes_per_locker']), '!remaining' => backup_migrate_format_size(max(0, $status['bytes_per_locker'] - $status['bytes_used']))))
        );
      }
      else {
        $form['nodesquirrel_status']['space'] = array(
          '#type' => 'item',
          '#title' => t('Total Storage Space'),
          '#markup' => t('!total', array('!total' => backup_migrate_format_size($status['bytes_per_locker'])))
        );
      }
    }
    $form['nodesquirrel_status']['manage'] = array(
      '#type' => 'item',
      '#title' => t('Management Console'),
      '#markup' => backup_migrate_nodesquirrel_get_manage_link($destination),
      '#description' => t('You can use the NodeSquirrel management console to add and edit your sites, reset your secret key, download and delete backups, and modify your NodeSquirrel account.'),
    );

  }

 /*thirdform $form += backup_migrate_nodesquirrel_credentials_settings_form($key, $status); */
   $collapse = !empty($status);
  $form['nodesquirrel_credentials'] = array(
    '#type' => 'fieldset',
    '#title' => t('NodeSquirrel Credentials'),
    '#collapsible' => $collapse,
    '#collapsed' => $collapse,
  );

  $form['nodesquirrel_credentials']['nodesquirrel_secret_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Secret Key'),
    '#size' => 80,
    '#default_value' => $key,
  );
  if (empty($key)) {
    $form['nodesquirrel_credentials']['secret_key_help'] = backup_migrate_nodesquirrel_get_activate_help_text();
  }

 /*fourthform $form += backup_migrate_nodesquirrel_schedule_settings_form($destination, $status); */
 $form = array();
  $form['nodesquirrel_schedule'] = array(
    '#type' => 'fieldset',
    '#title' => t('Backup Schedule'),
    '#description' => t('Set up a schedule to back up your site to NodeSquirrel. You can customize this schedule or add additional schedules in the !schedule.', array('!schedule' => l(t('Schedules tab'), BACKUP_MIGRATE_MENU_PATH . '/schedule'), '!cron' => l(t('cron'), 'http://drupal.org/cron'))),
  );

  $key = 'nodesquirrel_schedule';
  $form['nodesquirrel_schedule'][$key] = array();
  $defaults = array(
    'period' => empty($schedule) ? config_get('backup_migrate.settings','nodesquirrel_schedule') : $schedule->get('period'),
    'enabled' => empty($schedule) ? config_get('backup_migrate.settings','nodesquirrel_schedule_enabled') : $schedule->get('enabled'),
    'source_id' => empty($schedule) ? config_get('backup_migrate.settings','nodesquirrel_schedule_source_id') : $schedule->get('source_id'),
  );

  $form['nodesquirrel_schedule'][$key]['nodesquirrel_schedule_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Automatically backup to NodeSquirrel'),
    '#default_value' => $defaults['enabled'],
  );
  $form['nodesquirrel_schedule'][$key]['settings'] = array(
    '#type' => 'backup_migrate_dependent',
    '#dependencies' => array(
      'nodesquirrel_schedule_enabled' => TRUE,
    ),
  );
  $form['nodesquirrel_schedule'][$key]['settings']['nodesquirrel_schedule_source_id'] = _backup_migrate_get_source_pulldown($defaults['source_id']);
  $options = array(
    (60*60)       => t('Once an hour'),
    (60*60*24)    => t('Once a day'),
    (60*60*24*7)  => t('Once a week'),
  );
  $period = $defaults['period'];
  if (!isset($options[$period])) {
    $options[$period] = empty($schedule) ? t('Custom') : $schedule->get('frequency_description');
  }
  $form['nodesquirrel_schedule'][$key]['settings']['nodesquirrel_schedule'] = array(
    '#type' => 'select',
    '#title' => t('Schedule Frequency'),
    '#options' => $options,
    '#default_value' => $period,
  );
