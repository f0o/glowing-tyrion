glowing-tyrion
==============

LibreNMS Alerting Code

Table of Content:
- [About](#about)
- [Rules](#rules)
 - [Syntax](#rules-syntax)
 - [Examples](#rules-examples)
- [Templates](#templates)
 - [Syntax](#templates-syntax)
 - [Examples](#templates-examples)
- [Transports](#transports)

# <a name="about">About</a>

LibreNMS includes a highly customizable alerting system.  
The system requires a set of user-defined rules to evaluate the situation of each device, port, service or any other entity.

# <a name="rules">Rules</a>

Rules are defined using a logical language.  
The GUI provides a simple way of creating basic as well as complex Rules in a self-describing manner.  
More complex rules can be written manually.

## <a name="rules-syntax">Syntax</a>

Rules must consist of at least 3 elements: An __Entity__, a __Condition__ and a __Value__.  
Rules can contain braces and __Glues__.  
__Entities__ are provided as `%`-Noted pair of Table and Field. For Example: `%ports.ifOperStatus`.  
__Conditions__ can be any of:
- Equals `=`
- Not Equals `!=`
- Greater `>`
- Greater or Equal `>=`
- Smaller `<`
- Smaller or Equal `<=`

__Values__ can be Entities or any single-quoted data.  
__Glues__ can be either `&&` for `AND` or `||` for `OR`.

## <a name="rules-examples">Examples</a>

Alert when:
- Device goes down: `%devices.status != '1'`
- Any port changes: `%ports.ifOperStatus != 'up'`
- Root-directory gets too full: `%storage.storage_descr = '/' && %storage.storage_perc >= '75'`
- Any storage gets fuller than the 'warning': `%storage.storage_perc >= %storage_perc_warn`

# <a name="templates">Templates</a>

Templates can be assigned to a single or a group of rules.  
They can contain any kind of text.  
The template-parser understands `if` and `foreach` controls and replaces certain placeholders with information gathered about the alert.  

## <a name="templates-syntax">Syntax</a>

Controls:
- if-else (Else can be ommited):  
`{if %placeholder == 'value'}Some Text{else}Other Text{/if}`
- foreach-loop:  
`{foreach %placeholder}Key: %key<br/>Value: %value{/foreach}`

Placeholders:
- Hostname of the Device: `%hostname`
- Title for the Alert: `%title`
- Time Elapsed, Only available on recovery (`%state == 0`): `%elapsed`
- Alert-ID: `%id`
- Unique-ID: `%uid`
- Faults, Only available on alert (`%state == 1`), must be iterated in a foreach: `%faults`
- State: `%state`
- Severity: `%severity`
- Rule: `%rule`
- Timestamp: `%timestamp`
- Contacts, must be iterated in a foreach, `%key` holds email and `%value` holds name: `%contacts`

## <a name="templates-examples">Examples</a>

Default Template:  
```text
%title\r\n
Severity: %severity\r\n
{if %state == 0}Time elapsed: %elapsed\r\n{/if}
Timestamp: %timestamp\r\n
Unique-ID: %uid\r\n
Rule: %rule\r\n
{if %faults}Faults:\r\n
{foreach %faults}  #%key: %value\r\n{/foreach}{/if}
Alert sent to: {foreach %contacts}%value <%key> {/foreach}
```

# <a name="transports">Transports</a>

Transports are located within `$config['install_dir']/includes/alerts/transports.*.php` and defined as well as configured via `$config['alert']['transports']['Example'] = 'Some Options'`.
