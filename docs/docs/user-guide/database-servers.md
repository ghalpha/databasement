---
sidebar_position: 2
---

# Database Servers

Database servers are the source of your backups. Databasement can connect to and backup MySQL, PostgreSQL, and MariaDB servers.

## Adding a Server

1. Navigate to **Database Servers**
2. Click **Add Server**
3. Fill in the connection details:

| Field | Description |
|-------|-------------|
| **Name** | A friendly name to identify this server |
| **Type** | The database type (MySQL, PostgreSQL, MariaDB) |
| **Host** | Hostname or IP address of the database server |
| **Port** | Database port (3306 for MySQL/MariaDB, 5432 for PostgreSQL) |
| **Username** | Database user for connecting |
| **Password** | User's password |

4. Click **Test Connection** to verify connectivity
5. Click **Save** to add the server

## Connection Requirements

### MySQL / MariaDB

#### Creating the user

```sql
CREATE USER 'databasement'@'%' IDENTIFIED BY 'your_secure_password';
```

#### Permissions for backup and restore (all databases)

```sql
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES, PROCESS, EVENT, RELOAD,
      CREATE, DROP, ALTER, INDEX, INSERT, UPDATE, DELETE, REFERENCES
ON *.* TO 'databasement'@'%';

FLUSH PRIVILEGES;
```

:::note[Single database only]
To restrict the user to a single database, replace `*.*` with `database_name.*`. Note that with single-database permissions, the user cannot create or drop the database itself - you'll need to ensure the target database exists before restoring.
:::

### PostgreSQL

#### Creating the user

```sql
CREATE USER databasement WITH PASSWORD 'your_secure_password';
```

#### Permissions for backup and restore (all databases)

For full backup and restore capabilities, the user needs to be a superuser or have the `CREATEDB` privilege:

```sql
-- Option 1: Superuser (full access)
ALTER USER databasement WITH SUPERUSER;

-- Option 2: Create database privilege (can create/drop databases for restore)
ALTER USER databasement WITH CREATEDB;
```

If using Option 2, you also need to grant access to existing databases:

```sql
-- Grant ownership or full privileges on the database
GRANT ALL PRIVILEGES ON DATABASE database_name TO databasement;

-- Connect to the database and grant schema access
\c database_name
GRANT ALL PRIVILEGES ON SCHEMA public TO databasement;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO databasement;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO databasement;
```

:::note[Single database only]
For single-database access without `CREATEDB`, the target database must already exist. Grant `ALL PRIVILEGES` on that specific database and its schema. The user won't be able to drop/recreate the database during restore - Databasement will drop and recreate tables instead.
:::

## Testing Connections

Before saving a server, always test the connection:

1. Fill in all connection details
2. Click **Test Connection**
3. The system will attempt to connect and verify credentials
4. If successful, you'll see a confirmation message
5. If it fails, check the error message for troubleshooting

### Common Connection Issues

| Error | Solution |
|-------|----------|
| Connection refused | Verify host, port, and that the database server is running |
| Access denied | Check username and password |
| Unknown host | Verify the hostname is correct and DNS is resolving |
| Connection timeout | Check firewall rules and network connectivity |

## Editing Servers

1. Go to **Database Servers**
2. Find the server you want to edit
3. Click the **Edit** button
4. Update the connection details
5. Test the connection again
6. Save your changes

## Deleting Servers

1. Go to **Database Servers**
2. Find the server you want to delete
3. Click the **Delete** button
4. Confirm the deletion

:::warning
Deleting a server does not delete its backup snapshots. Existing snapshots will remain in storage but cannot be easily associated with the deleted server.
:::

## Server Status

On the Database Servers list, you can see:

- **Server name and type**
- **Host and port**
- **Last backup time** (if any)
- **Number of snapshots**

## Quick Actions

From the server list, you can quickly:

- **Backup**: Start an immediate backup of a database
- **Edit**: Modify server settings
- **Delete**: Remove the server
