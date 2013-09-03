### SportlobsterTask

## Index

1. [Installation](#1-installation)
2. [Usage](#2-usage)

## 1. Installation

To install the library via [Composer](http://getcomposer.org) update your `composer.json`:

```json
{
    "require": {
        # ...
        "sportlobster-task": "*"
    }
}
```

## 2. Usage

# Realtime manager

# Database manager

```sql
CREATE TABLE `sl_task` (
  `type` varchar(255) NOT NULL,
  `body` longtext NOT NULL COMMENT 'JSON encoded data',
  `state` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0: open; 1: in progress; 2: done; -1: error; -2: cancelled',
  `restart_count` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  KEY `sl_task_type_idx` (`type`),
  KEY `sl_task_type_state_idx` (`type`, `state`)
)
```