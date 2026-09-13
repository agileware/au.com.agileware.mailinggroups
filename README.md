# Mailing Groups ACL Check (au.com.agileware.mailinggroups)

This is a [CiviCRM](https://civicrm.org) extension that protects contact privacy when a Mailing
is created or edited "from" an existing Mailing (for example, using the *Create a similar
mailing* feature in CiviMail). When CiviCRM copies recipient selections across from an existing
Mailing, the acting user can end up with Groups or previous Mailings in the recipient list that
they do not actually have permission to see or select themselves. This extension automatically
removes any such Groups or Mailings before the record is saved, so recipients are never
determined by permissions the current user doesn't hold.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Usage

This extension works entirely in the background. There is no settings page, menu item,
CiviRule action, or API exposed — once installed and enabled, it hooks into every `Mailing`
create and edit operation via `hook_civicrm_pre`:

* **Recipient Groups (Include/Exclude)** — Before a Mailing is created or saved, any Group ID in
  the `groups.include` or `groups.exclude` lists that the acting user does not have access to
  (per `CRM_Core_Permission::group('Mailing')`, i.e. the Groups the user is permitted to select
  as Mailing recipients) is silently removed from the Mailing.
* **Previous Mailings (Include/Exclude)** — Any previous Mailing ID in the `mailings.include` or
  `mailings.exclude` lists is removed if either:
  * it is not a completed, standalone/winner Mailing belonging to the current CiviCRM domain, or
  * the acting user does not have Mailing ACL access to it (per
    `CRM_Mailing_BAO_Mailing::mailingACLIDs()`).
* **On-screen warnings** — If any Groups or past Mailings are removed, the user is shown a
  status message, e.g. *"One group was removed from the recipient list due to insufficient
  permissions."* (or the plural form), and similarly for past Mailings removed due to
  insufficient permissions or domain filtering. These messages are suppressed when CiviCRM is
  silently reloading the Mailing form for the editor (`force_rollback`), so the user isn't
  repeatedly warned about data they didn't just change.

## Special configuration requirements

None. This extension activates automatically once installed — there is nothing to configure.
It relies entirely on your site's existing CiviCRM ACL setup:

* **Group ACLs** — Groups must have the `Mailing` permission type configured (*Administer >
  Users and Permissions > Access Control > ACLs*) for `CRM_Core_Permission::group('Mailing')` to
  correctly restrict which Groups a user may see or select as Mailing recipients.
* **Mailing ACLs** — Access to previous Mailings is controlled by CiviCRM's standard Mailing ACL
  logic (`CRM_Mailing_BAO_Mailing::mailingACLIDs()`).

If your site does not use ACLs to restrict access to Groups or Mailings, every Group and Mailing
will be considered "allowed", and this extension will have no visible effect.

## Requirements

* CiviCRM 5.51 or later
* No dependent extensions

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin
Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

About the Authors
-----------------

This CiviCRM extension was developed by the team at [Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact Agileware](https://agileware.com.au/contact) today!

![Agileware](logo/agileware-logo.png)
