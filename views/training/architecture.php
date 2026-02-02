<?php
$pageTitle = $title ?? 'Cloud Architecture';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active">Architecture</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-cloud me-2"></i>Cloud Architecture Review</h1>
        <p class="text-muted mb-0">Hyperscaler deployment strategies for AWS, Azure, and GCP</p>
    </div>
    <div>
        <a href="<?= url('/training/architecture/download') ?>" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-download me-1"></i> Download Full Document
        </a>
    </div>
</div>

<!-- Executive Summary -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Executive Summary</h5>
    </div>
    <div class="card-body">
        <p>This document provides a comprehensive cloud architecture review for the SDBIP/IDP Management System, designed for South African municipalities. It outlines deployment strategies across major hyperscalers while ensuring compliance with MFMA regulations and POPIA data protection requirements.</p>

        <div class="row mt-4">
            <div class="col-md-3 text-center">
                <div class="bg-light rounded p-3">
                    <h2 class="text-primary mb-0">99.9%</h2>
                    <small class="text-muted">Uptime SLA</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="bg-light rounded p-3">
                    <h2 class="text-success mb-0">&lt; 1hr</h2>
                    <small class="text-muted">Recovery Time</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="bg-light rounded p-3">
                    <h2 class="text-info mb-0">3</h2>
                    <small class="text-muted">Cloud Providers</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="bg-light rounded p-3">
                    <h2 class="text-warning mb-0">100%</h2>
                    <small class="text-muted">SA Data Residency</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cloud Provider Cards -->
<div class="row mb-4">
    <!-- AWS -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark">
                    <i class="bi bi-cloud me-2"></i>Amazon Web Services
                </h5>
            </div>
            <div class="card-body">
                <h6>Key Services</h6>
                <ul class="small">
                    <li><strong>Compute:</strong> ECS Fargate / EKS</li>
                    <li><strong>Database:</strong> RDS MySQL Multi-AZ</li>
                    <li><strong>Cache:</strong> ElastiCache Redis</li>
                    <li><strong>Storage:</strong> S3 with versioning</li>
                    <li><strong>CDN:</strong> CloudFront</li>
                    <li><strong>Security:</strong> WAF, Shield, KMS</li>
                </ul>
                <div class="alert alert-warning py-2 mb-0">
                    <strong>Est. Cost:</strong> ~$900/month
                </div>
            </div>
        </div>
    </div>

    <!-- Azure -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-microsoft me-2"></i>Microsoft Azure
                </h5>
            </div>
            <div class="card-body">
                <h6>Key Services</h6>
                <ul class="small">
                    <li><strong>Compute:</strong> AKS / App Service</li>
                    <li><strong>Database:</strong> Azure MySQL Flexible</li>
                    <li><strong>Cache:</strong> Azure Cache for Redis</li>
                    <li><strong>Storage:</strong> Blob Storage</li>
                    <li><strong>CDN:</strong> Azure Front Door</li>
                    <li><strong>Identity:</strong> Entra ID (Azure AD)</li>
                </ul>
                <div class="alert alert-primary py-2 mb-0">
                    <strong>Est. Cost:</strong> ~$1,100/month
                </div>
            </div>
        </div>
    </div>

    <!-- GCP -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-google me-2"></i>Google Cloud Platform
                </h5>
            </div>
            <div class="card-body">
                <h6>Key Services</h6>
                <ul class="small">
                    <li><strong>Compute:</strong> GKE Autopilot / Cloud Run</li>
                    <li><strong>Database:</strong> Cloud SQL MySQL</li>
                    <li><strong>Cache:</strong> Memorystore Redis</li>
                    <li><strong>Storage:</strong> Cloud Storage</li>
                    <li><strong>CDN:</strong> Cloud CDN</li>
                    <li><strong>AI/ML:</strong> Vertex AI</li>
                </ul>
                <div class="alert alert-danger py-2 mb-0">
                    <strong>Est. Cost:</strong> ~$922/month
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Architecture Diagram -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Target Architecture</h5>
    </div>
    <div class="card-body">
        <pre class="bg-dark text-light p-4 rounded" style="font-size: 0.75rem; overflow-x: auto;">
┌─────────────────────────────────────────────────────────────────────┐
│                      Cloud Native Architecture                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                    CDN + WAF + DDoS Protection                │   │
│  └────────────────────────────┬────────────────────────────────┘   │
│                               │                                      │
│  ┌────────────────────────────▼────────────────────────────────┐   │
│  │                    Load Balancer (L7)                         │   │
│  └────────────────────────────┬────────────────────────────────┘   │
│                               │                                      │
│  ┌────────────────────────────▼────────────────────────────────┐   │
│  │                 Container Orchestration (K8s)                 │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │   │
│  │  │  PHP Pod 1  │  │  PHP Pod 2  │  │  PHP Pod N  │          │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘          │   │
│  │              Auto-Scaling (2-20 pods)                         │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                               │                                      │
│       ┌───────────────────────┼───────────────────────┐             │
│       ▼                       ▼                       ▼             │
│  ┌─────────────┐      ┌─────────────┐      ┌─────────────┐        │
│  │  Managed    │      │   Redis     │      │   Object    │        │
│  │   MySQL     │      │   Cache     │      │   Storage   │        │
│  │  (HA/DR)    │      │  (Cluster)  │      │  (POE/Docs) │        │
│  └─────────────┘      └─────────────┘      └─────────────┘        │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
        </pre>
    </div>
