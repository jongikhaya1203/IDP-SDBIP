# SDBIP/IDP Infrastructure Documentation

## Overview

This document describes the cloud infrastructure setup for the SDBIP/IDP Management System, supporting deployment on AWS, Azure, and Kubernetes.

## Architecture

```
                                   ┌─────────────────┐
                                   │   CloudFront/   │
                                   │   Front Door    │
                                   └────────┬────────┘
                                            │
                                   ┌────────▼────────┐
                                   │  Load Balancer  │
                                   │   (ALB/Azure)   │
                                   └────────┬────────┘
                                            │
                    ┌───────────────────────┼───────────────────────┐
                    │                       │                       │
           ┌────────▼────────┐    ┌────────▼────────┐    ┌────────▼────────┐
           │   App Pod/Task  │    │   App Pod/Task  │    │   App Pod/Task  │
           │   (PHP-FPM +    │    │   (PHP-FPM +    │    │   (PHP-FPM +    │
           │    Nginx)       │    │    Nginx)       │    │    Nginx)       │
           └────────┬────────┘    └────────┬────────┘    └────────┬────────┘
                    │                       │                       │
                    └───────────────────────┼───────────────────────┘
                                            │
                    ┌───────────────────────┼───────────────────────┐
                    │                       │                       │
           ┌────────▼────────┐    ┌────────▼────────┐    ┌────────▼────────┐
           │  MySQL/Aurora   │    │  Redis/         │    │   S3/Azure      │
           │  (RDS/Azure)    │    │  ElastiCache    │    │   Blob Storage  │
           └─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Quick Start

### Local Development with Docker

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Access application
open http://localhost:3000
```

### Running Monitoring Stack

```bash
# Start monitoring services
docker-compose -f docker-compose.monitoring.yml up -d

# Access Grafana
open http://localhost:3001  # admin/admin

# Access Prometheus
open http://localhost:9090
```

## Directory Structure

```
idp/
├── docker/
│   ├── nginx/           # Nginx configuration
│   ├── php/             # PHP configuration
│   ├── supervisor/      # Process manager
│   ├── prometheus/      # Prometheus config & rules
│   ├── grafana/         # Grafana dashboards & datasources
│   ├── alertmanager/    # Alert routing configuration
│   ├── loki/            # Log aggregation config
│   ├── promtail/        # Log collection config
│   └── blackbox/        # Endpoint probing config
├── terraform/
│   ├── aws/             # AWS infrastructure (ECS, RDS, ElastiCache)
│   └── azure/           # Azure infrastructure (Container Apps, MySQL)
├── k8s/
│   ├── deployment.yaml  # Kubernetes deployment
│   ├── service.yaml     # Service definitions
│   ├── ingress.yaml     # Ingress configuration
│   ├── configmap.yaml   # Configuration
│   └── secrets.yaml     # Secrets management
├── scripts/
│   ├── deploy.sh        # Deployment automation
│   ├── backup.sh        # Database backup
│   └── restore.sh       # Database restore
├── .github/workflows/
│   ├── ci-cd.yml        # CI/CD pipeline
│   └── security-scan.yml # Security scanning
├── Dockerfile           # Multi-stage build
├── docker-compose.yml   # Local development
└── docker-compose.monitoring.yml  # Monitoring stack
```

## AWS Deployment

### Prerequisites

- AWS CLI configured
- Terraform installed
- Docker installed
- ECR repository created

### Deploy to AWS

```bash
# Initialize Terraform
cd terraform/aws
terraform init

# Create tfvars file
cat > terraform.tfvars <<EOF
db_username = "sdbip_admin"
db_password = "SecurePassword123!"
domain_name = "sdbip.municipality.gov.za"
acm_certificate_arn = "arn:aws:acm:af-south-1:..."
ecr_repository_url = "123456789.dkr.ecr.af-south-1.amazonaws.com/sdbip"
alert_email = "it-support@municipality.gov.za"
EOF

# Plan and apply
terraform plan
terraform apply

# Deploy application
cd ../../
./scripts/deploy.sh -e production -t v1.0.0
```

### AWS Resources Created

| Resource | Purpose |
|----------|---------|
| VPC | Isolated network |
| ECS Cluster | Container orchestration |
| Aurora MySQL | Database |
| ElastiCache Redis | Caching & sessions |
| S3 | POE file storage |
| CloudFront | CDN |
| ALB | Load balancing |
| Secrets Manager | Credential storage |
| CloudWatch | Logging & monitoring |

## Azure Deployment

### Prerequisites

- Azure CLI configured
- Terraform installed
- Docker installed

### Deploy to Azure

```bash
# Initialize Terraform
cd terraform/azure
terraform init

# Create tfvars file
cat > terraform.tfvars <<EOF
db_username = "sdbip_admin"
db_password = "SecurePassword123!"
alert_email = "it-support@municipality.gov.za"
EOF

# Plan and apply
terraform plan
terraform apply
```

