---
sidebar_position: 2
---

# Database Servers

Database servers are the source of your backups. Databasement can connect to and backup MySQL, PostgreSQL, and MariaDB servers.

## Connection Requirements

### MySQL / MariaDB

The database user needs the following privileges for backup operations:

```sql
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES, PROCESS, EVENT, RELOAD
ON database_name.*
TO 'backup_user'@'%';
```

For backing up all databases:

```sql
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES, PROCESS, EVENT, RELOAD
ON *.*
TO 'backup_user'@'%';
```

### PostgreSQL

The user should have read access to the databases you want to backup:

```sql
GRANT CONNECT ON DATABASE database_name TO backup_user;
GRANT USAGE ON SCHEMA public TO backup_user;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO backup_user;
```

For full backup capabilities, consider using a superuser or the database owner.

## Testing Connections

Before saving a server, always test the connection by clicking **Test Connection**.

### Common Connection Issues

| Error              | Solution                                                   |
|--------------------|------------------------------------------------------------|
| Connection refused | Verify host, port, and that the database server is running |
| Access denied      | Check username and password                                |
| Unknown host       | Verify the hostname is correct and DNS is resolving        |
| Connection timeout | Check firewall rules and network connectivity              |