</div>

<!-- Security & Compliance -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Security Layers</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>Edge Security</strong>
                        <span class="badge bg-success">Implemented</span>
                    </div>
                    <small class="text-muted">DDoS Protection, WAF, Bot Detection, Rate Limiting</small>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>Network Security</strong>
                        <span class="badge bg-success">Implemented</span>
                    </div>
                    <small class="text-muted">VPC, Security Groups, Private Subnets, VPN</small>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>Application Security</strong>
                        <span class="badge bg-success">Implemented</span>
                    </div>
                    <small class="text-muted">TLS 1.3, CSRF, XSS Prevention, CORS</small>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>Data Security</strong>
                        <span class="badge bg-success">Implemented</span>
                    </div>
                    <small class="text-muted">Encryption at Rest (AES-256), In Transit, Key Management</small>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <strong>Identity & Access</strong>
                        <span class="badge bg-success">Implemented</span>
                    </div>
                    <small class="text-muted">RBAC, MFA, SSO, LDAP Integration</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-award me-2"></i>Compliance</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Regulation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>MFMA</strong><br>
                                <small class="text-muted">Municipal Finance Management Act</small>
                            </td>
                            <td><span class="badge bg-success">Compliant</span></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>POPIA</strong><br>
                                <small class="text-muted">Protection of Personal Information Act</small>
                            </td>
                            <td><span class="badge bg-success">Compliant</span></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>ISO 27001</strong><br>
                                <small class="text-muted">Information Security</small>
                            </td>
                            <td><span class="badge bg-warning text-dark">Ready</span></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>SOC 2 Type II</strong><br>
                                <small class="text-muted">Service Organization Controls</small>
                            </td>
                            <td><span class="badge bg-warning text-dark">Ready</span></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>King IV</strong><br>
                                <small class="text-muted">Corporate Governance</small>
                            </td>
                            <td><span class="badge bg-success">Compliant</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DR & Performance -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Disaster Recovery</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>RTO (Recovery Time)</th>
                        <td><span class="badge bg-success fs-6">&lt; 1 hour</span></td>
                    </tr>
                    <tr>
                        <th>RPO (Data Loss)</th>
                        <td><span class="badge bg-success fs-6">&lt; 15 min</span></td>
                    </tr>
                    <tr>
                        <th>Strategy</th>
                        <td>Active-Passive Warm Standby</td>
                    </tr>
                    <tr>
                        <th>Primary Region</th>
                        <td>South Africa North (Johannesburg)</td>
                    </tr>
                    <tr>
                        <th>DR Region</th>
                        <td>South Africa West (Cape Town)</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer me-2"></i>Performance Targets</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Page Load Time</th>
                        <td><span class="badge bg-success fs-6">&lt; 2 seconds</span></td>
                    </tr>
                    <tr>
                        <th>API Response</th>
                        <td><span class="badge bg-success fs-6">&lt; 200ms</span></td>
                    </tr>
                    <tr>
                        <th>Database Query</th>
                        <td><span class="badge bg-success fs-6">&lt; 50ms</span></td>
                    </tr>
                    <tr>
                        <th>Availability</th>
                        <td><span class="badge bg-success fs-6">99.9%</span></td>
                    </tr>
                    <tr>
                        <th>Error Rate</th>
                        <td><span class="badge bg-success fs-6">&lt; 0.1%</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Implementation Timeline -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Implementation Roadmap</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-info mb-3">
                    <div class="card-header bg-info text-white">
                        <strong>Phase 1: Foundation</strong>
                        <span class="badge bg-white text-info float-end">Months 1-2</span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item">Containerize application (Docker)</li>
                        <li class="list-group-item">Set up CI/CD pipeline</li>
                        <li class="list-group-item">Configure cloud infrastructure (IaC)</li>
                        <li class="list-group-item">Migrate database to managed service</li>
                        <li class="list-group-item">Implement basic monitoring</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning mb-3">
                    <div class="card-header bg-warning text-dark">
                        <strong>Phase 2: Migration</strong>
                        <span class="badge bg-dark float-end">Months 3-4</span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item">Deploy to production cloud</li>
                        <li class="list-group-item">Migrate file storage to object storage</li>
                        <li class="list-group-item">Configure CDN and load balancing</li>
                        <li class="list-group-item">Implement Redis caching</li>
                        <li class="list-group-item">Set up DR environment</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success mb-3">
                    <div class="card-header bg-success text-white">
                        <strong>Phase 3: Optimization</strong>
                        <span class="badge bg-white text-success float-end">Months 5-6</span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item">Performance tuning</li>
                        <li class="list-group-item">Implement auto-scaling</li>
                        <li class="list-group-item">Security hardening & pen testing</li>
                        <li class="list-group-item">Documentation & training</li>
                        <li class="list-group-item">Go-live and production cutover</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Download Section -->
<div class="card bg-light">
    <div class="card-body text-center py-4">
        <i class="bi bi-file-earmark-text text-primary" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Full Architecture Document</h5>
        <p class="text-muted">Download the complete Cloud Architecture Review document with detailed specifications.</p>
        <a href="<?= url('/training/architecture/download') ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-download me-1"></i> Download Full Document (Markdown)
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