### Azure Resources Created

| Resource | Purpose |
|----------|---------|
| Resource Group | Resource container |
| VNet | Virtual network |
| Container Apps | Container hosting |
| MySQL Flexible Server | Database |
| Redis Cache | Caching & sessions |
| Blob Storage | POE file storage |
| Front Door | CDN & WAF |
| Key Vault | Secrets management |
| Application Insights | APM |

## Kubernetes Deployment

### Prerequisites

- kubectl configured
- Helm installed (optional)
- Ingress controller installed

### Deploy to Kubernetes

```bash
# Create namespace
kubectl create namespace sdbip

# Apply secrets (update with real values)
kubectl apply -f k8s/secrets.yaml

# Apply configuration
kubectl apply -f k8s/configmap.yaml

# Deploy application
kubectl apply -f k8s/deployment.yaml
kubectl apply -f k8s/service.yaml
kubectl apply -f k8s/ingress.yaml

# Check status
kubectl get pods -n sdbip
kubectl get svc -n sdbip
```

## Monitoring & Alerting

### Prometheus Metrics

The application exposes metrics at `/metrics`:

- `http_requests_total` - Total HTTP requests
- `http_request_duration_seconds` - Request latency
- `phpfpm_active_processes` - Active PHP processes
- Custom SDBIP metrics

### Alert Channels

| Severity | Channel | Response Time |
|----------|---------|---------------|
| Critical | Email + SMS | Immediate |
| Warning | Email | Within 1 hour |
| Info | Slack/Teams | Next business day |

### Key Dashboards

1. **SDBIP Overview** - Application health, latency, error rates
2. **Infrastructure** - CPU, memory, disk usage
3. **Database** - Connections, queries, replication lag
4. **Redis** - Memory, commands, hit rate

## Backup & Recovery

### Automated Backups

```bash
# Run backup (cron job)
./scripts/backup.sh

# List available backups
./scripts/restore.sh -l

# Restore specific backup
./scripts/restore.sh -t 20240115_120000
```

### Backup Schedule

| Type | Frequency | Retention |
|------|-----------|-----------|
| Database | Daily | 30 days |
| POE Files | Weekly | 90 days |
| Full System | Monthly | 1 year |

### RTO/RPO

- **RPO** (Recovery Point Objective): 1 hour
- **RTO** (Recovery Time Objective): 4 hours

## Security

### Security Measures

1. **Network Security**
   - VPC isolation
   - Security groups/NSGs
   - Private subnets for databases
   - WAF protection

2. **Data Security**
   - Encryption at rest (AES-256)
   - Encryption in transit (TLS 1.3)
   - Secrets management
   - Regular key rotation

3. **Access Control**
   - RBAC implementation
   - LDAP/AD integration
   - MFA for admin access
   - Audit logging

### Compliance

- POPIA (Protection of Personal Information Act)
- MFMA (Municipal Finance Management Act)
- ISO 27001 aligned

## Scaling

### Horizontal Scaling

```yaml
# Kubernetes HPA
spec:
  minReplicas: 3
  maxReplicas: 20
  metrics:
    - type: Resource
      resource:
        name: cpu
        targetAverageUtilization: 70
```

### Auto-scaling Triggers

| Metric | Scale Up | Scale Down |
|--------|----------|------------|
| CPU | > 70% | < 30% |
| Memory | > 80% | < 40% |
| Request Queue | > 100 | < 20 |

## Maintenance

### Regular Tasks

- [ ] Weekly security patches
- [ ] Monthly certificate rotation
- [ ] Quarterly disaster recovery test
- [ ] Annual penetration testing

### Upgrade Process

1. Deploy to staging environment
2. Run smoke tests
3. Blue-green deployment to production
4. Monitor for 30 minutes
5. Rollback if issues detected

## Troubleshooting

### Common Issues

**Application not responding:**
```bash
kubectl logs -f deployment/sdbip-app -n sdbip
docker-compose logs -f app
```

**Database connection issues:**
```bash
# Test connection
mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD -e "SELECT 1"
```

**High memory usage:**
```bash
# Check PHP-FPM processes
docker exec sdbip-app ps aux | grep php-fpm
```

### Support Contacts

- **IT Support**: it-support@municipality.gov.za
- **Critical Issues**: it-critical@municipality.gov.za
- **On-call**: +27 XX XXX XXXX

## Cost Optimization

### AWS Cost Estimates

| Service | Monthly Cost (USD) |
|---------|-------------------|
| ECS Fargate | ~$150 |
| Aurora MySQL | ~$200 |
| ElastiCache | ~$50 |
| S3 + CloudFront | ~$30 |
| **Total** | **~$430** |

### Cost Saving Tips

1. Use Reserved Instances for production
2. Enable S3 lifecycle policies
3. Right-size database instances
4. Use Spot instances for non-critical workloads
