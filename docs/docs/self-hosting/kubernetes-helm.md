---
sidebar_position: 3
---

# Kubernetes + Helm

This guide will help you deploy Databasement on Kubernetes using Helm.

## Prerequisites

- A Kubernetes cluster
- [Helm](https://helm.sh/docs/intro/install/) v3.x installed
- [kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl/) configured for your cluster

## Installation

### 1. Add the Helm Repository

```bash
helm repo add databasement https://david-crty.github.io/databasement
helm repo update
```

### 2. Generate an Application Key

Before deploying, generate an application encryption key:

```bash
docker run --rm davidcrty/databasement:latest php artisan key:generate --show
```

Copy the output (e.g., `base64:abc123...`) for use in your values file.

### 3. Create a Values File

#### Minimal Configuration (SQLite)

For simple deployments using SQLite:

```yaml title="values.yaml"
app:
  url: https://backup.yourdomain.com
  key: "base64:your-generated-key-here"

ingress:
  enabled: true
  className: nginx
  host: backup.yourdomain.com
  # For HTTPS using cert-manager:
  # tlsSecretName: databasement-tls
  # annotations:  # Optional: for HTTPS
  #   cert-manager.io/cluster-issuer: letsencrypt-prod
```

#### Production Configuration (External Database)

For production, we recommend using MySQL or PostgreSQL instead of SQLite:

```yaml title="values.yaml"
# ... other app config

database:
  connection: mysql  # or pgsql
  host: your-mysql-host.example.com
  port: 3306
  name: databasement
  username: databasement
  password: your-secure-password
```

### 4. Install the Chart

```bash
helm upgrade --install databasement databasement/databasement -f values.yaml
```

### 5. Verify the Deployment

```bash
kubectl get pods -n databasement
kubectl get svc -n databasement
kubectl get ingress -n databasement
```

## Uninstalling

```bash
helm uninstall databasement -n databasement
```

:::caution
This will not delete the PersistentVolumeClaim by default. To delete all data:
```bash
kubectl delete pvc -l app.kubernetes.io/name=databasement -n databasement
```
:::
