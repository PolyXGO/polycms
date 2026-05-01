# Backup

Comprehensive backup and restore system for PolyCMS with cloud storage integration, maintenance mode, and rollback safety.

## Features

- Full site backup (database + files)
- Selective backup (database only, files only)
- Restore from backup with rollback safety
- Cloud storage support (upload backups to S3/external)
- Maintenance mode during backup/restore
- Scheduled backup support
- Backup history and management

## Admin Menu

Located under **Settings > Backups** in the admin panel.

### Menu Items

- **Backups** - List and manage existing backups
- **Settings** - Configure backup schedule, storage, retention

## Configuration

| Setting | Description |
|---------|-------------|
| Backup storage | Local or cloud storage destination |
| Retention | Number of backups to keep |
| Schedule | Automatic backup frequency |
| Maintenance mode | Auto-enable during restore |

## Version

1.0.0
