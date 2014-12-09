Table of Content:
- [Database](#db)
  - [`alerts`](#db-alerts)
  - [`alert_rules`](#db-alert_rules)
  - [`alert_schedule`](#db-alert_schedule)
  - [`alert_templates`](#db-alert_templates)
- [Files](#files)

# <a name="db">Database</a>

## <a name="db-alerts">Table: `alerts`</a>

This table holds all issued and not-yet issued alerts.  
Known values for field `state`:
- `0` OK
- `1` Alert
- `2` Acknowledged

Field `details` only holds data when `state = 1`. The data is a gzip-compressed JSON object with informations about the entities that caused the alert.

```text
+-------------+-------------+------+-----+-------------------+----------------+
| Field       | Type        | Null | Key | Default           | Extra          |
+-------------+-------------+------+-----+-------------------+----------------+
| id          | int(11)     | NO   | PRI | NULL              | auto_increment |
| rule_id     | int(11)     | NO   |     | NULL              |                |
| device_id   | int(11)     | NO   |     | NULL              |                |
| state       | int(11)     | NO   |     | NULL              |                |
| details     | longblob    | NO   |     | NULL              |                |
| time_logged | timestamp   | NO   |     | CURRENT_TIMESTAMP |                |
| alerted     | smallint(6) | NO   |     | 0                 |                |
+-------------+-------------+------+-----+-------------------+----------------+
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
