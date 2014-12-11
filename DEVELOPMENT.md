Table of Content:
- [Database](#db)
  - [`alerts`](#db-alerts)
  - [`alert_log`](#db-alert_log)
  - [`alert_rules`](#db-alert_rules)
  - [`alert_schedule`](#db-alert_schedule)
  - [`alert_templates`](#db-alert_templates)
- [Files](#files)
  - [`alerts.php`](#files-alerts.php)
    - [`DescribeAlert($alert)`](#files-alerts.php-1)
    - [`ExtTransports($obj)`](#files-alerts.php-2)
    - [`FormatAlertTpl($tpl,$obj)`](#files-alerts.php-3)
    - [`RunAlerts()`](#files-alerts.php-4)
    - [`TimeFormat($secs)`](#files-alerts.php-5)
  - [`alerts.inc.php`](#files-alerts.inc.php)
    - [`GenSQL($rule)`](#files-alerts.inc.php-1)
    - [`GetContacts($result)`](#files-alerts.inc.php-2)
    - [`RunRules($device)`](#files-alerts.inc.php-3)

# <a name="db">Database</a>

## <a name="db-alerts">Table: `alerts`</a>

Holds an overview of all current states per rule per device.

```text
+-----------+-----------+------+-----+-------------------+-----------------------------+
| Field     | Type      | Null | Key | Default           | Extra                       |
+-----------+-----------+------+-----+-------------------+-----------------------------+
| id        | int(11)   | NO   | PRI | NULL              | auto_increment              |
| device_id | int(11)   | NO   |     | NULL              |                             |
| rule_id   | int(11)   | NO   |     | NULL              |                             |
| state     | int(11)   | NO   |     | NULL              |                             |
| alerted   | int(11)   | NO   |     | NULL              |                             |
| open      | int(11)   | NO   |     | NULL              |                             |
| timestamp | timestamp | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
+-----------+-----------+------+-----+-------------------+-----------------------------+
```

## <a name="db-alert_log">Table: `alert_log`</a>

Holds all issued and not-yet issued alerts.  
Known values for field `state`:
- `0` OK
- `1` Alert
- `2` Acknowledged

Field `details` only holds data when `state = 1`. The data is a gzip-compressed JSON object with informations about the entities that caused the alert.

```text
+-------------+-----------+------+-----+-------------------+----------------+
| Field       | Type      | Null | Key | Default           | Extra          |
+-------------+-----------+------+-----+-------------------+----------------+
| id          | int(11)   | NO   | PRI | NULL              | auto_increment |
| rule_id     | int(11)   | NO   |     | NULL              |                |
| device_id   | int(11)   | NO   |     | NULL              |                |
| state       | int(11)   | NO   |     | NULL              |                |
| details     | longblob  | NO   |     | NULL              |                |
| time_logged | timestamp | NO   |     | CURRENT_TIMESTAMP |                |
+-------------+-----------+------+-----+-------------------+----------------+
```

## <a name="db-alert_rules">Table: `alert_rules`</a>

Holds device specific and global rules.  
When `device_id = -1` the rule is considered to be global and thus applied to all devices and prior to device-specific rules.

```text
+-----------+---------------------------------+------+-----+---------+----------------+
| Field     | Type                            | Null | Key | Default | Extra          |
+-----------+---------------------------------+------+-----+---------+----------------+
| id        | int(11)                         | NO   | PRI | NULL    | auto_increment |
| device_id | int(11)                         | NO   |     | NULL    |                |
| rule      | text                            | NO   |     | NULL    |                |
| severity  | enum('ok','warning','critical') | NO   |     | NULL    |                |
| disabled  | tinyint(1)                      | NO   |     | NULL    |                |
+-----------+---------------------------------+------+-----+---------+----------------+
```

## <a name="db-alert_schedule">Table: `alert_schedule`</a>

Holds timeschedule for scheduled downtimes in order to halt alerting for a predefined period of time.

```text
+-----------+-----------+------+-----+---------------------+----------------+
| Field     | Type      | Null | Key | Default             | Extra          |
+-----------+-----------+------+-----+---------------------+----------------+
| id        | int(11)   | NO   | PRI | NULL                | auto_increment |
| device_id | int(11)   | NO   |     | NULL                |                |
| start     | timestamp | NO   |     | 0000-00-00 00:00:00 |                |
| end       | timestamp | NO   |     | 0000-00-00 00:00:00 |                |
+-----------+-----------+------+-----+---------------------+----------------+
```

## <a name="db-alert_templates">Table: `alert_templates`</a>

Holds templates for single rules or a set of rules.  
`rule_id` is a `,` seperated list of IDs. It __must__ start and end with `,`.

```text
+----------+--------------+------+-----+---------+----------------+
| Field    | Type         | Null | Key | Default | Extra          |
+----------+--------------+------+-----+---------+----------------+
| id       | int(11)      | NO   | PRI | NULL    | auto_increment |
| rule_id  | varchar(255) | NO   |     | ,       |                |
| template | longtext     | NO   |     | NULL    |                |
+----------+--------------+------+-----+---------+----------------+
```

# <a name="files">Files</a>

## <a name="files-alerts.php">File: `/alerts.php`</a>

Cronjob that issues all non-alerted alerts. Requires no arguments.

### <a name="files-alerts.php-1">Function: `DescribeAlert($alert)`</a>

Create Alert-Object.

Params:
- `$alert` `array` containing the result from DB

Returns `array` containing data for placeholders used in Templates.

### <a name="files-alerts.php-2">Function: `ExtTransports($obj)`</a>

Invoke configured transports.

Params:
- `$obj` `array` returned from `DescribeAlert($alert)`

Returns `void`.

### <a name="files-alerts.php-3">Function: `FormatAlertTpl($tpl,$obj)`</a>

Format template.

Params:
- `$tpl` `string` Template to populate
- `$obj` `array` returned from `DescribeAlert($alert)`

Returns `string` formated Template.

### <a name="files-alerts.php-4">Function: `RunAlerts()`</a>

Process all non-alerted alerts.

Returns `void`

### <a name="files-alerts.php-5">Function: `TimeFormat($secs)`</a>

Convert seconds into human-friendly elapsed string.

Params:
- `int` amount of seconds

Returns `string` human-friendly formated elapsed time string.

## <a name="files-alerts.inc.php">File: `/includes/alerts.inc.php`</a>

Evaluate rules and track states.

### <a name="files-alerts.inc.php-1">Function: `GenSQL($rule)`</a>

Generate SQL-Query from rule.

Params:
- `$rule` `string` Rule

Returns `string` SQL-Query

### <a name="files-alerts.inc.php-2">Function: `GetContacts($result)`</a>

Gather contacts for alert.

Params:
- `$result` `array` Resulted Alert-Object after execution of the query returned by `GenSQL($rule)`.

Returns `array` Contacts

### <a name="files-alerts.inc.php-3">Function: `RunRules($device)`</a>

Evaluate all rules by device.

Params:
- `$device` `int` Device-ID

Returns `void`
