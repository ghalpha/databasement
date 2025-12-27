# Databasement Helm Chart

Deploy [Databasement](https://github.com/david-crty/databasement) on Kubernetes using Helm.

## Links

- [Documentation](https://david-crty.github.io/databasement)
- [GitHub Repository](https://github.com/david-crty/databasement)
- [Docker Hub](https://hub.docker.com/r/davidcrty/databasement)
- [Artifact Hub](https://artifacthub.io/packages/helm/databasement/databasement)

## Prerequisites

- Kubernetes 1.19+
- [Helm](https://helm.sh/docs/intro/install/) 3.x
- [kubectl](https://kubernetes.io/docs/tasks/tools/) configured for your cluster

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

```yaml
app:
  url: https://backup.yourdomain.com
  appKey:
    value: "base64:your-generated-key-here"

ingress:
  enabled: true
  className: nginx
  host: backup.yourdomain.com
  # For HTTPS using cert-manager:
  # tlsSecretName: databasement-tls
  # annotations:
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
  password:
    value: "your-secure-password"

ingress:
  enabled: true
  className: nginx
  host: backup.yourdomain.com
```

#### Using Existing Secrets

For sensitive values, you can reference existing Kubernetes secrets:

```yaml
app:
  appKey:
    fromSecret:
      secretName: "my-app-secret"
      secretKey: "APP_KEY"

database:
  connection: mysql
  host: mysql.example.com
  name: databasement
  username: databasement
  password:
    fromSecret:
      secretName: "my-db-secret"
      secretKey: "password"
```

### 4. Install the Chart

```bash
helm upgrade --install databasement databasement/databasement -f values.yaml
```

### 5. Verify the Deployment

```bash
kubectl get pods
kubectl get svc
kubectl get ingress
```

## Configuration

See [values.yaml](values.yaml) for the full list of configurable parameters.

For all available environment variables, see the [Configuration Documentation](https://david-crty.github.io/databasement/self-hosting/configuration).

### Custom Environment Variables

Use `extraEnv` to pass additional environment variables:

```yaml
extraEnv:
  AWS_ACCESS_KEY_ID: "your-access-key"
  AWS_SECRET_ACCESS_KEY: "your-secret-key"
  AWS_DEFAULT_REGION: "us-east-1"
```

### Environment Variables from Secrets/ConfigMaps

Use `extraEnvFrom` to load environment variables from existing secrets or configmaps:

```yaml
extraEnvFrom:
  - secretRef:
      name: aws-credentials
  - configMapRef:
      name: app-config
```

## Uninstalling

```bash
helm uninstall databasement
```

> **Caution:** This will not delete the PersistentVolumeClaim by default. To delete all data:
> ```bash
> kubectl delete pvc -l app.kubernetes.io/name=databasement
> ```

## License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/david-crty/databasement/blob/main/LICENSE) file for details.
