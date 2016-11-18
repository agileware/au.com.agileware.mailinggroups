<?php

require_once 'mailinggroups.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mailinggroups_civicrm_config(&$config) {
  _mailinggroups_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mailinggroups_civicrm_xmlMenu(&$files) {
  _mailinggroups_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mailinggroups_civicrm_install() {
  _mailinggroups_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mailinggroups_civicrm_uninstall() {
  _mailinggroups_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mailinggroups_civicrm_enable() {
  _mailinggroups_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mailinggroups_civicrm_disable() {
  _mailinggroups_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mailinggroups_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mailinggroups_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mailinggroups_civicrm_managed(&$entities) {
  _mailinggroups_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mailinggroups_civicrm_caseTypes(&$caseTypes) {
  _mailinggroups_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mailinggroups_civicrm_angularModules(&$angularModules) {
_mailinggroups_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mailinggroups_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mailinggroups_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_pre().
 *
 * @param string $op
 * @param string $objectName
 * @param int $id
 * @param array $params
 */
function mailinggroups_civicrm_pre($op, $objectName, $id, &$params) {
  if($objectName == 'Mailing') {
    $edit_load = FALSE;
    switch($op) {
      case 'edit':
        /* In theory all the contacts it makes sense to exclude should have been excluded already;
           throw a warning if this is not the case? */
        $edit_load = !empty($params['options']['force_rollback']);
        // We're loading it for the editor - now what?
      case 'create':
        // Filter out groups the user has no permissions for.
        if (isset($params['groups'])){
          $filtered = _mailinggroups_acl_filter_groups($params['groups']);

          if($filtered && !$edit_load) {
            CRM_Core_Session::setStatus(
              ts(
                'One group was removed from the recipient list due to insufficient permissions.',
                array(
                  'count' => $filtered,
                  'plural' => '%count groups were removed from the recipient list due to insufficient permissions.',
                )),
              ts('Recipients removed')
            );
          }
        }

        // Filter out mailings the user has no permissions for.
        if (isset($params['mailings'])){
          $filtered = _mailinggroups_acl_filter_mailings($params['mailings']);

          if($filtered && !$edit_load) {
            CRM_Core_Session::setStatus(
              ts(
                'One past mailing was removed from the recipient list due to insufficient permissions.',
                array(
                  'count' => $filtered,
                  'plural' => '%count past mailings were removed from the recipient list due to insufficient permissions.',
                )),
              ts('Recipients removed')
            );
          }
        }

      default:
        break;
    }
  }
}

/**
 * Filter the groups to only include those that the editing user can see.
 */
function _mailinggroups_acl_filter_groups(Array &$groups, $do_filter = TRUE) {
  $filtered = 0;

  // Get list of allowed groups for current user;
  static $allowed = NULL;
  if(!$allowed) {
    $allowed = CRM_Core_Permission::group('Mailing');
  }

  foreach($groups['include'] as $idx => $gid) {
    if (!array_key_exists($gid, $allowed)) {
      $filtered++;
      if($do_filter){
        unset($groups['include'][$idx]);
      }
    }
  }

  return $filtered;
}

/**
 * Filter the mailings to only include those that the editing user can see.
 */
function _mailinggroups_acl_filter_mailings(Array &$mailings, $do_filter = TRUE) {
  $filtered = 0;

  // Get list of allowed mailings for current user;
  static $allowed = NULL;
  if(!$allowed) {
    $allowed = CRM_Mailing_BAO_Mailing::mailingACLIDs();
  }

  foreach($mailings['include'] as $idx => $gid) {
    if (!in_array($gid, $allowed)) {
      $filtered++;
      if($do_filter){
        unset($mailings['include'][$idx]);
      }
    }
  }

  return $filtered;
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function mailinggroups_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function mailinggroups_civicrm_navigationMenu(&$menu) {
  _mailinggroups_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'au.com.agileware.mailinggroups')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _mailinggroups_civix_navigationMenu($menu);
} // */
