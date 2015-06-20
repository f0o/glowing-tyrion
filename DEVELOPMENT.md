Table of Content:
- [Database](#db)
  - [`alerts`](#db-alerts)
  - [`alert_log`](#db-alert_log)
  - [`alert_rules`](#db-alert_rules)
  - [`alert_map`](#db-alert_map)
  - [`alert_schedule`](#db-alert_schedule)
  - [`alert_schedule_items`](#db-alert_schedule_items)
  - [`alert_templates`](#db-alert_templates)
  - [`alert_templates_map`](#db-alert_templates_map)
- [Files](#files)
  - [`alerts.php`](#files-alerts.php)
  - [`alerts.inc.php`](#files-alerts.inc.php)

# <a name="db">Database</a>

## <a name="db-alerts">Table: `alerts`</a>

Holds an overview of all current states per rule per device.

Known values for field `state`:
- `0` OK
- `1` Alert
- `2` Acknowledged
- `3` Got Worse
- `4` Got Better

```text
+-----------+-----------+------+-----+-------------------+-----------------------------+
| Field     | Type      | Null | Key | Default           | Extra                       |
+-----------+-----------+------+-----+-------------------+-----------------------------+
| id        | int(11)   | NO   | PRI | NULL              | auto_increment              |
| device_id | int(11)   | NO   | MUL | NULL              |                             |
| rule_id   | int(11)   | NO   | MUL | NULL              |                             |
| state     | int(11)   | NO   |     | NULL              |                             |
| alerted   | int(11)   | NO   |     | NULL              |                             |
| open      | int(11)   | NO   |     | NULL              |                             |
| timestamp | timestamp | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
+-----------+-----------+------+-----+-------------------+-----------------------------+
```

## <a name="db-alert_log">Table: `alert_log`</a>

Field `details` only holds data when `state = 1`. The data is a gzip-compressed JSON object with informations about the entities that caused the alert.

```text
+-------------+-----------+------+-----+-------------------+----------------+
| Field       | Type      | Null | Key | Default           | Extra          |
+-------------+-----------+------+-----+-------------------+----------------+
| id          | int(11)   | NO   | PRI | NULL              | auto_increment |
| rule_id     | int(11)   | NO   | MUL | NULL              |                |
| device_id   | int(11)   | NO   | MUL | NULL              |                |
| state       | int(11)   | NO   |     | NULL              |                |
| details     | longblob  | NO   |     | NULL              |                |
| time_logged | timestamp | NO   | MUL | CURRENT_TIMESTAMP |                |
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
| device_id | varchar(255)                    | NO   | MUL |         |                |
| rule      | text                            | NO   |     | NULL    |                |
| severity  | enum('ok','warning','critical') | NO   |     | NULL    |                |
| extra     | varchar(255)                    | NO   |     | NULL    |                |
| disabled  | tinyint(1)                      | NO   |     | NULL    |                |
| name      | varchar(255)                    | NO   | UNI | NULL    |                |
+-----------+---------------------------------+------+-----+---------+----------------+
```

## <a name="db-alert_map">Table: `alert_map`</a>

Each entry is a link between a device or device-group and a rule.

```text
+--------+--------------+------+-----+---------+----------------+
| Field  | Type         | Null | Key | Default | Extra          |
+--------+--------------+------+-----+---------+----------------+
| id     | int(11)      | NO   | PRI | NULL    | auto_increment |
| rule   | int(11)      | NO   |     | 0       |                |
| target | varchar(255) | NO   |     |         |                |
+--------+--------------+------+-----+---------+----------------+
```

## <a name="db-alert_schedule">Table: `alert_schedule`</a>

Holds timeschedule for scheduled downtimes in order to halt alerting for a predefined period of time.

```text
+-------------+--------------+------+-----+---------------------+----------------+
| Field       | Type         | Null | Key | Default             | Extra          |
+-------------+--------------+------+-----+---------------------+----------------+
| schedule_id | int(11)      | NO   | PRI | NULL                | auto_increment |
| start       | timestamp    | NO   |     | 0000-00-00 00:00:00 |                |
| end         | timestamp    | NO   |     | 0000-00-00 00:00:00 |                |
| title       | varchar(255) | NO   |     | NULL                |                |
| notes       | text         | NO   |     | NULL                |                |
+-------------+--------------+------+-----+---------------------+----------------+
```

## <a name="db-alert_schedule_items">Table: `alert_schedule_items`</a>

Holds what devices to ignore for given schedule.

```text
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| item_id     | int(11)      | NO   | PRI | NULL    | auto_increment |
| schedule_id | int(11)      | NO   | MUL | NULL    |                |
| target      | varchar(255) | NO   |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
```

## <a name="db-alert_templates">Table: `alert_templates`</a>

Holds templates for single rules or a set of rules.  

```text
+----------+--------------+------+-----+---------+----------------+
| Field    | Type         | Null | Key | Default | Extra          |
+----------+--------------+------+-----+---------+----------------+
| id       | int(11)      | NO   | PRI | NULL    | auto_increment |
| rule_id  | varchar(255) | NO   |     | ,       |                |
| name     | varchar(255) | NO   |     | NULL    |                |
| template | longtext     | NO   |     | NULL    |                |
+----------+--------------+------+-----+---------+----------------+
```

## <a name="db-alert_template_map">Table: `alert_template_map`</a>

Holds what rules should use which template.

```text
+--------------------+---------+------+-----+---------+----------------+
| Field              | Type    | Null | Key | Default | Extra          |
+--------------------+---------+------+-----+---------+----------------+
| id                 | int(11) | NO   | PRI | NULL    | auto_increment |
| alert_templates_id | int(11) | NO   | MUL | NULL    |                |
| alert_rule_id      | int(11) | NO   |     | NULL    |                |
+--------------------+---------+------+-----+---------+----------------+
```

# <a name="files">Files</a>

## <a name="files-alerts.php">File: `/alerts.php`</a>

Cronjob that issues all non-alerted alerts. Requires no arguments.
Use phpdocumentator or doxygen or any other docblock parser to get function-docs

## <a name="files-alerts.inc.php">File: `/includes/alerts.inc.php`</a>

Evaluate rules and track states.
Use phpdocumentator or doxygen or any other docblock parser to get function-docs

